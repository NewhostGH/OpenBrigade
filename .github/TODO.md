# OpenBrigade Migration TODO

Working tracker for migrating the legacy eBrigade app (`archive/legacy_app/`) into
the native Laravel application, menu by menu. Large, forward-looking ideas that go
beyond the migration live in [IDEAS.md](IDEAS.md).

Rules and process live elsewhere — read them first:
[CONVENTIONS.md](../docs/dev/CONVENTIONS.md) (how code is written),
[ARCHITECTURE.md](../docs/dev/ARCHITECTURE.md) (where things live),
[DEVELOPMENT.md](../docs/dev/DEVELOPMENT.md) (how to run it),
[legacy-mapping.md](../docs/dev/legacy-mapping.md) (legacy file map),
[CONTRIBUTING.md](CONTRIBUTING.md) (branches, commits, PRs).

When you complete an item, tick its checkbox and move it down to **Shipped**.
Update the legacy file map (linked above) when a file moves from legacy to
native. Keep the gates green: `composer pint -- --test`, `composer analyse`,
`composer test`.

Legend: `[ ]` open · `[x]` done · WIP = implemented but parity not verified.
The **Backlog** is grouped so earlier groups underpin later ones; **Shipped**
keeps the record of completed work.

---

## Backlog

## Cross-cutting foundations

These underpin much of the feature work below (notifications/queues unblock the
whole Communication menu, reminders and guard generation).

### Production readiness

- [ ] **Notification / messaging infrastructure** — one unified layer (Laravel
  Notifications + `app/Mail` mailables + channels) instead of per-screen
  email/SMS/alert/push code. Configure a mail transport, queue the sends, and
  build reusable templates; this unblocks password-reset mail and the whole
  COMM menu. Add an SMS channel abstraction (provider-agnostic) consumed by the
  later SMS-gateway work.
- [ ] **Queues + scheduler** — wire a queue connection + worker (`app/Jobs`) so
  mail/SMS/exports run async, and a Laravel scheduler (`routes/console.php` /
  `Console\Kernel`) for recurring jobs: qualification/medical-aptitude expiry
  reminders, automatic guard/piquet generation, backups, retention purges.
- [ ] **RGPD / data-privacy compliance** — the app holds medical aptitude, home
  addresses, emergency contacts and member geolocation. Implement: data-subject
  export (portability), right-to-erasure workflow, retention policy + automated
  purge, consent tracking, a processing register, and access logging on
  sensitive records. Encrypt sensitive columns at rest.
- [ ] **Observability** — structured logging, error tracking (Sentry/Flare or
  similar), a health-check endpoint, and basic uptime/performance monitoring.
- [ ] **Backup robustness** — an automated backup schedule exists but is a
  no-op: make it actually run (DB + uploads), store off-site/retained, and add a
  periodic restore-drill check.

## Feature migration (menu by menu)

### Personnel (PERSO)

- [ ] Diploma print layout config (`diplome_edit.php`) — complex PDF field positioning admin screen
- [ ] Custom member fields (`specific_info.php`)
- [ ] `export_badges.php` — member badge/ID card PDF export

### Activité — Events & Interventions (ACT)

- [ ] Participant notifications (`evenement_notify.php`) — needs the notification layer
- [ ] Event report (`evenement_rapport.php`)
- [ ] Editable PDF for conventions
- [ ] Event billing & tariffs (`evenement_facturation*.php`, `evenement_tarif*.php`)

### Planning (PLA)

- [ ] Migrate calendars to a universal calendar library (FullCalendar or similar)
- [ ] Dashboard agenda widget on the new calendar library, opening the detailed calendar view
- [ ] Schedule (horaires) management
- [ ] Planning exports

### Garde — On-call roster (GAR)

- [ ] Use the new calendar library when implemented (see PLA)
- [ ] Automatic piquet/guard generation
- [ ] Rest periods (`repos_*.php`)
- [ ] Guard exports — PDF
- [ ] Demande de renfort — transmit the request to another section (email, or in-app message/notification): currently the request is only stored/displayed on the event; add a way to actually communicate it to the target section so they can respond with renfort sub-events — needs the notification layer

