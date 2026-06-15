# LDAP / Active Directory Authentication

OpenBrigade can delegate password verification to an LDAP directory (OpenLDAP,
Active Directory, FreeIPA, etc.) via `directorytree/ldaprecord-laravel`.

When LDAP is enabled, local user accounts in `pompier` are still required —
LDAP only replaces the password check. Group assignments, permissions, and all
other profile data remain in the OpenBrigade database.

---

## How it works

1. User submits their username and password on the login page.
2. AuthService looks up the local `pompier` account.
3. If `LDAP_ENABLED=true`, instead of verifying against `P_MDP`, it:
   - **Bind method** (default): binds with the service account, searches for
     the user's DN by login/email, then tries to bind as that user.
   - **UPN method** (Active Directory shortcut): constructs the bind DN as
     `{login}{LDAP_UPN_SUFFIX}` and tries binding directly.
4. A successful LDAP bind means the password is correct; the rest of the login
   flow (lockout checks, TOTP, session creation) continues as normal.

---

## Configuration

All LDAP settings live in `.env`. No database rows are needed.

### Required variables

```env
LDAP_ENABLED=true
LDAP_HOST=ldap.example.com
LDAP_BASE_DN=dc=example,dc=com
LDAP_USERNAME=cn=svc-openbrigade,ou=services,dc=example,dc=com
LDAP_PASSWORD=<service-account-password>
```

### Optional variables

```env
# Port (default 389; use 636 for LDAPS)
LDAP_PORT=389

# Use TLS (LDAPS on port 636)
LDAP_TLS=false

# Use STARTTLS (upgrades plain connection; requires server support)
LDAP_STARTTLS=false

# Connection timeout in seconds
LDAP_TIMEOUT=5

# Auth method: 'bind' (default) or 'upn' (Active Directory)
LDAP_AUTH_METHOD=bind

# Search filter for 'bind' method. {login} is replaced with the submitted login.
LDAP_USER_FILTER=(&(objectClass=person)(|(uid={login})(mail={login})))

# UPN suffix for 'upn' method (Active Directory).
# The bind DN will be: {login}@corp.example.com
LDAP_UPN_SUFFIX=@corp.example.com

# Log all LDAP operations to the app log (useful for debugging)
LDAP_LOGGING=false
```

---

## Authentication methods

### `bind` (default — OpenLDAP, FreeIPA, generic LDAP)

The service account (defined by `LDAP_USERNAME` / `LDAP_PASSWORD`) performs a
search to find the user's Distinguished Name. The DN is then used to attempt a
second bind as the end user.

The `LDAP_USER_FILTER` must match exactly one entry per login. The default
filter tries both `uid` and `mail` attributes:

```ldap
(&(objectClass=person)(|(uid={login})(mail={login})))
```

For Active Directory with the `bind` method, you typically want:

```env
LDAP_USER_FILTER=(&(objectClass=user)(|(sAMAccountName={login})(userPrincipalName={login})))
```

### `upn` (Active Directory — simpler, no service account search needed)

Builds the bind DN as `{login}{LDAP_UPN_SUFFIX}`. No prior search is
performed, so `LDAP_USERNAME` / `LDAP_PASSWORD` are only used for the
test-connection button in the admin panel.

```env
LDAP_AUTH_METHOD=upn
LDAP_UPN_SUFFIX=@corp.example.com
```

---

## Testing the connection

**Administration → Sécurité → Authentification** shows the current LDAP
configuration and a **"Tester la connexion"** button. This tests that the
service account can bind to the directory — it does not test user authentication.

If the button is disabled, `LDAP_ENABLED` is false. The button triggers
`POST /admin/security/ldap-test`.

---

## Encryption in transit

Production deployments **must** use TLS or STARTTLS. LDAP over plain TCP
exposes passwords in transit.

