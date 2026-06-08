# Backup & Restore (Admin Guide)

OpenBrigade includes a built-in database backup feature: manual and scheduled
`mysqldump` snapshots written to a storage disk, with download, restore, and
automatic retention. Reachable in the UI under **Configuration → Sauvegarde**
(`/admin/sauvegarde`, permission 14).

---

## Where backups are stored

Backups are written through a Laravel filesystem disk, configured in
`config/backup.php`:

| Setting | `.env` variable | Default | Meaning |
|---|---|---|---|
| `disk` | `BACKUP_DISK` | `local` | Filesystem disk backups are written to |
| `path` | `BACKUP_PATH` | `backups` | Directory on that disk |
| `keep` | `BACKUP_KEEP` | `30` | Files to keep; older ones are pruned after each backup |

With defaults, dumps land in `storage/app/backups/`. Each file is a `mysqldump`
named with the database and a timestamp.

> The per-installation retention count can also be overridden in the UI (Backup
> settings, stored in `ob_backup_settings`); it takes precedence over `BACKUP_KEEP`.

---

## Manual backup

- **UI:** Configuration → Sauvegarde → **Créer une sauvegarde**.
- The controller runs `mysqldump` (path configurable via
  `config('database.mysqldump_path')`, default `mysqldump`) against the default
  connection, writes the file, then prunes to the retention limit.

Backups can be **downloaded** and **deleted** from the same page.

---

## Automatic (scheduled) backups

Automatic backups are driven by two pieces:

1. **Settings** (`ob_backup_settings`, edited in the UI) — `auto_enabled`, frequency
   (hourly / daily / weekly / monthly), time of day, `start_date`, and retention.
2. **Scheduler** — `routes/console.php` schedules `backup:run-scheduled`
   `->everyMinute()`. The `RunAutomaticBackup` command checks whether the configured
   schedule is due and only then takes a backup.

For automatic backups to actually fire, the Laravel scheduler must be running. Add a
single cron entry on the host (or an equivalent in your container orchestration):

```cron
* * * * * cd /path/to/openbrigade && php artisan schedule:run >> /dev/null 2>&1
```

Under Docker, run the scheduler in the app container (e.g. a supervisor process or a
host cron calling `docker compose exec app php artisan schedule:run`).

You can trigger the due-check manually at any time:

```bash
php artisan backup:run-scheduled
```

---

## Restore

> **Restoring overwrites the current database.** Take a fresh backup first and
> confirm you have selected the correct file.

- **UI:** Configuration → Sauvegarde → choose a file → **Restaurer** (a confirmation
  modal guards the action). The controller reads the dump from the backup disk and
  replays it into the configured database.

To restore a downloaded dump manually:

```bash
# Local
mysql -u <user> -p <database> < backup_file.sql

# Docker
docker compose exec -T db mysql -u <user> -p<password> <database> < backup_file.sql
```

After a manual restore, clear caches and verify login:

```bash
php artisan optimize:clear
```

---

## Operational notes

- Keep backups **off the app server** for disaster recovery — point `BACKUP_DISK` at
  an S3-style disk, or sync `storage/app/backups/` to external storage.
- Test a restore periodically; an untested backup is not a backup.
- Backups contain personal data — protect them per your data-retention obligations.

---

## See also

- [database-migration.md](database-migration.md) — schema, migrations, parity validation
- [installation.md](installation.md) — deployment and `.env`
- [../dev/ARCHITECTURE.md](../dev/ARCHITECTURE.md) — `storage/` and `config/` layout
