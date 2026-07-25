# Changelog

All notable changes to **OpenBrigade** are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

The version stamped here is the **code** version — the single source of truth is
the [`VERSION`](VERSION) file at the repository root (surfaced as
`config('brigade.version')`). The **installed** version — what a running instance
has actually been migrated to — is stored in the database (`configuration` row
`version`) and stamped by a release migration; see
[`docs/dev/versioning.md`](docs/dev/versioning.md) for how the two relate and how
to cut a release.

## [Unreleased]

### Added

- Versioning & changelog policy: a root [`VERSION`](VERSION) file as the code-version
  single source of truth, this changelog (Keep a Changelog + SemVer), an `ob:version`
  console command reporting the code / installed / changelog versions, and
  [`docs/dev/versioning.md`](docs/dev/versioning.md) documenting the release process.

## [6.0.0] - 2026-05-06

The first native Laravel release. OpenBrigade is rebuilt on **Laravel 12 / PHP 8.4**,
migrating the legacy eBrigade 5.5 PHP application menu by menu. This entry summarises
the baseline state shipped with the initial native release; finer-grained history
lives in the migration tracker ([`.github/TODO.md`](.github/TODO.md)).

### Added

- Native application foundation on Laravel 12 / PHP 8.4, importing the legacy
  eBrigade 5.5 schema as a baseline migration.
- Universal `ob-*` UI component system (breadcrumb, toolbar, command bar, table,
  badge, avatar, toggle) and the migrated list/detail pages built on it.
- Section-scoped ACL habilitations (`PermissionResolver`), data isolation by
  section (`SectionScopeService`), and the super-admin account flag.
- Core feature migrations across Personnel, Activité, Garde, Planning, Client,
  Logistique, Inventaire, Communication, Document, Photos, Statistique,
  Organisation and Administration.
- Notification / messaging infrastructure, Redis queues + scheduler,
  observability (`/health`, structured logging, error tracking) and backup /
  restore robustness.

### Changed

- Installed version is now the single source of truth stored in the database
  (`configuration` row `version`, stamped by the baseline migration), overlaying
  `config('brigade.version')` at boot.

[Unreleased]: https://github.com/NewhostGH/OpenBrigade/compare/v6.0.0...HEAD
[6.0.0]: https://github.com/NewhostGH/OpenBrigade/releases/tag/v6.0.0
