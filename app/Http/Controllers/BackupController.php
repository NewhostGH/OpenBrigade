<?php

namespace App\Http\Controllers;

use App\Models\BackupSetting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    private function disk(): string
    {
        return config('backup.disk', 'local');
    }

    private function prefix(): string
    {
        return config('backup.path', 'backups');
    }

    public function index(): View
    {
        $files = collect(Storage::disk($this->disk())->files($this->prefix()))
            ->map(function (string $path): array {
                $filename = basename($path);
                $size     = Storage::disk($this->disk())->size($path);
                $mtime    = Storage::disk($this->disk())->lastModified($path);
                return [
                    'filename' => $filename,
                    'path'     => $path,
                    'size_kb'  => round($size / 1024, 1),
                    'date'     => Carbon::createFromTimestamp($mtime),
                ];
            })
            ->sortByDesc('date')
            ->values();

        $settings = BackupSetting::current();

        return view('admin.backup.index', compact('files', 'settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'retention_count' => ['required', 'integer', 'min:1', 'max:365'],
            'auto_enabled'    => ['nullable', 'boolean'],
            'frequency'       => ['required', Rule::in(BackupSetting::FREQUENCIES)],
            'run_time'        => ['required', 'date_format:H:i'],
            'start_date'      => ['required', 'date'],
            'day_of_week'     => ['required_if:frequency,weekly', 'nullable', 'integer', 'between:0,6'],
            'day_of_month'    => ['required_if:frequency,monthly', 'nullable', 'integer', 'between:1,31'],
            'naming_pattern'  => ['required', Rule::in(array_keys(BackupSetting::NAMING_PATTERNS))],
        ]);

        $data['auto_enabled'] = $request->boolean('auto_enabled');
        $data['day_of_week']  = $data['frequency'] === 'weekly' ? $data['day_of_week'] : null;
        $data['day_of_month'] = $data['frequency'] === 'monthly' ? $data['day_of_month'] : null;

        BackupSetting::current()->update($data);

        return redirect()->route('admin.backup')
            ->with('success', 'Préférences de sauvegarde mises à jour.');
    }

    public function store(): RedirectResponse
    {
        [$filename, $error] = $this->createBackup();

        if ($error !== null) {
            return redirect()->route('admin.backup')
                ->with('error', 'Échec de la sauvegarde : ' . $error);
        }

        return redirect()->route('admin.backup')
            ->with('success', "Sauvegarde créée : {$filename}");
    }

    /**
     * Run mysqldump, write the result to disk and prune old backups.
     *
     * @return array{0: string, 1: string|null} The created filename, and an error message on failure (null on success).
     */
    public function createBackup(): array
    {
        $db   = config('database.connections.' . config('database.default'));
        $host = $db['host'];
        $port = $db['port'];
        $name = $db['database'];
        $user = $db['username'];
        $pass = $db['password'];

        $filename = $this->buildFilename($name);
        $destPath = Storage::disk($this->disk())->path($this->prefix() . '/' . $filename);

        if (! is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }

        $cmd = [
            config('database.mysqldump_path', 'mysqldump'),
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $user,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--result-file=' . $destPath,
            $name,
        ];

        $env = ['MYSQL_PWD' => $pass];

        $process = new Process($cmd, null, $env);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            return [$filename, $process->getErrorOutput()];
        }

        $this->pruneOldBackups();

        return [$filename, null];
    }

    /**
     * Expand the configured naming pattern's {date}/{time}/{database} tokens into a filename.
     */
    private function buildFilename(string $database): string
    {
        $pattern = BackupSetting::current()->naming_pattern;
        $now     = Carbon::now();

        $name = strtr($pattern, [
            '{date}'     => $now->format('Y-m-d'),
            '{time}'     => $now->format('H-i-s'),
            '{database}' => $database,
        ]);

        return $name . '.sql';
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $path = $this->prefix() . '/' . $this->sanitize($filename);
        abort_unless(Storage::disk($this->disk())->exists($path), 404);

        return Storage::disk($this->disk())->download($path);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $path = $this->prefix() . '/' . $this->sanitize($filename);
        abort_unless(Storage::disk($this->disk())->exists($path), 404);

        Storage::disk($this->disk())->delete($path);

        return redirect()->route('admin.backup')
            ->with('success', "Fichier supprimé : {$filename}");
    }

    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'filename'  => ['required', 'string'],
            'confirm'   => ['required', 'in:CONFIRMER'],
        ]);

        $path = $this->prefix() . '/' . $this->sanitize($request->input('filename'));
        abort_unless(Storage::disk($this->disk())->exists($path), 404);

        $db   = config('database.connections.' . config('database.default'));
        $host = $db['host'];
        $port = $db['port'];
        $name = $db['database'];
        $user = $db['username'];
        $pass = $db['password'];
        $fullPath = Storage::disk($this->disk())->path($path);

        $cmd = [
            config('database.mysql_path', 'mysql'),
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $user,
            $name,
        ];

        $env = ['MYSQL_PWD' => $pass];

        $process = new Process($cmd, null, $env, file_get_contents($fullPath));
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            return redirect()->route('admin.backup')
                ->with('error', 'Échec de la restauration : ' . $process->getErrorOutput());
        }

        return redirect()->route('admin.backup')
            ->with('success', "Base restaurée depuis : {$request->input('filename')}");
    }

    private function sanitize(string $filename): string
    {
        // Strip path traversal; only allow the basename
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
