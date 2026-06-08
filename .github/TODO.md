# OpenBrigade Migration TODO

This file is the working tracker for migrating the legacy eBrigade app
(`archive/legacy_app/`) into the native Laravel application, menu by menu. It is the
operational companion to the reference docs — read the **Project instructions** below
before starting any task.

When you complete an item, tick its checkbox and append the commit subject in
parentheses. If a change touches behaviour described elsewhere, update the relevant
doc in the same change.

---

## Project instructions

A short working agreement for anyone (or anything) making changes in this repository.
These apply to every task, not just migration work.

1. **Read the rules first.** Before writing code, read
   [docs/dev/CONVENTIONS.md](../docs/dev/CONVENTIONS.md) (how code is written),
   [docs/dev/ARCHITECTURE.md](../docs/dev/ARCHITECTURE.md) (where things live), and
   [docs/dev/DEVELOPMENT.md](../docs/dev/DEVELOPMENT.md) (how to run it). Those are the
   single source of truth; this file does not restate their rules.
2. **One coherent change at a time.** Prefer finishing a single menu section or a
   single fix over spreading partial work across many. Keep commits small and focused.
3. **Reuse before adding.** Search for an existing model accessor, service, config
   entry, or `ob-*` component before writing a new one. Duplicated logic is a defect
   (SSOT, CONVENTIONS §1).
4. **Edit existing files in place.** Don't create parallel "v2" files; don't leave
   dead code behind.
5. **Keep the gates green.** Every change must pass:

   ```bash
   composer pint -- --test   # formatting
   composer analyse          # PHPStan / Larastan
   composer test             # Pest, including ConventionsTest
   ```

6. **Flag every remaining legacy reference** with a `TODO: Migrate code` marker
   (CONVENTIONS §7). Never point at a legacy `.php` page without the `/legacy/` prefix.
7. **Definition of done for a migrated page:** native route + controller + view, logic
   in a service/model, feature test covering the happy path and access control, output
   parity with the legacy page verified, legacy bridge entry removed, and the legacy
   file retired.
8. **Record progress here.** Tick the checkbox and add the commit subject. Update the
   file map in [docs/dev/legacy-mapping.md](../docs/dev/legacy-mapping.md) when a file
   moves from legacy to native.
9. **Follow the commit and branch conventions** in
   [CONTRIBUTING.md](CONTRIBUTING.md). Never commit directly to `main`.

---

## File migration strategy

Each menu section follows this repeatable process:

1. **Inventory** — list every `archive/legacy_app/` file in the menu (pages, save
   handlers, modals, XLS/PDF exports, JS helpers). Cross-check against
   [docs/dev/legacy-mapping.md](../docs/dev/legacy-mapping.md).
2. **Controller** — create/extend a controller under `app/Http/Controllers/`.
3. **Views** — create Blade views under `resources/views/<menu>/` using the `ob-*`
   component set (CONVENTIONS §8).
4. **Routes** — add named routes to `routes/web.php`; remove the matching entries from
   `routes/web_legacy_bridge.php` and `config/legacy_bridge.php`.
5. **Services / Models** — move business logic into a service or Eloquent model; no raw
   SQL in controllers.
6. **Tests** — add Pest feature tests (happy path + access control + key edge cases).
7. **Parity check** — verify the native page matches the legacy page (same data, same
   access rules).
8. **Retire legacy files** — once parity passes, delete the legacy files and update
   the file map.
9. **Modernize** — after cutover, improve UI/UX using the current component set.

---

## Phase 1 — Dashboard ✅

Replaced `index_d.php` with a native Laravel dashboard (widget architecture, 20
widgets). **Done.**

## Cross-cutting UI architecture ✅

- [x] Universal component system — `ob-breadcrumb`, `ob-toolbar`, `ob-table`,
  `ob-commandbar`, `ob-badge`, `ob-avatar`, `ob-toggle`; one CSS + JS file per module;
  `ObTable` ES6 class driven by `data-*` attributes (commit: feat: universal
  ob-component system)
- [x] `TableExportService` — universal XLS/CSV export, column-aware export URLs
- [x] Migrate all list pages to the `ob-*` component set (evenement, vehicule,
  matériel, consommable, company, astreintes, indispo, remplacement, monitoring,
  qualifications; breadcrumb on 14 further pages)
- [x] Migrate cotisations global page to `ob-toolbar` + `ob-commandbar`
- [x] Convention enforcement — `docs/dev/CONVENTIONS.md` + `tests/Feature/ConventionsTest.php`

> The 2026-06-04 conventions remediation (SSOT avatar/maps, Blade PHP removal, CSS
> prefixing, legacy flagging) is complete. The binding rules now live in
> [docs/dev/CONVENTIONS.md](../docs/dev/CONVENTIONS.md); regressions are caught by
> `ConventionsTest`.

---

## Phase 2 — Menu by menu

Legend: `[x]` done · `[ ]` open. Commit subjects in parentheses.

### Personnel (PERSO) ✅
- [x] Member list, profile view/edit, create/add
- [x] Trombinoscope and org chart
- [x] Exports (XLS, CSV, vCard, PDF livret/carte)
- [x] Qualifications and training records (CRUD from profile)
- [x] On-call availability / indisponibility (`indispo*.php`, `dispo.php`)
- [x] Full list parity (bulk select, grade badges, section filter, column toggle, card/table view)
- [x] Universal search across all fields
- [x] Cotisations — per-member CRUD and org-wide page (batch save, Excel export, Prélèvements, Virements)
- [x] Géolocalisation — Leaflet map with GPS markers

