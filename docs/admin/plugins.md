# Plugins

Administration ▸ Plugins (permission 14) is a KASM-style marketplace: catalog
merged from every enabled **registry**, plugins install with one click.

## Security model: read this first

**A plugin runs with the same privileges as the application itself.** No
sandbox. Mitigations:

- Installation and every lifecycle action require permission 14.
- The registry's SHA-256 is **mandatory** and verified before extraction;
  a corrupted or tampered archive is refused.
- Archives are checked for path traversal (`..`, absolute paths, drive
  letters) and archive-bomb caps (≤ 20 MB zip, ≤ 500 entries, ≤ 50 MB
  uncompressed).
- The manifest must match the catalog slug and support the running app
  version (`min_app_version`).
- A plugin that crashes at boot is skipped, logged, and listed on the
  Plugins page (can't take the app down).

Only add registries you trust: **a compromised registry means remote code
execution**. The official registry ships enabled and cannot be removed.

Each registry carries two escape hatches, **on by default**, badged red when
disabled (every disable confirmed and audited):

- **Vérification SSL**: off only behind an intercepting corporate proxy or
  for a dev registry with a self-signed certificate.
- **Vérification SHA-256**: off only for a registry you control whose
  catalog doesn't publish checksums; archives then install unverified.

## Registries

A registry is a URL serving a `registry.json`:

```json
{
  "name": "Dépôt officiel OpenBrigade",
  "plugins": [
    {
      "slug": "animaux",
      "name": "Animaux",
      "version": "1.0.0",
      "description": "Gestion des chiens de recherche et autres animaux.",
      "download_url": "https://github.com/NewhostGH/openbrigade-plugins/releases/download/animaux-1.0.0/animaux.zip",
      "sha256": "…",
      "min_app_version": "6.0.0",
      "max_app_version": "6.999",
      "author": "OpenBrigade",
      "category": "Opérationnel",
      "icon": "https://…/animaux-icon.png",
      "screenshots": ["https://…/animaux-1.png", "https://…/animaux-2.png"],
      "homepage": "https://github.com/NewhostGH/openbrigade-plugins"
    }
  ]
}
```

`author`, `category`, `icon`, `screenshots` (≤ 8) and `homepage` are optional
presentation fields (category filter, search, icon cards, detail sheet).
Icon/screenshot/homepage values must be http(s) URLs; anything else is
dropped.

### Compatibility window and parallel tracks

Every entry declares `min_app_version` (required) and `max_app_version`
(optional, inclusive). **Without a cap, a plugin claims compatibility with
every future OpenBrigade version**: publish one (e.g. `6.999`) as soon as you
know a major breaks you.

The **same slug may appear several times** in a registry (one entry per
track), so an author can maintain a 6.x line and a 7.x line in parallel:

```json
{ "slug": "animaux", "version": "1.8.2", "min_app_version": "6.0.0", "max_app_version": "6.999", … }
{ "slug": "animaux", "version": "2.1.0", "min_app_version": "7.0.0", … }
```

Each install sees, per slug, the **best version compatible with its own app
version** (updates only offered within a compatible track); when no track
fits, the plugin shows « Incompatible » with its required range. The install
pipeline re-checks the window against the shipped `plugin.json`; plugins
whose window no longer contains the running version after an app upgrade are
badged on the page.

Catalogs from every enabled registry are merged (first registry to publish a
slug owns all its tracks), cached one hour per registry (« Actualiser »
forces a refresh); a broken registry only degrades itself. Host your own by
duplicating the official repo and adding its raw `registry.json` URL from
the « Dépôts » panel.

## Plugin packages

A package is a zip containing, at its root (or inside a single top-level
directory, the GitHub release-archive layout):

```dir
plugin.json
src/…                    ← PSR-4 code (declared in "autoload")
database/migrations/…    ← optional, run on enable
```

`plugin.json`:

```json
{
  "name": "Animaux",
  "slug": "animaux",
  "version": "1.0.0",
  "description": "…",
  "min_app_version": "6.0.0",
  "provider": "ObPlugin\\Animaux\\AnimauxServiceProvider",
  "authors": ["OpenBrigade"],
  "autoload": { "ObPlugin\\Animaux\\": "src" }
}
```

The `provider` is a standard Laravel service provider (routes, views,
migrations, translations, nav entries). Installed plugins live under
`plugins/<slug>` (gitignored).

## Database tables

A plugin can create its own tables: ship standard Laravel migrations under
`database/migrations/`, and they run automatically when the plugin is
**enabled** (`migrate --path=…`, recorded in the app's `migrations` table).
Disabling never rolls them back, so data survives a disable/enable cycle; a
clean uninstall should be documented (or ship an artisan command for it).

## Naming conventions

Everything a plugin creates in shared namespaces carries three markers:
**`ob`**, **`plugin`**, and the **plugin name**, so artifacts can't collide
with the core app, each other, or be mistaken for core code:

| Artefact | Convention | Example (slug `animaux`) |
| --- | --- | --- |
| PHP namespace / classes | `ObPlugin\<StudlyName>\…` | `ObPlugin\Animaux\AnimauxServiceProvider` |
| Database tables | `ob_plugin_<slug>_<table>` | `ob_plugin_animaux_dossier` |
| Migration files | `…_ob_plugin_<slug>_<change>.php` | `2026_08_01_000000_create_ob_plugin_animaux_tables.php` |
| Route names | `ob-plugin.<slug>.…` | `ob-plugin.animaux.index` |
| URL prefix | `/plugins/<slug>/…` | `/plugins/animaux` |
| View / translation namespaces | `ob-plugin-<slug>::…` | `view('ob-plugin-animaux::index')` |
| Config keys | `ob-plugin-<slug>.…` | `config('ob-plugin-animaux.enabled')` |
| Cache keys / jobs / commands | `ob:plugin:<slug>:…` | `php artisan ob:plugin:animaux:sync` |

The registry pipeline does not (yet) enforce these: they are the review
bar for the official registry and the expectation for third-party ones.

## Lifecycle

| Action                    | Effect                                                                |
| ------------------------- | ---------------------------------------------------------------------- |
| Installer / Mettre à jour | download → verify → extract → validate → `plugins/<slug>`              |
| Activer                   | runs the plugin's migrations, then boots it on every request           |
| Désactiver                | stops booting it (**migrations are not rolled back**, no data loss)    |
| Désinstaller              | removes the files and the registration (disabled plugins only)         |

An **update** appears when a registry publishes a higher `version` (catalogs
cache 1 h; « Actualiser » forces a refresh). It replaces the plugin directory
wholesale, keeps the enabled state, and runs its **new** migrations
immediately when enabled, so the new version is live on the next request.

To write your own plugin, see [docs/dev/plugins.md](../dev/plugins.md).
