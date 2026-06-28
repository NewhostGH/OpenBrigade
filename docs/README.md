# OpenBrigade Documentation

OpenBrigade is a Laravel 12 fork of eBrigade. The legacy app is being migrated into a
native Laravel application menu by menu. These documents are organised as **single
sources of truth** — each topic is owned by exactly one file.

## Developer docs (`dev/`)

| Doc                                        | Owns                                                                                                                       |
| ------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------- |
| [CONVENTIONS.md](dev/CONVENTIONS.md)       | **How code is written** — SSOT rules, models, Blade, CSS/JS naming, exports, legacy flagging, UI component patterns        |
| [ARCHITECTURE.md](dev/ARCHITECTURE.md)     | **Where things live** — project file structure, layer responsibilities, the legacy bridge                                  |
| [DEVELOPMENT.md](dev/DEVELOPMENT.md)       | **How to run it** — setup (Docker/local/devcontainer), database, authentication, seeding, frontend assets, quality tooling |
| [legacy-mapping.md](dev/legacy-mapping.md) | **Legacy → Laravel file map** — every `archive/legacy_app/` file and its native target (or WIP)                            |

## Admin docs (`admin/`)

| Doc                                                  | Owns                                                                         |
| ---------------------------------------------------- | ---------------------------------------------------------------------------- |
| [installation.md](admin/installation.md)             | Deploying OpenBrigade (Docker / manual), upgrading                           |
| [database-migration.md](admin/database-migration.md) | Schema ownership, migrations, legacy parity validation                       |
| [backup-and-restore.md](admin/backup-and-restore.md) | Database backups, the scheduler, restore                                     |
| [observability.md](admin/observability.md)           | Structured logging, error tracking, `/health` endpoint, performance monitoring |
| [passwords.md](admin/passwords.md)                   | Admin password-reset procedures (shell-side, until self-service is wired)    |

## Security docs (`security/`)

| Doc | Owns |
| --- | --- |
| [password-policies.md](security/password-policies.md) | Named password policies, per-group assignment, NCSC/ANSSI guidance |
| [totp.md](security/totp.md) | TOTP two-factor authentication — enrolment, login flow, recovery codes |
| [ldap.md](security/ldap.md) | LDAP/AD authentication delegation, configuration, dev emulation with Docker |
| [hardening.md](security/hardening.md) | Defence-in-depth controls — security headers, rate limiting, upload safety, CSP |

## Legal (`legal/`)

| Doc | Owns |
| --- | --- |
| [licence-fr.txt](legal/licence-fr.txt) | GNU GPL v2 licence (French text) |

## Migration tracking

The menu-by-menu migration status and the working agreement for making changes live
in [.github/TODO.md](../.github/TODO.md). Contribution process (branches, commits, PRs)
is in [.github/CONTRIBUTING.md](../.github/CONTRIBUTING.md).

> `user/` is reserved for future end-user documentation.
