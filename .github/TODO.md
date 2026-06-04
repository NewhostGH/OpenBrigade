# Migration TODO

When implementing a TODO, cross out the checkbox and add the commit name. If necessary add / update documentation in the [README](../README.md), [copilot instructions](copilot-instructions.md), or [docs](../docs).

---

## Engineering Conventions (read before writing code)

> These are binding rules for all migrated code. They exist so the codebase stays maintainable as legacy pages are ported. Both human contributors and AI assistants MUST follow them. When you touch a file that breaks a rule, fix it or add a remediation entry under "Cleanup & Remediation Plan" below.

### 1. Single Source of Truth (SSOT)

A value or rule must be defined in exactly **one** place. Never copy logic between a controller, a service, and a Blade view.

- **Derived values** (anything computed from a model's fields — avatar URL, full name, état/status label, age, net total) → a **method or accessor on the Eloquent model** (e.g. `Personnel::getAvatarUrl()`). Views and controllers call the method; they never reconstruct the value inline.
- **Lookup / label / badge maps** (status → label+class, civility → prefix, etc.) → defined **once** in a `config/` file (e.g. `config/personnel.php`). Reference it everywhere; never re-declare the array per page.
- **External URLs** (third-party services, CDNs, map tiles, API endpoints, doc links) → defined **once** in `config/` (e.g. `config/services.php` or `config/links.php`) and read via `config('...')`. Never hardcode an external URL in a controller, service, Blade view, or JS file.
- **Business logic** (sums, filters, eligibility rules, query shaping) → a **service** (`app/Services/`) or the model. **Never** in a Blade view.
- **Raw DB rows are not exempt.** If a query builder / `DB::table()` returns `stdClass` rows that need a derived value, either (a) return Eloquent models so the accessor is available, or (b) put the derivation in a **shared helper/service method** and call it from both the model accessor and the raw-row path. Do not inline a second copy.
- **Column/field definitions** (list columns, export field lists) → one definition reused by list view and export.

### 2. Models

- One model per table is the goal. Where two models intentionally map to the same table (`User` = auth concerns, `Personnel` = domain concerns, both on `pompier`), **shared behaviour MUST live in a trait** (e.g. `app/Models/Concerns/HasAvatar.php`) used by both — never copy-pasted. Document the split at the top of each model.
- Casts, accessors, and shared scopes that apply to the underlying table belong in the shared trait, not in only one of the two models.

### 3. Blade views

- **Minimal PHP.** No business logic, no DB access, no array/map declarations in `@php` blocks. Presentation data (nav arrays, badge maps) comes from the controller or a dedicated view model / view composer.
- **No `<style>` blocks and no `@push('styles')` with inline CSS.** All CSS lives in `resources/css/<module>.css` and is bundled by Vite.
- A `@php` block, if unavoidable, should be a couple of trivial presentation lines — not logic.

### 4. CSS / JS naming

- **Every custom class and id uses the `ob-` prefix**, with a module sub-namespace: `ob-dash-card`, `ob-nav-siglet`, `ob-login-card`, `ob-pers-sidenav`. Bootstrap utility classes are used as-is; only *our* classes get the prefix.
- One CSS file and one JS file per module under `resources/css/` and `resources/js/`. No inline `<script>` with logic where a module file fits.

### 5. Legacy references must be flagged

- Any link, asset path, or redirect pointing at the legacy app (`/legacy/...`, `*.php?...`, `/trombinoscope/...`, hardcoded `archive/legacy_app/...`) MUST carry a marker comment on the same or preceding line:
  - Blade: `{{-- TODO: Migrate code --}}`
  - PHP/JS: `// TODO: Migrate code`
- This makes every remaining legacy coupling greppable (`grep -rn "TODO: Migrate code"`) and is the input list for Phase 4 decommission.
- A legacy URL with **no** `/legacy/` prefix is a bug, not a bridge — fix the route, don't just mark it.

---

## File Migration Strategy (apply to every menu section below)

Each menu section follows this repeatable process:

1. **Inventory** — list all `archive/legacy_app/` files that belong to the menu (pages, save handlers, modals, XLS/PDF exports, JS helpers).
2. **Controller** — create a Laravel controller under `app/Http/Controllers/<Menu>/`.
3. **Views** — create Blade views under `resources/views/<menu>/`; reuse the existing layout shell.
4. **Routes** — add named routes to `routes/web.php`; remove the corresponding entries from `routes/web_legacy_bridge.php` and `config/legacy_bridge.php`.
5. **Services / Models** — move business logic into a service or Eloquent model; no raw SQL in controllers.
6. **Tests** — add feature tests covering the happy path and key edge cases.
7. **Parity check** — verify output parity against the legacy page (same data, same access rules).
8. **Retire legacy files** — once parity passes, delete the legacy files from `archive/legacy_app/` and remove them from `archive/legacy_app/modified.txt` if listed.
9. **Modernize** — after cutover, consider UI/UX improvements using latest Bootstrap and Vite assets.

---

## Phase 1 — Dashboard

> **Goal:** replace `index_d.php` with a native Laravel dashboard. This is the first page users see after login and anchors all future menu migrations.

DONE

---

## Cross-cutting UI architecture

- [x] Universal component system — `ob-breadcrumb`, `ob-toolbar`, `ob-table`, `ob-commandbar`, `ob-badge`, `ob-avatar`, `ob-toggle`; one CSS + JS file per module in `resources/css/` and `resources/js/`; `ObTable` ES6 class driven by `data-*` attributes; col-toggle and export buttons wired globally via `data-for-table`; `overflow:clip` fix so dropdowns are never clipped by the table card (commit: feat: universal ob-component system — ob-table/toolbar/commandbar/breadcrumb with per-module CSS/JS)
- [x] `TableExportService` — replaces duplicated PhpSpreadsheet boilerplate in controllers; type-aware getters (date auto-format, badge label resolution); `toXlsx()` and `toCsv()` via `response()->streamDownload()`; `?cols=` param for column-aware export matching localStorage visibility state (commit: feat: TableExportService — universal XLS/CSV export, column-aware export URLs)
- [x] Migrate all list pages to `ob-table` + `ob-toolbar` + `ob-commandbar` + `ob-breadcrumb`: evenement, vehicule, matériel, consommable, company, astreintes, indispo, remplacement, monitoring, qualifications (10 pages); breadcrumb-only on 14 further pages; fix test stubs to include `columns => []` (commit: feat: migrate all list pages to universal ob-component system; add breadcrumb to all pages)
- [x] Migrate cotisations global page to `ob-toolbar` + `ob-commandbar`; add `action` and `showSelCount` props to `ob-commandbar`; keep per-row editable inputs; statut badges use `ob-badge-*` classes (commit: feat: migrate cotisations page to ob-toolbar/ob-commandbar)
- [x] Fix `FPDF` anonymous class property type error: remove `int` type annotations from `$y` and `$goDown` — PHP 8 forbids adding a type to an inherited untyped property (commit: fix: FPDF anonymous class property type declarations incompatible with PHP 8)
- [x] Fix `VehiculeController` wrong column names: `V_IMMAT` → `V_IMMATRICULATION`, `V_LIBELLE` → `V_INDICATIF`; add `TV_CODE`, `V_MODELE`, `V_ANNEE` to select and columns; enrich `vehiculeColumns()` with revision and carte-grise columns (commit: fix: VehiculeController wrong column names — V_IMMATRICULATION, V_INDICATIF; add model/year/revision/titre columns)
---

## Cleanup & Remediation Plan

> Findings from the 2026-06-04 conventions audit. Each item references the [Engineering Conventions](#engineering-conventions-read-before-writing-code) rule it restores. Do these before porting more pages — they define the patterns every later migration will copy.

### A. SSOT — avatar URL (rule 1)
- [x] Extract avatar logic into a shared trait `app/Models/Concerns/HasAvatar.php` exposing `getAvatarUrl()` (instance) + `avatarUrl($id, $photo, $civilite)` (static, for raw rows); used by both `Personnel` and `User`; duplicate method bodies deleted.
- [x] **Bug fixed:** `DashboardService` duty + birthday widgets no longer build avatars inline with the broken `/trombinoscope/<file>` path — they call `Personnel::avatarUrl(...)`, same SSOT as `getAvatarUrl()`.

### B. SSOT — duplicated maps & logic (rule 1)
- [x] Created `config/personnel.php` (labels once in `statuts`; `statut_badge_class` + `statuts_assignable` reference codes; `etat_badges`; `civilites`). Personnel helpers `statutBadge()` / `statutBadgeMap()` / `etatBadge()` / `civiliteLabel()` zip them. Removed inline maps from `PersonnelController::personnelColumns()`, `personnel/show.blade.php`, and the `edit.blade.php` statut + civilité dropdowns.
- [x] **État** rule moved to `Personnel::getEtatAttribute()` (`$personnel->etat`); reused by the controller `etat` column and show view.
- [x] **cotisNet** moved to `Personnel::getCotisNetAttribute()` (`$personnel->cotis_net`); removed from `show.blade.php`.
- [x] **Civility map** centralised in `config('personnel.civilites')`; reused on show (`civiliteLabel()`) and edit (dropdown `@foreach`).
- Note: `index.blade.php` *category filter* (ALL/INT/BEN/EXT/PRES with plural UI labels) is a distinct concern, intentionally left as-is.

### C. Excessive PHP in Blade (rule 3)
- [ ] `personnel/show.blade.php:12-51` — move the badge maps, état, totals, and `$sideNav` array out of the 50-line `@php` block into the controller (pass as view data) or a view model.
- [ ] Remove inline `<style>` / `@push('styles')` CSS from `personnel/show`, `personnel/edit`, `vehicule/form`, `personnel/geolocalisation`, `organisation/index`, `planning/index`; relocate to module CSS files.

### D. CSS prefix consistency (rule 4)
- [ ] Rename custom classes/ids in `dashboard.css`, `navbar.css`, `login.css`, `sidebar.css` and the inline `pers-*` classes to the `ob-` prefix with module sub-namespace (`ob-dash-*`, `ob-nav-*`, `ob-login-*`, `ob-pers-*`). Update all referencing Blade/JS. (Large mechanical change — do per module, one commit each, verify rendering after each.)

### E. Legacy reference flagging (rule 5)
- [ ] Add `{{-- TODO: Migrate code --}}` / `// TODO: Migrate code` to every legacy `/legacy/*.php`, `*.php?...`, and `/trombinoscope/...` reference across views, controllers, services, and JS (~60+ sites; see `grep -rn "/legacy/\|\.php?"`).
- [ ] **Bug:** `navbar.blade.php:62-93` quick-add menu links to `url('/ins_personnel.php...')` etc. **without** the `/legacy/` prefix — these don't route. Fix to the correct bridge route (or native route once it exists).

### F. Convention enforcement (prevent regression)
- [ ] Document these conventions in `docs/dev/ARCHITECTURE.md` (or a new `docs/dev/CONVENTIONS.md`) and link from `.github/CONTRIBUTING.md`.
- [ ] Add a CI/grep guard (or a test) that fails on: inline `<style>` in Blade, unprefixed custom CSS classes, and un-flagged `/legacy/` references in migrated files.

---

## Phase 2 — Menu by Menu

### Menu Coverage Map (legacy labels)
- [ ] Personnel -> handled in Personnel (PERSO)
- [ ] Activite -> handled in Activite - Events & Interventions (ACT)
- [ ] Planning -> handled in Planning (PLA)
- [ ] Calendrier -> handled in Activite (calendar.php) and Planning workflows
- [ ] Disponibilites -> handled in Personnel and Garde workflows (dispo/indispo)
- [ ] Absences -> handled in Personnel and Garde workflows (indispo/absence)
- [ ] Logistique -> handled in Logistique - Vehicles (VEH) and Inventaire (MAT/CONSO)
- [ ] Statistique -> handled in Statistique (STAT)
- [ ] Document -> handled in Document (DOC)
- [ ] Communication -> handled in Communication (COMM)
- [ ] Organisation -> handled in Organisation (ORGA)
- [ ] Module -> handled in Configuration - Admin via add-on/module management (ADDON)
- [ ] Configuration -> handled in Configuration - Admin (ADMIN)

### Personnel (PERSO)
> Files: `personnel.php`, `upd_personnel.php`, `ins_personnel.php`, `del_personnel.php`, `personnel_*.php`, `trombinoscope.php`, `organigramme.php`, `search_personnel*.php`, `export*.php`, `pdf_*.php`, `vcard*.php`, …

- [x] Inventory all PERSO legacy files (pages, handlers, exports, PDFs)
- [x] Migrate member list and profile view/edit (commit: feat: retire personnel.php and upd_personnel.php bridges; add personnel feature tests)
- [x] Migrate trombinoscope and org chart (commit: feat: migrate trombinoscope and company list — views, bridge retirements, tests)
- [x] Migrate personnel exports (XLS, CSV, vCard, PDF livret/carte) (commit: feat: migrate personnel exports — XLS/CSV list export, vCard, PDF livret, PDF carte adhérent)
- [x] Migrate qualifications and training records (`qualifications.php`, `personnel_formation.php`) (commit: feat: migrate astreintes management and qualifications — views, bridge retirements)
- [x] Migrate on-call availability and indisponibility management (`indispo*.php`, `dispo.php`) (commit: feat: migrate indisponibilités — IndispoController, view, bridge retirements)
- [x] Add tests and parity check; retire legacy files (commit: feat: retire personnel.php and upd_personnel.php bridges; add personnel feature tests)
- [x] Enhance personnel list: full feature parity — bulk-select checkboxes with action buttons (internal email, badge print, mailto, email-list download), grade badge images, hierarchical colour-coded section filter, subsection-include toggle, column-visibility toggle (localStorage), card/table view toggle, configurable page size (12/24/48/100/500), coloured status badges (BEN/EXT/PRES/INT + Actif/Archivé/Bloqué) (commit: feat: universal ob-component system — ob-table/toolbar/commandbar/breadcrumb with per-module CSS/JS)
- [x] Integrate universal search into personnel list: expand server-side search to all fields (nom, prénom, matricule, email, téléphone, grade, adresse, code postal, ville); remove redundant "Recherche" sidebar entry (commit: feat: extend personnel search to address/city fields; remove redundant Recherche nav entry)
- [x] Complete add/edit personnel form: add all missing fields vs legacy (`P_CIVILITE`, photo upload, login/password management, NPAI flag, suspension flag, notes/comments, licence fields) (commit: feat: complete personnel edit form — tabbed layout, photo upload, all missing fields, enhanced show page)
- [x] Personnel competences CRUD: add/edit/delete qualifications and training records from the member profile page (commit: feat: personnel competences CRUD — add/edit/delete qualifications from member profile)
- [x] Enhance personnel CRUD : full parity with legacy features — added missing fields (Abrégé/P_ABBREGE, Entreprise/C_ID, Droit d'accès GP_ID and GP_ID2 selectors, Date NPAI/DATE_NPAI), Accès tab in edit form, Participation section on show page with event list and presence badges, NPAI/charte/groupe2 display on show page, subnav anchor strip for quick section access (commit: feat: enhance personnel CRUD — missing fields, access tab, participation section, subnav)
- [x] Port cotisations: membership fee list per member, add/edit/delete fee entries, payment tracking (commit: feat: port cotisations — membership fee CRUD on member profile)
- [x] Port cotisations global page (`cotisations.php`): organisation-wide fee tracking — member list filtered by year, period (`periode` table), section (with subsection toggle), payment type, and paid/unpaid status; editable amount and date columns; bulk mark-as-paid with "check all"; batch save to `personnel_cotisation`; Excel export (`cotisations_xls.php`); Prélèvements tab (direct-debit batch file export, `cotisations_extract.php`); Virements tab (bank-transfer list) — permission 53; fix per-member form using free-text PERIODE_CODE (now a dropdown defaulting to A) and TP_ID NULL constraint (commit: feat: port cotisations global page — org-wide fee list, batch save, Excel export; fix PERIODE_CODE/TP_ID on per-member CRUD)
- [x] Port Prélèvements and Virements tabs from `cotisations.php` (tab 2/3): Prélèvements — summary of pending direct-debit members (TP_ID=1), batch-save with chosen date, paid/pending split; Virements — paginated list of REMBOURSEMENT=1/TP_ID=2 entries with date-range filter; shared `ob-subnav` tab bar on all three cotisations pages; legacy bridge routes redirected (commit: feat: port Prélèvements and Virements tabs — direct-debit batch save, virement list, cotisations tab nav)
- [x] Port géolocalisation: display and update GPS coordinates for members on an interactive map (commit: feat: port géolocalisation — Leaflet map with GPS markers, member profile GPS section)

### Activité — Events & Interventions (ACT)
> Files: `evenements.php`, `evenement_*.php`, `calendar.php`, `horaires.php`, `export*.php`, …

- [x] Inventory all ACT legacy files
- [x] Migrate event list and detail view (commit: feat: migrate event list/detail — EvenementController, views, bridge retirements, tests)
- [x] Migrate event creation, edit, save, and delete (commit: feat: event CRUD — create/edit/delete form, fix V_IMMAT in show, redirect legacy bridges)
- [ ] Migrate participant management (inscription, equipes, renforts)
- [ ] Migrate event material and vehicle assignment
- [ ] Migrate calendar view
- [ ] Migrate event exports (XLS, PDF rapport, iCal)
- [x] Add tests and parity check; retire legacy files (commit: feat: migrate event list/detail — EvenementController, views, bridge retirements, tests)

### Garde — On-call roster (GAR)
> Files: `astreintes.php`, `astreinte_*.php`, `auto_garde.php`, `automaticPiquet.php`, `tableau_garde*.php`, `feuille_garde.php`, `repos_*.php`, …

- [x] Inventory all GAR legacy files
- [x] Migrate roster display and assignment (commit: feat: migrate garde roster — GardeController, weekly view, bridge retirements, tests)
- [ ] Migrate automatic piquet/guard generation
- [x] Migrate guard sheet and replacement management (`remplacements.php`, `remplacement_edit.php`) (commit: feat: migrate remplacements and update nav)
- [ ] Migrate rest periods (`repos_*.php`)
- [ ] Migrate guard exports (XLS, PDF)
- [x] Add tests and parity check; retire legacy files (commit: feat: migrate garde roster — GardeController, weekly view, bridge retirements, tests)

### Planning (PLA)
> Files: `planning.php`, `planning_xls.php`, `myagenda.php`, `horaires.php`, `horaires_modal.php`, …

- [x] Inventory all PLA legacy files
- [x] Migrate weekly/monthly planning view (commit: feat: migrate planning — PlanningController monthly calendar, bridge retirements, tests)
- [x] Migrate personal agenda (`myagenda.php`) (commit: feat: migrate planning — PlanningController monthly calendar, bridge retirements, tests)
- [ ] Migrate schedule (horaires) management
- [ ] Migrate planning exports
- [x] Add tests and parity check; retire legacy files (commit: feat: migrate planning — PlanningController monthly calendar, bridge retirements, tests)

### Client (CLI)
> Files: `company.php`, `upd_company.php`, `ins_company.php`, `del_company.php`, `company_xls.php`, `cotisations.php`, `cotisation_edit.php`, `save_cotisations.php`, `prelevements.php`, `virements*.php`, `bilans.php`, `pdf_bilans.php`, `pdf_attestation_fiscale.php`, …

- [x] Inventory all CLI legacy files
- [x] Migrate company/client list and detail (commit: feat: migrate trombinoscope and company list — views, bridge retirements, tests)
- [ ] Migrate membership fees (cotisations) management
- [ ] Migrate direct-debit and wire transfer management
- [ ] Migrate billing and financial exports
- [ ] Migrate PDF attestations
- [ ] Add tests and parity check; retire legacy files

### Logistique — Vehicles (VEH)
> Files: `vehicule.php`, `upd_vehicule.php`, `ins_vehicule.php`, `del_vehicule.php`, `vehicule_*.php`, `type_vehicule.php`, …

- [x] Inventory all VEH legacy files
- [x] Migrate vehicle list and detail view/edit (commit: feat: migrate vehicles — VehiculeController, list/show views, bridge retirements, tests)
- [x] Migrate vehicle CRUD: create/edit/delete form; fix V_IMMAT/V_LIBELLE column names in show view (commit: feat: vehicle CRUD — create/edit/delete form, fix wrong column names in show view)
- [ ] Migrate vehicle type management
- [ ] Migrate vehicle assignment to events
- [ ] Migrate vehicle exports (XLS)
- [x] Add tests and parity check; retire legacy files (commit: feat: migrate vehicles — VehiculeController, list/show views, bridge retirements, tests)

### Inventaire — Equipment & Consumables (MAT / CONSO)
> Files: `materiel.php`, `upd_materiel.php`, `ins_materiel.php`, `del_materiel.php`, `materiel_*.php`, `type_materiel.php`, `consommable.php`, `upd_consommable.php`, `del_consommable.php`, `consommable_*.php`, `type_consommable.php`, …

- [x] Inventory all MAT/CONSO legacy files
- [x] Migrate equipment list and detail view/edit (commit: feat: migrate matériel and consommable lists — controllers, views, bridge retirements, tests)
- [ ] Migrate equipment type and category management
- [x] Migrate consumable stock management (commit: feat: migrate matériel and consommable lists — controllers, views, bridge retirements, tests)
- [ ] Migrate embarkation tracking (`materiel_embarquer.php`)
- [ ] Migrate equipment and consumable exports (XLS)
- [x] Add tests and parity check; retire legacy files (commit: feat: migrate matériel and consommable lists — controllers, views, bridge retirements, tests)

### Communication (COMM)
> Files: `mail_*.php`, `mailer.php`, `mailto.php`, `alerte_*.php`, `sms` (fonctions_sms), `chat.php`, `chat_message.php`, `histo_sms.php`, `push_monitor.php`, `rss.php`, …

- [x] Inventory all COMM legacy files
- [x] Migrate internal messaging and chat (commit: feat: migrate document library and message board — controllers, views, bridge retirements, tests)
- [ ] Migrate email composition and send (`mail_create.php`, `mail_send.php`)
- [ ] Migrate alert creation and sending
- [ ] Migrate SMS history view
- [ ] Migrate push notification monitor
- [x] Add tests and parity check; retire legacy files (commit: feat: migrate document library and message board — controllers, views, bridge retirements, tests)

### Document (DOC)
> Files: `documents.php`, `upd_document.php`, `upd_folder.php`, `save_documents.php`, `save_folder.php`, `delete_file.php`, `delete_event_file.php`, `showfile.php`, `download_*.php`, `pdf_document.php`, …

- [x] Inventory all DOC legacy files
- [x] Migrate document and folder tree view (commit: feat: migrate document library and message board — controllers, views, bridge retirements, tests)
- [ ] Migrate document upload and edit
- [ ] Migrate file serving and download
- [ ] Migrate document exports (PDF)
- [x] Add tests and parity check; retire legacy files (commit: feat: migrate document library and message board — controllers, views, bridge retirements, tests)

### Statistique (STAT)
> Files: `bilans.php`, `bilan_participation.php`, `export-*.php`, `export.php`, `report_cotisations.php`, `habilitations_xls.php`, …

- [x] Inventory all STAT legacy files
- [x] Migrate participation and event statistics (commit: feat: migrate statistiques — dashboard with charts, bridge retirement, tests)
- [ ] Migrate financial reports
- [ ] Migrate custom exports (XLS, TCD, HTML, TXT, SQL)
- [x] Add tests and parity check; retire legacy files (commit: feat: migrate statistiques — dashboard with charts, bridge retirement, tests)

### Organisation (ORGA)
> Files: `section.php`, `upd_section.php`, `ins_section.php`, `del_section.php`, `habilitations.php`, `upd_habilitations.php`, `poste.php`, `upd_poste.php`, `ins_poste.php`, `del_poste.php`, `grades*.php`, `equipe.php`, `upd_equipe.php`, `del_equipe.php`, `organigramme.php`, …

- [x] Inventory all ORGA legacy files
- [x] Migrate section/unit management (commit: feat: migrate organisation — hierarchy tree view, bridge retirements, tests)
- [ ] Migrate group and role (habilitations) management
- [ ] Migrate rank and grade management
- [ ] Migrate position (poste) management
- [ ] Migrate team (equipe) management
- [x] Add tests and parity check; retire legacy files (commit: feat: migrate organisation — hierarchy tree view, bridge retirements, tests)

### Configuration — Admin (ADMIN)
> Files: `configuration.php`, `save_configuration.php`, `parametrage.php`, `configuration_*.php`, `audit.php`, `backup.php`, `restore.php`, `upgrade.php`, `update_*.php`, `addons.php`, `addons_save.php`, `install_addon.php`, `download_*.php`, …

- [x] Inventory all ADMIN legacy files
- [ ] Migrate application settings (configuration, parametrage)
- [ ] Migrate theme and icon configuration
- [x] Migrate audit log view (commit: feat: migrate admin monitoring, disponibilités, and nav updates)
- [ ] Migrate backup and restore
- [ ] Migrate upgrade / SQL migration runner
- [ ] Migrate add-on / module management
- [ ] Add tests and parity check; retire legacy files

---

## Phase 2B - Login screen

> **Goal:** improve the login experience by leveraging the current Laravel authentication flow and modernizing the login screen.

- [x] Add tests and check parity with legacy login page (commit: feat: modernize login screen and add auth feature tests)
- [x] Modernise login screen using latest bootstrap css (commit: feat: modernize login screen and add auth feature tests)

---

## Phase 3 — API and Integrations

- [ ] Inventory legacy `api/` endpoints and their consumers
- [ ] Rewrite or proxy each endpoint as a versioned Laravel API route
- [ ] Migrate iCal export (`evenement_ical.php`)
- [ ] Migrate QR-code generation (`qrcode.php`, `qrcode_pic.php`)
- [ ] Migrate geolocation helpers (`geolocalize_all_persons.php`, `gmaps_*.php`)
- [ ] Add API tests and parity check; retire legacy API files

---

## Phase 3B — Non-Menu Plugins / Modules

> **Goal:** migrate plugin/module features that are not exposed as standard menu entries but still provide business functionality.

- [ ] Inventory non-menu plugin/module files (`addons.php`, `addons_save.php`, `install_addon.php`, `download_addon.php`, `download_module.php`, plugin entry scripts)
- [ ] Classify each plugin by type (UI page, background task, import/export helper, integration hook)
- [ ] Define Laravel module boundaries (`app/Modules/<ModuleName>/` or `app/Services/Modules/<ModuleName>Service.php`)
- [ ] Migrate plugin configuration storage from legacy tables/files to Laravel config + DB tables
- [ ] Migrate plugin routes and endpoints (web + api) with named routes and middleware
- [ ] Migrate plugin assets (JS/CSS/images) into `resources/` + `public/` with Vite handling where needed
- [ ] Migrate plugin permissions to centralized policies/gates (remove inline checks)
- [ ] Add feature tests per module and parity checks against legacy behavior
- [ ] Define plugin deprecation rules (disable in bridge, monitor logs, then remove legacy files)
- [ ] Remove legacy non-menu plugin loader paths after successful cutover

---

## Phase 4 — Cutover and Decommission

- [ ] Build and maintain a legacy-to-Laravel parity matrix (one row per legacy file)
- [ ] Run user acceptance validation on all critical workflows
- [ ] Remove the legacy bridge routes and LegacyBridgeController once all pages are migrated
- [ ] Delete `archive/legacy_app/` and all bridge configuration
- [ ] Execute production cutover plan
- [ ] Update README and docs to reflect the fully-migrated state
