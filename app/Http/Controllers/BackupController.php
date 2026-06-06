<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    private const DISK   = 'local';
    private const PREFIX = 'backups';

    public function index(): View
    {
        $files = collect(Storage::disk(self::DISK)->files(self::PREFIX))
            ->map(function (string $path): array {
                $filename = basename($path);
                $size     = Storage::disk(self::DISK)->size($path);
                $mtime    = Storage::disk(self::DISK)->lastModified($path);
                return [
                    'filename' => $filename,
                    'path'     => $path,
                    'size_kb'  => round($size / 1024, 1),
                    'date'     => Carbon::createFromTimestamp($mtime),
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('admin.backup.index', compact('files'));
    }

    public function store(): RedirectResponse
    {
        $db   = config('database.connections.mysql');
        $host = $db['host'];
        $port = $db['port'];
        $name = $db['database'];
        $user = $db['username'];
        $pass = $db['password'];

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $destPath = storage_path('app/' . self::PREFIX . '/' . $filename);

        if (! is_dir(storage_path('app/' . self::PREFIX))) {
            mkdir(storage_path('app/' . self::PREFIX), 0755, true);
        }

        $cmd = [
            'mysqldump',
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
            return redirect()->route('admin.backup')
                ->with('error', 'Échec de la sauvegarde : ' . $process->getErrorOutput());
        }

        $this->pruneOldBackups();

        return redirect()->route('admin.backup')
            ->with('success', "Sauvegarde créée : {$filename}");
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $path = self::PREFIX . '/' . $this->sanitize($filename);
        abort_unless(Storage::disk(self::DISK)->exists($path), 404);

        return Storage::disk(self::DISK)->download($path);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $path = self::PREFIX . '/' . $this->sanitize($filename);
        abort_unless(Storage::disk(self::DISK)->exists($path), 404);

        Storage::disk(self::DISK)->delete($path);

        return redirect()->route('admin.backup')
            ->with('success', "Fichier supprimé : {$filename}");
    }

    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'filename'  => ['required', 'string'],
            'confirm'   => ['required', 'in:CONFIRMER'],
        ]);

        $path = self::PREFIX . '/' . $this->sanitize($request->input('filename'));
        abort_unless(Storage::disk(self::DISK)->exists($path), 404);

        $db   = config('database.connections.mysql');
        $host = $db['host'];
        $port = $db['port'];
        $name = $db['database'];
        $user = $db['username'];
        $pass = $db['password'];
        $fullPath = storage_path('app/' . $path);

        $cmd = [
            'mysql',
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

    private function pruneOldBackups(int $keep = 30): void
    {
        $files = collect(Storage::disk(self::DISK)->files(self::PREFIX))
            ->sortBy(fn ($p) => Storage::disk(self::DISK)->lastModified($p))
            ->values();

        while ($files->count() > $keep) {
            Storage::disk(self::DISK)->delete($files->shift());
        }
    }
}
