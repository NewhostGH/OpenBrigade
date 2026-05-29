# Migration TODO

When implementing a TODO, cross out the checkbox and add the commit name. If necessary add / update documentation in the [README](../README.md), [copilot instructions](copilot-instructions.md), or [docs](../docs).

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

## Platform and Foundations
- [x] Stabilize Laravel 8.4 runtime and environment configuration (commit: chore: stabilize Laravel 8.4 runtime and environment configuration)
- [x] Restore and validate the artisan CLI workflow (commit: chore: restore artisan CLI and validate end-to-end Laravel workflow)
- [x] Define shared app structure (config, services, middleware, error handling) (commit: chore: define shared app structure with middleware, services, and error handling)
- [x] Set up baseline CI checks (lint, tests, static analysis) (commit: chore: setup baseline CI checks for lint, tests, and static analysis)

## Data and Persistence
- [x] Port legacy schema to Laravel migrations (commit: chore: port legacy schema to Laravel migrations)
- [x] Create and wire Eloquent models and core relationships (commit: feat: create and wire Eloquent models and core relationships)
- [x] Add seeders/factories for required development data (commit: feat: add seeders and factories for required development data)
- [x] Plan and validate data migration from legacy tables (commit: feat: add legacy data migration validation command and parity workflow)

## Security and Access
- [x] Implement authentication flow (login, logout, session lifecycle) (commit: feat: implement laravel authentication flow with login logout and session lifecycle)
- [x] Implement authorization model (roles, permissions, policies) (commit: feat: implement authorization model with legacy permission gates and RequirePermission middleware)
- [x] Replace inline legacy access checks with centralized guards/middleware (commit: feat: replace inline legacy access checks with EnsureUserIsActive and RequirePermission middleware)
- [x] Apply security hardening (XSS, SQLi, CSRF, session settings) (commit: feat: apply security hardening with SecurityHeaders middleware and session hardening)

## Legacy Bridge Stabilization
- [x] Convert all legacy files to UTF-8 encoding (commit: feat: stabilize legacy bridge foundation — UTF-8 encoding, DB redirect elimination, permission hardening)
- [x] Eliminate legacy DB redirect loop (configuration_db.php) (commit: feat: stabilize legacy bridge foundation — UTF-8 encoding, DB redirect elimination, permission hardening)
- [x] Fix double /index.php URL prefix in bridge and dashboard (commit: feat: stabilize legacy bridge foundation — UTF-8 encoding, DB redirect elimination, permission hardening)
- [x] Harden Docker storage/logs permissions (commit: feat: stabilize legacy bridge foundation — UTF-8 encoding, DB redirect elimination, permission hardening)

---

## Phase 1 — Dashboard

> **Goal:** replace `index_d.php` with a native Laravel dashboard. This is the first page users see after login and anchors all future menu migrations.

- [x] Inventory legacy dashboard widgets and KPIs (`index_d.php`, `save_accueil.php`)
- [x] Create `DashboardController` with Blade view `resources/views/dashboard/index.blade.php` (commit: feat: create dashboard controller and view)
- [X] Migrate each widget as a Blade component (agenda, alerts, quick-stats, on-call summary) (commit: feat: migrate dashboard widgets and polish sidebar/navbar UI)
- [X] Conserve DB as link source -> replaced with configuration file (commit: feat: migrate dashboard widgets and polish sidebar/navbar UI)
- [X] Wire dashboard route as the post-login landing page (commit: feat: migrate dashboard widgets and polish sidebar/navbar UI)
- [x] Add feature tests and check parity against `index_d.php` (commit: test: add dashboard feature tests and retire index_d.php from the bridge)
- [x] Modernize the interface using latest bootstrap css (commit: style: modernize dashboard UI — Bootstrap 5 compliance, remove dead imports, widget hover transitions)
- [x] Retire `index_d.php` from the bridge (commit: test: add dashboard feature tests and retire index_d.php from the bridge)

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
- [ ] Migrate trombinoscope and org chart
- [ ] Migrate personnel exports (XLS, CSV, vCard, PDF livret/carte)
- [ ] Migrate qualifications and training records (`qualifications.php`, `personnel_formation.php`)
- [ ] Migrate on-call availability and indisponibility management (`indispo*.php`, `dispo.php`)
- [x] Add tests and parity check; retire legacy files (commit: feat: retire personnel.php and upd_personnel.php bridges; add personnel feature tests)

