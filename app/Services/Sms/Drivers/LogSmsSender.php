<?php

// project: OpenBrigade

namespace App\Services\Sms\Drivers;

use App\Contracts\SmsSender;
use App\Services\Sms\SmsMessage;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Log;

/**
 * Dev-safe driver: records the SMS in the log and sends nothing. The default
 * so a fresh install never accidentally bills a real gateway.
 */
class LogSmsSender implements SmsSender
{
    public function send(SmsMessage $message): SmsResult
    {
        Log::channel(config('logging.default'))->info('SMS (log driver, not sent)', [
            'to' => $message->to,
            'from' => $message->from,
            'body' => $message->body,
        ]);

        return SmsResult::ok($this->name());
    }

    public function name(): string
    {
        return 'log';
    }
}
