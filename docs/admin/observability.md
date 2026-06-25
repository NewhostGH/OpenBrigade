# Observability (Journal d'activité)

OpenBrigade ships structured logging, error tracking, a health-check endpoint
and basic performance monitoring. Everything is administrable from
**Administration → Journal d'activité** (`/admin/monitoring`, permission `49`),
which has three tabs:

| Tab            | What it shows                                                                                                                                                                                                                                                       |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Journaux**   | The unified `ob_log_entry` log — every canal (activity, audit, auth, security, app, error, performance), filterable by canal/level, with an expandable context/trace column. Rendered with the standard `x-ob-table` component (column toggle, export, pagination). |
| **Santé**      | Live `/health` probes + a 24 h slow-request snapshot.                                                                                                                                                                                                               |
| **Paramètres** | Per-canal levels, outputs, retention, the Sentry DSN + error tracking, performance.                                                                                                                                                                                 |

> The legacy **Activité** tab and the `log_history` write path have been retired:
> business activity is now the **`activity` canal** of `ob_log_entry`. Existing
> `log_history` rows are backfilled into it by a migration.

---

## Logging pipeline

Logging is built on Laravel's Monolog stack. The default `stack` channel
(`config/logging.php`) fans every record out to two legs:

- **File** — a rotating daily file in `storage/logs/` (`daily` channel), or a
  single file (`single`).
- **Database** — the custom `database` channel
  ([`App\Logging\DatabaseLogger`](../../app/Logging/DatabaseLogger.php)) persists
  records to the unified **`ob_log_entry`** table via
  [`DatabaseLogHandler`](../../app/Logging/DatabaseLogHandler.php), enriched by
  [`RequestContextProcessor`](../../app/Logging/RequestContextProcessor.php) with
  the acting pompier and request metadata (IP, method, URL, user-agent).

`ob_log_entry` is the single source for structured logs, activity context and
captured **error traces** — an uncaught exception is recorded with its class,
message and full stack trace in dedicated columns.

### Canaux (channels) and per-canal levels

Logging is organised into logical **canaux**, each with its own minimum level so
an admin can, e.g., capture every `activity` and `auth` event while keeping `app`
at warning:

| Canal         | Default level | Source                                                |
| ------------- | ------------- | ----------------------------------------------------- |
| `activity`    | info          | Business activity (`Audit::activity`, ex-log_history) |
| `audit`       | info          | State-changing HTTP requests (`AuditRequests`)        |
| `auth`        | info          | Authentication / session events                       |
| `security`    | info          | Upload rejections, denials, throttling                |
| `app`         | warning       | General application logs                              |
| `error`       | error         | Uncaught exceptions                                   |
| `performance` | info          | Slow-request samples                                  |

The `database` channel accepts every record; the per-canal threshold is enforced
in [`DatabaseLogHandler`](../../app/Logging/DatabaseLogHandler.php) from the
`obs_level_<canal>` settings. The file leg mirrors at least the **lowest** canal
level. All of this is reconciled **at runtime** by
[`AppServiceProvider::configureObservability()`](../../app/Providers/AppServiceProvider.php),
so a change in the UI takes effect on the next request — no deploy, no `.env`
edit. The step is guarded: if settings can't be read (fresh install, DB outage,
tests) the shipped defaults stand.

### Settings

Stored as `NAME`/`VALUE` rows in the legacy `configuration` table and read
through [`App\Services\LoggingSettingService`](../../app/Services/LoggingSettingService.php)
(request-cached singleton, falls back to typed defaults).

| Setting                   | Default   | Effect                                                     |
| ------------------------- | --------- | ---------------------------------------------------------- |
| `obs_level_<canal>`       | see above | Minimum level kept in `ob_log_entry` for that canal.       |
| `obs_log_to_db`           | on        | Write to `ob_log_entry`.                                   |
| `obs_log_to_file`         | on        | Write to `storage/logs`.                                   |
| `obs_file_channel`        | `daily`   | `daily` (rotating) or `single`.                            |
| `obs_file_retention_days` | 14        | Daily-file retention.                                      |
| `obs_db_retention_days`   | 90        | Prune `ob_log_entry` older than this (`0` = keep forever). |
| `obs_sentry_dsn`          | ''        | Sentry/GlitchTip DSN (configured in the UI, not `.env`).   |
| `obs_error_tracking`      | off       | Report uncaught exceptions to Sentry/GlitchTip.            |
| `obs_perf_enabled`        | on        | Track request duration / peak memory.                      |
| `obs_perf_slow_ms`        | 1000      | Log requests slower than this to the `performance` canal.  |

