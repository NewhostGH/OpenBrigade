# Contributing to OpenBrigade

## Code of conduct

Be respectful and constructive. We follow the [Contributor Covenant](https://www.contributor-covenant.org/).

## Getting started

1. Fork <https://github.com/NewHostGH/OpenBrigade> and clone your fork.
2. Add the upstream remote: `git remote add upstream https://github.com/NewHostGH/OpenBrigade.git`.
3. Never commit directly to `main`: branch off it.

### Branch naming

| Purpose             | Pattern                     | Example                        |
| ------------------- | --------------------------- | ------------------------------ |
| Bug fix             | `fix/<short-description>`   | `fix/login-redirect`           |
| New feature         | `feat/<short-description>`  | `feat/export-csv`              |
| Documentation       | `docs/<short-description>`  | `docs/update-readme`           |
| Chore / maintenance | `chore/<short-description>` | `chore/upgrade-phpspreadsheet` |

## Development environment

Full setup (Docker Compose, VS Code Dev Container, manual install) is in
**[docs/dev/development.md](../docs/dev/development.md)**. Short version:

```bash
cp .env.example .env
docker compose --profile dev up -d
docker compose exec app php artisan migrate --seed
docker compose exec app sh -lc "npm ci && npm run build"
```

Nested Compose profiles (`app` ⊂ `minimal` ⊂ `full` ⊂ `dev`): pass `--profile`
or set `COMPOSE_PROFILES`; with none, `docker compose up` starts nothing. App
at `http://localhost:8080`; DBGate at `http://localhost:8888` (`dev` profile
only). Laravel 12 / PHP 8.4: no setup wizard, schema built by `php artisan migrate`.

## Making changes

- Keep commits small and focused: one logical change per commit.
- Follow the [Commit Message Guidelines](#commit-message-guidelines) (enforced by commitlint).
- Push to your fork and open a PR against `NewHostGH/OpenBrigade` `main`.

## Commit message guidelines

Follows **[Conventional Commits](https://www.conventionalcommits.org/)**, validated by commitlint on every `git commit`.

```format
<type>(<optional scope>): <subject>

[optional body]

[optional footer(s)]
```

| Type       | When to use                                                       |
| ---------- | ----------------------------------------------------------------- |
| `feat`     | A new feature or user-visible behavior change                     |
| `fix`      | A bug fix                                                         |
| `docs`     | Documentation-only changes                                        |
| `style`    | Formatting, whitespace: no logic change                           |
| `refactor` | Code restructuring with no feature or bug change                  |
| `perf`     | Performance improvement                                           |
| `test`     | Adding or fixing tests                                            |
| `build`    | Build system or external dependency changes (Vite, npm, Composer) |
| `ci`       | CI/CD pipeline changes                                            |
| `chore`    | Routine maintenance that touches none of the above                |
| `revert`   | Reverts a previous commit                                         |

- Scope names the subsystem, e.g. `feat(dashboard): add missing widget`.
- Subject: imperative present tense, no period, max 100 characters, French or English.
- Body (optional): explain *why*, not what; wrap at 100 characters.
- Breaking changes: footer starts with `BREAKING CHANGE:`.

```git
fix(vehicles): handle null VP_ID when no position assigned

Vehicles without an assigned position returned a 500.
Added a null-safe left join on the vehicule_position table.

Closes #42
```

## Git hooks (Husky + Commitlint)

Activate automatically after `npm install` (installs the `prepare` script).

| Hook         | What it does                                            |
| ------------ | ------------------------------------------------------- |
| `pre-commit` | Runs `npm run build`: verifies the Vite bundle compiles |
| `commit-msg` | Runs `commitlint`: rejects non-conventional messages    |

> **Docker users:** hooks run on your host Git, not inside the container. Run
> `npm install` once on the host too.

Bypass in an emergency: `git commit --no-verify -m "chore: emergency hotfix"`
(still needs a conventional message to pass review). Disable temporarily:
`HUSKY=0 git commit -m "..."`. Test a message without committing:
`echo "feat: my message" | npx commitlint`.

## Submitting a pull request

Fill in the PR template (problem, changes, how tested). Before submitting:

- [ ] `composer pint -- --test` passes (formatting).
- [ ] `composer analyse` passes (PHPStan / Larastan).
- [ ] `composer test` passes (Pest, including `ConventionsTest`).
- [ ] Existing features are not broken.
- [ ] A line was added under `## [Unreleased]` in [`CHANGELOG.md`](../CHANGELOG.md) if user-visible (see [versioning.md](../docs/dev/versioning.md)).
- [ ] No secrets or credentials are included.

A maintainer will review your PR; push new commits to address feedback.

## Submitting an issue

Use [GitHub Issues](https://github.com/NewHostGH/OpenBrigade/issues): the *Bug
Report* template (repro steps, expected vs. actual, environment) or *Feature
Request* template (use case, expected behavior). Search first to avoid
duplicates. **Security vulnerability**: do not open a public issue; use the
repository security advisories page.

## Coding guidelines

> Binding rules on SSOT, model design, Blade, CSS/JS naming, exports, legacy
> flagging and UI patterns: **[docs/dev/conventions.md](../docs/dev/conventions.md)**.
> User-facing copy, colors, icons, images: **[docs/dev/brand-identity.md](../docs/dev/brand-identity.md)**.

### Quality gates

Run before pushing; CI runs the same three and fails on any error:

```bash
composer pint -- --test     # code style (drop --test to auto-fix)
composer analyse            # PHPStan / Larastan, level 5
composer test               # Pest test suite
```

### PHP / Laravel

- Follow the existing file's style (Pint enforces the canonical style); keep files UTF-8.
- No raw SQL in controllers: use Eloquent/Query Builder in a Service class, with parameterised bindings.
- Native tables get the `ob_` prefix; legacy tables keep their original names (CONVENTIONS §2).
- Avoid introducing new Composer dependencies without discussion.

### JavaScript / CSS

- All JS/CSS via **npm** and **Vite**: no CDN `<script>`/`<link>` tags.
- Import Leaflet, Bootstrap, FA, etc. from `node_modules`.
- Per-page JS needing Blade data reads it via `window.MY_DATA = @json(...)` from a plain (non-module) `<script>` block.

### Git

- Never commit directly to `main`.
- Do not commit generated assets (`public/build/`), secrets (`.env`), or large binary files.
