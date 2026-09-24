# Backup and restore (admin guide)

Manual and scheduled snapshots of the **database and uploads tree**, written
to a storage disk with an optional **off-site mirror**, download, restore,
retention, and a periodic **restore drill**. UI: **Configuration → Sauvegarde**
(`/admin/sauvegarde`, permission 14). Engine:
[`App\Services\BackupService`](../../app/Services/BackupService.php).

---

## Where backups are stored

Backups are written through a Laravel filesystem disk, configured in
`config/backup.php`:

| Setting           | `.env` variable          | Default                     | Meaning                                                |
| ----------------- | ------------------------ | --------------------------- | ------------------------------------------------------ |
| `disk`            | `BACKUP_DISK`            | `local`                     | Filesystem disk backups are written to                 |
| `path`            | `BACKUP_PATH`            | `backups`                   | Directory on that disk                                 |
| `keep`            | `BACKUP_KEEP`            | `30`                        | Files to keep; older ones are pruned after each backup |
| `include_files`   | `BACKUP_INCLUDE_FILES`   | `true`                      | Bundle all stored files into the archive (see below)   |
| `offsite_disk`    | `BACKUP_OFFSITE_DISK`    | *(none)*                    | Second disk each backup is copied to (e.g. `s3`)       |
| `restore_drill`   | `BACKUP_RESTORE_DRILL`   | `true`                      | Enable the weekly restore-drill schedule               |
| `drill_database`  | `BACKUP_DRILL_DATABASE`  | `openbrigade_restore_drill` | Scratch DB the drill restores into                     |

With defaults, backups land in `storage/app/backups/` (via the `local` disk,
which roots at `storage/app/private`).

### Archive format

- **`include_files = true`** (default): a **`.zip`** with `database.sql` plus
  the stored-files tree (`config('backup.files_path')`, default `storage/app`)
  under their real paths, so extracting at the project root restores both.
  Bundled: `storage/app/private/profile_pictures` (profile photos),
  `storage/app/photos` (album photos), `storage/app/private/documents`
  (document library), `storage/app/private/sections` (section assets, RIB),
  `storage/app/private/charte` (charter), `storage/app/public/theme`
  (theme/logo). The backups directory itself is excluded.
- **`include_files = false`**: a plain **`.sql`** dump (DB only, legacy).

Database restore auto-detects `.zip` vs `.sql`; files restore by extracting the
archive over the project root (see Restore).

> Retention can also be set in the UI (`ob_backup_settings`); it takes
> precedence over `BACKUP_KEEP`.

---

## Manual backup

**UI:** Configuration → Sauvegarde → **Créer une sauvegarde**. Runs `mysqldump`
(path via `config('database.mysqldump_path')`, default `mysqldump`), then
prunes to the retention limit. Backups can be **downloaded** and **deleted**
from the same page.

---

## Automatic (scheduled) backups

1. **Settings** (`ob_backup_settings`, edited in the UI): `auto_enabled`, frequency
   (hourly / daily / weekly / monthly), time of day, `start_date`, retention.
2. **Scheduler**: `routes/console.php` schedules `backup:run-scheduled`
   `->everyMinute()`; `RunAutomaticBackup` checks whether it's due.

The Laravel scheduler must be running. Docker ships a **`scheduler`** service
(`minimal`/`full`/`dev` profiles, `php artisan schedule:work`), no host cron
needed. Outside Docker, add a cron entry:

```cron
* * * * * cd /path/to/openbrigade && php artisan schedule:run >> /dev/null 2>&1
```

You can trigger the due-check manually at any time:

```bash
php artisan backup:run-scheduled
```

### Off-site mirror

Set `BACKUP_OFFSITE_DISK` to a configured disk (typically `s3`) and each
backup is copied there immediately (fill the `AWS_*` block in `.env`). A copy
failure is logged as a warning and never voids the local backup.

### Restore drill

When `BACKUP_RESTORE_DRILL=true`, the scheduler runs `backup:restore-drill`
weekly (Mondays 04:00): restores the **latest** backup into a scratch database
(`drill_database`), asserts it loads, then drops it (never touching the live
database). Logged to the observability log. Run it on demand:

```bash
php artisan backup:restore-drill
```

> **DB privileges:** the drill needs the application DB user to hold
> `CREATE`/`DROP` on the scratch database. Grant it once:
>
> ```sql
> GRANT ALL PRIVILEGES ON `openbrigade_restore_drill`.* TO 'openbrigade'@'%';
> FLUSH PRIVILEGES;
> ```
>
> If the user cannot create databases, set `BACKUP_RESTORE_DRILL=false` and run
> the drill manually against a maintenance connection instead.

---

## Restore

> **Restoring overwrites the current database.** Take a fresh backup first and
> confirm the file.

**UI:** Configuration → Sauvegarde → choose a file → **Restaurer** (confirmation
modal). Restores the **database only** from the archive (auto-detects `.zip`
vs `.sql`; for a `.zip`, extracts and replays the embedded `database.sql`). To
recover stored files (photos, documents, RIB…) from a `.zip`, extract it over
the project root so `storage/app/…` paths land back in place.

To restore a downloaded backup manually:

```bash
# DB only (.sql)
mysql -u <user> -p <database> < backup_file.sql

# From a .zip (DB + files)
unzip backup_file.zip database.sql -d /tmp && mysql -u <user> -p <database> < /tmp/database.sql
unzip -o backup_file.zip 'storage/*' -d /path/to/openbrigade   # restores the files in place

# Docker (DB)
docker compose exec -T db mysql -u <user> -p<password> <database> < backup_file.sql
```

After a manual restore, clear caches and verify login:

```bash
php artisan optimize:clear
```

---

## Operational notes

- Keep backups **off the app server** for disaster recovery: set
  `BACKUP_OFFSITE_DISK=s3` so every backup is mirrored automatically.
- The **restore drill** tests a restore for you weekly, but review its log and
  still rehearse a full recovery periodically; an untested backup is not a backup.
- Backups contain personal data (and, with files bundled, member photos and
  documents); protect them per your data-retention obligations.

---

## See also

- [database-migration.md](database-migration.md): schema, migrations, parity validation
- [installation.md](installation.md): deployment and `.env`
- [../dev/architecture.md](../dev/architecture.md): `storage/` and `config/` layout
