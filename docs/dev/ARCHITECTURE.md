# Architecture & Project Structure

**The single source of truth for where things live.** OpenBrigade is a standard
Laravel 12 application. Legacy eBrigade pages are being migrated into it menu by menu;
until a page is ported it is served through the legacy bridge (see §Legacy bridge).

Companion docs: [CONVENTIONS.md](CONVENTIONS.md) (how to write code),
[DEVELOPMENT.md](DEVELOPMENT.md) (how to run it).

---

## Top-level layout

```files
app/            Application code (HTTP, models, services, console)
bootstrap/      Framework bootstrap (app.php, cache)
config/         Configuration (see below)
database/       Migrations, seeders, factories, legacy baseline
docs/           Documentation (dev/, admin/, user/)
public/         Web root; Vite build output in public/build/
resources/      Blade views, CSS and JS source (bundled by Vite)
routes/         web.php, api.php, console.php, web_legacy_bridge.php
storage/        Logs, cache, sessions, uploaded files, DB backups
tests/          Pest tests (Feature/, Unit/)
archive/        Frozen legacy eBrigade app (archive/legacy_app/) — read-only
.github/        CI, issue/PR templates, CONTRIBUTING, TODO
```

## `app/`

```files
app/
├── Console/Commands/      Artisan commands (e.g. RunAutomaticBackup)
├── Exceptions/            Custom exceptions / handler
├── Http/
│   ├── Controllers/       Request handlers, one per domain (PersonnelController, …)
│   │   └── Legacy/        LegacyBridgeController — serves not-yet-migrated pages
│   ├── Middleware/        e.g. permission middleware
│   └── Requests/          Form Request validation (e.g. Auth/LoginRequest)
├── Models/
│   ├── Concerns/          Shared traits (HasAvatar) for models on the same table
│   └── Pivots/            Custom pivot models (EvenementParticipation)
├── Providers/             Service providers
└── Services/              Business logic layer (see below)
```

### Layer responsibilities

- **Controllers** — thin: validate, delegate to a service/model, return a view or
  redirect. No raw SQL, no business logic (see [CONVENTIONS.md](CONVENTIONS.md) §3).
- **Services** (`app/Services/`) — reusable business logic; implement
  `ServiceInterface` where appropriate.

  | Service                  | Responsibility                                                                                                                             |
  | ------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------ |
  | `Auth/AuthService`       | Login against `pompier`, legacy-hash upgrade                                                                                               |
  | `Auth/LdapAuthService`   | LDAP authentication delegation (see [../security/ldap.md](../security/ldap.md))                                                            |
  | `NavigationService`      | Render `config/navigation.php` with permission + feature filtering                                                                         |
  | `FeatureService`         | Read/toggle feature-module flags (`ob_feature`), kept in sync with legacy `configuration`                                                  |
  | `DashboardService`       | Aggregate dashboard widget data                                                                                                            |
  | `BrigadeService`         | Brigade identity / global settings                                                                                                         |
  | `PermissionResolver`     | Section-scoped, ceiling-based permission resolution (see CONVENTIONS §9)                                                                   |
  | `SectionScopeService`    | Data-isolation authority for `multi_site` — per-request visible section set (see CONVENTIONS §10)                                          |
  | `PasswordPolicyService`  | Per-group password policy (length, expiry, attempts, blocklist) — see [../security/password-policies.md](../security/password-policies.md) |
  | `DocumentService`        | Document library — folders/files for a section (uses `DocumentAclService`)                                                                 |
  | `DocumentAclService`     | Per-folder document ACL resolution (principal sets + ancestor chain)                                                                       |
  | `NotificationService`    | Plain-text email dispatch, honouring the `mail_allowed` flag                                                                               |
  | `TableExportService`     | Universal XLSX / CSV export                                                                                                                |
  | `ICalExportService`      | iCal export                                                                                                                                |
  | `PersonnelExportService` | vCard / PDF data (livret, carte adhérent — rendered client-side with pdf-lib + section letterhead)                                         |

- **Models** — Eloquent; derived values live here as accessors. Two models may map to
  the same legacy table — shared behaviour goes in a `Concerns/` trait.

## `config/`

