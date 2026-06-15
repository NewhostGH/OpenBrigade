# TOTP Two-Factor Authentication

OpenBrigade supports TOTP (Time-based One-Time Password) 2FA via
`laravel/fortify`. Users scan a QR code with an authenticator app and confirm
their identity with a 6-digit code on each login.

---

## User flow

### Self-service enrolment

1. Navigate to **Mon compte → Double authentification** (`/account/2fa`).
2. Scan the QR code with any TOTP app (Google Authenticator, Aegis, Authy,
   Bitwarden, 1Password, etc.).
3. Enter the 6-digit code shown by the app to confirm enrolment.
4. Save the displayed **recovery codes** offline — they can be used if the
   device is lost.

### Login with 2FA enabled

1. Enter username and password as usual.
2. If 2FA is enrolled and confirmed, you are redirected to the TOTP challenge
   page (`/totp/challenge`).
3. Enter the 6-digit code from the authenticator app.
4. Alternatively, expand the "Use a recovery code" section and paste one of the
   saved recovery codes.

### Disabling 2FA

From `/account/2fa`, click **Désactiver** and enter the current TOTP code to
confirm.

### Regenerating recovery codes

From the same page, click **Régénérer les codes**. Old codes are invalidated
immediately.

---

## Forced enrolment via password policy

When a habilitation group's password policy has **`require_2fa = true`**:

- Users who have not enrolled are logged in normally after a correct password,
  then immediately redirected to `/account/2fa` with a warning.
- TOTP becomes mandatory on the **next** login once enrolled.

This lets you require 2FA for administrators without blocking all users.

See [password-policies.md](password-policies.md) for how to configure this.

---

## Technical details

### Library

TOTP is provided by `laravel/fortify`. Only the `twoFactorAuthentication`
feature is enabled; Fortify's own login routes and views are disabled.

### User model columns

Added to the `pompier` table:

| Column                      | Purpose                                        |
| --------------------------- | ---------------------------------------------- |
| `two_factor_secret`         | Encrypted TOTP secret (encrypted at rest)      |
| `two_factor_recovery_codes` | Encrypted JSON array of recovery codes         |
| `two_factor_confirmed_at`   | Timestamp set when the user confirms enrolment |

`two_factor_secret` and `two_factor_recovery_codes` are encrypted via
Laravel's built-in encryption (using `APP_KEY`). Never expose them raw.

### Session handshake

The TOTP challenge sits between password verification and session creation.
After a correct password:

- `_totp_user_id` is stored in the session.
- The user is **not** yet logged in.
- The `/totp/challenge` routes are under `guest` middleware (redirects away
  if already authenticated).

After a valid code:

- `completeTotpLogin()` reads the pending user ID, calls `Auth::login()`, and
  clears the session key.

### Rate limiting

Fortify registers a `two-factor` rate limiter: **5 attempts per minute** keyed
on the pending user ID (`_totp_user_id`). This prevents brute-forcing the
6-digit window.

---

## Recovery codes

Eight 10-character codes are generated at enrolment. Each is single-use — it
is removed from the encrypted JSON list after first use. Once all codes are
consumed, the user must regenerate them from `/account/2fa`.

---

## Troubleshooting

### "Code invalide" even though the code is correct

TOTP codes are time-based. Verify that the clock on the authenticator device
is synchronised (NTP). A drift of more than ±30 seconds will cause failures.
The Fortify provider allows a ±1 window (accepts the previous and next code).

### Lost device — no recovery codes

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