### Communication (COMM)

Needs the notification / messaging infrastructure above.

- [ ] Email composition and send
- [ ] Alert creation and sending
- [ ] SMS history view
- [ ] Push notification monitor
- [ ] Reminders / relances (`reminder.php`)
- [ ] RSS feed (`rss.php`)

### Client / finance (CLI)

- [ ] Billing and financial exports
- [ ] PDF attestations (fiscale, formation)
- [ ] Billable elements (`element_facturable.php`)
- [ ] Expense notes (`note_frais_*.php`)
- [ ] Prélèvements configuration (`config_prelevements.php`)
- [ ] Payment categories (`edit_categorie*.php`)

### Statistique (STAT)

- [ ] Financial reports (`report_cotisations.php`)
- [ ] Custom exports (XLS, TCD, HTML, TXT, SQL)

### Organisation (ORGA)

- [ ] First-run setup wizard (`wizard.php`)
- [ ] **Organisation-type setup wizard** — let an admin pick the organisation type
  (`config('brigade.organisation_types')`) and activate that type's seeded role set
  (`ob_group.org_type`); roles for every type are already seeded by `BaseHabilitations`
- [ ] Rank & grade management / rework grade system — full grade model (grade
  categories and grade icons CRUD already exist; this is the broader rework)
- [ ] Organigramme tab as an interactive org-chart (currently role-grouped lists)
- [ ] Section deactivation / radiation (`section_stop.php`, `radier_section.php`)
- [ ] Guard order & responsables (`choice_section_order.php`, `upd_responsable.php`)
- [ ] Competence hierarchy (`hierarchie_competence.php`)

### Configuration — Admin (ADMIN)

- [ ] Plugins marketplace — `/admin/plugins` is a placeholder; install/download flow to design
- [ ] Maintenance utilities (`update_app.php`, `buildsql.php`, `decrypt.php`, `import_api.php`, `debug_data.php`)

Settings not yet wired (authoritative list: the `todo` annotations in
`AdminController::settings()`; settings marked `obsolete` there are intentionally
retired — no work needed):

- [ ] Timezone (ID 76), default currency (IDs 98, 99)
- [ ] Numbering prefix / length (IDs 100, 101)
- [ ] Email notifications (ID 28) — needs the notification layer
- [ ] Mandatory profile photos (ID 68)
- [ ] Maintenance mode and text (IDs 37, 41)
- [ ] Telemetry opt-in (ID 80)
- [ ] Database optimization (ID 14)
- [ ] API enable / URL / token (IDs 64, 65, 66) — see API & integrations
- [ ] SMS provider settings (IDs 9, 10, 11, 12) — see COMM / SMS gateway

### Opérations d'urgence (DPS / SITAC / Victimes)

- [ ] DPS sizing calculator (`dps.php`, `dps_calc.php`, `dps_save.php`)
- [ ] SITAC tactical board (`sitac*.php`)
- [ ] Victim management (`victimes.php`, `liste_victimes.php`, `scan_victime.php`, `intervention_edit.php`)

## API & integrations (Phase 3)

- [ ] Inventory legacy `api/` endpoints and consumers
- [ ] Rewrite or proxy each as a versioned route under `routes/api.php`
- [ ] QR-code generation
- [ ] Geolocation helpers (`gmaps_evenement.php`, `localize*.php`, `map.php`, `zipcode.php`)
- [ ] API tests and parity check; retire legacy API files

## Plugins / modules (Phase 3B)

- [ ] Inventory plugin/module files (`addons.php`, `install_addon.php`, `download_*.php`)
- [ ] Define module boundaries; migrate config, routes, assets, permissions
- [ ] Animaux module (`personnel_maitre.php`, `cav_edit.php` — `ob_feature` flag exists, status wip)
- [ ] SMS gateway integration (`lib/SMSGatewayMe/`, `fonctions_sms.php`)
- [ ] Feature tests per module; remove legacy loaders after cutover

