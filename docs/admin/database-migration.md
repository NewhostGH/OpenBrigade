# Database and migrations (admin guide)

Schema creation, evolution and validation against legacy eBrigade. Dev setup:
[../dev/development.md](../dev/development.md).

---

## How the schema is owned

Schema is owned **entirely by Laravel migrations** under `database/migrations/`
(no PHP setup wizard, no manual SQL import).

1. **Baseline**: `2026_05_06_..._migrate_legacy_5_5_to_openbrigade_6_0_0.php`
   imports the full legacy eBrigade 5.5 schema as the 6.0.0 starting point.
2. **Forward-only changes**: every later migration is additive. Migrations are
   never edited after they ship; corrections are new migrations.

### Table naming

| Kind                              | Rule                         | Example                                            |
| --------------------------------- | ---------------------------- | -------------------------------------------------- |
| Legacy tables (from the baseline) | Keep original eBrigade names | `pompier`, `configuration`, `personnel_cotisation` |
| Native OpenBrigade tables         | Prefixed `ob_`               | `ob_backup_settings`, `ob_user_shortcuts`          |

The `ob_` prefix makes it immediately clear which tables are inherited vs. native. See
[../dev/conventions.md](../dev/conventions.md) §2.

---

## Running migrations

Local (project root):

```bash
php artisan migrate            # apply pending migrations
php artisan migrate:status     # list applied / pending
php artisan migrate --seed     # migrate, then seed development data
php artisan migrate:rollback   # roll back the last batch
php artisan migrate:fresh --seed   # DROP all tables and rebuild (DESTRUCTIVE)
```

Docker: prefix each command with `docker compose exec app`, e.g.:

```bash
docker compose exec app php artisan migrate --seed
```

> `migrate:fresh` and `migrate:rollback` are destructive. Take a backup first
> (see [backup-and-restore.md](backup-and-restore.md)).

**First run**: `.env` configured → `migrate` → `migrate:status` (confirm
applied) → `legacy:migration:validate` (confirm baseline tables exist).

---

## Legacy parity validation

`legacy:migration:validate` (in `routes/console.php`) reads
`database/migrations/legacy/reference.sql`, checks each legacy table exists,
and reports row counts.

```bash
php artisan legacy:migration:validate
```

### Options

| Option           | Effect                                                           |
| ---------------- | ---------------------------------------------------------------- |
| `--table=<name>` | Validate only the named table(s); repeatable                     |
| `--strict`       | Also fail on row-count mismatches against a live legacy database |

```bash
# Validate specific tables only
php artisan legacy:migration:validate --table=pompier --table=evenement

# Strict row-count parity against a live legacy DB
php artisan legacy:migration:validate --strict
```

### Strict mode: legacy source connection

`--strict` compares row counts against a **live legacy database**. Configure
the optional `legacy` connection in `.env`:

```env
LEGACY_DB_HOST=legacy-db-host
LEGACY_DB_PORT=3306
LEGACY_DB_DATABASE=ebrigade_legacy
LEGACY_DB_USERNAME=legacy_user
LEGACY_DB_PASSWORD=legacy_password
```

Without these variables, run the command without `--strict` for an existence-only
check.

---

## Migrating from an existing eBrigade installation

To move a production eBrigade 5.x database into OpenBrigade:

1. **Back up** the legacy database (`mysqldump`).
2. Point `.env` at a **fresh, empty** database, run `php artisan migrate`
   (builds the schema from the baseline).
3. Import legacy data into the matching tables (baseline preserves legacy
   table/column names, so a straight load works for shared tables).
4. Set `LEGACY_DB_*` to the old database and run
   `php artisan legacy:migration:validate --strict` to confirm row-count parity.
5. Reset an admin password ([../dev/development.md](../dev/development.md) §3)
   and verify login.

---

## See also

- [backup-and-restore.md](backup-and-restore.md): taking and restoring backups
- [installation.md](installation.md): deploying OpenBrigade
- [../dev/development.md](../dev/development.md): local setup, seeding, auth
- [../dev/architecture.md](../dev/architecture.md): `database/` layout
