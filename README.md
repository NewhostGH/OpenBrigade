# OpenBrigade

**OpenBrigade** is a free, open-source fork of eBrigade, based on the last available open-source release (5.3.2). It is a web application for managing volunteer emergency-response organizations (fire brigades, rescue teams, associations, etc.).

> The original eBrigade editor announced on 15/07/2022 that the open-source version would no longer receive updates. Version 5.3.2 is the last available release. OpenBrigade picks up where it left off.

---

## Features

- Personnel and membership management
- Event and intervention tracking
- Scheduling, on-call rosters, and duty tables
- Equipment and vehicle inventory
- Training records and qualifications
- Document management
- Reporting and exports (Excel, PDF)
- Notifications (email / SMS)

---

## Quick Start (Docker)

```bash
git clone https://github.com/NewHostGH/OpenBrigade.git
cd OpenBrigade
cp .env.example .env   # edit credentials as needed
docker compose --profile minimal up -d   # app + db; use --profile full for clamav + error tracking
```

The stack uses nested Compose profiles: `app` ⊂ `minimal` ⊂ `full` ⊂ `dev`.
Pass `--profile` or set `COMPOSE_PROFILES` in `.env`; with no profile nothing starts.
Open `http://localhost:8080` and follow the setup wizard. Frontend assets are
built with Vite during the Docker image build (no CDN). For local (non-Docker)
builds: `npm install && npm run build`.

See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for full setup (incl. VS Code Dev Container).

Documentation:

- [Documentation index](docs/README.md): full doc map (developer + admin)
- [Developer setup](docs/dev/development.md): environment, database, auth, assets, tooling
- [Database Migration Guide](docs/admin/database-migration.md): schema, migrations, parity

### Data migration validation

```bash
php artisan legacy:migration:validate                                    # checks legacy tables exist + row counts
php artisan legacy:migration:validate --strict                           # compare against a live legacy DB (needs LEGACY_DB_* in .env)
php artisan legacy:migration:validate --table=personnel --table=evenement  # scope to specific tables
```

Reads `database/migrations/legacy/reference.sql`. For `--strict`, set
`LEGACY_DB_HOST`, `LEGACY_DB_PORT`, `LEGACY_DB_DATABASE`, `LEGACY_DB_USERNAME`,
`LEGACY_DB_PASSWORD` in `.env`.

---

## Contributing

Contributions are welcome! See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for
branching, commits, PRs, and how to report bugs or request features.

---

## License

GNU General Public License v2.0 or later. See [LICENSE](LICENSE) for details ([version française](docs/legal/license-fr.txt)).

---

## Credits

Originally developed as **eBrigade** by Nicolas MARCHE (eBrigade Technologies), Copyright © 2004-2021.  
See [README-eBrigade 5.3.2.txt](archive/legacy_app/README-eBrigade%205.3.2.txt) for the original release notes.
