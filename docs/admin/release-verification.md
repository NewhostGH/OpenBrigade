# Release verification

After a deploy, `ob:release:verify` runs a set of **post-deploy smoke checks**
and exits non-zero if the release is not serving correctly. It is the gate a CD
pipeline (issue #73) runs after building assets and migrating, so a bad release
fails loudly instead of silently serving errors.

```bash
php artisan ob:release:verify           # table report, exit 0/1
php artisan ob:release:verify --strict  # degraded checks also fail the gate
php artisan ob:release:verify --json    # machine-readable report
php artisan ob:release:verify --no-webhook
```

Exit code `0` = the release passed, `1` = it did not. In a pipeline, gate the
promotion/traffic-switch step on this exit code.

## What it checks

All logic lives in `App\Services\ReleaseVerificationService`; each check is
isolated and reports a status of `ok` / `degraded` / `down` / `skipped`. The
overall status is the worst individual one (`skipped` never worsens it) — the
same vocabulary as the [`/health` endpoint](observability.md).

| Check            | Fails (`down`) when…                                                                        |
| ---------------- | ------------------------------------------------------------------------------------------- |
| `infrastructure` | Any backing service the `/health` endpoint probes is down (DB, cache, storage, queue, mail) |
| `migrations`     | Migrations on disk have not been applied — code and schema are out of sync                  |
| `assets`         | The built Vite manifest is missing — `npm run build` never ran for this release             |
| `configuration`  | `APP_KEY` missing, or `APP_DEBUG` on / `APP_ENV` wrong in production                         |
| `version`        | The expected release version does not match the installed-version SSOT (see below)          |
| `routes`         | A critical named route (`login`, `health`, `dashboard`) is not registered                   |

`infrastructure` reuses the exact probes behind `/health`, so the release gate
and the live health panel never disagree.

## Configuration

Everything is in [`config/release.php`](../../config/release.php), driven by
environment variables (see `.env.example.prod`):

| Env var                        | Default                      | Purpose                                                          |
| ------------------------------ | ---------------------------- | --------------------------------------------------------------- |
| `RELEASE_VERIFY_STRICT`        | `false`                      | Treat `degraded` as a failure (same as `--strict`)              |
| `RELEASE_EXPECTED_VERSION`     | *(empty → skip)*             | Assert the deployed version; set it to the tag being released    |
| `RELEASE_VERIFY_WEBHOOK_URL`   | *(empty → skip)*             | POST the report here after every run (deploy tracker / uptime)   |
| `RELEASE_VERIFY_WEBHOOK_TIMEOUT` | `5`                        | Webhook timeout in seconds                                       |

### Version assertion (SSOT)

The deployed version is the **installed-version SSOT** — `configuration` row 1,
surfaced as `config('brigade.version')` and stamped by the release migrations.
Set `RELEASE_EXPECTED_VERSION` to the tag your pipeline is releasing and
verification fails if the running app reports a different version — catching a
half-applied deploy where the code shipped but the migrations did not.

## Monitoring hook

When `RELEASE_VERIFY_WEBHOOK_URL` is set, the report is POSTed after every run
as `{event: "release.verified", status, version, timestamp, checks}`. This is
best-effort: a failed or unreachable webhook is logged but never changes the
exit code, so a flaky monitoring endpoint can't fail an otherwise-good deploy.

## In a pipeline

```yaml
# after build + migrate, before switching traffic
- run: php artisan ob:release:verify --strict
```

See also: [Observability](observability.md) · [Database migration](database-migration.md).
