# Engineering Conventions

**The single source of truth for how code is written in OpenBrigade.** These rules
are binding for all migrated and new code. They exist so the codebase stays
maintainable as legacy eBrigade pages are ported page by page.

`tests/Feature/ConventionsTest.php` enforces the most mechanical rules in CI — it
fails the build if you break them. Run `composer test` before pushing.

Companion docs: [ARCHITECTURE.md](ARCHITECTURE.md) (where things live),
[DEVELOPMENT.md](DEVELOPMENT.md) (how to run it), [TODO.md](../../.github/TODO.md)
(migration tracker).

---

## 1. Single Source of Truth (SSOT)

A value or rule is defined in exactly **one** place. Never copy logic between a
controller, a service, and a Blade view.

| What                                                                            | Where it belongs                                                              |
| ------------------------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| Derived values (avatar URL, full name, état/status label, age, net total)       | A method or accessor on the Eloquent model (e.g. `Personnel::getAvatarUrl()`) |
| Lookup / label / badge maps (status → label+class, civility → prefix)           | A `config/` file (e.g. `config/personnel.php`), referenced everywhere         |
| External URLs (third-party services, CDNs, map tiles, API endpoints, doc links) | A `config/` file, read via `config('...')` — never hardcoded in PHP/Blade/JS  |
| Business logic (sums, filters, eligibility rules, query shaping)                | A service (`app/Services/`) or the model — never a Blade view                 |
| Raw DB rows needing a derived value                                             | The shared model accessor or a service helper — never a second inline copy    |
| Column / field definitions (list columns, export field lists)                   | One definition reused by both the list view and the export                    |

**Raw rows are not exempt.** If a query builder returns `stdClass` rows that need a
derived value, either return Eloquent models (so the accessor is available) or put
the derivation in a shared service method called from both paths.

## 2. Models

- **One model per table** is the goal.
- Where two models intentionally map to the same table (`User` = auth concerns,
  `Personnel` = domain concerns, both on `pompier`), shared behaviour **MUST** live
  in a trait (e.g. `app/Models/Concerns/HasAvatar.php`) used by both — never
  copy-pasted. Document the split at the top of each model.
- Casts, accessors, and shared scopes that apply to the underlying table belong in
  the shared trait, not in only one of the two models.

### New database tables

- Tables introduced for **native OpenBrigade features** (not part of the migrated
  legacy schema) **MUST** be prefixed `ob_` (e.g. `ob_backup_settings`,
  `ob_user_shortcuts`). The prefix makes it obvious — in migrations, models, and raw
  queries — which tables are native vs inherited from the legacy `eBrigade` schema.
- Legacy tables (`pompier`, `configuration`, `personnel_cotisation`, …) keep their
  original names. Never rename them or add the `ob_` prefix retroactively.
- Declare `protected $table = 'ob_...'` explicitly on the model — Eloquent's
  pluralized-class-name guess won't include the prefix.

## 3. Controllers & services

- **No raw SQL in controllers.** Use Eloquent or the Query Builder, and put any
  non-trivial business logic in a service under `app/Services/`.
- SQL uses parameterised bindings — never string-interpolate user input.
- Services that encapsulate a reusable operation implement
  `App\Services\ServiceInterface`.
- Controllers stay thin: validate (via a Form Request where input is non-trivial),
  call a service/model, return a view or redirect.

## 4. Blade views

- **Minimal PHP.** No business logic, no DB access, no array/map declarations in
  `@php` blocks. Presentation data (nav arrays, badge maps) comes from the controller
  or a view composer. A `@php` block, if truly unavoidable, is a couple of trivial
  presentation lines.
- **No `<style>` blocks and no `@push('styles')` with inline CSS.** All CSS lives in
  `resources/css/<module>.css` and is bundled by Vite.
- **No inline `<script>` with logic** where a module JS file fits.

## 5. CSS / JS naming