| Option   | Env                                   | Notes                                            |
| -------- | ------------------------------------- | ------------------------------------------------ |
| LDAPS    | `LDAP_TLS=true`, `LDAP_PORT=636`      | Wraps the entire connection in TLS. Recommended. |
| STARTTLS | `LDAP_STARTTLS=true`, `LDAP_PORT=389` | Upgrades after connection. Check server support. |
| None     | (default)                             | Suitable only for local dev / localhost.         |

---

## Notes on local accounts

- Password hashes in `pompier.P_MDP` are **not** consulted when LDAP is
  enabled. Disabling LDAP falls back to local hashes automatically.
- Password history and expiry (`P_MDP_EXPIRY`) still come from the password
  policy, not from LDAP attributes.
- Changing a password from within OpenBrigade does **not** push the change to
  LDAP. Users must change passwords through the directory's own tooling.
- Failed-attempt counters (`P_PASSWORD_FAILURE`) and lockouts are tracked
  locally in `pompier`, regardless of directory policy.

---

## Development — emulating LDAP with Docker

For local development without a real directory, run a lightweight OpenLDAP
container.

### Quick start with Bitnami OpenLDAP

Add to `docker-compose.override.yml` (never commit credentials):

```yaml
services:
  ldap:
    image: bitnami/openldap:latest
    ports:
      - "389:1389"
    environment:
      LDAP_ROOT: dc=local,dc=com
      LDAP_ADMIN_USERNAME: admin
      LDAP_ADMIN_PASSWORD: dev-secret
      LDAP_USERS: "dev1,dev2"
      LDAP_PASSWORDS: "DevPass1!,DevPass2!"
    volumes:
      - ldap_data:/bitnami/openldap

volumes:
  ldap_data:
```

Corresponding `.env` (development only):

```env
LDAP_ENABLED=true
LDAP_HOST=ldap          # Docker service name, resolved inside the network
LDAP_PORT=1389
LDAP_BASE_DN=dc=local,dc=com
LDAP_USERNAME=cn=admin,dc=local,dc=com
LDAP_PASSWORD=dev-secret
LDAP_USER_FILTER=(&(objectClass=inetOrgPerson)(uid={login}))
LDAP_LOGGING=true
```

The Bitnami image creates users in `ou=users,dc=local,dc=com` with the
`inetOrgPerson` class and `uid` set to the username.

### Seeding test users

```bash
# Enter the LDAP container
docker compose exec ldap bash

# Add an extra user (requires ldif)
cat > /tmp/user.ldif <<EOF
dn: cn=johndoe,ou=users,dc=local,dc=com
objectClass: inetOrgPerson
cn: johndoe
sn: Doe
uid: johndoe
userPassword: SuperPass123!
EOF

ldapadd -x -H ldap://localhost:1389 \
  -D "cn=admin,dc=local,dc=com" \
  -w dev-secret \
  -f /tmp/user.ldif
```

The login (`P_CODE`) in `pompier` must match the `uid` attribute (or whatever
attribute `LDAP_USER_FILTER` targets).

### Verifying the bind manually

```bash
ldapsearch -x \
  -H ldap://localhost:389 \
  -D "uid=dev1,ou=users,dc=local,dc=com" \
  -w "DevPass1!" \
  -b "dc=local,dc=com" "(uid=dev1)"
```

A non-zero exit code means the bind failed (wrong DN, wrong password, or the
server is unreachable).

### Alternative: glauth (lightweight, no Docker volumes)

[glauth](https://github.com/glauth/glauth) is a single static binary that
reads users from a TOML file — ideal for CI or very quick local tests.

```toml
# glauth.cfg
[ldap]
  enabled = true
  listen = "0.0.0.0:389"

[ldaps]
  enabled = false

[backend]
  datastore = "config"
  baseDN = "dc=local,dc=com"

[[users]]
  name = "dev1"
  unixid = 5001
  primarygroup = 5501
  passsha256 = "..."   # echo -n "DevPass1!" | sha256sum

[[groups]]
  name = "firefighters"
  unixid = 5501
```

Run with:

```bash
./glauth64 -c glauth.cfg
```

`LDAP_USER_FILTER` for glauth: `(&(objectClass=*)(uid={login}))`
