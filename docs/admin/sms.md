# SMS gateway

OpenBrigade sends SMS through a **provider-agnostic** layer: set `SMS_DRIVER`
and credentials, no code changes.

- **`App\Services\Sms\SmsManager`**: resolves and caches the configured
  driver, forwards sends (`config('sms.driver')`).
- **`App\Contracts\SmsSender`**: one-method contract every driver implements
  (`send(SmsMessage): SmsResult`).
- **`App\Notifications\Channels\SmsChannel`**: lets notifications declare
  `'sms'` in `via()` and implement `toSms($notifiable)`.

Recipient numbers come from `routeNotificationForSms()` (`User` uses
`P_PHONE2`, falling back to `P_PHONE`).

## Drivers

| `SMS_DRIVER`   | Behavior                                                                 |
| -------------- | ------------------------------------------------------------------------ |
| `log`          | **Default.** Writes the message to the log and sends nothing. Send-safe. |
| `null`         | Silently discards every message.                                         |
| `smsgatewayme` | Sends via a device registered on [SMSGateway.me](https://smsgateway.me). |
| `smsmode`      | [smsmode](https://www.smsmode.com) REST API (API-key auth).              |
| `clickatell`   | Clickatell one-API (`platform.clickatell.com`, API-key auth).            |
| `smseagle`     | Self-hosted [SMSEagle](https://www.smseagle.eu) appliance (APIv2).       |
| `http`         | Generic gateway: a URL template you fill with placeholders.              |

Set `SMS_DRIVER=log` in any environment where real SMS must never be sent.
Each real driver reads its secret from `SMS_*` env vars (below) or the
matching Administration ▸ Notifications setting row.

```dotenv
# smsmode: REST API key, optional sender via SMS_FROM
SMSMODE_API_KEY=your-api-key
# SMSMODE_ENDPOINT=https://rest.smsmode.com

# Clickatell: one-API integration key
CLICKATELL_API_KEY=your-api-key
# CLICKATELL_ENDPOINT=https://platform.clickatell.com

# SMSEagle: appliance host (bare host or full URL) + access token
SMSEAGLE_HOST=10.0.0.5
SMSEAGLE_TOKEN=your-access-token

# Generic HTTP: {to} {message} {from} {token} are rawurlencoded on send
SMS_HTTP_URL=https://gateway.example/send?to={to}&text={message}&key={token}
SMS_HTTP_METHOD=GET            # or POST
SMS_HTTP_TOKEN=your-secret
```

> **Balance / credits are not ported.** The legacy `getSMSCredit_*` /
> `show_sms_account_balance` helpers (remaining-credit display) have no
> equivalent: the `SmsSender` contract only sends. Check credit on the
> provider's own dashboard.

## Configuring from the admin UI (Administration ▸ Notifications)

The gateway can also be configured without touching `.env`. These
`configuration` rows take precedence over the environment: any field left
empty falls back to the corresponding `SMS_*` env value, so an install
configured through `.env` keeps working untouched. Changes apply immediately
(`App\Services\SmsSettingService` resolves at send time).

| Setting row    | Meaning                                              | Env fallback                        |
| -------------- | --------------------------------------------------- | ----------------------------------- |
| `sms_provider` | Driver name (see the table above)                   | `SMS_DRIVER`                        |
| `sms_user`     | Sender id for smsmode, unused by the others         | (none)                              |
| `sms_password` | API key / token / secret for the selected driver    | `SMSGATEWAYME_TOKEN`, `SMSMODE_API_KEY`, `CLICKATELL_API_KEY`, `SMSEAGLE_TOKEN`, `SMS_HTTP_TOKEN` |
| `sms_api_id`   | Device ID (SMSGateway.me), host (SMSEagle), or URL template (`http`) | `SMSGATEWAYME_DEVICE_ID`, `SMSEAGLE_HOST`, `SMS_HTTP_URL` |

The generic `http` driver's method comes from `config/sms.php`
(`SMS_HTTP_METHOD`) only. Its URL template and secret are taken from the
`sms_api_id` and `sms_password` rows (env fallback to `SMS_HTTP_URL` and
`SMS_HTTP_TOKEN`).

Legacy spellings of the provider (`smsgateway.me`, `smsgateway`) are
normalized. An unknown provider resolves to the `null` driver (SMS disabled)
with a warning in the log rather than an error.

## Configuring SMSGateway.me

Relays messages through an Android phone running its app, no per-message
carrier contract required.

1. Install the **SMSGateway.me** app on an Android device with an active SIM,
   sign in / create an account at <https://smsgateway.me>.
2. The device registers itself; note its **Device ID** (Devices page).
3. Create an **API token**: Account → *API Tokens* → generate a token.
4. Fill the environment:

   ```dotenv
   SMS_DRIVER=smsgatewayme
   SMS_FROM=OpenBrigade            # sender label, where the gateway allows it
   SMSGATEWAYME_TOKEN=your-api-token
   SMSGATEWAYME_DEVICE_ID=12345
   # Optional: override the API base if self-hosting the gateway:
   # SMSGATEWAYME_ENDPOINT=https://smsgateway.me/api/v4
   ```

5. Restart the app and `queue-worker` container to load the new config.

Messages are POSTed to `{endpoint}/message/send` with the token in the
`Authorization` header.

## Enabling / disabling SMS globally

Independent of the driver, SMS can be hard-disabled with the
`sms_allowed` application setting (`configuration` table): set it to `0` and
`NotificationService::sendSms()` becomes a no-op. When the setting is absent,
SMS is allowed (the default driver is the send-safe `log` driver).

## Testing

```bash
# Uses whatever SMS_DRIVER is configured (log driver just records it):
php artisan tinker
>>> app(\App\Services\NotificationService::class)->sendSms('+33612345678', 'Test OpenBrigade');
```

With `SMS_DRIVER=log`, check `storage/logs/laravel.log` for the recorded
message.

## Adding another provider

Implement `App\Contracts\SmsSender`, drop the class under
`app/Services/Sms/Drivers/`, and add a `match` arm in `SmsManager::resolve()`
plus a config block under `config/sms.php`. Nothing else changes.