### Activité — Events & Interventions (ACT)
- [x] Event list and detail view
- [x] Create / edit / save / delete
- [x] Participant management (inscription, equipes, renforts)
- [x] Material and vehicle assignment
- [x] Calendar view
- [x] Exports (XLS + iCal)
- [ ] Editable PDF for conventions
- [ ] Main courante (incident log)

### Garde — On-call roster (GAR)
- [x] Roster display and assignment
- [x] Guard sheet and replacement management
- [ ] Automatic piquet/guard generation
- [ ] Rest periods (`repos_*.php`)
- [ ] Guard exports (XLS, PDF)

### Planning (PLA)
- [x] Weekly/monthly planning view
- [x] Personal agenda (`myagenda.php`)
- [ ] Schedule (horaires) management
- [ ] Planning exports

### Client (CLI)
- [x] Company/client list and detail
- [ ] Billing and financial exports
- [ ] PDF attestations (fiscale, formation)

### Logistique — Vehicles (VEH)
- [x] Vehicle list, detail, CRUD
- [x] Vehicle type management
- [ ] Vehicle assignment to events
- [ ] Vehicle exports (XLS)

### Inventaire — Equipment & Consumables (MAT / CONSO)
- [x] Equipment list and detail/edit
- [x] Consumable stock management
- [x] Type management (matériel, consommable)
- [ ] Equipment category management
- [ ] Embarkation tracking (`materiel_embarquer.php`)
- [ ] Equipment/consumable exports (XLS)

### Communication (COMM)
- [x] Internal messaging and chat board
- [ ] Email composition and send (`mail_create.php`, `mail_send.php`)
- [ ] Alert creation and sending (`alerte_*.php`)
- [ ] SMS history view
- [ ] Push notification monitor

### Document (DOC)
- [x] Document and folder tree view
- [ ] Document upload and edit
- [ ] File serving and download
- [ ] Document exports (PDF)

### Statistique (STAT)
- [x] Participation and event statistics (charts)
- [ ] Financial reports (`report_cotisations.php`)
- [ ] Custom exports (XLS, TCD, HTML, TXT, SQL)

### Organisation (ORGA)
- [x] Section/unit management
- [ ] Group and role (habilitations) management — group × permission matrix done; rank/grade and poste pending
- [ ] Rank and grade management
- [ ] Position (poste) management
- [ ] Team (equipe) management

### Configuration — Admin (ADMIN)
- [x] Application settings — configuration CRUD (tabbed UI, toggle/select/file controls)
- [x] Parametrage reference tables — type-evenement/participation/materiel/consommable/vehicule CRUD
- [x] Theme and icon configuration — IS_FILE image upload + grade icon management
- [x] Audit log view
- [x] Backup and restore — mysqldump, list/download/delete/restore, retention prune
- [x] Maintenance page — system info + migration status (replaces `upgrade.php`)
- [x] Habilitations — group × permission matrix, access-group CRUD, system groups protected
- [ ] Add-on / module management — install/download from ebrigade.app not applicable to the fork; `nav.modules.list` still points at legacy `addons.php`
- [x] Add tests and parity check; retire migrated ADMIN legacy files —
  `tests/Feature/AdminTest.php` (auth, permission gating, view rendering, legacy
  redirects); migrated ADMIN bridge routes now redirect to native routes
  (configuration, save_configuration, configuration_theme, configuration_icone_grade,
  parametrage, habilitations + save/upd, audit, history, backup, upgrade).
  `paramfn`/`paramfnv` (billable + vehicle function params) and grade category CRUD
  stay on the legacy bridge — still WIP, tracked under ORGA

### Cross-cutting settings not yet wired (from settings annotations)
- [ ] `password_quality` (ID 15) — complexity validation not enforced in `AuthService`
- [ ] `password_expiry_days` (ID 70) — expiry not enforced on login in `AuthService`
- [ ] `info_connexion` (ID 69) — native first-login banner driven by this flag
- [ ] `ameliorations` (ID 80) — telemetry opt-in; no endpoint implemented

---

## Phase 2B — Login screen ✅
- [x] Parity tests with the legacy login page
- [x] Modernised login screen (current Bootstrap)

## Phase 3 — API and integrations
- [ ] Inventory legacy `api/` endpoints and consumers
- [ ] Rewrite or proxy each as a versioned Laravel API route under `routes/api.php`
- [x] iCal export (`evenement_ical.php`)
- [ ] QR-code generation (`qrcode.php`, `qrcode_pic.php`)
- [ ] Geolocation helpers (`geolocalize_all_persons.php`, `gmaps_*.php`)
- [ ] API tests and parity check; retire legacy API files

## Phase 3B — Non-menu plugins / modules
- [ ] Inventory plugin/module files (`addons.php`, `install_addon.php`, `download_*.php`)
- [ ] Define module boundaries and migrate config, routes, assets, permissions
- [ ] Feature tests per module; deprecation rules; remove legacy loaders after cutover

## Phase 4 — Cutover and decommission
- [ ] Keep the legacy → Laravel parity matrix current (see legacy-mapping.md)
- [ ] User acceptance validation on all critical workflows
- [ ] Remove the legacy bridge routes and `LegacyBridgeController`
- [ ] Delete `archive/legacy_app/` and all bridge configuration
- [ ] Execute production cutover plan
- [ ] Update README and docs to reflect the fully-migrated state
