<?php

// project: OpenBrigade

namespace App\Contracts;

use App\Services\Sms\SmsManager;
use App\Services\Sms\SmsMessage;
use App\Services\Sms\SmsResult;

/**
 * A single SMS provider. Implementations live under App\Services\Sms\Drivers
 * and are resolved by {@see SmsManager} from config('sms').
 * Adding a new provider means adding one driver — no caller changes.
 */
interface SmsSender
{
    public function send(SmsMessage $message): SmsResult;

    /** Short driver key, used in logs and results (e.g. "smsgatewayme"). */
    public function name(): string;
}
