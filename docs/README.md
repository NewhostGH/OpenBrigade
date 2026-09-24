# OpenBrigade documentation

Each topic below is owned by exactly one file (**single source of truth**). AI
coding agents start at [AGENTS.md](../AGENTS.md) (repository root) for the
short rules and doc-maintenance checklist, which points here for details.

## Developer docs (`dev/`)

| Doc                                        | Owns                                                                                                                      |
| ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------- |
| [conventions.md](dev/conventions.md)       | **How code is written**: SSOT rules, models, Blade, CSS/JS naming, exports, legacy flagging, UI component patterns        |
| [architecture.md](dev/architecture.md)     | **Where things live**: project file structure, layer responsibilities, the legacy bridge                                  |
| [development.md](dev/development.md)       | **How to run it**: setup (Docker/local/devcontainer), database, authentication, seeding, frontend assets, quality tooling |
| [legacy-mapping.md](dev/legacy-mapping.md) | **Legacy → Laravel file map**: every `archive/legacy_app/` file and its native target (or WIP)                            |
| [versioning.md](dev/versioning.md)         | **How releases are versioned**: SemVer policy, the `CHANGELOG.md` workflow, `ob:version`, and cutting a tagged release    |
| [brand-identity.md](dev/brand-identity.md) | **How the product looks and sounds**: tone, French/English copy, symbols, colors, icons, images                           |

## Admin docs (`admin/`)

| Doc                                                  | Owns                                                                           |
| ---------------------------------------------------- | ------------------------------------------------------------------------------ |
| [installation.md](admin/installation.md)             | Deploying OpenBrigade (Docker / manual), upgrading                             |
| [database-migration.md](admin/database-migration.md) | Schema ownership, migrations, legacy parity validation                         |
| [backup-and-restore.md](admin/backup-and-restore.md) | DB + uploads backups, off-site mirror, scheduler, restore & restore drill      |
| [sms.md](admin/sms.md)                               | Provider-agnostic SMS layer, SMSGateway.me setup, adding a provider            |
| [observability.md](admin/observability.md)           | Structured logging, error tracking, `/health` endpoint, performance monitoring |
| [passwords.md](admin/passwords.md)                   | Admin password-reset procedures (shell-side, until self-service is wired)      |

## Security docs (`security/`)

| Doc                                                   | Owns                                                                           |
| ----------------------------------------------------- | ------------------------------------------------------------------------------ |
| [password-policies.md](security/password-policies.md) | Named password policies, per-group assignment, NCSC/ANSSI guidance             |
| [totp.md](security/totp.md)                           | TOTP two-factor authentication: enrolment, login flow, recovery codes          |
| [ldap.md](security/ldap.md)                           | LDAP/AD authentication delegation, configuration, dev emulation with Docker    |
| [hardening.md](security/hardening.md)                 | Defense-in-depth controls: security headers, rate limiting, upload safety, CSP |

## Legal (`legal/`)

| Doc                                    | Owns                             |
| -------------------------------------- | -------------------------------- |
| [license-fr.txt](legal/license-fr.txt) | GNU GPL v2 license (French text) |

## Migration tracking

Menu-by-menu migration status: [.github/TODO.md](../.github/TODO.md).
Contribution process (branches, commits, PRs): [.github/CONTRIBUTING.md](../.github/CONTRIBUTING.md).

> `user/` is reserved for future end-user documentation.
