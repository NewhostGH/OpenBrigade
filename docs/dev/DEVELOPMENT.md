# Development Environment

Everything needed to get OpenBrigade running locally and to work on it day to day:
environment setup, database, authentication, seeding, frontend assets, and quality
tooling.

This is the **single source of truth** for "how do I run and develop this project".
For *how to write the code* see [CONVENTIONS.md](CONVENTIONS.md); for *where things
live* see [ARCHITECTURE.md](ARCHITECTURE.md); for *contribution process* (branches,
commits, PRs) see [CONTRIBUTING.md](../../.github/CONTRIBUTING.md).

OpenBrigade is a **Laravel 12 / PHP 8.4** application backed by **MySQL/MariaDB**.
Frontend assets are bundled with **Vite** (npm). Quality is enforced by **Pint**
(format), **PHPStan/Larastan** (static analysis) and **Pest** (tests).

---

## 1. Setup

Pick one of the three options below. Docker is recommended.

### Option A — Docker Compose (recommended)

Requires [Docker](https://docs.docker.com/get-docker/) and Docker Compose.

```bash
cp .env.example .env        # adjust credentials if needed
docker compose up -d
```

| Service                         | URL / port              | Notes                                                        |
| ------------------------------- | ----------------------- | ------------------------------------------------------------ |
| Application (`openbrigade_app`) | <http://localhost:8080> | `APP_PORT`, Apache → port 80                                 |
| Database (`openbrigade_db`)     | `localhost:3306`        | MariaDB 11.4, `DB_PORT_EXTERNAL`                             |
| DBGate (`openbrigade_dbgate`)   | <http://localhost:8888> | Web DB browser, `DBGATE_PORT` — dev only (`COMPOSE_PROFILES=development`) |

After the containers are up, run migrations and seed development data:

```bash
docker compose exec app php artisan migrate --seed
docker compose exec app sh -lc "npm ci && npm run build"
```

Stop everything with `docker compose down` (add `-v` to also drop the database
volume).

### Option B — VS Code Dev Container

Requires [VS Code](https://code.visualstudio.com/) and the
[Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers).

1. Open the repository folder in VS Code.
2. Run **Dev Containers: Reopen in Container** (or accept the prompt).
3. The container builds from `.devcontainer/Dockerfile`; the app is forwarded on
   port `8080`.

### Option C — Manual

| Dependency      | Version                                                                                          |
| --------------- | ------------------------------------------------------------------------------------------------ |
| PHP             | 8.4 (ext: `mbstring`, `xml`, `ctype`, `json`, `intl`, `pdo_mysql`, `zip`, `gd`; optional `ldap`) |
| Composer        | 2.x                                                                                              |
| Node.js         | 18+ (with npm)                                                                                   |
| MySQL / MariaDB | 5.7+ / 10.3+                                                                                     |
| Web server      | Nginx 1.24+ or Apache 2.4+ (`mod_rewrite`)                                                       |

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve            # http://localhost:8000
```

> OpenBrigade does **not** use the legacy PHP setup wizard. The schema is created
> exclusively by Laravel migrations (see §2).

---

## 2. Database & migrations

The schema is owned by Laravel migrations under `database/migrations/`. The first
migration imports the legacy eBrigade baseline; every later migration is a
forward-only change on top of it.

```bash
php artisan migrate            # apply pending migrations
php artisan migrate:status     # list applied / pending
php artisan migrate --seed     # migrate then seed
php artisan migrate:fresh --seed   # DROP everything and rebuild (destructive)
```

Full reference, the legacy baseline, the `ob_` table-prefix rule, and the
`legacy:migration:validate` parity command are documented in
[../admin/database-migration.md](../admin/database-migration.md).

---

## 3. Authentication

Authentication reads the **legacy `pompier` table directly** — there is no separate
`users` table. The Laravel `User` model maps to `pompier`, so existing legacy
accounts log in unchanged.

| Concern             | Location                                                                                 |
| ------------------- | ---------------------------------------------------------------------------------------- |
| Auth provider model | `config/auth.php` → `App\Models\User`                                                    |
| Table mapping       | `app/Models/User.php` → `protected $table = 'pompier'`                                   |
| Login logic         | `app/Services/Auth/AuthService.php` — by `P_EMAIL` or `P_CODE`, requires `P_FIN IS NULL` |

Password checking accepts **both** legacy MD5 hashes and modern `password_hash()`
values. On a successful login against a legacy MD5 hash, the stored hash is
transparently upgraded to a modern one.

### Reset a password

Local:

```bash
php artisan tinker --execute='App\Models\User::query()->where("P_CODE","admin")->update(["P_MDP"=>Illuminate\Support\Facades\Hash::make("NewStrongPass123!"),"P_PASSWORD_FAILURE"=>null,"P_MDP_EXPIRY"=>null]);'
```

Docker (prefix with `docker compose exec app`), or reset by email by swapping
`where("P_CODE","admin")` for `where("P_EMAIL","admin@mybrigade.org")`.

### Verify which table auth uses

```bash
php artisan tinker --execute='echo (new App\Models\User())->getTable();'   # -> pompier
```

---

## 4. Seeding

```bash
php artisan db:seed                                              # DatabaseSeeder
php artisan db:seed --class=Database\\Seeders\\DevelopmentDataSeeder
```

- `DatabaseSeeder` currently calls `DevelopmentDataSeeder`.
- `DevelopmentDataSeeder` creates/updates the login `dev.manager` with password
  `password`. Confirm it exists:

  ```bash
  php artisan tinker --execute='var_dump(App\Models\User::query()->where("P_CODE","dev.manager")->exists());'
  ```

Under Docker, prefix every command with `docker compose exec app`.

---

## 5. Frontend assets (CSS / JS / Vite)

All source lives under `resources/`. **Nothing is loaded from a CDN** — Bootstrap,
FontAwesome, Leaflet, etc. are npm packages bundled by Vite into `public/build/`
(git-ignored). Blade views load bundles via the `@vite()` helper.

### CSS structure (`resources/css/`)

`app.css` is an **import hub only** — never add rules there. Add your code to the
file that matches the area, or create a new `resources/css/<module>.css` and add an
`@import './<module>.css';` line at the bottom of `app.css`.

| File                         | What goes there                                                        |
| ---------------------------- | ---------------------------------------------------------------------- |
| `app.css`                    | Import hub only                                                        |
| `variables.css`              | Design tokens (`--sidebar-*`, `--font-size-*`, colours)                |
| `base.css`                   | `body`, resets, global utilities                                       |
| `layout.css`                 | Content offset, responsive `@media`                                    |
| `navbar.css` / `sidebar.css` | Shell chrome                                                           |
| `components.css`             | Reusable `ob-*` components (toolbar, table, badge, avatar…)            |
| `<module>.css`               | One file per feature module (`ob-personnel.css`, `ob-planning.css`, …) |

### JS structure (`resources/js/`)

`app.js` is the single global entry (jQuery + Bootstrap + layout logic). Page-specific
scripts are their own files (`ob-<module>-<page>.js`) loaded with
`@push('scripts') @vite('resources/js/ob-foo.js') @endpush`.

Per-page JS that needs Blade data must read it from a plain (non-module) `<script>`
block: `window.MY_DATA = @json($data);` — the deferred ES module then reads
`window.MY_DATA`.

### Build & dev server

```bash
npm install        # install / update dependencies (also installs Husky hooks)
npm run build      # production build → public/build/
npm run dev        # Vite dev server with hot reload (local only)
```

After a `build`, hard-refresh the browser (Ctrl+Shift+R). After editing Blade
templates under Docker, clear the compiled views:

```bash
docker compose exec app php artisan view:clear
```

> The Vite dev server (`npm run dev`) must be reachable from the browser, so it only
> works when running on the host. Under Docker, use the `build` workflow or expose
> the HMR port in `docker-compose.yml`.

### Navigation menu

Top-level navigation is defined in **`config/navigation.php`** (not the database) and
rendered server-side by `App\Services\NavigationService`. Each group/item carries an
optional `permission` key (integer feature ID). Adding an entry needs no rebuild. See
the inline comments in that file for the field reference.

---

## 6. Quality tooling

Run these before pushing — CI runs the same three steps and fails the build on any
error.

```bash
composer pint -- --test     # code style check (drop --test to auto-fix)
composer analyse            # PHPStan / Larastan, level 5
composer test               # Pest test suite
```

`tests/Feature/ConventionsTest.php` mechanically enforces the most important
[conventions](CONVENTIONS.md) (no inline `<style>`, legacy refs flagged, no missing
`/legacy/` prefix). Keep it green.

---

## See also

- [CONVENTIONS.md](CONVENTIONS.md) — binding coding rules and UI component patterns
- [ARCHITECTURE.md](ARCHITECTURE.md) — directory layout and layer responsibilities
- [admin/database-migration.md](../admin/database-migration.md) — schema, baseline, parity
- [CONTRIBUTING.md](../../.github/CONTRIBUTING.md) — branches, commits, PR process
