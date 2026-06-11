# OpenBrigade Migration TODO

Working tracker for migrating the legacy eBrigade app (`archive/legacy_app/`) into
the native Laravel application, menu by menu.

Rules and process live elsewhere — read them first:
[CONVENTIONS.md](../docs/dev/CONVENTIONS.md) (how code is written),
[ARCHITECTURE.md](../docs/dev/ARCHITECTURE.md) (where things live),
[DEVELOPMENT.md](../docs/dev/DEVELOPMENT.md) (how to run it),
[legacy-mapping.md](../docs/dev/legacy-mapping.md) (legacy file map),
[CONTRIBUTING.md](CONTRIBUTING.md) (branches, commits, PRs).

When you complete an item, tick its checkbox. Update
[legacy-mapping.md](../docs/dev/legacy-mapping.md) when a file moves from legacy
to native. Keep the gates green: `composer pint -- --test`, `composer analyse`,
`composer test`.

Legend: `[x]` done · `[ ]` open · WIP = implemented but parity not verified.

---

## Phase 1 — Dashboard (done)

- [x] Native dashboard replacing `index_d.php` (widget architecture, 20 widgets)
- [ ] Widget layout persistence (`save_accueil.php`)
- [ ] First-run setup wizard (`wizard.php`)

## Authentication & account (AUTH)

- [x] Login / logout (legacy-hash upgrade)
- [ ] Password change (`change_password.php`, `save_password.php`)
- [ ] Lost password / send credentials (`lost_password.php`, `send_id.php`)
- [ ] Charter acceptance on first login (`charte.php`)
- [ ] Connected users view (`connected_users.php`)

## Cross-cutting (done)

- [x] Universal `ob-*` component system (breadcrumb, toolbar, table, commandbar, badge, avatar, toggle)
- [x] `TableExportService` — universal XLS/CSV export
- [x] All list pages migrated to the `ob-*` component set
- [x] Convention enforcement — CONVENTIONS.md + `ConventionsTest`
- [x] Static-analysis remediation — model `@property` docblocks, PHPStan at 0 errors, Pint clean

## Cross-cutting — Data isolation by section (multi_site)

- [x] `SectionScopeService` — visible-set authority, navbar switcher, `<x-ob-section-select>`
- [x] Wired into Personnel, Véhicules, Cotisations, Organisation controllers
- [ ] Extend scoping to remaining section-tied controllers (Evenement, Garde, Materiel, Consommable, Document, Message, Statistique)

---

## Phase 2 — Menu by menu

### Personnel (PERSO)
- [x] Member list, profile view/edit, create/add
- [x] Trombinoscope and org chart
- [x] Exports — XLS, CSV, vCard, PDF livret/carte (client-side pdf-lib + section letterhead)
- [x] Qualifications and training records
- [x] On-call availability / indisponibility
- [x] Full list parity, universal search
- [x] Cotisations — per-member CRUD and org-wide page
- [x] Géolocalisation — Leaflet map
- [ ] Rework grade system
- [ ] Trainings & diplomas CRUD (`personnel_formation.php`, `diplome_edit.php`)
- [ ] Tenues / uniforms (`personnel_tenues.php`)
- [ ] User preferences (`personnel_preferences.php`)
- [ ] Salarié data (`upd_personnel_salarie.php`)
- [ ] Emergency contacts (`personnel_contact.php`)
- [ ] Homonym management (`homonymes_*.php`)
- [ ] Contact / email lists (`listecontacts.php`, `listemails.php`)
- [ ] Custom member fields (`specific_info.php`)
- [ ] Remaining exports (`formations_xls.php`, `qualifications_xls.php`, `personnel_reunion_xls.php`, `export_badges.php`)

### Activité — Events & Interventions (ACT)
- [x] Event list, detail, create/edit/delete
- [x] Participants, équipes, renforts, matériel and vehicle assignment
- [x] Calendar view
- [x] Exports (XLS + iCal)
- [ ] Editable PDF for conventions
- [ ] Main courante (incident log)
- [ ] Event duplication (`evenement_duplicate.php`)
- [ ] Event options & participant choices (`evenement_options.php`, `evenement_option_choix.php`)
- [ ] Required competences / diplomas per event (`evenement_competences.php`, `evenement_diplome.php`)
- [ ] Participant notifications (`evenement_notify.php`)
- [ ] Event report (`evenement_rapport.php`)
- [ ] Per-event trombinoscope (`evenement_trombinoscope.php`)
- [ ] Event billing & tariffs (`evenement_facturation*.php`, `evenement_tarif*.php`)
- [ ] Remaining exports (`evenement_xls.php`, `evenement_vehicule_xls.php`)

### Garde — On-call roster (GAR)
- [x] Roster display and assignment
- [x] Guard sheet and replacement management
- [ ] Use the new calendar library when implemented (see PLA)
- [ ] Automatic piquet/guard generation
- [ ] Rest periods (`repos_*.php`)
- [ ] Guard exports (XLS, PDF)
- [ ] Type de garde management (`type_garde.php`)
- [ ] Demande de renfort (`demande_renfort.php`)

