# Password Reset (Admin Guide)

There is currently no self-service password-reset flow in the UI. Until one is
implemented, an administrator with shell access can reset any account's password
using the dedicated Artisan command below.

---

## Artisan command (recommended)

```bash
php artisan user:reset-password
```

The command is fully interactive: it prompts for the account identifier, shows
account details, asks for the new password (twice), and lets you choose whether
to force a change on next login and whether to unblock a locked account.

**With Docker Compose:**

```bash
docker compose exec app php artisan user:reset-password
```

### Non-interactive (scripting)

All prompts can be bypassed via options for use in scripts:

```bash
php artisan user:reset-password SP001 \
  --password="TemporaryPass1!" \
  --force-change \
  --unblock
```

| Option           | Description                                      |
| ---------------- | ------------------------------------------------ |
| `identifier`     | Positional arg — matricule (`P_CODE`) or e-mail  |
| `--password=`    | New password (skips the interactive prompt)      |
| `--force-change` | Forces the user to change password on next login |
| `--unblock`      | Resets `GP_ID` to 1 if the account is blocked    |

---

## Manual reset via Tinker (fallback)

If the Artisan command is unavailable, the same result can be achieved through
the Tinker REPL.

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

## Unblocking a locked account (Tinker)

If an account is blocked (`GP_ID = -1`) rather than just having a bad password,
the block flag must be cleared too:

```php
$u = App\Models\User::where('P_CODE', 'SP001')->firstOrFail();

$u->forceFill([
    'GP_ID'              => 1,     // restore to default group
    'P_MDP'              => password_hash('TemporaryPass1!', PASSWORD_DEFAULT),
    'P_MDP_EXPIRY'       => now()->toDateString(),
    'P_PASSWORD_FAILURE' => null,
])->save();
```

The `--unblock` option on `user:reset-password` does this in one step.

---

## How passwords are stored

| Field                | Table     | Description                                                             |
| -------------------- | --------- | ----------------------------------------------------------------------- |
| `P_MDP`              | `pompier` | bcrypt hash (or MD5 for legacy accounts not yet migrated)               |
| `P_MDP_EXPIRY`       | `pompier` | Date after which the password is considered expired; `null` = no expiry |
| `P_PASSWORD_FAILURE` | `pompier` | Consecutive failed-login counter; `null` = no failures                  |

Legacy MD5 hashes are automatically upgraded to bcrypt the next time the member
logs in successfully — no manual migration needed.

---

## See also

- `app/Console/Commands/ResetUserPassword.php` — the Artisan command
- `app/Services/Auth/AuthService.php` — login, hash upgrade, failure tracking
- `archive/legacy_app/change_password.php` — legacy self-service change flow
  (not yet ported to Laravel)