## Cutover & decommission (Phase 4)

- [ ] Keep the legacy parity matrix current (legacy-mapping.md)
- [ ] User acceptance validation on critical workflows
- [ ] Remove the legacy bridge routes and `LegacyBridgeController`
- [ ] Delete `archive/legacy_app/` and all bridge configuration
- [ ] Execute production cutover plan
- [ ] Update README and docs to the fully-migrated state

## Release strategy

How the app is built, shipped and upgraded in production.

- [ ] **CD pipeline** — extend the existing CI (`.github/workflows/ci.yml`) into
  a deploy pipeline (build assets, run migrations, zero-downtime release,
  rollback path); gate on the green checks (pint/phpstan/test).
- [ ] **Migration & release runbook** — documented deploy steps, DB-migration
  policy (forward-only, backward-compatible where possible), and a rollback
  procedure.
- [ ] **Environments** — clearly defined local / staging / production configs
  and secrets management; staging mirrors production for UAT.
- [ ] **Versioning & changelog** — adopt semantic versioning + a maintained
  `CHANGELOG.md`; tag releases.
- [ ] **In-app update / maintenance flow** — successor to legacy `update_app.php`
  / `upgrade.php`: surface migration status, run pending migrations, and toggle
  maintenance mode from the admin UI.
- [ ] **Release verification** — post-deploy smoke checks (health endpoint,
  critical workflows) and monitoring hooks tied to the observability work.

---

## Shipped

## Foundations

- [X] **i18n / l10n scaffolding (French only for now)** — move hard-coded UI
  strings into a `lang/fr` layer and route them through Laravel localization, so
  copy is centralized and a second locale is later a drop-in. No translations
  yet — implementation + French strings only.
- [X] **Security headers & upload safety** — CSP/HSTS/security headers
middleware, rate limiting on auth and sensitive endpoints, and validation +
type/size + malware scanning on all uploads (profile photos, RIB, documents,
album photos).

### Dashboard

- [x] Native dashboard replacing `index_d.php` (widget architecture, 20 widgets)
- [x] Widget layout persistence (`save_accueil.php`) — `ob_dashboard_layout` table, `DashboardService::getWidgetLayout()`, `POST /dashboard/layout`, HTML5 drag-and-drop with debounced save

### Authentication & account (AUTH)

- [x] Login / logout (legacy-hash upgrade)
- [x] Password change (`change_password.php`, `save_password.php`)
- [x] Lost password / send credentials (`lost_password.php`, `send_id.php`) — mailing not set up yet
- [x] Charter acceptance on first login (`charte.php`)
- [x] Connected users view (`connected_users.php`)
- [x] TOTP two-factor authentication (laravel/fortify) — `TotpController`, `docs/security/totp.md`
- [x] LDAP authentication delegation — multi-domain, OU rules, attribute mapping, local-password fallback; `docs/security/ldap.md`
- [x] Per-group password policies (NCSC/ANSSI-aligned) — complexity/history/expiry, HIBP check, strength meter, enforcement middleware; `PasswordPolicyService`, `docs/security/password-policies.md`

### Cross-cutting

- [x] Universal `ob-*` component system (breadcrumb, toolbar, table, commandbar, badge, avatar, toggle)
- [x] `TableExportService` — universal XLS/CSV export
- [x] All list pages migrated to the `ob-*` component set
- [x] **Error / empty pages** — data-driven custom pages for the full HTTP status
  set (400/401/403/404/405/407/408/409/410/411/412/413/416/418/419/429/500/502/503/504/505),
  metadata in `config/error_pages.php`, resolved by `App\Support\ErrorPage`. Each shows a
  client → réseau → serveur connection diagram with the failing node highlighted
  (`errors/partials/diagram`). 4xx keep the normal app shell for authenticated users;
  5xx and re-login codes (401/419) render standalone (`errors/standalone`). 503 honours
  the `php artisan down --message`. No-JS `<noscript>` notice on the app + login layouts
  (replaces legacy `error.php` / `noscript.php`)
