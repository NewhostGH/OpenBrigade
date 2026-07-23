<?php

namespace App\Services\Plugins;

use App\Models\PluginRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * KASM-style multi-registry catalog: every enabled registry URL serves a
 * registry.json; catalogs are merged (first registry wins on slug collision),
 * cached one hour per registry, and a broken registry degrades only itself —
 * its error is surfaced next to the others' plugins.
 *
 * registry.json schema:
 *   { "name": "...", "plugins": [ { "slug", "name", "version", "description",
 *     "download_url", "sha256", "min_app_version", "max_app_version"?,
 *     "author"?, "category"?, "icon"?, "screenshots"?: [url, …],
 *     "homepage"? } ] }
 *
 * The same slug may appear several times — one entry per compatibility
 * track (e.g. a 6.x line and a 7.x line maintained in parallel). The
 * catalog keeps, per slug, the best entry COMPATIBLE with the running app
 * version; when no track is compatible the newest entry is kept but marked
 * `compatible => false` so the UI can refuse installation.
 */
class PluginRegistryService
{
    private const CACHE_TTL = 3600;

    private const REQUIRED = ['slug', 'name', 'version', 'description', 'download_url', 'sha256', 'min_app_version'];

    /**
     * Merged catalog across every enabled registry.
     *
     * @return array{plugins: array<string,array<string,mixed>>, errors: array<string,string>}
     */
    public function catalog(bool $fresh = false): array
    {
        $tracks = [];
        $errors = [];

        foreach ($this->registries() as $registry) {
            try {
                if ($fresh) {
                    Cache::forget($this->cacheKey($registry));
                }

                foreach ($this->fetch($registry) as $plugin) {
                    // First registry to publish a slug owns it (all tracks).
                    // Entries carry their registry's verification flags.
                    if (isset($tracks[$plugin['slug']]) && $tracks[$plugin['slug']][0]['registry'] !== $registry->name) {
                        continue;
                    }

                    $tracks[$plugin['slug']][] = $plugin + [
                        'registry' => $registry->name,
                        'verify_ssl' => (bool) $registry->verify_ssl,
                        'verify_checksum' => (bool) $registry->verify_checksum,
                    ];
                }
            } catch (Throwable $e) {
                $errors[$registry->name] = $e->getMessage();
            }
        }

        $appVersion = (string) config('brigade.version');
        $plugins = [];
        foreach ($tracks as $slug => $entries) {
            $plugins[$slug] = $this->selectTrack($entries, $appVersion);
        }

        return ['plugins' => $plugins, 'errors' => $errors];
    }

    /**
     * Pick, among a slug's tracks, the highest-version entry compatible with
     * the running app version — or the highest-version entry marked
     * incompatible when none fits.
     *
     * @param  array<int,array<string,mixed>>  $entries
     * @return array<string,mixed>
     */
    private function selectTrack(array $entries, string $appVersion): array
    {
        $byVersion = fn (array $a, array $b) => version_compare((string) $b['version'], (string) $a['version']);

        $compatible = array_filter($entries, fn (array $e) => PluginManifest::compatible(
            $appVersion,
            (string) $e['min_app_version'],
            (string) ($e['max_app_version'] ?? ''),
        ));

        if ($compatible !== []) {
            usort($compatible, $byVersion);

            return $compatible[0] + ['compatible' => true];
        }

        usort($entries, $byVersion);

        return $entries[0] + ['compatible' => false];
    }

    /** A single catalog entry by slug, or null. */
    public function findPlugin(string $slug): ?array
    {
        return $this->catalog()['plugins'][$slug] ?? null;
    }

    /** @return iterable<int,PluginRegistry> — overridable seam for tests. */
    protected function registries(): iterable
    {
        try {
            return PluginRegistry::query()->where('enabled', true)->orderBy('id')->get();
        } catch (Throwable) {
            return [];
        }
    }

    /** @return array<int,array<string,mixed>> validated plugin entries */
    private function fetch(PluginRegistry $registry): array
    {
        return Cache::remember($this->cacheKey($registry), self::CACHE_TTL, function () use ($registry) {
            $response = Http::timeout(10)
                ->withOptions(['verify' => (bool) $registry->verify_ssl])
                ->get($this->normalizeUrl($registry->url));
            if (! $response->successful()) {
                throw new InvalidPluginException(__('admin.plugins.err_registry_http', ['status' => $response->status()]));
            }

            $data = $response->json();
            if (! is_array($data) || ! isset($data['plugins']) || ! is_array($data['plugins'])) {
                throw new InvalidPluginException(__('admin.plugins.err_registry_schema'));
            }

            $valid = [];
            foreach ($data['plugins'] as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $ok = true;
                foreach (self::REQUIRED as $key) {
                    if (! isset($entry[$key]) || ! is_string($entry[$key]) || trim($entry[$key]) === '') {
                        $ok = false;
                        break;
                    }
                }
                if ($ok && preg_match('/^[a-z0-9][a-z0-9-]{1,49}$/', $entry['slug'])) {
                    $valid[] = $this->sanitizeOptional($entry);
                }
            }

            return $valid;
        });
    }

    /**
     * Normalise the optional presentation fields: only https(-ish) URLs
     * survive for icon/screenshots/homepage, category is a short plain
     * string, everything unexpected is dropped.
     *
     * @param  array<string,mixed>  $entry
     * @return array<string,mixed>
     */
    private function sanitizeOptional(array $entry): array
    {
        $url = fn ($v) => is_string($v)
            && (str_starts_with($v, 'https://') || str_starts_with($v, 'http://'))
            && strlen($v) <= 500 ? $v : null;

        $entry['max_app_version'] = is_string($entry['max_app_version'] ?? null)
            ? mb_substr(trim($entry['max_app_version']), 0, 20)
            : '';
        $entry['icon'] = $url($entry['icon'] ?? null);
        $entry['homepage'] = $url($entry['homepage'] ?? null);
        $entry['author'] = is_string($entry['author'] ?? null) ? mb_substr($entry['author'], 0, 100) : null;
        $entry['category'] = is_string($entry['category'] ?? null) && trim($entry['category']) !== ''
            ? mb_substr(trim($entry['category']), 0, 40)
            : null;
        $entry['screenshots'] = array_slice(
            array_values(array_filter(array_map($url, (array) ($entry['screenshots'] ?? [])))),
            0,
            8,
        );

        return $entry;
    }

    /**
     * Accept the common paste mistake of a registry *site* URL: when the
     * path doesn't already point at a .json document, request the
     * conventional registry.json next to it instead of the HTML page.
     * URLs with a query string are left untouched.
     */
    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        if (str_ends_with($path, '.json') || parse_url($url, PHP_URL_QUERY) !== null) {
            return $url;
        }

        return rtrim($url, '/').'/registry.json';
    }

    private function cacheKey(PluginRegistry $registry): string
    {
        return 'ob:plugins:registry:'.$registry->id.':'.sha1($registry->url);
    }
}
