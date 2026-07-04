<?php

// project: OpenBrigade

namespace App\Notifications\Channels;

use App\Services\Sms\SmsManager;
use App\Services\Sms\SmsMessage;
use Illuminate\Notifications\Notification;

/**
 * Laravel notification channel for SMS. A notification opts in by declaring
 * "sms" in via() and implementing toSms($notifiable), returning a string or an
 * {@see SmsMessage}. The recipient number comes from the notifiable's
 * routeNotificationFor('sms').
 */
class SmsChannel
{
    public function __construct(private readonly SmsManager $sms) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $to = $notifiable->routeNotificationFor('sms', $notification);

        if (empty($to)) {
            return;
        }

        /** @var SmsMessage|string $content */
        $content = $notification->toSms($notifiable);

        $message = $content instanceof SmsMessage
            ? $content
            : new SmsMessage((string) $to, (string) $content);

        // Fall back to the notifiable's route when the message left it blank.
        if ($message->to === '') {
            $message->to = (string) $to;
        }

        $this->sms->sendMessage($message);
    }
}