### Activité — Events & Interventions (ACT)
> Files: `evenements.php`, `evenement_*.php`, `calendar.php`, `horaires.php`, `export*.php`, …

- [x] Inventory all ACT legacy files
- [x] Migrate event list and detail view (commit: feat: migrate event list/detail — EvenementController, views, bridge retirements, tests)
- [ ] Migrate event creation, edit, save, and delete
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
- [ ] Migrate guard sheet and replacement management (`remplacements.php`, `remplacement_edit.php`)
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

- [ ] Inventory all CLI legacy files
- [ ] Migrate company/client list and detail
- [ ] Migrate membership fees (cotisations) management
- [ ] Migrate direct-debit and wire transfer management
- [ ] Migrate billing and financial exports
- [ ] Migrate PDF attestations
- [ ] Add tests and parity check; retire legacy files

### Logistique — Vehicles (VEH)
> Files: `vehicule.php`, `upd_vehicule.php`, `ins_vehicule.php`, `del_vehicule.php`, `vehicule_*.php`, `type_vehicule.php`, …

- [x] Inventory all VEH legacy files
- [x] Migrate vehicle list and detail view/edit (commit: feat: migrate vehicles — VehiculeController, list/show views, bridge retirements, tests)
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

- [ ] Inventory all COMM legacy files
- [ ] Migrate internal messaging and chat
- [ ] Migrate email composition and send (`mail_create.php`, `mail_send.php`)
- [ ] Migrate alert creation and sending
- [ ] Migrate SMS history view
- [ ] Migrate push notification monitor
- [ ] Add tests and parity check; retire legacy files

### Document (DOC)
> Files: `documents.php`, `upd_document.php`, `upd_folder.php`, `save_documents.php`, `save_folder.php`, `delete_file.php`, `delete_event_file.php`, `showfile.php`, `download_*.php`, `pdf_document.php`, …

- [ ] Inventory all DOC legacy files
- [ ] Migrate document and folder tree view
- [ ] Migrate document upload and edit
- [ ] Migrate file serving and download
- [ ] Migrate document exports (PDF)
- [ ] Add tests and parity check; retire legacy files

### Statistique (STAT)
> Files: `bilans.php`, `bilan_participation.php`, `export-*.php`, `export.php`, `report_cotisations.php`, `habilitations_xls.php`, …

- [ ] Inventory all STAT legacy files
- [ ] Migrate participation and event statistics
- [ ] Migrate financial reports
- [ ] Migrate custom exports (XLS, TCD, HTML, TXT, SQL)
- [ ] Add tests and parity check; retire legacy files

### Organisation (ORGA)
> Files: `section.php`, `upd_section.php`, `ins_section.php`, `del_section.php`, `habilitations.php`, `upd_habilitations.php`, `poste.php`, `upd_poste.php`, `ins_poste.php`, `del_poste.php`, `grades*.php`, `equipe.php`, `upd_equipe.php`, `del_equipe.php`, `organigramme.php`, …

- [ ] Inventory all ORGA legacy files
- [ ] Migrate section/unit management
- [ ] Migrate group and role (habilitations) management
- [ ] Migrate rank and grade management
- [ ] Migrate position (poste) management
- [ ] Migrate team (equipe) management
- [ ] Add tests and parity check; retire legacy files

### Configuration — Admin (ADMIN)
> Files: `configuration.php`, `save_configuration.php`, `parametrage.php`, `configuration_*.php`, `audit.php`, `backup.php`, `restore.php`, `upgrade.php`, `update_*.php`, `addons.php`, `addons_save.php`, `install_addon.php`, `download_*.php`, …

- [ ] Inventory all ADMIN legacy files
- [ ] Migrate application settings (configuration, parametrage)
- [ ] Migrate theme and icon configuration
- [ ] Migrate audit log view
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
