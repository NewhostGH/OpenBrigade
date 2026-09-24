# OpenBrigade Migration TODO

Working tracker for migrating the legacy eBrigade app (`archive/legacy_app/`) into
the native Laravel application, menu by menu. Large forward-looking ideas beyond
the migration live in [IDEAS.md](IDEAS.md).

Rules and process: [conventions.md](../docs/dev/conventions.md) (how code is
written), [architecture.md](../docs/dev/architecture.md) (where things live),
[development.md](../docs/dev/development.md) (how to run it),
[legacy-mapping.md](../docs/dev/legacy-mapping.md) (legacy file map),
[CONTRIBUTING.md](CONTRIBUTING.md) (branches, commits, PRs).

When you complete an item, tick its checkbox and move it to **Shipped**. Update
the legacy file map when a file moves from legacy to native. Keep the gates
green: `composer pint -- --test`, `composer analyse`, `composer test`.

Legend: `[ ]` open · `[x]` done · WIP = implemented but parity not verified.
The **Backlog** is grouped so earlier groups underpin later ones.

---

## Backlog

### Production readiness

- [ ] **RGPD / data-privacy compliance**: the app holds medical aptitude, home
  addresses, emergency contacts and member geolocation. Implement: data-subject
  export (portability), right-to-erasure workflow, retention policy + automated
  purge, consent tracking, a processing register, and access logging on
  sensitive records. Encrypt sensitive columns at rest.

## Feature migration (menu by menu)

### Personnel (PERSO)

- [ ] Diploma print layout config (`diplome_edit.php`): complex PDF field positioning admin screen
- [ ] Custom member fields (`specific_info.php`)
- [ ] `export_badges.php`: member badge/ID card PDF export

### Activité: Events & Interventions (ACT)

- [ ] Participant notifications (`evenement_notify.php`): needs the notification layer
- [ ] Event report (`evenement_rapport.php`)
- [ ] Editable PDF for conventions
- [ ] Event billing & tariffs (`evenement_facturation*.php`, `evenement_tarif*.php`)

### Planning (PLA)

- [ ] Migrate calendars to a universal calendar library (FullCalendar or similar)
- [ ] Dashboard agenda widget on the new calendar library, opening the detailed calendar view
- [ ] Schedule (horaires) management
- [ ] Planning exports

### Garde: On-call roster (GAR)

- [ ] Use the new calendar library when implemented (see PLA)
- [ ] Automatic piquet/guard generation
- [ ] Rest periods (`repos_*.php`)
- [ ] Guard exports: PDF
- [ ] Demande de renfort, transmit the request to another section (email, or in-app message/notification): currently the request is only stored/displayed on the event; add a way to actually communicate it to the target section so they can respond with renfort sub-events, needs the notification layer

### Communication (COMM)

Needs the notification / messaging infrastructure above.

- [ ] Email composition and send
- [ ] Alert creation and sending
- [ ] SMS history view
- [ ] Push notification monitor
- [ ] Reminders / relances (`reminder.php`)
- [x] RSS feed (`rss.php`)

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

- [ ] Guard order & responsables (`choice_section_order.php`, `upd_responsable.php`)

### Configuration: Admin (ADMIN)

*(cleared, see Shipped ▸ Configuration, Admin. Deferrals: in-app update
flow → Release strategy; import-API endpoints → API & integrations (the
settings themselves are live); masked SMS-password input → COMM.)*

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

Shipped: marketplace (registries, install pipeline, `ob_plugin` runtime,
`PluginLoader`), Animaux plugin, SMS gateway integration, feature tests. See
`docs/admin/plugins.md` / `docs/dev/plugins.md`.

## Cutover & decommission (Phase 4)

- [ ] Keep the legacy parity matrix current (legacy-mapping.md)
- [ ] User acceptance validation on critical workflows
- [ ] Remove the legacy bridge routes and `LegacyBridgeController`
- [ ] Delete `archive/legacy_app/` and all bridge configuration
- [ ] Execute production cutover plan
- [ ] Update README and docs to the fully-migrated state

## Release strategy

How the app is built, shipped and upgraded in production.