### Planning (PLA)
- [x] Weekly/monthly planning view
- [x] Personal agenda
- [ ] Migrate calendars to a universal calendar library (FullCalendar or similar)
- [ ] Dashboard agenda widget on the new calendar library, opening the detailed calendar view
- [ ] Schedule (horaires) management
- [ ] Planning exports

### Client (CLI)
- [x] Company/client list and detail
- [ ] Billing and financial exports
- [ ] PDF attestations (fiscale, formation)
- [ ] Billable elements (`element_facturable.php`)
- [ ] Expense notes (`note_frais_*.php`)
- [ ] Prélèvements configuration (`config_prelevements.php`)
- [ ] Payment categories (`edit_categorie*.php`)

### Logistique — Vehicles (VEH)
- [x] Vehicle list, detail, CRUD, type management
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
- [ ] Email composition and send
- [ ] Alert creation and sending
- [ ] SMS history view
- [ ] Push notification monitor
- [ ] Reminders / relances (`reminder.php`)
- [ ] RSS feed (`rss.php`)

### Document (DOC)
- [x] Document and folder tree view
- [ ] Document upload and edit
- [ ] File serving and download
- [ ] Document exports (PDF)
- [ ] Document configuration (`config_doc.php`)

### Statistique (STAT)
- [x] Participation and event statistics (charts)
- [x] Bilan annuel — Généralités / Activités / Formations with pdf-lib export (WIP)
- [ ] Financial reports (`report_cotisations.php`)
- [ ] Custom exports (XLS, TCD, HTML, TXT, SQL)

### Organisation (ORGA)
- [x] Section list + CRUD, organigramme tree
- [x] Cartographie — Leaflet map of sections
- [x] Groups and roles (habilitations) — section-scoped, ceiling-based model
- [x] Section show page — tabs Informations, Organigramme, Personnalisation (letterhead, badge, lock delay, devis/facture texts, signature), Agréments & Médailles
- [ ] Section Cotisation tab — RIB file upload and remaining fields (IBAN/BIC manual entry is done)
- [ ] Organigramme tab as an interactive org-chart (currently role-grouped lists)
- [ ] Rank and grade management
- [ ] Position (poste) management
- [ ] Team (equipe) management
- [ ] Section deactivation / radiation (`section_stop.php`, `radier_section.php`)
- [ ] Guard order & responsables (`choice_section_order.php`, `upd_responsable.php`)
- [ ] Competence hierarchy (`hierarchie_competence.php`)
- [ ] Habilitations export (`habilitations_xls.php`)

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
- [ ] Plugins marketplace — `/admin/plugins` is a placeholder; install/download flow to design
- [ ] `paramfn` / `paramfnv` (billable + vehicle function params) and grade category CRUD — still on the legacy bridge
- [ ] Maintenance utilities (`update_app.php`, `buildsql.php`, `decrypt.php`, `import_api.php`, `debug_data.php`)

### Opérations d'urgence (DPS / SITAC / Victimes)
- [ ] DPS sizing calculator (`dps.php`, `dps_calc.php`, `dps_save.php`)
- [ ] SITAC tactical board (`sitac*.php`)
- [ ] Victim management (`victimes.php`, `liste_victimes.php`, `scan_victime.php`, `intervention_edit.php`)

### Settings not yet wired
- [ ] `password_quality` (ID 15) — complexity validation in `AuthService`
- [ ] `password_expiry_days` (ID 70) — expiry enforcement on login
- [ ] `info_connexion` (ID 69) — first-login banner
- [ ] `ameliorations` (ID 80) — telemetry opt-in

---

## Phase 2B — Login screen (done)
- [x] Parity tests with the legacy login page
- [x] Modernised login screen

## Phase 3 — API and integrations
- [ ] Inventory legacy `api/` endpoints and consumers
- [ ] Rewrite or proxy each as a versioned route under `routes/api.php`
- [x] iCal export
- [ ] QR-code generation
- [ ] Geolocation helpers (`gmaps_evenement.php`, `localize*.php`, `map.php`, `zipcode.php`)
- [ ] API tests and parity check; retire legacy API files

## Phase 3B — Non-menu plugins / modules
- [ ] Inventory plugin/module files (`addons.php`, `install_addon.php`, `download_*.php`)
- [ ] Define module boundaries; migrate config, routes, assets, permissions
- [ ] Animaux module (`personnel_maitre.php`, `cav_edit.php` — `ob_feature` flag exists, status wip)
- [ ] SMS gateway integration (`lib/SMSGatewayMe/`, `fonctions_sms.php`)
- [ ] Feature tests per module; remove legacy loaders after cutover

## Phase 4 — Cutover and decommission
- [ ] Keep the legacy parity matrix current (legacy-mapping.md)
- [ ] User acceptance validation on critical workflows
- [ ] Remove the legacy bridge routes and `LegacyBridgeController`
- [ ] Delete `archive/legacy_app/` and all bridge configuration
- [ ] Execute production cutover plan
- [ ] Update README and docs to the fully-migrated state