| File                                                      | Purpose                                                                                           |
| --------------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| `app.php`                                                 | Application identity (name, env, debug, URL)                                                      |
| `auth.php`                                                | Auth guards/providers — `users` provider → `App\Models\User` (table `pompier`)                    |
| `fortify.php`                                             | Laravel Fortify config — enables TOTP two-factor (see [../security/totp.md](../security/totp.md)) |
| `ldap.php`                                                | LDAP connections + auth settings (see [../security/ldap.md](../security/ldap.md))                 |
| `habilitations.php`                                       | Obsolete-feature list for the permission matrices (SSOT for those)                                |
| `documents.php`                                           | Document library — storage subpath, supported extensions, size cap, feature IDs                   |
| `brigade.php`                                             | Brigade-specific settings (version, features)                                                     |
| `personnel.php`                                           | Personnel lookup maps (statuts, badges, civilités) — SSOT for those                               |
| `navigation.php`                                          | Top-level menu definition (rendered by `NavigationService`)                                       |
| `legacy_bridge.php`                                       | Which legacy `.php` pages the bridge still serves                                                 |
| `backup.php`                                              | Backup destination, schedule, retention                                                           |
| `database.php`                                            | Connections (default + optional `legacy` for parity validation)                                   |
| `cache.php` / `queue.php` / `session.php` / `logging.php` | Framework subsystems                                                                              |

> New lookup maps and external URLs belong in `config/` (SSOT, rule 1) — never inline.

## `database/`

```files
database/
├── migrations/
│   ├── 2026_05_06_..._migrate_legacy_5_5_to_openbrigade_6_0_0.php   Baseline import
│   ├── 2026_05_07_..._create_sessions_table.php
│   ├── 2026_05_12_..._convert_database_to_utf8.php
│   ├── 2026_05_28_..._create_user_shortcuts_table.php  (ob_user_shortcuts)
│   ├── 2026_06_04_..._alter_qualification_q_val_to_varchar.php
│   ├── 2026_06_08_..._create_ob_backup_settings_table.php
│   ├── 2026_06_08_..._create_ob_habilitation_tables.php + ..._backfill_ob_habilitations_from_legacy.php
│   ├── 2026_06_09_..._create_ob_personnel_assignments.php + ..._backfill_ob_personnel_assignments.php
│   ├── 2026_06_09_..._add_urgence_person_id_to_pompier.php
│   ├── 2026_06_09_..._create_ob_feature.php + ..._backfill_ob_feature_from_configuration.php
│   ├── 2026_06_11_..._create_ob_dashboard_layout_table.php + ..._extend_ob_acl.php
│   ├── 2026_06_12_..._create_ob_document_acl.php
│   ├── 2026_06_13_..._create_ob_password_policy_table.php
│   ├── 2026_06_13_..._add_totp_support.php
│   ├── 2026_06_13_..._create_ldap_domains.php
│   └── legacy/
│       └── reference.sql        Legacy schema reference (parity validation source)
├── seeders/                     DatabaseSeeder, DevelopmentDataSeeder
└── factories/                   Model factories
```

- The schema is **forward-only** after the baseline. Native tables are prefixed `ob_`
  (rule 2); legacy tables keep their original names.
- See [../admin/database-migration.md](../admin/database-migration.md) for the
  migration workflow and `legacy:migration:validate` parity command.

## `routes/`

| File                    | Responses                                                          |
| ----------------------- | ------------------------------------------------------------------ |
| `web.php`               | Native HTML routes (the migrated app)                              |
| `api.php`               | JSON API routes                                                    |
| `console.php`           | Closure-based Artisan commands (incl. `legacy:migration:validate`) |
| `web_legacy_bridge.php` | Bridge routes for pages not yet migrated                           |

## `resources/`

```files
resources/
├── views/
│   ├── layout/          app, navbar, sidebar shells
│   ├── components/      ob-breadcrumb, ob-toolbar, ob-commandbar, ob-table
│   ├── <module>/        One English folder per domain (personnel, event, vehicle, …; see CONVENTIONS §11)
│   └── admin/           Settings, parametrage, habilitations, backup, maintenance
├── css/                 app.css import hub + per-module ob-*.css (Vite)
└── js/                  app.js global entry + per-page ob-*.js (Vite)
```

Asset structure and the build pipeline are documented in
[DEVELOPMENT.md](DEVELOPMENT.md) §5.