- **Every custom class and id uses the `ob-` prefix**, with a module sub-namespace.
  Bootstrap utility classes are used as-is; only *our* classes get the prefix.

  | Scope                | Prefix        | Example             |
  | -------------------- | ------------- | ------------------- |
  | Dashboard-specific   | `ob-dash-*`   | `ob-dash-stat-tile` |
  | Reusable widget card | `ob-widget-*` | `ob-widget-card`    |
  | Sidebar / navbar     | `ob-*`        | `ob-navbar-lateral` |
  | Login page           | `ob-login-*`  | `ob-login-card`     |
  | Personnel module     | `ob-pers-*`   | `ob-pers-sidenav`   |

- CSS **design tokens** (`--sidebar-*`, `--siglet-*`, `--font-size-*`) are not classes
  — do not rename or prefix them.
- One CSS file and one JS file per module under `resources/css/` and `resources/js/`.

### Migrating a module's CSS to the prefix

Use a collision-safe Perl one-liner; the lookbehind/lookahead prevents
double-prefixing (`ob-ob-*`) and protects design tokens:

```bash
perl -i -pe 's/(?<![a-zA-Z0-9_\-])(OLD_CLASS)(?![a-zA-Z0-9_\-])/ob-new-class/g' file...
```

The lookbehind does **not** exclude `$`, so a JS variable sharing a token name (e.g.
jQuery `$siglet`) can be wrongly renamed. **Always run `npm run build` after each
module** — Vite catches these; fix by reverting just the variable name.

## 6. Exports

- **XLSX / CSV** exports **MUST** use `App\Services\TableExportService`
  (`toXlsx()` / `toCsv()`). Never instantiate `PhpSpreadsheet` directly in a
  controller.
- **iCal** exports **MUST** use `App\Services\ICalExportService` (`toResponse()`).
  Never instantiate `Sabre\VObject` directly.
- Export column definitions follow the flat `[[label, getter], …]` format understood
  by `TableExportService`. Define columns once and reuse them for both the list view
  and the export (rule 1).

## 7. Legacy references must be flagged

The legacy app is still reachable through the bridge
(`routes/web_legacy_bridge.php` + `LegacyBridgeController` + `config/legacy_bridge.php`)
during migration. Any link, asset path, or redirect that points at it
(`/legacy/...`, `*.php?...`, `archive/legacy_app/...`) **MUST** carry a marker comment
on the same or preceding line:

- Blade: `{{-- TODO: Migrate code --}}`
- PHP / JS: `// TODO: Migrate code`

This keeps every remaining legacy coupling greppable — it is the work list for Phase 4
decommission:

```bash
grep -rn "TODO: Migrate code" resources/ app/
```

A legacy URL **without** a `/legacy/` prefix (e.g. `url('/ins_personnel.php')`) is a
**routing bug**, not a bridge — fix the route. When a native route already exists for
a legacy destination, use `route()` instead of `url('/legacy/...')`.

### Quick reference — legacy URL → native route

| Legacy URL                               | Native route                                                    |
| ---------------------------------------- | --------------------------------------------------------------- |
| `evenement_display.php?evenement={code}` | `route('evenement.show', $code)`                                |
| `evenement_choice.php`                   | `route('evenement.index')`                                      |
| `personnel.php`                          | `route('personnel.index')`                                      |
| `upd_personnel.php?pompier={id}`         | `route('personnel.show', $id)` / `route('personnel.edit', $id)` |
| `upd_personnel.php?tab=2` (formations)   | `route('personnel.qualifications', $id)`                        |
| `vehicule.php`                           | `route('vehicule.index')`                                       |
| `consommable.php`                        | `route('consommable.index')`                                    |
| `remplacements.php`                      | `route('remplacement.index')`                                   |
| `tableau_garde.php`                      | `route('garde.index')`                                          |
| `message.php`                            | `route('message.index')`                                        |
| `documents.php`                          | `route('document.index')`                                       |
| `upd_document.php` / `save_documents.php`| `route('document.index')` (upload/edit modals)                  |
| `showfile.php` (library doc)             | `route('document.download', $id)`                               |
| `bilans.php`                             | `route('statistique.index')`                                    |

The complete legacy-file → new-implementation map is in
[legacy-mapping.md](legacy-mapping.md).

---

## 8. UI component patterns

