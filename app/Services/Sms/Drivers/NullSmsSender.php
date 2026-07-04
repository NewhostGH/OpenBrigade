<?php

// project: OpenBrigade

namespace App\Services\Sms\Drivers;

use App\Contracts\SmsSender;
use App\Services\Sms\SmsMessage;
use App\Services\Sms\SmsResult;

/**
 * Discards every message. Use to hard-disable SMS without touching call sites.
 */
class NullSmsSender implements SmsSender
{
    public function send(SmsMessage $message): SmsResult
    {
        return SmsResult::ok($this->name());
    }

    public function name(): string
    {
        return 'null';
    }
}
