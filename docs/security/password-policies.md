# Password policies

OpenBrigade supports named password policies that can be assigned per
habilitation group. Different groups (e.g. regular members vs. administrators)
can have different rules for password length, complexity, expiry, and lockout.

---

## Concepts

### Policy table

Policies live in the `ob_password_policy` database table. Each row has:

| Column              | Description                                                   |
| ------------------- | ------------------------------------------------------------- |
| `name`              | Human-readable label                                          |
| `min_length`        | Minimum character count (NCSC recommends ≥ 12)                |
| `require_uppercase` | Mandate at least one A-Z                                      |
| `require_lowercase` | Mandate at least one a-z                                      |
| `require_digits`    | Mandate at least one 0-9                                      |
| `require_special`   | Mandate at least one non-alphanumeric character               |
| `expiry_days`       | Days before the password expires (0 = never)                  |
| `max_attempts`      | Failed attempts before lockout (0 = disabled)                 |
| `blocklist_check`   | Reject common/known-bad passwords                             |
| `require_2fa`       | Force TOTP enrolment for users in groups using this policy    |
| `is_default`        | Exactly one row may be the fallback for groups with no policy |

### Resolution order

When enforcing a policy for a user:

1. Policy assigned to the user's primary group (`GP_ID`)
2. Policy assigned to the user's secondary group (`GP_ID2`)
3. The `is_default = true` row
4. Hard-coded NCSC fallback (12 chars, no complexity, no expiry, blocklist on)

### Group assignment

Each `ob_group` row has a nullable `password_policy_id` FK. Assigning a policy
to a group routes all group members to that policy. A group with no policy
inherits the default.

---

## Guidance followed

### NCSC (UK National Cyber Security Centre)

<https://www.ncsc.gov.uk/collection/passwords/updating-your-approach>

Applied as defaults: minimum **12 characters** (length is the primary
strength driver); **no complexity requirements** (forced complexity makes
passwords weaker in practice); **no forced rotation** (routine expiry causes
predictable increments like `Spring2024!` → `Summer2024!`); **block
predictable/breached passwords** (see [Blocklist](#blocklist)); **throttle
failures** (5-10 attempts before lockout is the sweet spot).

### ANSSI (Agence nationale de la sécurité des systèmes d'information)

<https://cyber.gouv.fr/publications/recommandations-relatives-lauthentification-multifacteur-et-aux-mots-de-passe>

Tiered model based on entropy and authentication context:

| Tier | Min. length | Min. character types |
| ---- | ----------- | -------------------- |
| 1    | 12          | 4                    |
| 2    | 14          | 3                    |
| 3    | 16          | 2                    |
| 4    | 20          | 1                    |

For privileged accounts (administrators), ANSSI requires MFA in addition to a
strong password: map to an admin group policy with `min_length = 16`, at
least 2 character-type requirements, and `require_2fa = true`.

---

## Managing policies

Policies are managed at **Administration → Sécurité → Mot de passe**.

**Create**: **Nouvelle politique** → name, length, toggle options → select
habilitation groups in the right sidebar → save. Only one policy may be
**Politique par défaut**; setting a new default clears the previous one.

**Edit/delete**: pencil/trash buttons in the policy table. The default
policy cannot be deleted.

### Recommended setup for most deployments

| Group          | Policy                                                                                                  |
| -------------- | ------------------------------------------------------------------------------------------------------- |
| Members        | Default policy - NCSC profile (12 chars, blocklist, 10 attempts, no expiry)                             |
| Administrators | Admin policy - ANSSI tier 3 (16 chars, uppercase + digits, blocklist, 5 attempts, `require_2fa = true`) |

---

## Blocklist

When a policy has `blocklist_check = true`, `PasswordPolicyService` rejects:

- **Trivial patterns**: all-same-character (`aaaaaaa`) and consecutive
  ascending/descending sequences of 5+ characters (`abcdefg`, `123456`).
- **Known breaches**: on password changes (not login, to avoid latency),
  checked against the Have I Been Pwned k-anonymity API via Laravel's
  `Password::uncompromised()`. Fails open if the API is unreachable.

---

## 2FA requirement

When a policy has `require_2fa = true`:

- On successful password login, if the user has **not enrolled TOTP**, they are
  logged in and immediately redirected to `/account/2fa` with a warning.
- Once enrolled, subsequent logins require the TOTP code before completing.

See [totp.md](totp.md) for the full TOTP flow.
