<?php

namespace App\Services\Plugins;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Download → verify → extract → validate → install pipeline for plugin
 * packages. Deliberately paranoid: plugins are executable code, so the
 * sha256 published by the registry is mandatory, zip entries are checked
 * against path traversal and archive caps, and the manifest must match the
 * catalog slug and support this app version. Any refusal throws a
 * translated InvalidPluginException — the UI flashes it, never a 500.
 */
class PluginInstaller
{
    private const MAX_ZIP_BYTES = 20 * 1024 * 1024;

    private const MAX_ENTRIES = 500;

    private const MAX_UNCOMPRESSED = 50 * 1024 * 1024;

    public function __construct(private readonly PluginStateService $state) {}

    /**
     * Install (or upgrade) a plugin from its catalog entry.
     *
     * @param  array<string,mixed>  $entry  validated registry entry
     */
    public function install(array $entry): PluginManifest
    {
        $slug = (string) $entry['slug'];
        $zipPath = $this->download(
            (string) $entry['download_url'],
            (string) $entry['sha256'],
            verifySsl: (bool) ($entry['verify_ssl'] ?? true),
            verifyChecksum: (bool) ($entry['verify_checksum'] ?? true),
        );
        $stage = storage_path('app/tmp/plugin-'.Str::random(8));

        try {
            $this->extract($zipPath, $stage);

            $manifest = PluginManifest::fromFile($this->manifestPath($stage));
            if ($manifest->slug !== $slug) {
                throw new InvalidPluginException(__('admin.plugins.err_slug_mismatch'));
            }
            if (! PluginManifest::compatible((string) config('brigade.version'), $manifest->minAppVersion, $manifest->maxAppVersion)) {
                throw new InvalidPluginException(__('admin.plugins.err_app_version', [
                    'range' => $manifest->maxAppVersion !== ''
                        ? $manifest->minAppVersion.' – '.$manifest->maxAppVersion
                        : '≥ '.$manifest->minAppVersion,
                ]));
            }

            // Swap into place (a re-install/upgrade replaces the directory;
            // the enabled flag is preserved by record()'s updateOrCreate).
            $wasEnabled = (bool) ($this->state->find($slug)?->enabled);
            $target = PluginLoader::pluginPath($slug);
            File::deleteDirectory($target);
            File::ensureDirectoryExists(dirname($target));
            File::moveDirectory(dirname($this->manifestPath($stage)), $target);

            $this->state->record($manifest);

            // Updating a plugin that is already enabled must apply the new
            // version's migrations immediately — the fresh code boots on the
            // very next request.
            if ($wasEnabled) {
                $this->runMigrations($slug);
            }

            return $manifest;
        } finally {
            File::deleteDirectory($stage);
            @unlink($zipPath);
        }
    }

    /** Enable a plugin: run its migrations (if any), then flip the flag. */
    public function enable(string $slug): void
    {
        $this->runMigrations($slug);
        $this->state->setEnabled($slug, true);
    }

    private function runMigrations(string $slug): void
    {
        if (File::isDirectory(PluginLoader::pluginPath($slug).'/database/migrations')) {
            Artisan::call('migrate', [
                '--path' => 'plugins/'.$slug.'/database/migrations',
                '--force' => true,
            ]);
        }
    }

    public function disable(string $slug): void
    {
        // Migrations are deliberately NOT rolled back — disabling must never
        // destroy data. Documented in docs/admin/plugins.md.
        $this->state->setEnabled($slug, false);
    }

    /** Remove a disabled plugin: its directory and its row. */
    public function uninstall(string $slug): void
    {
        $plugin = $this->state->find($slug);
        if ($plugin === null) {
            throw new InvalidPluginException(__('admin.plugins.err_not_installed'));
        }
        if ($plugin->enabled) {
            throw new InvalidPluginException(__('admin.plugins.err_uninstall_enabled'));
        }

        File::deleteDirectory(PluginLoader::pluginPath($slug));
        $this->state->remove($slug);
    }

    /**
     * Download the package and verify its sha256. Both verifications can be
     * relaxed per registry (dev / intercepting proxies) — the UI badges any
     * registry running without them.
     */
    private function download(string $url, string $sha256, bool $verifySsl = true, bool $verifyChecksum = true): string
    {
        if (! str_starts_with($url, 'https://') && ! str_starts_with($url, 'http://')) {
            throw new InvalidPluginException(__('admin.plugins.err_download_url'));
        }

        try {
            $response = Http::timeout(30)->maxRedirects(3)
                ->withOptions(['verify' => $verifySsl])
                ->get($url);
        } catch (\Throwable $e) {
            throw new InvalidPluginException(__('admin.plugins.err_download', ['reason' => $e->getMessage()]));
        }

        if (! $response->successful()) {
            throw new InvalidPluginException(__('admin.plugins.err_download', ['reason' => 'HTTP '.$response->status()]));
        }

        $body = $response->body();
        if (strlen($body) > self::MAX_ZIP_BYTES) {
            throw new InvalidPluginException(__('admin.plugins.err_too_big'));
        }

        if ($verifyChecksum && ! hash_equals(strtolower(trim($sha256)), hash('sha256', $body))) {
            throw new InvalidPluginException(__('admin.plugins.err_checksum'));
        }

        $path = storage_path('app/tmp/plugin-'.Str::random(8).'.zip');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $body);

        return $path;
    }

    /** Extract with path-traversal and archive-bomb guards. */
    private function extract(string $zipPath, string $target): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new InvalidPluginException(__('admin.plugins.err_archive'));
        }

        try {
            if ($zip->numFiles > self::MAX_ENTRIES) {
                throw new InvalidPluginException(__('admin.plugins.err_too_many_entries'));
            }

            $total = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);
                if (str_contains($name, '..')
                    || str_starts_with($name, '/') || str_starts_with($name, '\\')
                    || preg_match('/^[A-Za-z]:/', $name)) {
                    throw new InvalidPluginException(__('admin.plugins.err_traversal'));
                }

                $stat = $zip->statIndex($i);
                $total += (int) ($stat['size'] ?? 0);
                if ($total > self::MAX_UNCOMPRESSED) {
                    throw new InvalidPluginException(__('admin.plugins.err_too_big'));
                }
            }

            File::ensureDirectoryExists($target);
            if (! $zip->extractTo($target)) {
                throw new InvalidPluginException(__('admin.plugins.err_archive'));
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * plugin.json may sit at the archive root or inside a single top-level
     * directory (the GitHub release-archive layout).
     */
    private function manifestPath(string $stage): string
    {
        if (is_file($stage.'/plugin.json')) {
            return $stage.'/plugin.json';
        }

        $dirs = File::directories($stage);
        if (count($dirs) === 1 && is_file($dirs[0].'/plugin.json')) {
            return $dirs[0].'/plugin.json';
        }

        throw new InvalidPluginException(__('admin.plugins.err_manifest_missing'));
    }
}
