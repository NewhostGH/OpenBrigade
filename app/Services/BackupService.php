<?php

// project: OpenBrigade

namespace App\Services;

use App\Models\BackupSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Backup engine: dumps the database (and, when enabled, the uploads tree) into
 * a single archive on the primary disk, mirrors it to an optional off-site
 * disk, prunes to the retention count, and can restore an archive or run a
 * non-destructive restore drill that proves a backup is recoverable.
 *
 * Archive formats:
 *   - .zip  when uploads are bundled — contains `database.sql` + `uploads/…`
 *   - .sql  DB-only (legacy behaviour, when include_uploads is off)
 */
class BackupService implements ServiceInterface
{
    private function disk(): string
    {
        return config('backup.disk', 'local');
    }

    private function prefix(): string
    {
        return trim((string) config('backup.path', 'backups'), '/');
    }

    /**
     * Create a backup archive, mirror it off-site and prune old ones.
     *
     * @return array{0: string, 1: string|null} [filename, error|null]
     */
    public function createBackup(): array
    {
        $filesRoot = (string) config('backup.files_path');
        $includeFiles = (bool) config('backup.include_files', true) && is_dir($filesRoot);

        $filename = $this->buildFilename($includeFiles ? 'zip' : 'sql');
        $relPath = $this->prefix().'/'.$filename;
        $destPath = Storage::disk($this->disk())->path($relPath);

        if (! is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }

        $sqlTemp = $includeFiles
            ? tempnam(sys_get_temp_dir(), 'obdump_').'.sql'
            : $destPath;

        $error = $this->dumpDatabase($sqlTemp);
        if ($error !== null) {
            @unlink($sqlTemp);

            return [$filename, $error];
        }

        if ($includeFiles) {
            try {
                // Exclude the backup output directory so the archive never
                // swallows itself or older backups.
                $excludeDir = Storage::disk($this->disk())->path($this->prefix());
                $this->buildZip($destPath, $sqlTemp, $filesRoot, $excludeDir);
            } catch (\Throwable $e) {
                @unlink($sqlTemp);

                return [$filename, $e->getMessage()];
            } finally {
                @unlink($sqlTemp);
            }
        }

        $this->pruneOldBackups();

        // Off-site failure must not void a good local backup — log and carry on.
        $offsiteError = $this->mirrorOffsite($relPath, $filename);
        if ($offsiteError !== null) {
            Log::warning('BackupService: off-site mirror failed', [
                'file' => $filename,
                'error' => $offsiteError,
            ]);
        }

        return [$filename, null];
    }

    /** Run mysqldump into $target. Returns an error string or null on success. */
    private function dumpDatabase(string $target): ?string
    {
        $db = config('database.connections.'.config('database.default'));

        $cmd = [
            config('database.mysqldump_path', 'mysqldump'),
            '--host='.$db['host'],
            '--port='.$db['port'],
            '--user='.$db['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            '--result-file='.$target,
            $db['database'],
        ];

        $process = new Process($cmd, null, ['MYSQL_PWD' => $db['password']]);
        $process->setTimeout(600);
        $process->run();

        return $process->isSuccessful() ? null : $process->getErrorOutput();
    }

    /**
     * Bundle the SQL dump and the whole stored-files tree into a zip at
     * $zipPath. Files are stored under `storage/app/…` mirroring their real
     * location so extracting the archive at the project root restores them in
     * place. $excludeDir (the backup output directory) is skipped entirely.
     */
    private function buildZip(string $zipPath, string $sqlPath, string $filesRoot, string $excludeDir): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Impossible de créer l'archive {$zipPath}.");
        }

        $zip->addFile($sqlPath, 'database.sql');

