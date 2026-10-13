# Environments

The three environments an OpenBrigade install runs in, how their configuration
differs, and where secrets live. Deploying to them: [release-runbook.md](release-runbook.md).

## The three environments

| Environment    | Purpose                                      | Start from          | `APP_ENV`     | Compose profile |
| -------------- | -------------------------------------------- | ------------------- | ------------- | --------------- |
| **local**      | Development on a laptop or devcontainer      | `.env.example.dev`  | `development` | `dev`           |
| **staging**    | Acceptance testing (UAT) of the next release | `.env.example.prod` | `staging`     | `full`          |
| **production** | The live instance                            | `.env.example.prod` | `production`  | `full`          |

`APP_ENV` also picks the Docker build: `development` installs Composer dev
packages, any other value builds a production image.

## What differs

Staging **mirrors production**: same image, same Compose profile, same PHP and
MariaDB versions, same settings, and the same tag deployed before production.
Only the keys below change.

| Key                                   | local                   | staging                  | production                                            |
| ------------------------------------- | ----------------------- | ------------------------ | ----------------------------------------------------- |
| `APP_DEBUG`                           | `true`                  | `false`                  | `false`                                               |
| `APP_URL`                             | `http://localhost:8080` | staging host (HTTPS)     | public host (HTTPS)                                   |
| `LOG_LEVEL`                           | `debug`                 | `info`                   | `info`                                                |
| `SESSION_SECURE_COOKIE`               | `false`                 | `true`                   | `true`                                                |
| `MAIL_MAILER` / `MAIL_HOST`           | Mailpit                 | `log`, or a sandbox SMTP | Production SMTP relay                                 |
| `SMS_DRIVER`                          | `log`                   | `log`                    | Real gateway ([sms.md](sms.md))                       |
| `SENTRY_ENVIRONMENT`                  | `local`                 | `staging`                | `production`                                          |
| `BACKUP_OFFSITE_DISK`                 | empty                   | empty                    | `s3` ([backup-and-restore.md](backup-and-restore.md)) |
| `RELEASE_EXPECTED_VERSION`            | empty                   | tag being tested         | tag being deployed                                    |
| `APP_KEY`, `DB_*`, every `*_PASSWORD` | dev values              | **own values**           | **own values**                                        |

Staging must never send real mail or SMS: members would receive messages from
test activities.

## Staging data

UAT needs realistic data, but a production copy holds personal data (RGPD):

- Restore a production backup into staging only on a host with production-level
  access control, and only for the time of the test campaign.
- Prefer seeded data (`php artisan migrate --seed`) for anything shown outside
  the core team.
- After restoring a production backup, re-check `MAIL_MAILER` and `SMS_DRIVER`
  in staging's `.env`, and turn off automatic backups and the off-site mirror
  in Configuration ▸ Sauvegarde so staging never overwrites production's archives.

## Secrets

- Secrets live **only** in the environment's `.env` (or the host/orchestrator
  environment), never in git. `.env` is git-ignored; the `.env.example.*`
  templates hold placeholders only (`CHANGE_ME`).
- On the server: `chmod 600 .env`, owned by the deploy user; the web server
  reads it through the container or PHP-FPM, not over HTTP (`public/` is the
  document root).
- Each environment has its **own** `APP_KEY`, database password and API tokens.
  Copying production's `APP_KEY` to staging lets staging decrypt production
  sessions and encrypted columns.
- **Never rotate `APP_KEY` on a live instance** without a plan: it invalidates
  sessions and makes values encrypted with it unreadable.
- Settings stored in the database (SMTP password, SMS credentials, the
  [API tokens](api.md), the error-tracking DSN) are part of the backup: treat
  backup archives as secrets too.
- When someone with server access leaves: rotate the database password, the
  SMTP/SMS credentials and the API tokens.
