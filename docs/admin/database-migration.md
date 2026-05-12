# Database Migration Guide

This guide shows how to run Laravel database migrations in OpenBrigade.

## Prerequisites

- `.env` is configured.
- Database container is running (Docker setup) or database server is reachable (local setup).

## Docker Compose Commands

Run migrations inside the app container:

```bash
docker compose exec app php artisan migrate
```

Check migration status:

```bash
docker compose exec app php artisan migrate:status
```

Run pending migrations and seed development data:

```bash
docker compose exec app php artisan migrate --seed
```

Rollback last migration batch:

```bash
docker compose exec app php artisan migrate:rollback
```

Drop all tables and rebuild schema (destructive):

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Local (No Docker) Commands

From the project root, run:

```bash
php artisan migrate
```

Check migration status:

```bash
php artisan migrate:status
```

Run pending migrations and seed development data:

```bash
php artisan migrate --seed
```

Rollback last migration batch:

```bash
php artisan migrate:rollback
```

Drop all tables and rebuild schema (destructive):

```bash
php artisan migrate:fresh --seed
```

## Legacy Data Migration Validation

Validate that baseline legacy tables exist in the OpenBrigade database:

```bash
docker compose exec app php artisan legacy:migration:validate
```

Local equivalent:

```bash
php artisan legacy:migration:validate
```

Strict validation compares row counts against a legacy source database (requires `LEGACY_DB_*` variables in `.env`):

```bash
docker compose exec app php artisan legacy:migration:validate --strict
```

## Recommended Workflow

1. Start services and ensure DB is healthy.
2. Run `migrate`.
3. Run `migrate:status`.
4. Run `legacy:migration:validate`.
5. If needed, run strict validation with legacy DB credentials.
