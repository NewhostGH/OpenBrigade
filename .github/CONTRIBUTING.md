# Contributing to OpenBrigade

Thank you for your interest in contributing to **OpenBrigade**! This document explains how to get involved.

---

## Table of Contents

- [Contributing to OpenBrigade](#contributing-to-openbrigade)
  - [Table of Contents](#table-of-contents)
  - [Code of Conduct](#code-of-conduct)
  - [How to Fork the Repository](#how-to-fork-the-repository)
  - [How to Branch](#how-to-branch)
    - [Branch naming conventions](#branch-naming-conventions)
    - [Creating a branch](#creating-a-branch)
  - [Setting Up Your Development Environment](#setting-up-your-development-environment)
  - [Making Changes](#making-changes)
  - [Commit Message Guidelines](#commit-message-guidelines)
    - [Format](#format)
    - [Types](#types)
    - [Scope (optional)](#scope-optional)
    - [Subject line rules](#subject-line-rules)
    - [Body (optional)](#body-optional)
    - [Footer (optional)](#footer-optional)
    - [Full examples](#full-examples)
  - [Git Hooks (Husky + Commitlint)](#git-hooks-husky--commitlint)
    - [First-time setup](#first-time-setup)
    - [Bypassing hooks (for emergencies only)](#bypassing-hooks-for-emergencies-only)
    - [Disabling hooks locally](#disabling-hooks-locally)
    - [Testing commitlint without committing](#testing-commitlint-without-committing)
  - [How to Submit a Pull Request](#how-to-submit-a-pull-request)
    - [Before submitting, please check](#before-submitting-please-check)
  - [How to Submit an Issue](#how-to-submit-an-issue)
  - [Coding Guidelines](#coding-guidelines)
    - [Quality gates](#quality-gates)
    - [PHP / Laravel](#php--laravel)
    - [JavaScript / CSS](#javascript--css)
    - [Git](#git)

---

## Code of Conduct

Please be respectful and constructive in all interactions. We follow the [Contributor Covenant](https://www.contributor-covenant.org/) Code of Conduct. Harassment or abusive behaviour will not be tolerated.

---

## How to Fork the Repository

1. Go to <https://github.com/NewHostGH/OpenBrigade>.
2. Click the **Fork** button (top-right corner).
3. GitHub creates a copy under your own account: `https://github.com/<your-username>/OpenBrigade`.
4. Clone your fork locally:

   ```bash
   git clone https://github.com/<your-username>/OpenBrigade.git
   cd OpenBrigade
   ```

5. Add the upstream remote so you can keep your fork up to date:

   ```bash
   git remote add upstream https://github.com/NewHostGH/OpenBrigade.git
   ```

---

## How to Branch

Always create a new branch for your work — **never commit directly to `main`**.

### Branch naming conventions

| Purpose             | Pattern                     | Example                        |
| ------------------- | --------------------------- | ------------------------------ |
| Bug fix             | `fix/<short-description>`   | `fix/login-redirect`           |
| New feature         | `feat/<short-description>`  | `feat/export-csv`              |
| Documentation       | `docs/<short-description>`  | `docs/update-readme`           |
| Chore / maintenance | `chore/<short-description>` | `chore/upgrade-phpspreadsheet` |

### Creating a branch

```bash
# Make sure your local main is up to date
git checkout main
git fetch upstream
git merge upstream/main

# Create and switch to your new branch
git checkout -b feat/my-awesome-feature
```

---

## Setting Up Your Development Environment

Full setup instructions — Docker Compose, the VS Code Dev Container, and manual
installation — live in **[docs/dev/DEVELOPMENT.md](../docs/dev/DEVELOPMENT.md)**, the
single source of truth for running the project. The short version:

```bash
cp .env.example .env        # adjust credentials if needed
docker compose up -d
docker compose exec app php artisan migrate --seed
docker compose exec app sh -lc "npm ci && npm run build"
```

The application is served at `http://localhost:8080`; CloudBeaver (web DB browser) at
`http://localhost:8081`. Stop everything with `docker compose down`.

OpenBrigade is a **Laravel 12 / PHP 8.4** application — there is no PHP setup wizard;
the schema is built by `php artisan migrate`.

---

## Making Changes

1. Write your code on your feature branch.
2. Keep commits small and focused — one logical change per commit.
3. Follow the [Commit Message Guidelines](#commit-message-guidelines) below — the format is enforced automatically by commitlint.
4. Push your branch to your fork:

   ```bash
   git push origin feat/my-awesome-feature
   ```

---

## Commit Message Guidelines

This project follows the **[Conventional Commits](https://www.conventionalcommits.org/)** specification.  
Commit messages are validated automatically on every `git commit` via commitlint.

### Format

```format
<type>(<optional scope>): <subject>

[optional body]

[optional footer(s)]
```

### Types

| Type       | When to use                                                        |
| ---------- | ------------------------------------------------------------------ |
| `feat`     | A new feature or user-visible behaviour change                     |
| `fix`      | A bug fix                                                          |
| `docs`     | Documentation-only changes (CONTRIBUTING, README, inline comments) |
| `style`    | Formatting, whitespace — no logic change                           |
| `refactor` | Code restructuring with no feature or bug change                   |
| `perf`     | Performance improvement                                            |
| `test`     | Adding or fixing tests                                             |
| `build`    | Build system or external dependency changes (Vite, npm, Composer)  |
| `ci`       | CI/CD pipeline changes (GitHub Actions, Docker)                    |
| `chore`    | Routine maintenance that touches none of the above                 |
| `revert`   | Reverts a previous commit                                          |

### Scope (optional)

A scope names the subsystem affected. Use the Laravel layer or menu section:

```git
feat(dashboard): add missing "Mes activités" widget
fix(auth): prevent redirect loop on invalid session
chore(deps): upgrade leaflet to 1.9.4
test(personnel): add access-control feature tests
```

### Subject line rules

- Use the **imperative present tense**: "add feature" not "added feature" or "adds feature".
- **No period** at the end.
- Maximum **100 characters**.
- May be written in **French or English** — no case requirement.

### Body (optional)

Separate from the subject with a blank line.  
Explain **why** the change was made, not what (the diff shows what).  
Wrap lines at 100 characters.

### Footer (optional)

Reference issues or breaking changes:

```git
fix(vehicles): handle null VP_ID when no position assigned

Vehicles without an assigned position returned a 500.
Added a null-safe left join on the vehicule_position table.

Closes #42
```

Breaking changes must start with `BREAKING CHANGE:`:

```git
refactor(auth)!: remove legacy session-based permission cache

BREAKING CHANGE: GP_ID is no longer cached in $_SESSION.
All permission checks now query the habilitation table directly.
```

### Full examples

```git
feat(dashboard): add Note de frais and Demande de remplaçant widgets

Both widgets were present in the legacy index_d.php but missing from
the Laravel dashboard. Added getExpenses() and getReplacementRequests()
to DashboardService with graceful try/catch for optional tables.
```

```git
fix(csp): allow OpenStreetMap tile domains in img-src

Leaflet geo map was blocked by the Content-Security-Policy.
Added https://*.tile.openstreetmap.org to img-src and connect-src.
```

```git
chore(deps): replace CDN Leaflet with npm package bundled via Vite

Removed unpkg <script> and <link> CDN tags.
Created resources/js/geolocalisation.js as a dedicated Vite entry.
```

---

## Git Hooks (Husky + Commitlint)

The repository ships with two Git hooks managed by **[Husky](https://typicode.github.io/husky/)**.  
They activate automatically after `npm install`.

| Hook         | What it does                                                                                   |
| ------------ | ---------------------------------------------------------------------------------------------- |
| `pre-commit` | Runs `npm run build` — verifies the Vite bundle compiles before the commit is recorded         |
| `commit-msg` | Runs `commitlint` — rejects the commit if the message violates the Conventional Commits format |

### First-time setup

After cloning the repository, install Node dependencies:

```bash
npm install
```

Husky installs itself via the `prepare` lifecycle script. The hooks are now active.

> **Docker users:** `npm install` inside the Docker container does not install hooks — hooks run on your **host machine**'s Git. Run `npm install` once on the host as well.

### Bypassing hooks (for emergencies only)

```bash
git commit --no-verify -m "chore: emergency hotfix"
```

Use `--no-verify` sparingly. Bypassed commits still need a conventional message to pass PR review.

### Disabling hooks locally

```bash
# Temporarily disable all hooks
HUSKY=0 git commit -m "..."
```

### Testing commitlint without committing

```bash
echo "feat: my message" | npx commitlint
echo "bad message"      | npx commitlint   # should fail
```

---

## How to Submit a Pull Request

1. Go to your fork on GitHub.
2. Click **Compare & pull request** (GitHub shows this automatically after a push).
3. Set the **base repository** to `NewHostGH/OpenBrigade` and the **base branch** to `main`.
4. Fill in the pull request template:
   - What problem does this solve?
   - What changes were made?
   - How was it tested?
5. Submit the pull request.

A maintainer will review your PR. Please be patient — this is a community project. Address any requested changes by pushing new commits to the same branch.

### Before submitting, please check

- [ ] `composer pint -- --test` passes (formatting).
- [ ] `composer analyse` passes (PHPStan / Larastan).
- [ ] `composer test` passes (Pest, including `ConventionsTest`).
- [ ] Existing features are not broken.
- [ ] The PR description clearly explains the changes.
- [ ] No secrets or credentials are included.

---

## How to Submit an Issue

Use the [GitHub Issues](https://github.com/NewHostGH/OpenBrigade/issues) page.

- **Bug report** — Use the *Bug Report* template. Include steps to reproduce, expected vs. actual behaviour, and your environment (PHP version, MySQL version, OS).
- **Feature request** — Use the *Feature Request* template. Explain the use case and the expected behaviour.
- **Security vulnerability** — Do **not** open a public issue. Contact the maintainers directly via the repository security advisories page.

Before opening an issue, please search existing issues to avoid duplicates.

---

## Coding Guidelines

> For the full set of binding rules on SSOT, model design, Blade views, CSS/JS naming,
> exports, legacy flagging, and UI component patterns, see
> **[docs/dev/CONVENTIONS.md](../docs/dev/CONVENTIONS.md)** — the single source of truth
> for how code is written here. The summary below covers the basics; the conventions
> document covers everything the CI test enforces.

### Quality gates

Run these before pushing; CI runs the same three and fails on any error:

```bash
composer pint -- --test     # code style (drop --test to auto-fix)
composer analyse            # PHPStan / Larastan, level 5
composer test               # Pest test suite
```

### PHP / Laravel

- Follow the existing code style already present in the file you are editing (Pint
  enforces the canonical style).
- Keep PHP files UTF-8 encoded.
- No raw SQL in controllers — use Eloquent or the Query Builder in a Service class.
- SQL queries must use parameterised bindings (never string-interpolate user input).
- Native tables get the `ob_` prefix; legacy tables keep their original names
  (CONVENTIONS §2).
- Avoid introducing new Composer dependencies without discussion.

### JavaScript / CSS

- All JS and CSS must be managed via **npm** and bundled through **Vite** — no CDN `<script>` or `<link>` tags.
- Import Leaflet, Bootstrap, FA, etc. from `node_modules`; the bundler handles versioning and hashing.
- Per-page JS that requires Blade data should expose it via `window.MY_DATA = @json(...)` from a plain (non-module) `<script>` block so the deferred ES module can read it safely.

### Git

- Never commit directly to `main`.
- Follow the [Commit Message Guidelines](#commit-message-guidelines) — commitlint enforces them automatically.
- Do not commit generated assets (`public/build/`), secrets (`.env`), or large binary files.
- `public/build/` is git-ignored; CI rebuilds it.
