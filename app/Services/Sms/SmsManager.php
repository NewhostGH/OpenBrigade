<?php

// project: OpenBrigade

namespace App\Services\Sms;

use App\Contracts\SmsSender;
use App\Services\ServiceInterface;
use App\Services\Sms\Drivers\LogSmsSender;
use App\Services\Sms\Drivers\NullSmsSender;
use App\Services\Sms\Drivers\SmsGatewayMeSender;
use App\Services\SmsSettingService;
use InvalidArgumentException;

/**
 * Provider-agnostic SMS entry point. The driver and its credentials are
 * resolved at send time from the administrable settings (Administration ▸
 * Notifications, via {@see SmsSettingService}) with config/sms.php (env) as
 * fallback — so a settings change applies without a restart. Callers and the
 * notification SMS channel never depend on a concrete provider.
 *
 * // TODO: COMM — persist an SMS history row per send (COMM ▸ SMS history view).
 */
class SmsManager implements ServiceInterface
{
    /** Send a one-off SMS. Returns the driver's result. */
    public function send(string $to, string $body, ?string $from = null): SmsResult
    {
        $message = new SmsMessage($to, $body, $from ?? config('sms.from'));

        return $this->driver()->send($message);
    }

    /** Send a pre-built message. */
    public function sendMessage(SmsMessage $message): SmsResult
    {
        if ($message->from === null) {
            $message->from = config('sms.from');
        }

        return $this->driver()->send($message);
    }

    public function driver(): SmsSender
    {
        $settings = app(SmsSettingService::class);

        return $this->resolve($settings->driver(), $settings);
    }

    private function resolve(string $name, SmsSettingService $settings): SmsSender
    {
        return match ($name) {
            'log' => new LogSmsSender,
            'null' => new NullSmsSender,
            'smsgatewayme' => new SmsGatewayMeSender($settings->smsGatewayMeOptions()),
            default => throw new InvalidArgumentException("Unsupported SMS driver [{$name}]."),
        };
    }
}
