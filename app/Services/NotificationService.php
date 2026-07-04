<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Services;

use App\Mail\PlainMessage;
use App\Services\Sms\SmsManager;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Central notification dispatcher — the single entry point for outbound
 * email and SMS. Email is queued (see {@see PlainMessage}) so sends never
 * block the request; the queue worker delivers them. Richer, event-driven
 * messages should use Laravel Notifications (App\Notifications\*) which run
 * through the same mail transport and the provider-agnostic SMS channel.
 */
class NotificationService implements ServiceInterface
{
    public function __construct(private readonly SmsManager $sms) {}

    /**
     * Queue a plain-text email through the branded layout.
     *
     * Returns true when the message was handed to the mailer/queue, false when
     * email is disabled (mail_allowed = 0) or when queueing throws.
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $body,
        ?string $fromName = null,
        ?string $fromEmail = null,
    ): bool {
        if (! $this->isMailAllowed()) {
            return false;
        }

        try {
            Mail::to($to)->send(new PlainMessage($subject, $body, $fromName, $fromEmail));

            return true;
        } catch (\Throwable $e) {
            Log::warning('NotificationService: email delivery failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send an SMS through the configured, provider-agnostic gateway.
     * Returns a failed result when SMS is disabled (sms_allowed = 0).
     */
    public function sendSms(string $to, string $body, ?string $from = null): SmsResult
    {
        if (! $this->isSmsAllowed()) {
            return SmsResult::failed('disabled', 'SMS is disabled (sms_allowed = 0).');
        }

        return $this->sms->send($to, $body, $from);
    }

    public function isMailAllowed(): bool
    {
        return (bool) DB::table('configuration')->where('NAME', 'mail_allowed')->value('VALUE');
    }

    public function isSmsAllowed(): bool
    {
        // Absent setting → allow (the configured driver defaults to the send-safe
        // log driver). Set sms_allowed = 0 to hard-disable SMS.
        $value = DB::table('configuration')->where('NAME', 'sms_allowed')->value('VALUE');

        return $value === null || (bool) $value;
    }
}
