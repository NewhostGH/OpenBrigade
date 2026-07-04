# SMS gateway

OpenBrigade sends SMS through a **provider-agnostic** layer, so the rest of the
app (notifications, reminders, alerts) never depends on a specific gateway.
Choosing a provider is a matter of setting `SMS_DRIVER` and its credentials —
no code changes.

## How it fits together

```diagram
Caller / Notification ─▶ NotificationService::sendSms()  ─▶ SmsManager
                                                              │ resolves config('sms.driver')
                                                              ▼
                                         ┌──────────────┬──────────────┬───────────────┐
                                         │ log (default)│ null         │ smsgatewayme  │
                                         └──────────────┴──────────────┴───────────────┘
```

- **`App\Services\Sms\SmsManager`** — resolves and caches the configured driver
  and forwards sends.
- **`App\Contracts\SmsSender`** — the one-method contract every driver
  implements (`send(SmsMessage): SmsResult`).
- **`App\Notifications\Channels\SmsChannel`** — lets Laravel notifications
  declare `'sms'` in `via()` and implement `toSms($notifiable)`.

Recipient numbers come from the notifiable's `routeNotificationForSms()`
(`User` uses `P_PHONE2`, falling back to `P_PHONE`).

## Drivers

| `SMS_DRIVER`   | Behaviour                                                                 |
| -------------- | ------------------------------------------------------------------------ |
| `log`          | **Default.** Writes the message to the log and sends nothing. Send-safe. |
| `null`         | Silently discards every message.                                         |
| `smsgatewayme` | Sends via a device registered on [SMSGateway.me](https://smsgateway.me). |

Set `SMS_DRIVER=log` in any environment where real SMS must never be sent.

## Configuring SMSGateway.me

SMSGateway.me relays messages through an Android phone running its app, which
acts as the actual SMS sender — no per-message carrier contract required.

1. Install the **SMSGateway.me** app on an Android device with an active SIM and
   sign in / create an account at <https://smsgateway.me>.
2. The device registers itself; note its **Device ID** (Devices page).
3. Create an **API token**: Account → *API Tokens* → generate a token.
4. Fill the environment:

   ```dotenv
   SMS_DRIVER=smsgatewayme
   SMS_FROM=OpenBrigade            # sender label, where the gateway allows it
   SMSGATEWAYME_TOKEN=your-api-token
   SMSGATEWAYME_DEVICE_ID=12345
   # Optional — override the API base if self-hosting the gateway:
   # SMSGATEWAYME_ENDPOINT=https://smsgateway.me/api/v4
   ```

5. Restart the app and the `queue-worker` container so the new config is loaded.

Messages are POSTed to `{endpoint}/message/send` with the token in the
`Authorization` header. A send returns an `SmsResult` carrying the provider
message id (`reference`) on success, or the error on failure.

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