- [x] Convention enforcement — CONVENTIONS.md + `ConventionsTest`
- [x] Static-analysis remediation — model `@property` docblocks, PHPStan at 0 errors, Pint clean

### Data isolation by section (multi_site)

- [x] `SectionScopeService` — visible-set authority, navbar switcher, `<x-ob-section-select>`
- [x] Wired into Personnel, Véhicules, Cotisations, Organisation controllers
- [x] Extend scoping to remaining section-tied controllers (Evenement, Garde, Materiel, Consommable, Message) — Document was already done; Statistique is single-section by design (deferred)
- [x] Organizational root section (`S_ID = 0`, `S_PARENT = -1`) is now a first-class, selectable & assignable section; `-1` (`SectionScopeService::ALL`) is the dedicated "all / global" sentinel everywhere (request filters, resolver chain/scope, `ob_user_assignment` / `ob_user_permission` global rows). Migration `2026_06_16_000100` re-sentinels existing global rows `0 → -1`. Navbar switcher reflects the explicit choice (`chosenSectionId`) so "Toutes mes sections" highlights correctly
- [x] **Seed the organizational root section** (`S_ID = 0`, `S_PARENT = -1`) in `CoreSeeder` — `seedRootSection()` uses `insertOrIgnore` (idempotent); runs before `BaseHabilitations` and `SuperAdminProvisioner` so the super-admin gets `P_SECTION = 0` on fresh installs
- [x] Section-scope test for the root: `PermissionRootScopeTest` — 10 unit tests covering `sectionChain` with root (S_ID=0), root ceiling deny cascade, child deny non-cascade, and `effectiveDenied` union across the full ancestor chain; `AdminTest` stub updated with `categorie_consommable` count
- [x] `GeolocationController::index` — replaced exact `P_SECTION =` match with `SectionScopeService::apply()` so the map honours section isolation, navbar scope and root subtree
- [x] `PermissionController::exportGroup` — fixed `section_id > 0` guard to `!== null` so root section (`S_ID = 0`) is included; absent/empty = no filter convention
- [x] Dropped `section_flat` — `DashboardService::getDuty()` and the hours-to-validate widget now derive `NIV` depth from the `section` tree (`getSectionFamilyUp()`) instead of the denormalized cache; migration `2026_06_16_000200` drops the table, `rebuild_section_flat.php` bridge retired (`bridgeable => false`)

### Login screen (Phase 2B)

- [x] Parity tests with the legacy login page
- [x] Modernised login screen

## Features by menu

### Personnel (PERSO)

- [x] Member list, profile view/edit, create/add
- [x] Trombinoscope and org chart
- [x] Exports — XLS, CSV, vCard, PDF livret/carte (client-side pdf-lib + section letterhead)
- [x] Qualifications and training records
- [x] On-call availability / indisponibility
- [x] Full list parity, universal search
- [x] Cotisations — per-member CRUD and org-wide page
- [x] Géolocalisation — Leaflet map
- [x] Tenues / uniforms (`personnel_tenues.php`) — dotation habillement card on personnel show + dedicated manage page (`/personnel/{id}/tenues`); perm 70 = full edit (add/update/delete items, model/year/size/nb); self = size-only update; read-only view for others
- [x] User preferences (`personnel_preferences.php`) — `/personnel/{id}/preferences` page; PP_ID 1 (tooltips toggle), PP_ID 4 (org chart order), PP_ID 15 (items per page); self-edit or perm 2; upsert into `personnel_preferences`; preferences icon in personnel show header
- [x] Salarié data (`upd_personnel_salarie.php`) — TS_ contract/hours fields card on personnel show page (perm 2)
- [x] Emergency contacts (`personnel_contact.php`)
- [ ] Contact types referential CRUD (`contact_type` table) — the "Identifiants de contact" card on the profile only renders when `contact_type` has rows, but there is currently no UI to manage those types; add an admin/référentiel CRUD card (icon, label) so contact handles can be configured without touching the DB
- [x] Homonym management (`homonymes_*.php`) — detect same-name records on personnel show; side-by-side merge page with selective data transfer (competences, formations, participations), radiate/delete options (perm 2/3)
- [x] Contact / email lists (`listecontacts.php`, `listemails.php`) — emails.txt + contacts.csv bulk export from personnel list
- [x] Qualifications export (`qualifications_xls.php`) — XLS / CSV via `TableExportService`, section-scoped, filter & `?cols=` aware
- [x] Meeting participation export (`personnel_reunion_xls.php`) — per-member meeting participation XLS from the personnel show page (`export_badges.php` still pending)
- [x] Trainings CRUD (`personnel_formation.php`) — formations card on personnel show page (add/edit/delete, perm 4; perm 40 for others)
- [x] Per-member formations XLS export (`formations_xls.php`) — button in Formations card

