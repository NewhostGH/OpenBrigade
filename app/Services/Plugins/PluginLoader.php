<?php

namespace App\Services\Plugins;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Boots the enabled plugins: for each one, registers a composer-less PSR-4
 * autoloader for the prefixes declared in its manifest, then registers its
 * service provider with the application.
 *
 * Every plugin is isolated in its own try/catch: a broken plugin logs an
 * error and lands in loadFailures() (surfaced on the admin page) — it can
 * never take the whole application down.
 */
class PluginLoader
{
    /** @var array<string,string> slug => failure reason */
    private array $failures = [];

    public function __construct(private readonly PluginStateService $state) {}

    public function boot(Application $app): void
    {
        foreach ($this->state->enabledPlugins() as $slug) {
            try {
                $this->bootPlugin($app, $slug);
            } catch (Throwable $e) {
                $this->failures[$slug] = $e->getMessage();
                Log::error('PluginLoader: plugin failed to load', [
                    'plugin' => $slug,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /** @return array<string,string> slug => failure reason */
    public function loadFailures(): array
    {
        return $this->failures;
    }

    public static function pluginPath(string $slug): string
    {
        return base_path('plugins/'.$slug);
    }

    private function bootPlugin(Application $app, string $slug): void
    {
        $dir = self::pluginPath($slug);
        $manifest = PluginManifest::fromFile($dir.'/plugin.json');

        if ($manifest->autoload !== []) {
            $this->registerAutoloader($dir, $manifest->autoload);
        }

        if (! class_exists($manifest->provider)) {
            throw new InvalidPluginException(__('admin.plugins.err_provider', ['class' => $manifest->provider]));
        }

        $app->register($manifest->provider);
    }

    /** @param  array<string,string>  $map  PSR-4 prefix => relative directory */
    private function registerAutoloader(string $dir, array $map): void
    {
        spl_autoload_register(function (string $class) use ($dir, $map): void {
            foreach ($map as $prefix => $relative) {
                $prefix = rtrim($prefix, '\\').'\\';
                if (! str_starts_with($class, $prefix)) {
                    continue;
                }

                $file = $dir.'/'.rtrim($relative, '/').'/'
                    .str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
                if (is_file($file)) {
                    require $file;
                }

                return;
            }
        });
    }
}
