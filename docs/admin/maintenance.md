# Maintenance

Administration ▸ Maintenance (permission 14) groups the system information,
migration status and the maintenance-related settings.

## Maintenance mode

Toggle **Mode maintenance** (configuration row 37) to restrict the app to
administrators. While enabled:

- users **without** permission 14 get the standard 503 page carrying the
  **Texte de maintenance** (row 41; legacy `<br>` markup is flattened to
  plain text) and can still log out;
- **administrators** (permission 14 or super admin) keep full access — use
  this to perform the maintenance and switch the mode back off;
- **guests** keep the whole login flow, with a maintenance notice shown on
  the login page, so an administrator can always sign in.

This replaces Laravel's `php artisan down` for day-to-day use: it is
administrable from the UI, survives deployments and never locks the admin
out. Enforced by `App\Http\Middleware\MaintenanceMode` (web group);
`/health` and `/up` are API-style routes outside the gate, so uptime probes
keep reporting during maintenance.

## Database optimization

The **Optimisation automatique de la base de données** toggle (row 14)
gates `ob:db:optimize`, a weekly `OPTIMIZE TABLE` pass over every table
(Sundays 04:30, per-table failure isolation, summary in the audit trail).
The **Optimiser la base de données** button runs it immediately
(`--force`, bypassing the toggle). Tables are briefly locked — prefer
off-peak hours.

## Actions

The **Actions** card offers one-click, audited operations:

| Button                       | Effect                                                        |
| ---------------------------- | ------------------------------------------------------------- |
| Vider les caches             | `cache:clear` + `config:clear` + `route:clear` + `view:clear` |
| Optimiser la base de données | `ob:db:optimize --force` (summary shown in the flash)         |
| Purger les journaux          | `ob:logs:prune` (honours the configured retention)            |

## Telemetry

The **Aider à améliorer OpenBrigade** opt-in (row 80, Options tab) enables
a weekly anonymous ping (`ob:telemetry:ping`, Mondays 03:30) to
`https://telemetry.openbrigade.fr` (overridable via `OB_TELEMETRY_URL`).
The payload is strictly limited to: a non-reversible instance hash
(sha256 of the app key, truncated), the app / PHP / Laravel versions, the
organisation type id and the active-member count rounded to the nearest
ten. No names, emails, hostnames or IPs — unlike the legacy
`push_monitoring_info()` it replaces. An unreachable endpoint is silently
ignored.

## Legacy utilities

- `update_app.php` (in-app self-update) — deferred to the RELEASE epic
  (in-app update / maintenance flow).
- `decrypt.php`, `debug_data.php` — dropped: debug helpers with no place in
  the new app (the latter was an XSS hazard).
- `buildsql.php` — obsolete: no stored SQL functions in the Laravel schema.