### Activité — Events & Interventions (ACT)

- [x] Event list, detail, create/edit/delete
- [x] Participants, équipes, renforts, matériel and vehicle assignment
- [x] Calendar view
- [x] Exports (XLS + iCal)
- [x] Event duplication (`evenement_duplicate.php`)
- [x] Required competences / diplomas per event (`evenement_competences.php`) — `Postes requis` card on event show: required positions from `evenement_competences`, with actual vs required headcount (counts enrolled participants holding each qualification); inline qty update; delete; add via modal; perm 15
- [x] Per-event trombinoscope (`evenement_trombinoscope.php`) — photo grid of non-absent participants, grouped by function, with grade image and profile link; button in event show header
- [x] Event list export (`evenement_xls.php`) — XLS / CSV via `TableExportService`, period/type/section/search-aware
- [x] Per-event vehicle export (`evenement_vehicule_xls.php`) — XLS via `TableExportService`, button in the event detail Véhicules card
- [x] Main courante (incident log) — `evenement_log` card on event show: list + add/edit/delete modals (perm 15), important-row highlighting, in event section nav
- [x] Event options & participant choices (`evenement_options.php`, `evenement_option_choix.php`) — option groups + checkbox/text/dropdown/date options (perm 15), per-participant choices modal, cascade delete

### Garde — On-call roster (GAR)

- [x] Roster display and assignment
- [x] Guard sheet and replacement management
- [x] Replacement-request list export (XLS / CSV) — mine/section tabs, via `TableExportService`
- [x] Guard exports — XLS / CSV (monthly on-call/astreinte roster via `TableExportService`, section-scoped, `?cols=` aware)
- [x] Type de garde management (`type_garde.php`)
- [x] Demande de renfort (`demande_renfort.php`) — `Demande de renfort` card on event show (vehicle counts, material categories, meeting point, specific request); dedicated manage page at `/events/{code}/renfort-request` with per-type vehicle inputs and category checkboxes; perm 15 to edit

### Planning (PLA)

- [x] Weekly/monthly planning view
- [x] Personal agenda

### Client (CLI)

- [x] Company/client list and detail
- [x] Client list export (XLS / CSV) — section-scoped, search/type-filtered, `?cols=` aware via `TableExportService`

### Logistique — Vehicles (VEH)

- [x] Vehicle list, detail, CRUD, type management
- [x] Vehicle assignment to events — full event history on vehicle show page; year filter, function type column, total km stats, pagination; pre-existing `$typeVehicule`→`$vehicleType` bug fixed
- [x] Vehicle exports (XLS / CSV) — `TableExportService`, section/status/search-aware, `?cols=` selection

### Inventaire — Equipment & Consumables (MAT / CONSO)