New list and detail pages are built from the reusable `ob-*` component set. Follow
these patterns rather than hand-rolling markup.

### Component inventory

| Component   | Tag / class                          | File                                                 |
| ----------- | ------------------------------------ | ---------------------------------------------------- |
| Breadcrumb  | `<x-ob-breadcrumb>`                  | `resources/views/components/ob-breadcrumb.blade.php` |
| Toolbar     | `<x-ob-toolbar>`                     | `resources/views/components/ob-toolbar.blade.php`    |
| Command bar | `<x-ob-commandbar>`                  | `resources/views/components/ob-commandbar.blade.php` |
| Table       | `<x-ob-table>`                       | `resources/views/components/ob-table.blade.php`      |
| Section select | `<x-ob-section-select>`           | `resources/views/components/ob-section-select.blade.php` (see §10) |
| Badge       | `<span class="ob-badge ob-badge-*">` | `resources/css/ob-badge.css`                         |
| Widget card | `<div class="ob-widget-card">`       | `resources/css/ob-components.css`                    |
| Avatar      | `Personnel::getAvatarUrl()`          | `app/Models/Concerns/HasAvatar.php`                  |

### List page skeleton

```blade
@extends('layout.app')

<x-ob-breadcrumb :items="[['label' => 'Foos', 'url' => route('foo.index')], ['label' => 'Foos']]"/>

<x-ob-toolbar title="Foos" :total="$items->total()" filter-action="{{ route('foo.index') }}"
    filter-id="filterForm" :columns="$columns" table-id="fooTable">
    <a href="{{ route('foo.create') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau
    </a>
    <x-slot:filters>
        <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm"
               placeholder="Rechercher…" data-ob-search="filterForm">
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="fooTable" :total="$items->total()" total-label="foo">
    <x-ob-table :columns="$columns" :items="$items" table-id="fooTable" route-show="foo.show"/>
</x-ob-commandbar>
```

Controller side — `$columns` is defined once and reused for the export (rule 1 + 6):

```php
private function fooColumns(): array
{
    return [
        ['key' => 'name',   'label' => 'Nom',    'type' => 'text',
         'value' => fn ($r) => $r->F_NAME,        'exportable' => true],
        ['key' => 'status', 'label' => 'Statut',  'type' => 'badge',
         'value' => fn ($r) => $r->F_STATUS,
         'badgeMap' => ['A' => ['Actif', 'ob-badge-actif']],
         'exportable' => true],
    ];
}
```

### Detail page rules

- **Header card first** — title + status badge + action buttons, then stacked
  `ob-widget-card` sections.
- **`dl` grid for key-value pairs** — `grid-template-columns: auto 1fr`,
  `gap: 5px 16px`, `font-size: var(--font-size-sm)`; `<dt>` gets `text-muted fw-normal`.
- **`ob-widget-empty`** for empty-state paragraphs inside a card body.
- **`p-0` on `ob-widget-card-body`** when the body is a bare table.
- **Flash messages** rendered right after the breadcrumb with
  `alert-dismissible fade show`.
- For 4+ sections, add a sticky subnav anchor strip mirroring `ob-pers-sidenav` in
  `personnel/show.blade.php`.
- Inline create/edit uses Bootstrap modals (same pattern as the qualification /
  cotisation modals on `personnel/show.blade.php`).

### Badge classes

| Class                                      | Meaning                             | Colour                        |
| ------------------------------------------ | ----------------------------------- | ----------------------------- |
| `ob-badge-actif`                           | Active / open                       | Green                         |
| `ob-badge-archive`                         | Archived / neutral chip             | Grey                          |
| `ob-badge-bloqued`                         | Blocked / cancelled                 | Red                           |
| `ob-badge-int` / `-ben` / `-ext` / `-pres` | Personnel statut (INT/BEN/EXT/PRES) | Blue / Teal / Orange / Purple |

Prefer the model accessor over a hardcoded class:

```blade
<span class="ob-badge {{ $personnel->statutBadgeClass() }}">{{ $personnel->statutBadgeLabel() }}</span>
```

### Routes (CRUD pattern)

