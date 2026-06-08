# OpenBrigade Documentation

OpenBrigade is a Laravel 12 fork of eBrigade. The legacy app is being migrated into a
native Laravel application menu by menu. These documents are organised as **single
sources of truth** — each topic is owned by exactly one file.

## Developer docs (`dev/`)

| Doc | Owns |
|---|---|
| [CONVENTIONS.md](dev/CONVENTIONS.md) | **How code is written** — SSOT rules, models, Blade, CSS/JS naming, exports, legacy flagging, UI component patterns |
| [ARCHITECTURE.md](dev/ARCHITECTURE.md) | **Where things live** — project file structure, layer responsibilities, the legacy bridge |
| [DEVELOPMENT.md](dev/DEVELOPMENT.md) | **How to run it** — setup (Docker/local/devcontainer), database, authentication, seeding, frontend assets, quality tooling |
| [legacy-mapping.md](dev/legacy-mapping.md) | **Legacy → Laravel file map** — every `archive/legacy_app/` file and its native target (or WIP) |

## Admin docs (`admin/`)

| Doc | Owns |
|---|---|
| [installation.md](admin/installation.md) | Deploying OpenBrigade (Docker / manual), upgrading |
| [database-migration.md](admin/database-migration.md) | Schema ownership, migrations, legacy parity validation |
| [backup-and-restore.md](admin/backup-and-restore.md) | Database backups, the scheduler, restore |

## Migration tracking

The menu-by-menu migration status and the working agreement for making changes live
in [.github/TODO.md](../.github/TODO.md). Contribution process (branches, commits, PRs)
is in [.github/CONTRIBUTING.md](../.github/CONTRIBUTING.md).

> `user/` is reserved for future end-user documentation.