- [x] Equipment list and detail/edit
- [x] Consumable stock management
- [x] Type management (matériel, consommable)
- [x] Equipment category management — `categorie_materiel` CRUD in ReferenceController; TM_USAGE field in equipment-type form uses category dropdown; icon preview with FontAwesome
- [x] Embarkation tracking (`materiel_embarquer.php`) — assign/unassign equipment to vehicle from vehicle show page; `equipmentAttach`/`Detach` in `VehicleController`; perm 17; available equipment grouped by type in select; detach button per row
- [x] Consumable category CRUD (`edit_categorie_consommable.php`) — `/admin/references/consumable-category`; inline edit (name, description, icon, order); delete blocked if used by consumable types; badge count on each row; card in references index; perm 5
- [x] Equipment/consumable exports (XLS / CSV) — `TableExportService`, section/search-aware, `?cols=` selection

### Communication (COMM)

- [x] Internal messaging and chat board

### Document (DOC)

- [x] Native library — `ob-*` file-explorer (collapsible folder tree, folders + files in one table, type icons, list/card views); `Document`/`DocumentFolder`/`TypeDocument`/`DocumentSecurity` models + `DocumentService`
- [x] Folder management — create / rename / delete (permission 47)
- [x] Document upload and edit — upload (multi-file), retype, move, delete (permission 47)
- [x] File serving and download — native `document.download`, type/doc-security + section checked (PDF inline, else attachment)
- [x] Document exports — XLS/CSV via `TableExportService` (visible columns, current folder/type)
- [x] Document type & security config — `type_document` CRUD (`DocumentTypeController`, perm 47), `document_security` shown as reference. (Legacy `config_doc.php` is PDF attestation text, not library config — tracked under the PDF/billing items, not here.)
- [x] **Per-object ACL on files & folders** — granular rights (read / download / write / delete / share / fullcontrol) granted to **users / groups / roles / everyone** with explicit **allow *and* deny** (deny wins); folder ACEs **inherited** by descendant folders & documents, the item's own ACEs override. Overlays the section/type security — **no ACE keeps the legacy behaviour** (backward compatible). `ob_document_acl` + `ObDocumentAcl` + `DocumentAclService` (resolver, memoised, 9 unit tests); enforced on every gate (download/write/delete/share); **"Partager"** page (`DocumentAclController`) per file/folder. See [project_documents] memory.

### Photos (PHOTO)

- [x] Native album photo library — `ob-*` grid + bs5-lightbox; `ob_photo_album` + `ob_photo` tables; `PhotoService`, `PhotoController`, section-scoped, perm 44 view / 47 manage
- [x] Public storage — `storage/app/public/photos/{S_ID}/{album_id}/{filename}` served via `storage:link` symlink
- [x] Album CRUD — create, rename/describe, delete (with photo file cleanup)
- [x] Photo upload (multi-file per album), caption edit, set cover, delete
- [x] bs5-lightbox integration — full-screen gallery with keyboard nav, grouped per album
- [x] Drag-and-drop reorder of photos within an album (`sort_order`) — HTML5 native drag, AJAX PATCH to `photo.reorder`, `PhotoService::reorder()` persists positions; drag cursor + dragover outline via CSS
- [x] Bulk delete photos — select-mode toggle, per-card checkmark overlay, floating bulk-action bar, `photo.bulk-destroy` route + controller action (perm 47)
- [x] Photo download (single + zip album) — `photo.download` per photo (perm 44), `photo.album.download` ZIP stream with collision-safe filenames; download button on each card + toolbar button

### Statistique (STAT)

- [x] Participation and event statistics (charts)
- [x] Bilan annuel — Généralités / Activités / Formations with pdf-lib export (WIP)

### Organisation (ORGA)

