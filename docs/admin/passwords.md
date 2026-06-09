# Password Reset (Admin Guide)

There is currently no self-service password-reset flow in the UI. Until one is
implemented, an administrator with shell access can reset any account's password
via the Artisan Tinker REPL.

---

## Quick reset via one-liner

**With Docker Compose:**

```bash
docker compose exec app php artisan tinker --execute="
\$u = App\Models\User::where('P_CODE', 'SP001')->firstOrFail();
\$u->forceFill([
    'P_MDP'              => password_hash('TemporaryPass1!', PASSWORD_DEFAULT),
    'P_MDP_EXPIRY'       => now()->toDateString(),
    'P_PASSWORD_FAILURE' => null,
])->save();
echo 'Done — ' . \$u->P_NOM . ' ' . \$u->P_PRENOM . PHP_EOL;
"
```

**Without Docker:**

```bash
php artisan tinker --execute="
\$u = App\Models\User::where('P_CODE', 'SP001')->firstOrFail();
\$u->forceFill([
    'P_MDP'              => password_hash('TemporaryPass1!', PASSWORD_DEFAULT),
    'P_MDP_EXPIRY'       => now()->toDateString(),
    'P_PASSWORD_FAILURE' => null,
])->save();
echo 'Done — ' . \$u->P_NOM . ' ' . \$u->P_PRENOM . PHP_EOL;
"
```

Replace `SP001` with the member's **matricule** (`P_CODE`) and
`TemporaryPass1!` with any temporary password you choose.

Setting `P_MDP_EXPIRY` to today's date forces the member to change their
password on first login. Clear it (`null`) if you do not want that behaviour.

---

## Interactive session

If you prefer to inspect the account first, open the REPL:

```bash
php artisan tinker
```

Then, step by step:

```php
// 1 — find the account (by matricule or email)
$u = App\Models\User::where('P_CODE', 'SP001')->firstOrFail();
// or
$u = App\Models\User::where('P_EMAIL', 'jean.dupont@example.com')->firstOrFail();

// 2 — inspect current state
echo $u->P_NOM . ' ' . $u->P_PRENOM;
echo "\nDernière connexion : " . $u->P_LAST_CONNECT;
echo "\nÉchecs mot de passe : " . $u->P_PASSWORD_FAILURE;

// 3 — set the new password
$u->forceFill([
    'P_MDP'              => password_hash('TemporaryPass1!', PASSWORD_DEFAULT),
    'P_MDP_EXPIRY'       => now()->toDateString(), // forces change on next login
    'P_PASSWORD_FAILURE' => null,                  // clears any lockout counter
])->save();
```

---

## Unblocking a locked account

If an account is blocked (`GP_ID = -1`) rather than just having a bad password,
the password reset alone is not enough — the block flag must be cleared too:

```php
$u = App\Models\User::where('P_CODE', 'SP001')->firstOrFail();

$u->forceFill([
    'GP_ID'              => 1,     // restore to default group
    'P_MDP'              => password_hash('TemporaryPass1!', PASSWORD_DEFAULT),
    'P_MDP_EXPIRY'       => now()->toDateString(),
    'P_PASSWORD_FAILURE' => null,
])->save();
```

---

## How passwords are stored

| Field | Table | Description |
|---|---|---|
| `P_MDP` | `pompier` | bcrypt hash (or MD5 for legacy accounts not yet migrated) |
| `P_MDP_EXPIRY` | `pompier` | Date after which the password is considered expired; `null` = no expiry |
| `P_PASSWORD_FAILURE` | `pompier` | Consecutive failed-login counter; `null` = no failures |

Legacy MD5 hashes are automatically upgraded to bcrypt the next time the member
logs in successfully — no manual migration needed.

---

## See also

- `app/Services/Auth/AuthService.php` — login, hash upgrade, failure tracking
- `archive/legacy_app/change_password.php` — legacy self-service change flow
  (not yet ported to Laravel)