```php
Route::get('/foos',        [FooController::class, 'index'])->name('foo.index');
Route::get('/foos/create', [FooController::class, 'create'])->name('foo.create'); // BEFORE resource
Route::post('/foos',       [FooController::class, 'store'])->name('foo.store');
Route::resource('foos', FooController::class)->only(['show', 'edit', 'update', 'destroy']);
```

> **Critical:** declare static-segment routes (`/foos/create`) **before**
> `Route::resource()`, or Laravel matches `create` as the `{foo}` parameter.

## 9. Permission checks (section-scoped, full ACL)

Permissions are a **section-scoped ACL with explicit allow *and* deny** at every
tier. A feature (`F_ID`) is decided for a user in a section by the first matching
rule — most specific wins (see `PermissionResolver`):

| # | Rule                                                   | Source table             | Result |
| - | ------------------------------------------------------ | ------------------------ | ------ |
| 1 | per-person **deny**                                    | `ob_user_permission`     | DENY   |
| 2 | per-person **allow**                                   | `ob_user_permission`     | ALLOW  |
| 3 | section ceiling **deny** (any section in the chain)    | `ob_section_permission`  | DENY   |
| 4 | group/role **deny** (any held group/role)              | `ob_group_permission`    | DENY   |
| 5 | group/role **allow** (any held group/role)             | `ob_group_permission`    | ALLOW  |
| 6 | nothing grants it                                      | —                        | DENY   |

A row is "in scope" when its `section_id ≤ 0` (global, inherited everywhere) or it
names a section in the active chain (section + ancestors). Global groups
(`ob_personnel_group`) always apply. Within a tier, **deny wins**. The model
defaults keep this backwards compatible: `ob_group_permission.effect` defaults to
`allow` and `ob_user_permission` is empty, so with legacy data the cascade reduces
to "section deny, then group/role allow."

Permissions are resolved through one path only — `App\Services\PermissionResolver`,
reached via the model helper:

```php
auth()->user()->hasPermission($fid);                  // active section (session ‹hab.section›, default home)
auth()->user()->hasPermissionInSection($fid, $sId);   // explicit section, ignores active-role filter
Gate::allows('feature', $fid);                          // same, through the Gate
->middleware('permission:'.$fid)                         // RequirePermission middleware, same path
```

Rules:

- **Never decide access from a raw table query.** Do not `DB::table('habilitation')`,
  `ob_group_permission`, `ob_section_permission`, `ob_user_permission`,
  `section_role`, or read `pompier.GP_ID` to gate a feature — call `hasPermission()`.
  The resolver already composes the full cascade above (per-person overrides +
  global groups + section roles + the section deny-list, parent caps child).
- **The admin matrices write allow/deny, never just "on/off".** Editing a grant
  sets `effect = allow|deny` or removes the row (neutral). The fourth tab,
  **Dérogations**, edits `ob_user_permission` (per-person overrides, section-scoped).
- **Menus and nav** gate every item with `hasPermission()` (see `NavigationService`,
  `RequirePermission`, the navbar quick-add). New menus must do the same.
- **Reading org structure for display** (e.g. "who holds a role in section X") is the
  one allowed direct use, and it must read the **new** tables: `ob_user_assignment`
  (role memberships) joined to `ob_group` / `groupe` — never `section_role`, which is
  legacy reference data no longer written to.
- **Assigning** a member: both global groups (`pompier.GP_ID` / `GP_ID2`,
  `ObGroup::groups()`) and section roles (`ob_user_assignment`) are edited on the
  member's CRUD form, "Accès" tab, and persisted on save — the role editor is only
  rendered/honoured for users with F_ID 9 (see `PersonnelController::syncRoles`). A
  member belongs to multiple sections through these role rows. The admin matrices
  live under `admin.habilitations` (tabs Plafonds · Groupes · Rôles).

See [project_habilitations memory] and `tests/Unit/PermissionResolverTest.php` for the
resolution algorithm and worked examples.