### Activity / audit trail (`App\Support\Audit`)

Activity, authentication and security events are recorded through a single
ergonomic helper that buckets each event into a canal — a readable one-liner
anywhere in the backend:

```php
Audit::activity('password.changed', ['target' => $id]);  // canal "activity"
Audit::action('event.deleted', ['event_id' => $id]);     // canal "audit"
Audit::auth('login.failed', ['login' => $login], 'warning');
Audit::security('upload.rejected', ['reason' => $msg], 'warning');
```

These go to `ob_log_entry`; whether each is kept is governed by its canal's
`obs_level_<canal>` setting. Actor / IP / URL are attached automatically.

Coverage is broad without per-controller churn:

- [`AuditRequests`](../../app/Http/Middleware/AuditRequests.php) middleware logs
  **every state-changing request** (POST/PUT/PATCH/DELETE) — route, method,
  status — instrumenting all controllers from one place. Reads are ignored.
- Authentication events (`login.success`, `login.failed`, `logout`) are logged
  in `AuthController`.
- Upload rejections are audited at the `UploadRejectedException` render hook
  (`bootstrap/app.php`) — the single choke point for every rejection reason.
- Business activity (`AccountController` etc.) uses `Audit::activity()` on the
  **`activity` canal**, which replaces the retired `log_history` insert path.

### Retention

The scheduled command `ob:logs:prune` (daily at 03:10, see `routes/console.php`)
deletes `ob_log_entry` rows older than `obs_db_retention_days`. Run it manually:

```bash
php artisan ob:logs:prune
```

---

## Error tracking — self-hosted Sentry / GlitchTip

Errors are reported through the official `sentry/sentry-laravel` SDK. The backend
is **GlitchTip**, a lightweight, Sentry-API-compatible self-hosted server, shipped
in `docker-compose.yml` as part of the `full` and `dev` Compose profiles.

