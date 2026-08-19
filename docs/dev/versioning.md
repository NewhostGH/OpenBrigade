# Versioning & changelog

**Single source of truth for how OpenBrigade is versioned and how a release is cut.**

OpenBrigade follows [Semantic Versioning 2.0.0](https://semver.org/spec/v2.0.0.html)
and keeps a [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)-formatted
[`CHANGELOG.md`](../../CHANGELOG.md) at the repository root.

Companion docs: [database-migration.md](../admin/database-migration.md) (schema
ownership & migration policy), [installation.md](../admin/installation.md)
(deploy / upgrade), [CONTRIBUTING.md](../../.github/CONTRIBUTING.md) (commits).

---

## The three versions

Three version values coexist. `App\Services\VersionService` is the one place that
names and compares them; nothing else should re-derive them.

| Version       | Where it lives                                                   | Means                                          |
| ------------- | ---------------------------------------------------------------- | ---------------------------------------------- |
| **code**      | root [`VERSION`](../../VERSION) file → `config('brigade.version')` | The version of the checked-out source          |
| **installed** | database, `configuration` row `version`                          | The version the running instance is migrated to |
| **changelog** | top `## [x.y.z]` heading in `CHANGELOG.md`                        | The latest documented release                  |

On a correctly released instance all three agree. The **code** version is the
SSOT for the source; the **installed** version is the SSOT for a running instance
and overlays `config('brigade.version')` / `config('app.version')` at boot (see
`App\Services\GeneralSettingService::appVersion()` and
`App\Providers\AppServiceProvider::configureAppIdentity()`).

`APP_VERSION` in `.env` overrides the code version when set — a deploy-time escape
hatch only; the committed source of truth is the `VERSION` file.

### Drift

When **code ≠ installed** the code has been deployed but its release migration has
not run yet. `VersionService::hasDrift()` reports this and `php artisan ob:version`
warns about it. Running `php artisan migrate` clears the drift.

## `ob:version`

```bash
php artisan ob:version          # human-readable table + drift warning
php artisan ob:version --json   # { "code", "installed", "changelog", "drift" }
```

Use it in deploy scripts and post-deploy [release verification](../../.github/TODO.md).

## SemVer policy

Given a version `MAJOR.MINOR.PATCH`, increment:

- **MAJOR** — incompatible changes: a removed/renamed route, a breaking schema
  change with no backward-compatible path, a dropped legacy bridge target, or any
  change flagged `BREAKING CHANGE:` in a commit.
- **MINOR** — backward-compatible functionality: a migrated menu, a new screen,
  a new export.
- **PATCH** — backward-compatible bug fixes only.

Pre-release builds use a suffix (`6.1.0-rc.1`); build metadata uses `+` (`6.1.0+ci.42`).
The native migration itself shipped as **6.0.0** (legacy eBrigade was 5.5).

## Keeping the changelog

`CHANGELOG.md` always carries an `## [Unreleased]` section at the top. **Every PR
that changes user-visible behaviour adds a line there** under the appropriate
Keep a Changelog group (`Added` / `Changed` / `Deprecated` / `Removed` / `Fixed`
/ `Security`). Documentation-only or internal-refactor PRs may skip it.

## Cutting a release

1. **Pick the version** per the SemVer policy above from the `Unreleased` entries.
2. **Bump the code version** — write the new version to [`VERSION`](../../VERSION).
3. **Roll the changelog** — rename `## [Unreleased]` to `## [x.y.z] - YYYY-MM-DD`,
   add a fresh empty `## [Unreleased]`, and update the compare/tag links at the
   bottom of `CHANGELOG.md`.
4. **Add a release migration** that stamps the installed version so any instance
   records what it was migrated to:

   ```php
   use App\Support\ReleaseVersion;
   use Illuminate\Database\Migrations\Migration;

   return new class extends Migration
   {
       public function up(): void
       {
           // …any schema changes for this release first…
           ReleaseVersion::stamp('6.1.0');
       }
   };
   ```

   `ReleaseVersion::stamp()` is the single place that writes `configuration.version`
   — never inline the update. Migrations are **forward-only and backward-compatible
   where possible** (see [database-migration.md](../admin/database-migration.md)).
5. **Commit** with a `chore(release): x.y.z` message, **tag** `vX.Y.Z`, and push
   the tag. The tag is the immutable release marker referenced from `CHANGELOG.md`.

## See also

- [CHANGELOG.md](../../CHANGELOG.md) — the maintained changelog
- [database-migration.md](../admin/database-migration.md) — migration policy
- [.github/TODO.md](../../.github/TODO.md) — release-strategy backlog & tracker
