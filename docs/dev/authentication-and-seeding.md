# Authentication and Seeding (Local + Docker)

This guide explains:

- how to reset a password in local mode and Docker mode,
- how to run seeders,
- which table is used by the new Laravel authentication.

## Short answer

Yes, authentication is still using the legacy table `pompier`.

There is no new `users` table for login yet. The Laravel `User` model is mapped to `pompier`, so existing legacy users are read directly.

## Where auth reads users

Authentication uses:

- `config/auth.php` provider `users` -> model `App\\Models\\User`
- `app/Models/User.php` -> `protected $table = 'pompier';`
- `app/Services/Auth/AuthService.php` -> login by `P_EMAIL` or `P_CODE`, with `P_FIN IS NULL`

Password check accepts both:

- legacy MD5 hashes,
- modern `password_hash()` values.

On successful login with a legacy MD5 hash, the hash is automatically upgraded to a modern hash.

## Reset password (Local mode)

Run from project root.

### Option A: reset by login code (`P_CODE`)

```bash
php artisan tinker --execute='App\Models\User::query()->where("P_CODE","admin")->update(["P_MDP"=>Illuminate\Support\Facades\Hash::make("NewStrongPass123!"),"P_PASSWORD_FAILURE"=>null,"P_MDP_EXPIRY"=>null]);'
```

### Option B: reset by email (`P_EMAIL`)

```bash
php artisan tinker --execute='App\Models\User::query()->where("P_EMAIL","admin@mybrigade.org")->update(["P_MDP"=>Illuminate\Support\Facades\Hash::make("NewStrongPass123!"),"P_PASSWORD_FAILURE"=>null,"P_MDP_EXPIRY"=>null]);'
```

## Reset password (Docker mode)

Run from project root.

```bash
docker compose exec app php artisan tinker --execute='App\Models\User::query()->where("P_CODE","admin")->update(["P_MDP"=>Illuminate\Support\Facades\Hash::make("NewStrongPass123!"),"P_PASSWORD_FAILURE"=>null,"P_MDP_EXPIRY"=>null]);'
```

If you prefer email:

```bash
docker compose exec app php artisan tinker --execute='App\Models\User::query()->where("P_EMAIL","admin@mybrigade.org")->update(["P_MDP"=>Illuminate\Support\Facades\Hash::make("NewStrongPass123!"),"P_PASSWORD_FAILURE"=>null,"P_MDP_EXPIRY"=>null]);'
```

## Run seeders

## Local mode

```bash
php artisan db:seed
```

Run only the development seeder:

```bash
php artisan db:seed --class=Database\\Seeders\\DevelopmentDataSeeder
```

## Docker mode

```bash
docker compose exec app php artisan db:seed
```

Run only the development seeder:

```bash
docker compose exec app php artisan db:seed --class=Database\\Seeders\\DevelopmentDataSeeder
```

Notes:

- `DatabaseSeeder` currently calls `DevelopmentDataSeeder`.
- `DevelopmentDataSeeder` creates/updates `dev.manager` with password `password`.

## Verify which table auth uses

### 1) Quick static verification in code

Confirm these files:

- `app/Models/User.php` (`$table = 'pompier'`)
- `config/auth.php` (provider model is `App\\Models\\User`)
- `app/Services/Auth/AuthService.php` (queries `User::query()` with `P_CODE`/`P_EMAIL`)

### 2) Runtime verification with Tinker

Local:

```bash
php artisan tinker --execute='echo (new App\Models\User())->getTable();'
```

Docker:

```bash
docker compose exec app php artisan tinker --execute='echo (new App\Models\User())->getTable();'
```

Expected output:

```text
pompier
```

### 3) Verify seeded login exists in `pompier`

Local:

```bash
php artisan tinker --execute='var_dump(App\Models\User::query()->where("P_CODE","dev.manager")->exists());'
```

Docker:

```bash
docker compose exec app php artisan tinker --execute='var_dump(App\Models\User::query()->where("P_CODE","dev.manager")->exists());'
```

Expected output includes `bool(true)` if seeders were executed.

## Does a new table fetch old users?

Current status:

- No separate new auth table is used for login.
- Legacy users are already the auth source because Laravel points directly to `pompier`.
- So there is no copy/sync step required today for authentication.