Reporting is **opt-in twice over**: it only happens when the `obs_error_tracking`
setting is on **and** a DSN is set. The **DSN is an admin setting**
(`obs_sentry_dsn`, entered under Journal d'activité ▸ Paramètres), not a `.env`
var — the `SENTRY_LARAVEL_DSN` env remains only as a one-time fallback imported
into that setting on first migrate. When disabled, `AppServiceProvider` clears
the DSN, so [`Integration::handles()`](../../bootstrap/app.php) in the exception
handler is a safe no-op.

### Bringing up GlitchTip

```bash
# Set GLITCHTIP_SECRET_KEY and GLITCHTIP_DB_PASSWORD in .env first.
# GlitchTip ships with the "full" (and "dev") profile:
docker compose --profile full up -d
```

1. Open `http://localhost:8000` (or `GLITCHTIP_PORT`), create an account and an
   organisation/project.
2. Copy the project's **DSN**, but **rewrite the host** for the Docker network
   (see below), then paste it into the **DSN Sentry / GlitchTip** field under
   Journal d'activité → Paramètres and enable **Suivi des erreurs**.
3. (Recommended) set `GLITCHTIP_OPEN_REGISTRATION=false` and restart so no new
   accounts can self-register.

> **DSN host on the compose network.** Error reporting is server-side: the PHP
> SDK runs inside the `app` container and connects to the DSN host itself, so the
> `localhost:8000` that GlitchTip prints is wrong (inside `app`, `localhost` is
> the app container). Use GlitchTip's **service name + internal port**:
>
> - ❌ `http://<key>@localhost:8000/1`
> - ✅ `http://<key>@glitchtip-web:8080/1`
>
> (`glitchtip-web` listens on `8080` internally; the `8000:8080` mapping is
> host-only.) Verify from the app container:
> `docker compose exec app sh -lc "wget -qO- http://glitchtip-web:8080/_health/ && echo OK"`.
> This is the opposite direction from the uptime monitor, which reaches the app
> *from* GlitchTip via `host.docker.internal:8080`.

The DSN lives in the admin settings (`obs_sentry_dsn`), not `.env`. The remaining
env keys (see `.env.example.*`) tune the SDK and the server: `SENTRY_ENVIRONMENT`,
`SENTRY_TRACES_SAMPLE_RATE`, `GLITCHTIP_*` (and `SENTRY_LARAVEL_DSN` only as a
one-time fallback imported on first migrate).

Configuration lives in [`config/sentry.php`](../../config/sentry.php). Validation
errors, auth/authorization failures, HTTP exceptions and rejected uploads are
ignored (not actionable). PII is off by default.

> **Why the DSN is resolved in `register()`, not `boot()`.** The Sentry service
> provider boots *before* `AppServiceProvider` (package providers boot before app
> providers) and there it eagerly builds its hub and decides whether to capture
> events from `config('sentry.dsn')` at that moment. So the DSN is applied in
> `AppServiceProvider::register()` (`configureErrorTracking()`), which runs before
> any provider boots — doing it in `boot()` is too late and silently drops every
> event. Verify the wiring with `php artisan sentry:test` (it prints the resolved
> DSN and sends a test event).

### Verifying the pipeline (Diagnostic)

The **Santé** tab has a **Diagnostic** card that deliberately triggers an issue
so you can confirm the whole pipeline works end to end:

- **Déclencher une exception** — throws an uncaught exception: returns a real 500,
  reported to Sentry/GlitchTip (when enabled) and logged to the `error` canal.
- **Journaliser une erreur de test** — writes one `error`-canal entry (no 500).
- **Simuler une requête lente** — sleeps past `obs_perf_slow_ms` so a
  `performance` entry is recorded.

Uncaught exceptions are tagged into the `error` canal via
`$exceptions->context()` in [`bootstrap/app.php`](../../bootstrap/app.php).

---

## Health check

`GET /health` returns a JSON report for uptime probes and load balancers — no
authentication (it exposes availability only, never data). It probes the
database, cache, storage writability, free disk, and ClamAV (when upload scanning
is enabled), via
[`App\Services\HealthCheckService`](../../app/Services/HealthCheckService.php).

- **200** — overall `ok` or `degraded`.
- **503** — at least one probe is `down`.

```json
{
  "status": "ok",
  "version": "OpenBrigade 6.0",
  "timestamp": "2026-06-23T14:02:11+00:00",
  "checks": {
    "database": { "status": "ok", "latency_ms": 3 },
    "cache":    { "status": "ok" },
    "storage":  { "status": "ok", "writable": true },
    "disk":     { "status": "ok", "free_pct": 64 },
    "clamav":   { "status": "skipped", "reason": "scan disabled" }
  }
}
```

Laravel's bare `/up` probe is still available. Set `APP_VERSION` (a tag or short
commit SHA) to surface a meaningful version here and as the Sentry release.

### Uptime monitoring (GlitchTip)

GlitchTip's built-in uptime monitors (run by the `glitchtip-worker` container)
can poll `/health`. In GlitchTip → your organization → **Uptime Monitors** →
**New Monitor**:

| Field           | Value                                     |
| --------------- | ----------------------------------------- |
| Monitor type    | `GET`                                     |
| URL             | `http://host.docker.internal:8080/health` |
| Expected status | `200`                                     |
| Interval        | `60` seconds                              |

> Use `host.docker.internal:8080`, **not** `http://app/health`: GlitchTip
> validates the URL as a fully-qualified domain and rejects single-label hosts
> like `app` with a `422`. `host.docker.internal` (Docker Desktop) has dots and
> reaches the app via its published host port.

Alerts route to the monitor's project; configure a real `GLITCHTIP_EMAIL_URL`
(SMTP) or a webhook integration, since the compose default `consolemail://` only
prints to the worker log.

---

## Performance monitoring

[`App\Http\Middleware\TrackPerformance`](../../app/Http/Middleware/TrackPerformance.php)
(appended to the `web` group) times every request and logs those slower than
`obs_perf_slow_ms` to the `performance` channel of `ob_log_entry`, with the
duration, peak memory, status code and route. The **Santé** tab summarises the
last 24 h (count, average, max) and lists the ten slowest requests.

For request-level distributed tracing, raise `SENTRY_TRACES_SAMPLE_RATE` so the
Sentry SDK also reports performance transactions to GlitchTip.
