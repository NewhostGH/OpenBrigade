<?php

namespace App\Http\Controllers;

use App\Models\BackupSetting;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(private readonly BackupService $backups) {}

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
                $size = Storage::disk($this->disk())->size($path);
                $mtime = Storage::disk($this->disk())->lastModified($path);

                return [
                    'filename' => $filename,
                    'path' => $path,
                    'size_kb' => round($size / 1024, 1),
                    'date' => Carbon::createFromTimestamp($mtime),
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
            'auto_enabled' => ['nullable', 'boolean'],
            'frequency' => ['required', Rule::in(BackupSetting::FREQUENCIES)],
            'run_time' => ['required', 'date_format:H:i'],
            'start_date' => ['required', 'date'],
            'day_of_week' => ['required_if:frequency,weekly', 'nullable', 'integer', 'between:0,6'],
            'day_of_month' => ['required_if:frequency,monthly', 'nullable', 'integer', 'between:1,31'],
            'naming_pattern' => ['required', Rule::in(array_keys(BackupSetting::NAMING_PATTERNS))],
        ]);

        $data['auto_enabled'] = $request->boolean('auto_enabled');
        $data['day_of_week'] = $data['frequency'] === 'weekly' ? $data['day_of_week'] : null;
        $data['day_of_month'] = $data['frequency'] === 'monthly' ? $data['day_of_month'] : null;

        BackupSetting::current()->update($data);

        return redirect()->route('admin.backup')
            ->with('success', 'Préférences de sauvegarde mises à jour.');
    }

    public function store(): RedirectResponse
    {
        [$filename, $error] = $this->backups->createBackup();

        if ($error !== null) {
            return redirect()->route('admin.backup')
                ->with('error', 'Échec de la sauvegarde : '.$error);
        }

        return redirect()->route('admin.backup')
            ->with('success', "Sauvegarde créée : {$filename}");
    }

    public function download(string $filename): StreamedResponse
    {
        $path = $this->prefix().'/'.$this->sanitize($filename);
        abort_unless(Storage::disk($this->disk())->exists($path), 404);

        return Storage::disk($this->disk())->download($path);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $path = $this->prefix().'/'.$this->sanitize($filename);
        abort_unless(Storage::disk($this->disk())->exists($path), 404);

        Storage::disk($this->disk())->delete($path);

        return redirect()->route('admin.backup')
            ->with('success', "Fichier supprimé : {$filename}");
    }

    public function restore(Request $request): RedirectResponse
    {
        $request->validate([
            'filename' => ['required', 'string'],
            'confirm' => ['required', 'in:CONFIRMER'],
        ]);

        $filename = (string) $request->input('filename');
        $error = $this->backups->restore($filename);

        if ($error !== null) {
            return redirect()->route('admin.backup')
                ->with('error', 'Échec de la restauration : '.$error);
        }

        return redirect()->route('admin.backup')
            ->with('success', "Base restaurée depuis : {$filename}");
    }

    private function sanitize(string $filename): string
    {
        // Strip path traversal; only allow the basename
        return basename(str_replace(['..', '/'], '', $filename));
    }
}
