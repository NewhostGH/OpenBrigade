# Observability (Journal d'activité)

Structured logging, error tracking, a health-check endpoint and performance
monitoring, administrable from **Administration → Journal d'activité**
(`/admin/monitoring`, permission `49`), three tabs:

| Tab            | Shows                                                                           |
| -------------- | -------------------------------------------------------------------------------- |
| **Journaux**   | Unified `ob_log_entry` log, every canal, filterable by canal/level, expandable context/trace, `x-ob-table` (columns, export, pagination). |
| **Santé**      | Live `/health` probes + 24 h slow-request snapshot.                            |
| **Paramètres** | Per-canal levels, outputs, retention, Sentry DSN + error tracking, performance. |

> The legacy **Activité** tab / `log_history` write path are retired: business
> activity is now the **`activity` canal** of `ob_log_entry` (backfilled by
> migration).

---

## Logging pipeline

Built on Laravel's Monolog stack. The default `stack` channel
(`config/logging.php`) fans out to two legs:

- **File**: a rotating daily file in `storage/logs/` (`daily`), or `single`.
- **Database**: the custom `database` channel
  ([`App\Logging\DatabaseLogger`](../../app/Logging/DatabaseLogger.php)) persists
  to the unified **`ob_log_entry`** table via
  [`DatabaseLogHandler`](../../app/Logging/DatabaseLogHandler.php), enriched by
  [`RequestContextProcessor`](../../app/Logging/RequestContextProcessor.php)
  with the acting pompier and request metadata (IP, method, URL, user-agent).

`ob_log_entry` is the single source for structured logs, activity context and
captured **error traces** (class, message, full stack trace).

### Canaux (channels) and per-canal levels

Logging is organized into **canaux**, each with its own minimum level (e.g.
capture every `activity`/`auth` event while keeping `app` at warning):

| Canal         | Default level | Source                                                |
| ------------- | ------------- | ----------------------------------------------------- |
| `activity`    | info          | Business activity (`Audit::activity`, ex-log_history) |
| `audit`       | info          | State-changing HTTP requests (`AuditRequests`)        |
| `auth`        | info          | Authentication / session events                       |
| `security`    | info          | Upload rejections, denials, throttling                |
| `app`         | warning       | General application logs                              |
| `error`       | error         | Uncaught exceptions                                   |
| `performance` | info          | Slow-request samples                                  |

The `database` channel accepts every record; the per-canal threshold is
enforced in [`DatabaseLogHandler`](../../app/Logging/DatabaseLogHandler.php)
from the `obs_level_<canal>` settings. The file leg mirrors at least the
**lowest** canal level. Reconciled **at runtime** by
[`AppServiceProvider::configureObservability()`](../../app/Providers/AppServiceProvider.php):
a UI change takes effect on the next request, no deploy or `.env` edit. If
settings can't be read (fresh install, DB outage, tests) the shipped defaults
stand.

### Settings

Stored as `NAME`/`VALUE` rows in the legacy `configuration` table, read
through [`App\Services\LoggingSettingService`](../../app/Services/LoggingSettingService.php)
(request-cached, falls back to typed defaults).

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
helper that buckets each event into a canal, one line anywhere in the
backend:

```php
Audit::activity('password.changed', ['target' => $id]);  // canal "activity"
Audit::action('event.deleted', ['event_id' => $id]);     // canal "audit"
Audit::auth('login.failed', ['login' => $login], 'warning');
Audit::security('upload.rejected', ['reason' => $msg], 'warning');
```

These go to `ob_log_entry`; whether each is kept is governed by its canal's
`obs_level_<canal>` setting. Actor / IP / URL are attached automatically.
Coverage is broad without per-controller churn:

- [`AuditRequests`](../../app/Http/Middleware/AuditRequests.php) middleware
  logs **every state-changing request** (POST/PUT/PATCH/DELETE) from one
  place; reads are ignored.
- Authentication events (`login.success`, `login.failed`, `logout`) logged in
  `AuthController`.
- Upload rejections audited at the `UploadRejectedException` render hook
  (`bootstrap/app.php`).
- Business activity (`AccountController` etc.) uses `Audit::activity()` on the
  **`activity` canal**, replacing the retired `log_history` insert path.

### Retention

`ob:logs:prune` (daily 03:10, `routes/console.php`) deletes `ob_log_entry`
rows older than `obs_db_retention_days`. Run it manually:

```bash
php artisan ob:logs:prune
```

---

## Error tracking: self-hosted Sentry / GlitchTip

Errors are reported through the official `sentry/sentry-laravel` SDK. Backend:
**GlitchTip**, a Sentry-API-compatible self-hosted server, shipped in
`docker-compose.yml` (`full`/`dev` profiles).

