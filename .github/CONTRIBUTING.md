# Contributing to OpenBrigade

Thank you for your interest in contributing to **OpenBrigade**! This document explains how to get involved.

---

## Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [How to Fork the Repository](#how-to-fork-the-repository)
3. [How to Branch](#how-to-branch)
4. [Setting Up Your Development Environment](#setting-up-your-development-environment)
5. [Making Changes](#making-changes)
6. [How to Submit a Pull Request](#how-to-submit-a-pull-request)
7. [How to Submit an Issue](#how-to-submit-an-issue)
8. [Coding Guidelines](#coding-guidelines)

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

| Purpose | Pattern | Example |
|---------|---------|---------|
| Bug fix | `fix/<short-description>` | `fix/login-redirect` |
| New feature | `feat/<short-description>` | `feat/export-csv` |
| Documentation | `docs/<short-description>` | `docs/update-readme` |
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

### Option A — Docker Compose (recommended)

Requires [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/).

```bash
cp .env.example .env   # Edit DB credentials if needed
docker compose up -d
```

The application will be available at `http://localhost:8080`.  
phpMyAdmin is available at `http://localhost:8081`.

Stop containers:

```bash
docker compose down
```

### Option B — VS Code Dev Container

Requires [VS Code](https://code.visualstudio.com/) and the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers).

1. Open the repository folder in VS Code.
2. When prompted, click **Reopen in Container** (or use the command palette: `Dev Containers: Reopen in Container`).
3. VS Code will build and start the container automatically.
4. The app will be served on port `8080` (forwarded to your host).

### Option C — Manual

- PHP 7.4 – 8.3 with extensions: `mysqli`, `mbstring`, `gd`, `zip`, `ldap` (optional), `imap` (optional)
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ with `mod_rewrite` enabled

Configure a virtual host pointing to the repository root, create a MySQL database, then navigate to `http://localhost/` to run the setup wizard.

---

## Making Changes

1. Write your code on your feature branch.
2. Keep commits small and focused — one logical change per commit.
3. Write a clear commit message:

   ```
   fix: prevent redirect loop on invalid session

   When the session token was missing the application entered an infinite
   redirect loop. Added a check in lost_session.php to break out early.
   ```

4. Push your branch to your fork:

   ```bash
   git push origin feat/my-awesome-feature
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

### Before submitting, please check:

- [ ] The code runs without PHP errors or warnings.
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

- Follow the existing code style (PHP, HTML, JS) already present in the file you are editing.
- Keep PHP files UTF-8 encoded.
- Avoid introducing new external dependencies without discussion.
- SQL queries should use parameterised statements (or at minimum properly escape user input with `mysqli_real_escape_string`).
- Do not commit large binary files or generated assets (uploads, exports, etc.).
