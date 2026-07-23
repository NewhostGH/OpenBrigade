<?php

namespace App\Services\Plugins;

use App\Models\Plugin;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Installed-plugin state (ob_plugin) — the boot-time seam consulted by
 * PluginLoader on every request, so it is memoised and Throwable-guarded
 * (no table → no plugins, never a broken boot).
 */
class PluginStateService
{
    /** @var array<int,string>|null */
    private ?array $enabled = null;

    /** Slugs of the enabled plugins, in installation order. */
    public function enabledPlugins(): array
    {
        if ($this->enabled === null) {
            try {
                $this->enabled = Plugin::query()
                    ->where('enabled', true)
                    ->orderBy('id')
                    ->pluck('slug')
                    ->all();
            } catch (Throwable) {
                $this->enabled = [];
            }
        }

        return $this->enabled;
    }

    /** @return Collection<int,Plugin> */
    public function all(): Collection
    {
        try {
            return Plugin::query()->orderBy('name')->get();
        } catch (Throwable) {
            return new Collection;
        }
    }

    public function find(string $slug): ?Plugin
    {
        try {
            return Plugin::query()->where('slug', $slug)->first();
        } catch (Throwable) {
            return null;
        }
    }

    public function record(PluginManifest $manifest): void
    {
        Plugin::query()->updateOrCreate(
            ['slug' => $manifest->slug],
            [
                'name' => $manifest->name,
                'version' => $manifest->version,
                'min_app_version' => $manifest->minAppVersion,
                'max_app_version' => $manifest->maxAppVersion,
                'installed_at' => now(),
            ],
        );
        $this->enabled = null;
    }

    public function setEnabled(string $slug, bool $enabled): void
    {
        Plugin::query()->where('slug', $slug)->update(['enabled' => $enabled]);
        $this->enabled = null;
    }

    public function remove(string $slug): void
    {
        Plugin::query()->where('slug', $slug)->delete();
        $this->enabled = null;
    }
}
