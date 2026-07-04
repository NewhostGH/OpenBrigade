# Backup & Restore (Admin Guide)

OpenBrigade includes a built-in backup feature: manual and scheduled snapshots
of the **database and the uploads tree**, written to a storage disk with an
optional **off-site mirror**, download, restore, automatic retention, and a
periodic **restore drill** that proves backups are recoverable. Reachable in the
UI under **Configuration → Sauvegarde** (`/admin/sauvegarde`, permission 14).

The backup engine lives in
[`App\Services\BackupService`](../../app/Services/BackupService.php); the
controller, the `backup:run-scheduled` command and the `backup:restore-drill`
command all delegate to it.

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

- **`include_files = true`** (default): each backup is a **`.zip`** containing
  `database.sql` plus the **entire stored-files tree** (`config('backup.files_path')`,
  default `storage/app`), so one file restores both the data and every user file:

  | Content            | Location bundled                       |
  | ------------------ | -------------------------------------- |
  | Profile photos     | `storage/app/private/profile_pictures` |
  | Album photos       | `storage/app/photos`                   |
  | Document library   | `storage/app/private/documents`        |
  | Section assets, RIB| `storage/app/private/sections`         |
  | Charter            | `storage/app/private/charte`           |
  | Theme / logo       | `storage/app/public/theme`             |

  Files are stored in the zip under their real `storage/app/…` paths, so
  extracting the archive at the project root restores them in place. The backups
  directory itself is always excluded (no self-nesting).
- **`include_files = false`**: a plain **`.sql`** dump (DB only, legacy behaviour).

Restore of the **database** auto-detects `.zip` vs `.sql`; **files** are restored
by extracting the archive over the project root (see Restore below).

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

For automatic backups to actually fire, the Laravel scheduler must be running.
The Docker stack ships a dedicated **`scheduler`** service (part of the
`minimal`/`full`/`dev` profiles) running `php artisan schedule:work`, so the
schedule fires out of the box — no host cron needed.

Outside Docker, add a single cron entry on the host:

```cron
* * * * * cd /path/to/openbrigade && php artisan schedule:run >> /dev/null 2>&1
```

You can trigger the due-check manually at any time:

```bash
php artisan backup:run-scheduled
```

### Off-site mirror

Set `BACKUP_OFFSITE_DISK` to a configured disk (typically the S3-compatible
`s3` disk) and each freshly written backup is copied there immediately, so
losing the server does not lose the backups with it. Fill the `AWS_*` block in
`.env` for the `s3` disk. An off-site copy failure is logged as a warning and
**never** voids a good local backup.

### Restore drill

When `BACKUP_RESTORE_DRILL=true`, the scheduler runs `backup:restore-drill`
weekly (Mondays 04:00). It restores the **latest** backup into a scratch
database (`drill_database`), asserts it loads (tables present), then drops it —
**never touching the live database**. Success/failure is logged to the
observability log. Run it on demand:

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
> confirm you have selected the correct file.

- **UI:** Configuration → Sauvegarde → choose a file → **Restaurer** (a confirmation
  modal guards the action). It restores the **database** from the archive
  (auto-detecting `.zip` vs `.sql`; for a `.zip` it extracts and replays the
  embedded `database.sql`).

> **Files are not restored by the UI action** — only the database is. To recover
> the stored files (photos, documents, RIB…) from a `.zip`, extract it over the
> project root so the `storage/app/…` paths land back in place (see below).

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

- Keep backups **off the app server** for disaster recovery — set
  `BACKUP_OFFSITE_DISK=s3` so every backup is mirrored automatically.
- The **restore drill** tests a restore for you weekly — but review its log and
  still rehearse a full recovery periodically; an untested backup is not a backup.
- Backups contain personal data (and, with files bundled, member photos and
  documents) — protect them per your data-retention obligations.

---

## See also

- [database-migration.md](database-migration.md) — schema, migrations, parity validation
- [installation.md](installation.md) — deployment and `.env`
- [../dev/ARCHITECTURE.md](../dev/ARCHITECTURE.md) — `storage/` and `config/` layout