**Permissions vs. feature flags.** `hasPermission()` answers *"may this user do
X?"*; feature flags answer *"is capability X switched on for this brigade?"* They
are orthogonal and a gated screen needs **both**. Feature flags go through one
path only — `App\Services\FeatureService` (the `ob_feature` registry), never a raw
`configuration` / `ob_feature` query:

```php
app(FeatureService::class)->isEnabled('vehicules');     // read a flag
->middleware('feature:vehicules')                        // RequireFeature gate (404 when off)
'feature' => 'vehicules'                                 // nav hook (NavigationService hides it)
```

Toggle flags only via `FeatureService::setEnabled()` (it keeps the legacy
`configuration` row in sync). See [ARCHITECTURE.md](ARCHITECTURE.md) §"Feature
flags & gating".

---

## 10. Data isolation by section (multi_site)

**Single authority:** `App\Services\SectionScopeService` is the sole arbiter of what
data a user may see or edit, when the `multi_site` feature flag is on. It computes
one **visible set** per request:

```equation
visible = (user's member sections + descendants)  ∩  (navbar-chosen section + descendants)
```

A user is a member of a section via:

- `pompier.P_SECTION` (principal/home section, required, always included)
- `ob_personnel_section` (additional memberships)
- `ob_user_assignment` scoped to a `section_id` (role assignment sections)

The **navbar section switcher** shows the base set (membership + descendants, never
narrowed by the current choice — otherwise switching sideways becomes impossible).
It uses `switcherSections()` which returns objects in **org-chart order** (`S_ORDER`,
`S_CODE`), so the tree structure itself determines rank, not alphabetical luck.

The **active operation scope** (`activeOperationScope()`) is what's actually filtered
when viewing or editing data. It intersects the base set with the navbar choice,
returning `null` when unrestricted (multi_site off).

**Controllers:** In every action that lists or saves data tied to a section, use
`SectionScopeService` to enforce isolation:

```php
// Listing — data isolation
app(SectionScopeService::class)->apply($query, 'column_name', $requestedFilter, $subsections);

// Creating — default section  
$validated['S_ID'] = app(SectionScopeService::class)->defaultSectionId();

// Editing — prevent out-of-scope assignment
$validated['S_ID'] = app(SectionScopeService::class)->coerce((int) $validated['S_ID']);

// Navbar switch — validate the chosen section is in the user's base set
if (!app(SectionScopeService::class)->canChoose((int) $sId)) abort(403);
```

**Section hierarchy:** Use `S_PARENT = 0` (or NULL, deprecated) for root sections.
When querying or displaying sections, always use:

```php
->orderBy('S_ORDER')      // Explicit ordering first (applies within siblings)
->orderBy('S_CODE')       // Then alphabetically
```

This ensures the org chart root (CIS or your top-level section) appears first,
not last, and users see the intended hierarchy. `SectionScopeService::descendantIds()`
expands a section to itself + all descendants; use it to build subtrees for
scope checks or cycle prevention in section editor.

**Form membership multiselect:** When a user edits `ob_personnel_section` memberships,
only touch rows within the editor's visible scope. Out-of-scope rows are preserved
untouched — a narrowly-scoped editor cannot accidentally strip a member's
wider-scope memberships. Same pattern for `ob_user_assignment` role rows.

**Section dropdown component:** `<x-ob-section-select>` (class
`App\View\Components\ObSectionSelect`) renders nothing when multi_site is off and
self-feeds from `SectionScopeService` when it is on, so there's one rendering source
for every section select across forms and filters.

**Global groups do not widen visibility.** Global role assignments
(`ob_user_assignment` with `section_id = 0`) grant permissions but never grant data
access — only membership of a section itself does. The navbar and scope checking
never look at roles.

---

## See also

- [ARCHITECTURE.md](ARCHITECTURE.md) — directory structure and layer responsibilities
- [DEVELOPMENT.md](DEVELOPMENT.md) — running the app, assets, tooling
- [legacy-mapping.md](legacy-mapping.md) — full legacy → Laravel file map
- [TODO.md](../../.github/TODO.md) — migration strategy and per-menu tracker
- [CONTRIBUTING.md](../../.github/CONTRIBUTING.md) — branching, commits, PR process