        if (is_dir($filesRoot)) {
            $base = rtrim($filesRoot, '/');
            // Archive paths mirror storage/app/… (relative to storage_path()).
            $prefixFrom = rtrim(storage_path(), '/');
            $exclude = rtrim($excludeDir, '/');

            /** @var \SplFileInfo $file */
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY,
            ) as $file) {
                if ($file->isDir()) {
                    continue;
                }
                $path = $file->getPathname();

                // Skip the backups directory (and anything under it).
                if ($path === $exclude || str_starts_with($path, $exclude.'/')) {
                    continue;
                }

                $local = 'storage/'.ltrim(substr($path, strlen($prefixFrom)), '/');
                $zip->addFile($path, $local);
            }
        }

        $zip->close();
    }

    /** Copy a freshly written backup to the off-site disk. Returns error|null. */
    public function mirrorOffsite(string $localRelPath, string $filename): ?string
    {
        $offsiteDisk = config('backup.offsite_disk');
        if (empty($offsiteDisk)) {
            return null;
        }

        try {
            $stream = Storage::disk($this->disk())->readStream($localRelPath);
            $offsitePath = trim((string) config('backup.offsite_path', 'backups'), '/').'/'.$filename;
            Storage::disk($offsiteDisk)->writeStream($offsitePath, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }

            return null;
        } catch (\Throwable $e) {
            return 'Copie hors-site échouée : '.$e->getMessage();
        }
    }

    /**
     * Restore an archive into the live database. $filename is a basename on the
     * primary backup disk. Returns an error string or null on success.
     */
    public function restore(string $filename): ?string
    {
        $path = $this->prefix().'/'.$this->sanitize($filename);
        if (! Storage::disk($this->disk())->exists($path)) {
            return "Fichier introuvable : {$filename}";
        }

        $db = config('database.connections.'.config('database.default'));

        return $this->importArchive(
            Storage::disk($this->disk())->path($path),
            $db['database'],
        );
    }

    /**
     * Non-destructive restore drill: restore the latest backup into a scratch
     * database and assert it loads, then drop it. Never touches the live DB.
     *
     * @return array{0: bool, 1: string} [passed, human-readable message]
     */
    public function restoreDrill(): array
    {
        $latest = $this->latestBackup();
        if ($latest === null) {
            return [false, 'Aucune sauvegarde à vérifier.'];
        }

        $scratch = (string) config('backup.drill_database', 'openbrigade_restore_drill');
        if ($scratch === '' || $scratch === config('database.connections.'.config('database.default').'.database')) {
            return [false, 'Base de test invalide (drill_database).'];
        }

        try {
            $this->runMysql(['-e', "CREATE DATABASE IF NOT EXISTS `{$scratch}`"]);
            $error = $this->importArchive(Storage::disk($this->disk())->path($latest), $scratch);

            if ($error !== null) {
                return [false, "Échec de restauration de {$latest} : {$error}"];
            }

            $tables = (int) DB::connection()
                ->select('SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ?', [$scratch])[0]->c;

            if ($tables === 0) {
                return [false, "La restauration de {$latest} n'a produit aucune table."];
            }

            return [true, "Sauvegarde {$latest} restaurée avec succès ({$tables} tables)."];
        } catch (\Throwable $e) {
            return [false, 'Erreur pendant le test de restauration : '.$e->getMessage()];
        } finally {
            try {
                $this->runMysql(['-e', "DROP DATABASE IF EXISTS `{$scratch}`"]);
            } catch (\Throwable) {
                // best-effort cleanup
            }
        }
    }

    /** Import a .sql or .zip archive from an absolute path into $database. */
    private function importArchive(string $absPath, string $database): ?string
    {
        $sqlPath = $absPath;
        $temp = null;

        if (str_ends_with(strtolower($absPath), '.zip')) {
            $zip = new ZipArchive;
            if ($zip->open($absPath) !== true) {
                return "Archive illisible : {$absPath}";
            }
            $sql = $zip->getFromName('database.sql');
            $zip->close();

            if ($sql === false) {
                return "database.sql absent de l'archive.";
            }

            $temp = tempnam(sys_get_temp_dir(), 'obrestore_').'.sql';
            file_put_contents($temp, $sql);
            $sqlPath = $temp;
        }

        try {
            $process = $this->mysqlProcess([$database], file_get_contents($sqlPath) ?: '');
            $process->run();

            return $process->isSuccessful() ? null : $process->getErrorOutput();
        } finally {
            if ($temp !== null) {
                @unlink($temp);
            }
        }
    }

    /** Latest backup relative path on the primary disk, or null. */
    private function latestBackup(): ?string
    {
        return collect(Storage::disk($this->disk())->files($this->prefix()))
            ->sortByDesc(fn ($p) => Storage::disk($this->disk())->lastModified($p))
            ->first();
    }

    private function runMysql(array $args): void
    {
        $process = $this->mysqlProcess($args);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }

    private function mysqlProcess(array $args, ?string $input = null): Process
    {
        $db = config('database.connections.'.config('database.default'));

        $cmd = array_merge([
            config('database.mysql_path', 'mysql'),
            '--host='.$db['host'],
            '--port='.$db['port'],
            '--user='.$db['username'],
        ], $args);

        $process = new Process($cmd, null, ['MYSQL_PWD' => $db['password']], $input);
        $process->setTimeout(600);

        return $process;
    }

    private function buildFilename(string $extension): string
    {
        $pattern = BackupSetting::current()->naming_pattern;
        $now = Carbon::now();
        $database = config('database.connections.'.config('database.default').'.database');

        $name = strtr($pattern, [
            '{date}' => $now->format('Y-m-d'),
            '{time}' => $now->format('H-i-s'),
            '{database}' => $database,
        ]);

        return $name.'.'.$extension;
    }

    public function sanitize(string $filename): string
    {
        return basename(str_replace(['..', '/'], '', $filename));
    }

    private function pruneOldBackups(): void
    {
        $keep = BackupSetting::current()->retention_count ?? config('backup.keep', 30);

        $files = collect(Storage::disk($this->disk())->files($this->prefix()))
            ->sortBy(fn ($p) => Storage::disk($this->disk())->lastModified($p))
            ->values();

        while ($files->count() > $keep) {
            Storage::disk($this->disk())->delete($files->shift());
        }
    }
}
