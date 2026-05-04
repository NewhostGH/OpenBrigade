# Copilot Instructions – OpenBrigade

## Project Overview

**OpenBrigade** is a free, open-source PHP web application for managing volunteer emergency-response organizations (fire brigades, rescue teams, civil-protection associations, etc.). It is a community-maintained fork of **eBrigade 5.3.2**, the last open-source release of that project (original author: Nicolas MARCHE, eBrigade Technologies, © 2004–2021).

The new name reflects the project's open-source nature and its new life as a community-driven initiative. The main contributor and maintainer is [Maximilian NEU] working at [Newhost], but we welcome contributions from anyone interested in improving the software.

The application is licensed under the **GNU GPL v2.0 or later**.

---

## File Naming Conventions
- All PHP files are lowercase with underscores (`_`) as separators (e.g. `fonctions_mail.php`).
- CSS files are lowercase with hyphens (`-`) as separators (e.g. `main-styles.css`).
- JavaScript files are lowercase with hyphens (`-`) as separators (e.g. `form-validation.js`).
- Images are lowercase with hyphens (`-`) as separators (e.g. `logo.png`).

## File prefixes:

'''
  # project: OpenBrigade

  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
'''

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.1 |
| Database | MariaDB 10.11 / MySQL 5.7+ |
| Web server | Apache 2.4 with `mod_rewrite` |
| Front-end | Plain HTML, CSS, JavaScript (jQuery), Bootstrap |
| PDF generation | FPDF (via `lib/`) |
| Excel exports | PHP spreadsheet helpers |
| Containerisation | Docker Compose (app + db + phpMyAdmin) |

---

## Repository Structure

```
/
├── .devcontainer/          # VS Code Dev Container configuration
├── .github/                # GitHub templates, contributing guide, Copilot instructions
├── .env.example            # Environment variable template (copy to .env)
├── Dockerfile              # PHP 8.1 + Apache image
├── docker-compose.yml      # App (port 8080), MariaDB, phpMyAdmin (port 8081)
├── php.ini                 # Custom PHP settings applied at container build
│
├── conf/                   # Runtime configuration written by the setup wizard
├── user-data/              # User-uploaded files (avatars, documents, etc.)
├── sql/                    # SQL schema and migration files
├── lib/                    # Third-party PHP libraries (FPDF, PHPMailer, etc.)
├── css/                    # Stylesheets
├── js/                     # JavaScript files
├── images/                 # Static images
├── documentation/          # User / admin documentation
│
├── index.php               # Application entry point / dashboard
├── login.php               # Authentication
├── config.php              # Database connection and global constants
├── fonctions.php           # Core helper functions (used everywhere)
├── fonctions_*.php         # Domain-specific helper modules:
│   ├── fonctions_mail.php       – email sending
│   ├── fonctions_sms.php        – SMS notifications
│   ├── fonctions_gardes.php     – on-call / duty roster logic
│   ├── fonctions_sql.php        – SQL helpers and query builders
│   ├── fonctions_documents.php  – document management
│   └── ...
│
├── personnel*.php          # Member / personnel management
├── evenement*.php          # Events and interventions
├── astreinte*.php          # On-call rosters
├── vehicule*.php           # Vehicle inventory
├── materiel*.php           # Equipment inventory
├── cotisation*.php         # Membership fees
├── formation*.php          # Training records
├── pdf_*.php               # PDF generation endpoints
├── export*.php             # Data export endpoints (XLS, CSV, etc.)
└── api/                    # Lightweight REST-like API endpoints
```

---

## Key Conventions

- **Flat file layout** — almost all PHP pages live at the repository root; there is no MVC framework. Each `*.php` file is both a controller and a view.
- **Database access** — uses procedural MySQLi (`mysqli_*`) and PDO helpers defined in `fonctions_sql.php`. No ORM.
- **Authentication** — session-based (`$_SESSION`). Role/permission checks are done inline at the top of each page.
- **Configuration** — database credentials and app settings are stored in `conf/` (written by the setup wizard) and loaded via `config.php`. Never commit real credentials.
- **Internationalisation** — language strings are loaded from files in `lib/lang/`. The active language is set per-user.
- **Assets** — CSS and JS are included directly via `<link>` / `<script>` tags; no build pipeline (no npm/webpack).

---

## Development Setup

1. Copy `.env.example` to `.env` and adjust credentials.
2. Open the repo in VS Code — the Dev Container (`.devcontainer/`) will be detected automatically.
3. The Dev Container mounts the source code directly into `/var/www/html` so edits are reflected immediately.
4. Access the app at `http://localhost:8080` and run the setup wizard on first launch.
5. phpMyAdmin is available at `http://localhost:8081`.

---

## How to Contribute

The full contributing guide lives at [`.github/CONTRIBUTING.md`](.github/CONTRIBUTING.md). Key points:

- Fork the repository and create a branch (`fix/`, `feat/`, `docs/`, or `chore/` prefix).
- Never commit directly to `main`.
- Keep commits small and focused; use conventional commit messages (`fix:`, `feat:`, `docs:`, `chore:`).
- Before opening a pull request, verify the app runs without PHP errors and that no credentials are committed.
- Submit bugs and feature requests via [GitHub Issues](https://github.com/NewHostGH/OpenBrigade/issues) using the provided templates.
- Report security vulnerabilities privately via the repository's security advisories page — **never** in a public issue.

---

## Coding Guidelines

- Match the existing procedural PHP style; do not introduce frameworks or autoloaders.
- Keep SQL queries in `fonctions_sql.php` helper functions where possible; use prepared statements to prevent SQL injection.
- Escape all output with `htmlspecialchars()` to prevent XSS.
- Do not hard-code credentials, paths, or locale-specific strings.
- Test any database changes against MariaDB 10.11 (the version in `docker-compose.yml`).