Reporting is **opt-in twice over**: only when `obs_error_tracking` is on
**and** a DSN is set. The **DSN is an admin setting** (`obs_sentry_dsn`,
Journal d'activité ▸ Paramètres), not `.env`: `SENTRY_LARAVEL_DSN` is only a
one-time fallback imported into that setting on first migrate. When disabled,
`AppServiceProvider` clears the DSN, so
[`Integration::handles()`](../../bootstrap/app.php) is a safe no-op.

### Bringing up GlitchTip

```bash
# Set GLITCHTIP_SECRET_KEY and GLITCHTIP_DB_PASSWORD in .env first.
# GlitchTip ships with the "full" (and "dev") profile:
docker compose --profile full up -d
```

1. Open `http://localhost:8000` (or `GLITCHTIP_PORT`), create an account and
   an organization/project.
2. Copy the project's **DSN**, **rewrite the host** for the Docker network
   (see below), paste it into **DSN Sentry / GlitchTip** under Journal
   d'activité → Paramètres, enable **Suivi des erreurs**.
3. (Recommended) set `GLITCHTIP_OPEN_REGISTRATION=false` and restart.

> **DSN host on the compose network.** The PHP SDK runs inside the `app`
> container, so the `localhost:8000` GlitchTip prints is wrong (inside `app`,
> `localhost` is the app container itself). Use the service name + internal
> port: `http://<key>@glitchtip-web:8080/1` (not `localhost:8000`;
> `glitchtip-web` listens on `8080` internally, `8000:8080` is host-only).
> Verify: `docker compose exec app sh -lc "wget -qO- http://glitchtip-web:8080/_health/ && echo OK"`.
> This is the reverse direction from the uptime monitor, which reaches the app
> *from* GlitchTip via `host.docker.internal:8080`.

DSN lives in admin settings (`obs_sentry_dsn`), not `.env`. Remaining env keys
(`.env.example.*`) tune the SDK/server: `SENTRY_ENVIRONMENT`,
`SENTRY_TRACES_SAMPLE_RATE`, `GLITCHTIP_*`.

Config: [`config/sentry.php`](../../config/sentry.php). Validation errors,
auth/authorization failures, HTTP exceptions and rejected uploads are ignored
(not actionable). PII is off by default.

> **DSN resolved in `register()`, not `boot()`**: the Sentry provider boots
> before `AppServiceProvider` and eagerly decides whether to capture events
> from `config('sentry.dsn')` at that moment, so the DSN is applied in
> `AppServiceProvider::register()` (`configureErrorTracking()`); `boot()` is
> too late and silently drops every event. Verify with `php artisan sentry:test`.

### Verifying the pipeline (Diagnostic)

The **Santé** tab's **Diagnostic** card deliberately triggers an issue to
confirm the pipeline end to end:

- **Déclencher une exception**: uncaught exception, real 500, reported to
  Sentry/GlitchTip (when enabled), logged to the `error` canal.
- **Journaliser une erreur de test**: writes one `error`-canal entry (no 500).
- **Simuler une requête lente**: sleeps past `obs_perf_slow_ms` to record a
  `performance` entry.

---

## Health check

`GET /health` returns a JSON report for uptime probes and load balancers, no
authentication (exposes availability only, never data). Probes the database,
cache, storage writability, free disk, ClamAV (when upload scanning is
enabled), Redis, the queue and the mail transport, via
[`App\Services\HealthCheckService`](../../app/Services/HealthCheckService.php).

- **200**: overall `ok` or `degraded`.
- **503**: at least one probe is `down`.

```json
{
  "status": "ok",
  "version": "OpenBrigade 6.0",
  "checks": {
    "database": { "status": "ok", "latency_ms": 3 },
    "queue":    { "status": "ok", "pending": 0, "failed_24h": 0, "heartbeat_age_s": 42 },
    "mail":     { "status": "ok", "transport": "smtp", "failures_24h": 0 }
  }
}
```

### Redis, queue & mail probes

- **redis**: skipped unless it backs the queue, cache or sessions. Otherwise a
  timed `PING`: > 100 ms → `degraded`, unreachable → `down`.
- **queue**: skipped on the `sync` driver. Reports pending depth
  (`Queue::size()` on redis, `jobs` row count on database driver) and
  `failed_jobs` over 24 h (> 0 → `degraded`). **Worker liveness** rides on a
  heartbeat: the scheduler dispatches `App\Jobs\QueueHeartbeatJob` every
  5 minutes, a worker stamps a cache key on pickup. A stamp older than
  15 minutes, or missing, reports `down` (scheduler → queue → worker chain
  broken). Fresh installs read `down` until the first heartbeat lands.
- **mail**: send-safe `log`/`array` mailers are skipped. `smtp` gets a real TCP
  handshake (banner + `EHLO`, 3 s timeout). `NotificationService` delivery
  failures over 24 h surface as `failures_24h` (> 0 → `degraded`).

Laravel's bare `/up` probe is still available. The version surfaced here (and
as the Sentry release) is the **installed version stored in the database**
(configuration row 1, stamped by the release migrations); `APP_VERSION` in
`.env` is only the fallback when the database is unreachable.

### Uptime monitoring (GlitchTip)

GlitchTip's built-in uptime monitors (`glitchtip-worker` container) can poll
`/health`. In GlitchTip → your organization → **Uptime Monitors** → **New
Monitor**:

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
(`web` group) times every request and logs those slower than
`obs_perf_slow_ms` to the `performance` channel, with duration, peak memory,
status code and route. **Santé** summarizes the last 24 h (count, average,
max) and lists the ten slowest requests.

For distributed tracing, raise `SENTRY_TRACES_SAMPLE_RATE` so the SDK also
reports performance transactions to GlitchTip.
