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

The easiest way to run OpenBrigade locally is with Docker Compose:

```bash
git clone https://github.com/NewHostGH/OpenBrigade.git
cd OpenBrigade
cp .env.example .env   # edit credentials as needed
docker compose up -d
```

Then open `http://localhost:8080` in your browser and follow the setup wizard.

See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for development setup instructions including the VS Code Dev Container.

---

## Requirements (manual install)

| Dependency | Version |
|------------|---------|
| PHP        | 7.4 – 8.3 |
| MySQL / MariaDB | 5.7+ / 10.3+ |
| Web server | Apache 2.4+ (mod_rewrite) |

---

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](.github/CONTRIBUTING.md) for guidelines on how to:

- Fork the repository
- Create a branch
- Submit a pull request
- Report a bug or request a feature

---

## License

GNU General Public License v2.0 or later. See [license.txt](license.txt) for details.

---

## Credits

Originally developed as **eBrigade** by Nicolas MARCHE (eBrigade Technologies), Copyright © 2004–2021.  
See [README-eBrigade 5.3.2.txt](README-eBrigade%205.3.2.txt) for the original release notes.
