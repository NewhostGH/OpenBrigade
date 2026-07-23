# Developing OpenBrigade plugins

How to build, test and publish a plugin for the Administration ▸ Plugins
marketplace. For the admin-side view (registries, security model,
lifecycle), see [docs/admin/plugins.md](../admin/plugins.md).

## What a plugin is

A plugin is a zip extracted to `plugins/<slug>/`, described by a
`plugin.json` manifest, and booted through a **standard Laravel service
provider**. There is no plugin API layer: an enabled plugin's provider is
registered like any application provider, with the same privileges.

```dir
hello-world/
├── plugin.json
├── src/
│   └── HelloWorldServiceProvider.php   ← + any other PSR-4 classes
├── routes/web.php                      ← optional, loaded by YOUR provider
├── resources/views/…                   ← optional, loaded by YOUR provider
├── lang/fr/…                           ← optional, loaded by YOUR provider
└── database/migrations/…               ← optional, run by the app on enable
```

### plugin.json

```json
{
  "name": "Hello World",
  "slug": "hello-world",
  "version": "1.0.0",
  "description": "Minimal example plugin.",
  "min_app_version": "6.0.0",
  "max_app_version": "6.999",
  "provider": "ObPlugin\\HelloWorld\\HelloWorldServiceProvider",
  "authors": ["You"],
  "autoload": { "ObPlugin\\HelloWorld\\": "src" }
}
```

- `slug` — `^[a-z0-9][a-z0-9-]{1,49}$`, must equal the registry entry's slug
  and the install directory name.
- `min_app_version` / `max_app_version` — inclusive compatibility window,
  compared (`version_compare`) against the installed version (configuration
  row 1); installation is refused outside it. `max_app_version` is optional
  but **strongly recommended** (e.g. `6.999` for a 6.x-only plugin): without
  it your plugin claims compatibility with every future OpenBrigade major.
- `provider` — FQCN resolved through `autoload`, a PSR-4 prefix → directory
  map relative to the plugin root (no `..`, no absolute paths).
- Use the conventional namespace (`ObPlugin\<Name>\…`) to avoid collisions
  with the app (`App\…`) and other plugins.

### Maintaining several app lines in parallel

Publish **one registry entry per track, same slug**: e.g. keep
`1.x (min 6.0.0, max 6.999)` maintained for 6.x installs while shipping
`2.x (min 7.0.0)` for 7.x. Each install only sees — and only receives
updates from — the track compatible with its own app version.

## The service provider

A plain `Illuminate\Support\ServiceProvider`. Everything a Laravel package
provider can do works here:

```php
namespace ObPlugin\HelloWorld;

use Illuminate\Support\ServiceProvider;

class HelloWorldServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('plugins/hello-world');

        $this->loadRoutesFrom($base.'/routes/web.php');
        $this->loadViewsFrom($base.'/resources/views', 'hello-world');
        $this->loadTranslationsFrom($base.'/lang', 'hello-world');

        // Sidebar entry: NavigationService reads config('navigation.top')
        // at request time, so pushing into it from boot() just works.
        $groups = config('navigation.top', []);
        $groups[0]['items'][] = [
            'key' => 'hello-world.index', 'label' => 'Hello World',
            'url' => '/hello-world', 'icon' => 'hand', 'permission' => 0,
        ];
        config(['navigation.top' => $groups]);
    }
}
```

Views are referenced namespaced (`view('hello-world::index')`), translations
too (`__('hello-world::messages.title')`).

### What you can (and should) reuse from the app

- **Routes**: wrap them in `Route::middleware(['web', 'auth'])` and gate with
  the same route middleware as core: `permission:<id>` (legacy permission
  ids) and `feature:<key>` (ob_feature flags).
- **Models / DB**: Eloquent and the query builder against the app schema, or
  your own tables (ship migrations — prefix them `obp_<slug>_…`).
- **Services** via the container: `PermissionResolver`, `SectionScopeService`
  (data isolation!), `FeatureService`, `NotificationService` (email/SMS),
  `GeneralSettingService`, `App\Support\Audit` for the activity trail,
  `App\Support\Money`, …
- **UI**: extend `layout.app`, use the `x-ob-*` Blade components and the
  `ob-*` CSS classes so plugin pages look native.

### Constraints

- **No composer dependencies.** The autoloader is composer-less: only the
  app's `vendor/` and your PSR-4 sources exist. Need a library the app
  doesn't ship? Bundle its (license-permitting) sources under your PSR-4
  tree, or contribute the dependency to core.
- Blade conventions apply to shipped views (i18n via `__()`, no inline
  `<style>` blocks); `php artisan ob:views:lint` won't check plugin views,
  but broken compiled Blade will surface as a load failure.
- A provider that throws at boot doesn't break the app: the plugin is
  skipped, logged, and flagged on the Plugins page until fixed.

## Versioning & updates

The marketplace compares the **registry entry's `version`** with the
installed row (`version_compare`): newer catalog version → « Mettre à
jour » badge + button. An update is a re-install of the new zip:

1. download + SHA-256 verification (per the registry's flags);
2. the plugin directory is **replaced wholesale** — never store state in
   `plugins/<slug>/`, use the database or `storage/`;
3. the enabled flag is preserved, and if the plugin was enabled its **new
   migrations run immediately** (Laravel's migrations table tracks them, so
   only new files execute);
4. the new code boots on the next request.

Migrations are never rolled back — on disable *or* update — so write them
additive and idempotent-friendly. Bump `version` on every published change;
the catalog caches for 1 h (« Actualiser » forces a refresh).

## Local development loop

1. Work directly in `plugins/<slug>/` (the directory is gitignored in the
   app repo — keep the plugin in its own repo) and enable it once; code
   changes apply immediately since the provider is re-registered per
   request.
2. To test the **pipeline**, build the zip and serve a local registry:

   ```bash
   cd my-plugin && zip -r ../hello-world-1.0.0.zip . && cd ..
   sha256sum hello-world-1.0.0.zip     # → registry.json "sha256"
   ```

   Serve `registry.json` + the zip (GitHub raw works, or any static host),
   add it in the « Dépôts » panel. For a dev registry without real
   certificates/checksums, relax the per-registry SSL / SHA-256 toggles.
3. The example registry
   ([openbrigade-plugin-registry-example](https://github.com/NewhostGH/openbrigade-plugin-registry-example))
   is the reference layout to fork for publishing.

## Publishing checklist

- [ ] `slug` identical in `plugin.json`, `registry.json` and the zip layout
- [ ] `version` bumped (semver), `min_app_version` accurate
- [ ] `sha256` = hash of the exact uploaded zip bytes
- [ ] Optional store metadata: `category`, `icon`, `screenshots` (≤ 8),
      `homepage`, `author` — https URLs only
- [ ] Migrations additive; no state kept under `plugins/<slug>/`
- [ ] Tested: install → enable → use → update → disable → uninstall
