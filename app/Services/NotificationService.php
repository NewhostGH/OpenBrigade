<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Central notification dispatcher.
 *
 * Currently implements the email channel only; additional channels (SMS, push,
 * in-app) will be added in the COMM phase.
 *
 * // TODO: COMM — extend to SMS, push, and in-app channels.
 */
class NotificationService implements ServiceInterface
{
    /**
     * Send a plain-text email.
     *
     * Returns true when the message was handed off to the mailer, false when
     * email is disabled (mail_allowed = 0) or when the mailer throws.
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
            Mail::raw($body, function ($msg) use ($to, $subject, $fromName, $fromEmail) {
                $msg->to($to)->subject($subject);
                if ($fromName !== null || $fromEmail !== null) {
                    $msg->from(
                        $fromEmail ?? config('mail.from.address'),
                        $fromName ?? config('mail.from.name'),
                    );
                }
            });

            return true;
        } catch (\Throwable $e) {
            Log::warning('NotificationService: email delivery failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function isMailAllowed(): bool
    {
        return (bool) DB::table('configuration')->where('NAME', 'mail_allowed')->value('VALUE');
    }
}