- [ ] **CD pipeline**: extend the existing CI (`.github/workflows/ci.yml`) into
  a deploy pipeline (build assets, run migrations, zero-downtime release,
  rollback path); gate on the green checks (pint/phpstan/test).
- [ ] **Migration & release runbook**: documented deploy steps, DB-migration
  policy (forward-only, backward-compatible where possible), and a rollback
  procedure.
- [ ] **Environments**: clearly defined local / staging / production configs
  and secrets management; staging mirrors production for UAT.
- [ ] **In-app update / maintenance flow**: successor to legacy `update_app.php`
  / `upgrade.php`: surface migration status, run pending migrations, and toggle
  maintenance mode from the admin UI.
- [x] **Release verification**: `ob:release:verify` post-deploy smoke-check
  gate (`ReleaseVerificationService`); see `docs/admin/release-verification.md`.

---

## Shipped

One line per fully-done area; see the linked docs for detail.

- **Release strategy**: SemVer 2.0.0 + Keep-a-Changelog `CHANGELOG.md`, `VERSION` file, `php artisan ob:version`. See `docs/dev/versioning.md`.
- **Production readiness**: notification/messaging infra (mail+SMS, queues+scheduler), observability (structured logging, Sentry/GlitchTip, `/health`), Redis/queue/mail health probes, backup robustness (off-site mirror, weekly restore drill). See `docs/admin/observability.md`, `docs/admin/backup-and-restore.md`, `docs/admin/sms.md`.
- **Foundations**: French-only i18n scaffolding (`lang/fr`), security headers/rate limiting/upload malware scanning.
- **Dashboard**: native widget-based dashboard (20 widgets) replacing `index_d.php`, with layout persistence.
- **Authentication & account (AUTH)**: login/logout, password change/reset, charter acceptance, connected-users view, TOTP 2FA, LDAP delegation, per-group password policies. See `docs/security/totp.md`, `docs/security/ldap.md`, `docs/security/password-policies.md`.
- **Cross-cutting**: universal `ob-*` component set on all list pages, `TableExportService`, data-driven error/empty pages for the full HTTP status set, `ConventionsTest` enforcement, PHPStan/Pint clean.
- **Data isolation (multi_site)**: `SectionScopeService` wired across all section-tied controllers, organizational root section (`S_ID = 0`) as first-class, `section_flat` dropped in favor of live tree derivation.
- **Login screen**: parity tests + modernized UI.
- **Personnel (PERSO)**: full CRUD, trombinoscope, org chart, exports (XLS/CSV/vCard/PDF), qualifications, cotisations, géolocalisation, tenues, preferences, salarié data, contacts, homonym merge, trainings.
- **Activité (ACT)**: event CRUD, participants/équipes/renforts/matériel/vehicles, calendar, exports (XLS+iCal), duplication, required competences, per-event trombinoscope, main courante, event options.
- **Garde (GAR)**: roster display/assignment, replacement management, exports, type de garde management, demande de renfort.
- **Planning (PLA)**: weekly/monthly view, personal agenda.
- **Client (CLI)**: company CRUD + exports.
- **Vehicles (VEH)**: CRUD, type management, event-assignment history, exports.
- **Equipment/Consumables (MAT/CONSO)**: CRUD, category management, embarkation tracking, exports.
- **Communication (COMM)**: internal messaging board.
- **Document (DOC)**: native `ob-*` file-explorer library, folder/type/security config, per-object ACL (allow/deny, inherited). See [project_documents] memory.
- **Photos (PHOTO)**: native album library, upload/reorder/bulk delete, lightbox, download/zip.
- **Statistique (STAT)**: participation charts, bilan annuel (WIP parity).
- **Organisation (ORGA)**: section CRUD, cartographie, habilitations (full section-scoped ACL), section deactivation/radiation, first-run setup wizard, rank/grade rework, interactive org-chart. See [project_habilitations] memory.
- **Configuration: Admin (ADMIN)**: settings CRUD, référentiels, theme/icons, audit log, backup/restore, maintenance, habilitations UI, feature registry, legacy settings wired, notifications page, plugins marketplace. See `docs/admin/plugins.md`.
- **Settings wired**: password/session policies, action history, sensitive-data handling, first-login banner, org identity — all via Administration ▸ Sécurité / Organisation.
- **API & integrations**: iCal export.
