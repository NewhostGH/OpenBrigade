# Security hardening (Renforcement)

OpenBrigade exposes a set of defence-in-depth controls under
**Administration → Sécurité → Renforcement** (`/admin/security?tab=hardening`,
permission `14`). Every control is a setting stored in the legacy
`configuration` table and read through
[`App\Services\SecuritySettingService`](../../app/Services/SecuritySettingService.php).

The controls fall into three groups: HTTP security headers, authentication rate
limiting, and upload safety (including optional ClamAV malware scanning).

---

## HTTP security headers

Applied by [`App\Http\Middleware\SecurityHeaders`](../../app/Http/Middleware/SecurityHeaders.php)
on every web response. `X-Frame-Options`, `X-Content-Type-Options`,
`Referrer-Policy` and `Permissions-Policy` are always sent. Two are toggleable:

| Setting               | Default          | Effect                                                                                                                       |
| --------------------- | ---------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| `sec_csp_enabled`     | on               | Emit a `Content-Security-Policy` restricting script/style/img/connect origins.                                               |
| `sec_csp_report_only` | off              | Send the policy as `Content-Security-Policy-Report-Only` (logs violations without enforcing) — use to test before enforcing. |
| `sec_hsts_enabled`    | off              | Emit `Strict-Transport-Security`. **Only sent over a real HTTPS request**, so an HTTP-only deployment is never locked out.   |
| `sec_hsts_max_age`    | 15552000 (180 d) | HSTS `max-age` in seconds.                                                                                                   |

> Enable HSTS only once HTTPS is in place and confirmed working. Once a browser
> has seen a long `max-age`, it will refuse plain HTTP to your domain until it
> expires.

---

## Authentication rate limiting

The `throttle:auth` middleware (registered in
[`FortifyServiceProvider`](../../app/Providers/FortifyServiceProvider.php)) guards
the login and password-reset POST endpoints, keyed by client IP.

| Setting                      | Default | Effect                    |
| ---------------------------- | ------- | ------------------------- |
| `sec_ratelimit_auth_enabled` | on      | Turn throttling on/off.   |
| `sec_ratelimit_auth_max`     | 5       | Max attempts per window.  |
| `sec_ratelimit_auth_window`  | 1       | Window length in minutes. |

Exceeding the limit returns HTTP 429 until the window resets.

---

## Upload safety

Every file upload in the app (profile photos, RIB, library documents, album
photos, grade icons, theme images) is routed through
[`App\Services\UploadSecurityService::assertSafe()`](../../app/Services/UploadSecurityService.php),
which runs two layers.

### 1. MIME hardening (`sec_upload_mime_hardening`, default on)

- Rejects any file whose declared extension — at **any** part of the name, so
  `invoice.php.png` is caught — is on the forbidden list in
  [`config/uploads.php`](../../config/uploads.php) (executables, scripts,
  server-side code).
- Rejects files whose leading magic bytes match a dangerous binary signature
  (PE/ELF/Mach-O/shebang), regardless of extension.
- Verifies the real MIME type (via `finfo`) is consistent with the declared
  extension (with equivalences for image and ZIP-container office formats).

### 2. ClamAV malware scanning (`sec_upload_scan_enabled`)

When enabled, each upload is streamed to a [ClamAV](https://www.clamav.net/)
`clamd` daemon over the INSTREAM protocol by
[`App\Support\ClamavScanner`](../../app/Support/ClamavScanner.php) (no extra
Composer dependency — the wire protocol is implemented directly).

- A detected threat → the upload is rejected and the signature is logged.
- The daemon being unreachable → **fail-open by default** (the upload proceeds
  and a warning is logged), so a clamd outage never blocks all uploads. Set
  `uploads.clamav.fail_open` to `false` in `config/uploads.php` to fail closed.

| Setting                   | Default                     | Effect                 |
| ------------------------- | --------------------------- | ---------------------- |
| `sec_upload_scan_enabled` | on in Docker, off otherwise | Run ClamAV on uploads. |
| `sec_clamav_host`         | `clamav`                    | clamd host.            |
| `sec_clamav_port`         | 3310                        | clamd TCP port.        |

Use the **Tester** button next to the host/port fields to check reachability
(`PING`/`PONG`) without saving.

---

## Running ClamAV

### Docker (default)

`docker-compose.yml` ships a `clamav/clamav` service reachable from the app
container as host `clamav` on port `3310`. The app's `SECURITY_UPLOAD_SCAN`
environment variable defaults to `1` in Docker, so scanning is on out of the box.

The container downloads virus signatures on first boot (a few minutes); until it
is healthy the scanner fails open. The app does **not** wait on clamav health to
start.

### Bare-metal Linux

Install and run clamd, then point the app at it:

```bash
sudo apt-get update
sudo apt-get install -y clamav clamav-daemon
sudo freshclam                 # fetch initial signature database
sudo systemctl enable --now clamav-daemon
```

Expose clamd over TCP by setting in `/etc/clamav/clamd.conf`:

```ini
TCPSocket 3310
TCPAddr 127.0.0.1
```

then restart: `sudo systemctl restart clamav-daemon`.

Finally, in **Sécurité → Renforcement**, set the host (e.g. `127.0.0.1`) and port
(`3310`), enable **Analyse antivirus des téléversements**, and use **Tester** to
confirm the daemon answers.

### Verifying

Upload the harmless [EICAR test file](https://www.eicar.org/download-anti-malware-testfile/)
— with scanning on it must be rejected as malware. A normal image must still
upload successfully.
