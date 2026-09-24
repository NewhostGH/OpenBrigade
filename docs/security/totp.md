# TOTP two-factor authentication

OpenBrigade supports TOTP (Time-based One-Time Password) 2FA via
`laravel/fortify`. Users scan a QR code with an authenticator app and confirm
their identity with a 6-digit code on each login.

---

## User flow

- **Enrollment**: **Mon compte → Double authentification** (`/account/2fa`) →
  scan the QR code with any TOTP app (Google Authenticator, Aegis, Authy,
  Bitwarden, 1Password, etc.) → enter the 6-digit code to confirm → save the
  displayed **recovery codes** offline.
- **Login**: after password, if 2FA is enrolled and confirmed you're
  redirected to `/totp/challenge`; enter the 6-digit code, or expand "Use a
  recovery code" to paste one.
- **Disable**: from `/account/2fa`, click **Désactiver**, enter the current
  TOTP code.
- **Regenerate recovery codes**: same page, **Régénérer les codes** (old
  codes invalidated immediately).

---

## Forced enrollment via password policy

When a habilitation group's password policy has **`require_2fa = true`**:

- Users who have not enrolled are logged in normally after a correct password,
  then immediately redirected to `/account/2fa` with a warning.
- TOTP becomes mandatory on the **next** login once enrolled.

This lets you require 2FA for administrators without blocking all users.

See [password-policies.md](password-policies.md) for how to configure this.

---

## Technical details

TOTP is provided by `laravel/fortify`; only the `twoFactorAuthentication`
feature is enabled, Fortify's own login routes/views are disabled.

### User model columns

Added to the `pompier` table:

| Column                      | Purpose                                        |
| --------------------------- | ---------------------------------------------- |
| `two_factor_secret`         | Encrypted TOTP secret (encrypted at rest)      |
| `two_factor_recovery_codes` | Encrypted JSON array of recovery codes         |
| `two_factor_confirmed_at`   | Timestamp set when the user confirms enrollment |

`two_factor_secret` and `two_factor_recovery_codes` are encrypted via
Laravel's built-in encryption (using `APP_KEY`). Never expose them raw.

### Session handshake

The TOTP challenge sits between password verification and session creation.
After a correct password, `_totp_user_id` is stored in the session (user
**not** yet logged in); `/totp/challenge` routes are under `guest`
middleware. After a valid code, `completeTotpLogin()` reads the pending user
ID, calls `Auth::login()`, and clears the session key.

### Rate limiting

`App\Providers\FortifyServiceProvider` defines a `two-factor` rate limiter
(**5 attempts per minute**, keyed on the pending user ID `_totp_user_id`), but
that provider is not currently listed in `bootstrap/providers.php`, so the
limiter is not active. Only `App\Providers\AppServiceProvider`, which
registers the `auth` limiter, is guaranteed to boot.

---

## Recovery codes

Eight 10-character codes are generated at enrollment. Each is single-use: it
is removed from the encrypted JSON list after first use. Once all codes are
consumed, the user must regenerate them from `/account/2fa`.

---

## Troubleshooting

### "Code invalide" even though the code is correct

TOTP codes are time-based. Verify that the clock on the authenticator device
is synchronized (NTP). A drift of more than ±30 seconds will cause failures.
The Fortify provider allows a ±1 window (accepts the previous and next code).

### Lost device, no recovery codes

An administrator with shell access can disable 2FA directly:

```bash
php artisan tinker
>>> \App\Models\User::where('P_CODE','SP001')->first()->forceFill([
...     'two_factor_secret' => null,
...     'two_factor_recovery_codes' => null,
...     'two_factor_confirmed_at' => null,
... ])->save();
```

With Docker Compose:

```bash
docker compose exec app php artisan tinker
```