## `storage/`

- `logs/` — application logs
- `framework/` — cache, sessions, compiled views
- `app/backups/` — database dumps written by the backup feature
- `app/private/documents/{S_ID}/{DF_ID}/` — document-library files (auth-gated via `DocumentController`)
- `app/private/trombi/` — personnel portrait photos (auth-gated via `PersonnelController::photo()`)
- `app/private/sections/{S_ID}/pdf/` — section letterhead PDFs (auth-gated via `OrganizationController`)
- `app/private/sections/{S_ID}/images/` — section badge images (auth-gated via `OrganizationController`)
- `app/photos/{S_ID}/{album_id}/` — photo-album images (auth-gated via `PhotoController::photoServe()`)

### Public vs private storage policy

**`public/`** must only contain files that belong to the application itself and carry no
expectation of privacy — UI assets, default avatars, app-bundled PDFs:

| Path             | Contents                                                               |
| ---------------- | ---------------------------------------------------------------------- |
| `public/build/`  | Vite-compiled JS/CSS bundles                                           |
| `public/images/` | App UI images (`boy.png`, `girl.png`, `autre.png`, grade icons, theme) |
| `public/pdf/`    | App-bundled PDF templates (`pdf_page.pdf`)                             |

**Everything user-uploaded goes into `storage/app/private/…`** and is served exclusively
through an authenticated controller action. Direct URL guessing must not grant access to
any user data. Run `php artisan storage:migrate` (see `MigrateStorage` command) to
relocate files from legacy public paths into the canonical private tree.

---

## Feature flags & gating

Optional capabilities (Véhicules, Matériel, Cotisations, Cartographie, Animaux, …)
are switched on/off from one place. The two legacy `configuration` buckets —
TAB 1 "Fonctionnalités" and TAB 6 "Modules" — are unified into the native
`ob_feature` registry.

- **`ob_feature`** (one row per capability): `key` (mirrors `configuration.NAME`),
  `name`, `category` (`fonctionnalite` | `module`), `status` (`native` | `wip`),
  `enabled`, `legacy_config_id`. Back-filled from `configuration`; toggles are
  written back to the legacy row so un-migrated code keeps working.
- **`FeatureService`** — per-request-cached `isEnabled(key)` / `all()` /
  `setEnabled()`. The single read/write point; never query `ob_feature` directly.
- **Route gate** — `Route::…->middleware('feature:vehicules')` (alias
  `RequireFeature`) responds **404** when the flag is off, so a disabled screen
  behaves as if it does not exist.
- **Nav gate** — add `'feature' => 'key'` to a `config/navigation.php` group or
  item and `NavigationService` hides it when the flag is off.
- **Admin UI** — Administration ▸ **Fonctionnalités** (`/admin/fonctionnalites`)
  lists every capability with a toggle; `status = wip` (not yet migrated, e.g.
  Animaux) shows a **WIP** marker but remains toggleable. Administration ▸
  **Plugins** (`/admin/plugins`) is a WIP placeholder for a future community
  marketplace.

To gate a newly-migrated screen: add `feature:<key>` to its route(s) and a
`'feature' => '<key>'` hook to its nav entry, then flip the row to `status = native`.

---

## Legacy bridge

Until every menu is migrated, the legacy eBrigade app is kept available read-only
under `archive/legacy_app/` and reached through:

- `routes/web_legacy_bridge.php` — `/legacy/...` route entries
- `app/Http/Controllers/Legacy/LegacyBridgeController.php` — executes the legacy script
- `config/legacy_bridge.php` — the allow-list of bridged pages

When a page is migrated, its route moves to `web.php`, its bridge entry is removed,
and any remaining references are flagged per [CONVENTIONS.md](CONVENTIONS.md) §7. The
end state (Phase 4) deletes `archive/legacy_app/` and the bridge entirely. Migration
status is tracked in [TODO.md](../../.github/TODO.md); the full file map is in
[legacy-mapping.md](legacy-mapping.md).

---

## See also

- [CONVENTIONS.md](CONVENTIONS.md) — binding coding rules and UI patterns
- [DEVELOPMENT.md](DEVELOPMENT.md) — setup, database, auth, assets, tooling
- [legacy-mapping.md](legacy-mapping.md) — legacy file → new implementation map
