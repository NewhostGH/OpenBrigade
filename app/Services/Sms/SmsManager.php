<?php

// project: OpenBrigade

namespace App\Services\Sms;

use App\Contracts\SmsSender;
use App\Services\ServiceInterface;
use App\Services\Sms\Drivers\LogSmsSender;
use App\Services\Sms\Drivers\NullSmsSender;
use App\Services\Sms\Drivers\SmsGatewayMeSender;
use InvalidArgumentException;

/**
 * Provider-agnostic SMS entry point. Resolves the configured driver
 * (config('sms.driver')) and forwards sends to it, so callers and the
 * notification SMS channel never depend on a concrete provider.
 *
 * // TODO: COMM — persist an SMS history row per send (COMM ▸ SMS history view).
 */
class SmsManager implements ServiceInterface
{
    private ?SmsSender $driver = null;

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
        return $this->driver ??= $this->resolve((string) config('sms.driver', 'log'));
    }

    private function resolve(string $name): SmsSender
    {
        return match ($name) {
            'log' => new LogSmsSender,
            'null' => new NullSmsSender,
            'smsgatewayme' => new SmsGatewayMeSender((array) config('sms.drivers.smsgatewayme')),
            default => throw new InvalidArgumentException("Unsupported SMS driver [{$name}]."),
        };
    }
}
