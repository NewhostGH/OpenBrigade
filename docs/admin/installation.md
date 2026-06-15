# Installation & Deployment (Admin Guide)

How to deploy OpenBrigade to a server. For local development setup use
[../dev/DEVELOPMENT.md](../dev/DEVELOPMENT.md) instead.

OpenBrigade is a **Laravel 12 / PHP 8.4** application backed by **MySQL/MariaDB**, with
frontend assets built by **Vite**.

---

## Requirements

| Dependency      | Version                                                                                          |
| --------------- | ------------------------------------------------------------------------------------------------ |
| PHP             | 8.4 (ext: `mbstring`, `xml`, `ctype`, `json`, `intl`, `pdo_mysql`, `zip`, `gd`; optional `ldap`) |
| Composer        | 2.x                                                                                              |
| Node.js         | 18+ (build-time only)                                                                            |
| MySQL / MariaDB | 5.7+ / 10.3+                                                                                     |
| Web server      | Nginx 1.24+ or Apache 2.4+ (document root → `public/`)                                           |

---

## Option A — Docker Compose

The shipped `docker-compose.yml` runs the app, a MariaDB 11.4 database, and CloudBeaver
(web DB browser).

```bash
git clone https://github.com/NewHostGH/OpenBrigade.git
cd OpenBrigade
cp .env.example .env          # set APP_KEY, DB creds, APP_URL, mail, etc.
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app sh -lc "npm ci && npm run build"
```

| Service     | Default URL                           |
| ----------- | ------------------------------------- |
| Application | <http://localhost:8080> (`APP_PORT`)  |
| Database    | `localhost:3306` (`DB_PORT_EXTERNAL`) |
| CloudBeaver | <http://localhost:8081> (`CB_PORT`)   |

For production behind a reverse proxy, set `APP_URL`, terminate TLS at the proxy, and
do **not** expose the database/CloudBeaver ports publicly.

---

## Option B — Manual deployment

```bash
git clone https://github.com/NewHostGH/OpenBrigade.git
cd OpenBrigade

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

# configure DB credentials in .env, then:
php artisan migrate

npm ci
npm run build

# cache config/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan storage:link        # expose storage/app/public uploads
```

Point the web server document root at `public/`. Ensure `storage/` and
`bootstrap/cache/` are writable by the web server user.

### Apache

Enable `mod_rewrite`; the shipped `public/.htaccess` handles front-controller routing.

### Nginx

```nginx
root /var/www/openbrigade/public;
index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

---

## Post-install checklist

- [ ] `php artisan migrate:status` shows all migrations applied.
- [ ] `php artisan legacy:migration:validate` passes (baseline tables present).
- [ ] Reset/seed an admin login (see [../dev/DEVELOPMENT.md](../dev/DEVELOPMENT.md) §3)
      and confirm login works.
- [ ] Mail is configured (`MAIL_*` in `.env`) and a test message sends.
- [ ] The Laravel scheduler runs (needed for automatic backups) — add the cron entry
      from [backup-and-restore.md](backup-and-restore.md).
- [ ] A first database backup has been taken and a restore tested.
- [ ] Database/admin ports are not publicly exposed; TLS is enforced.

---

## Upgrading

1. Take a database backup ([backup-and-restore.md](backup-and-restore.md)).
2. `git pull` the new version.
3. `composer install --no-dev --optimize-autoloader`
4. `php artisan migrate` (forward-only — never edit shipped migrations).
5. `npm ci && npm run build`
6. `php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache`

---

## See also

- [database-migration.md](database-migration.md) — schema and parity validation
- [backup-and-restore.md](backup-and-restore.md) — backups and the scheduler
- [../dev/DEVELOPMENT.md](../dev/DEVELOPMENT.md) — environment, auth, seeding