- [x] Section list + CRUD, organigramme tree
- [x] Cartographie — Leaflet map of sections
- [x] Groups and roles (habilitations) — section-scoped, ceiling-based model
- [x] Rebuilt base habilitations — super-admin account flag (`pompier.P_SUPERADMIN`, uncappable, last-one protected), four capability base groups (Admin/Auditor/User/Guest), classified permission catalog (`ob_permission`), per-org-type section roles, and a production/dev seeding split (`CoreSeeder` vs `DevelopmentDataSeeder`)
- [x] Section show page — tabs Informations, Organigramme, Personnalisation (letterhead, badge, lock delay, devis/facture texts, signature), Agréments & Médailles
- [x] Section Cotisation tab — RIB file upload and remaining fields — `CODE_BANQUE`, `ETABLISSEMENT`, `GUICHET`, `COMPTE`, `CLE_RIB` fields added; RIB file upload (PDF/JPG/PNG, stored in private storage, migration `2026_06_15_180000`); download route `organization.sections.rib.download`
- [x] Position (poste) management — `Compétences` page at `/admin/references/position`; CRUD with boolean flags (formation, secourisme, expirable, diplôme, etc.); edit modal per row; delete blocked if used in qualifications or event requirements; perm 18
- [x] Team (equipe) management — `Types de compétence` page at `/admin/references/team`; CRUD with inline edit; delete blocked if contains postes; badge links to filtered position list; both pages added to references index; perm 18
- [x] Protect the organizational root section (`S_ID = 0`): `destroySection` returns 302 with error; `updateSection` forces `S_INACTIVE = false`; reparent already pinned to `-1`
- [x] Habilitations export (`habilitations_xls.php`)

### Configuration — Admin (ADMIN)

- [x] Application settings CRUD (tabbed UI)
- [x] Parametrage reference tables (type-evenement/participation/materiel/consommable/vehicule)
- [x] Theme and icon configuration, grade icons
- [x] Audit log view
- [x] Backup and restore
- [x] Maintenance page (replaces `upgrade.php`)
- [x] Habilitations — section-scoped ceiling model, 3-tab admin UI, `PermissionResolver`
- [x] Feature/module unification — `ob_feature` registry, `FeatureService`, `feature:` middleware, Fonctionnalités admin page
- [x] Tests and parity for migrated ADMIN pages; bridge routes redirect to native
- [x] Full ACL with groups — allow/deny at every tier (user override > section deny > group/role deny > group/role allow > default deny); `ob_user_permission` + `ob_group_permission.effect`; tri-state matrices + 4th "Dérogations" tab; resolver precedence tests. See CONVENTIONS §9.
- [x] Surface user-level overrides in "Mes droits" — personal allow/deny rows from `ob_user_permission` shown in the preview table with dedicated icons and strikethrough styling
- [x] `paramfnv` vehicle function types (`type_fonction_vehicule`) — CRUD at `/admin/references/vehicle-function`; inline list with name/description/order; perm 5
- [x] Grade category (`categorie_grade`) CRUD — at `/admin/references/grade-category`; inline description edit; delete blocked if grades assigned; badge count; link to grade icons page; perm 5
- [x] `paramfn` participation function enhanced fields — `PS_ID`/`PS_ID2` (required competence + alternative) and `INSTRUCTOR` flag added to `type_participation` create/edit; grouped competence dropdowns with optgroups per team; edit modal on each row; perm 5. Legacy `paramfn.php` bridge retired for this functionality.

### Settings wired

- [x] Password policies — complexity, history, expiry (IDs 15, 16, 17, 70) — handled via Administration > Sécurité (annotated obsolete in `AdminController::settings()`)
- [x] Session policies (IDs 34, 36, 49) — handled via Administration > Sécurité
- [x] Action history (ID 25) — handled via Administration > Sécurité
- [x] Sensitive data handling (ID 33), file ACLs (ID 42), terms of use (ID 48) — handled via Sécurité / document ACL system
- [x] First-login banner (ID 69) — handled via Administration > Sécurité
- [x] Organisation identity — name, description, contact mail, logo, login image (IDs 6, 8, 39, 40, 71, 75) — `AppIdentityService` reads and memoises all 6 settings; sidebar uses org name + logo; login page uses org name + splash background image

### API & integrations (Phase 3)

- [x] iCal export
