<?php

namespace App\Services\Plugins;

/**
 * The plugin.json manifest shipped at the root of every plugin package.
 *
 * Required: name, slug, version, description, min_app_version, provider
 * (FQCN of the plugin's service provider). Optional: max_app_version
 * (inclusive compatibility cap — without it a plugin would forever claim
 * compatibility with future OpenBrigade majors), authors (list of strings),
 * autoload (PSR-4 prefix => relative directory map).
 */
class PluginManifest
{
    private const SLUG_PATTERN = '/^[a-z0-9][a-z0-9-]{1,49}$/';

    /**
     * @param  array<int,string>  $authors
     * @param  array<string,string>  $autoload
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $version,
        public readonly string $description,
        public readonly string $minAppVersion,
        public readonly string $provider,
        public readonly string $maxAppVersion = '',
        public readonly array $authors = [],
        public readonly array $autoload = [],
    ) {}

    /** Inclusive compatibility window check against an app version. */
    public static function compatible(string $appVersion, string $min, string $max): bool
    {
        return version_compare($appVersion, $min, '>=')
            && ($max === '' || version_compare($appVersion, $max, '<='));
    }

    public static function fromFile(string $path): self
    {
        if (! is_file($path)) {
            throw new InvalidPluginException(__('admin.plugins.err_manifest_missing'));
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data)) {
            throw new InvalidPluginException(__('admin.plugins.err_manifest_invalid'));
        }

        return self::fromArray($data);
    }

    /** @param  array<string,mixed>  $data */
    public static function fromArray(array $data): self
    {
        foreach (['name', 'slug', 'version', 'description', 'min_app_version', 'provider'] as $key) {
            if (! isset($data[$key]) || ! is_string($data[$key]) || trim($data[$key]) === '') {
                throw new InvalidPluginException(__('admin.plugins.err_manifest_field', ['field' => $key]));
            }
        }

        if (! preg_match(self::SLUG_PATTERN, $data['slug'])) {
            throw new InvalidPluginException(__('admin.plugins.err_slug'));
        }

        $autoload = [];
        foreach ((array) ($data['autoload'] ?? []) as $prefix => $dir) {
            if (! is_string($prefix) || ! is_string($dir)
                || str_contains($dir, '..') || str_starts_with($dir, '/')) {
                throw new InvalidPluginException(__('admin.plugins.err_autoload'));
            }
            $autoload[$prefix] = $dir;
        }

        return new self(
            slug: $data['slug'],
            name: $data['name'],
            version: $data['version'],
            description: $data['description'],
            minAppVersion: $data['min_app_version'],
            provider: $data['provider'],
            maxAppVersion: is_string($data['max_app_version'] ?? null) ? trim($data['max_app_version']) : '',
            authors: array_values(array_filter((array) ($data['authors'] ?? []), 'is_string')),
            autoload: $autoload,
        );
    }
}
