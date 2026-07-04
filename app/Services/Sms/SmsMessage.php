<?php

// project: OpenBrigade

namespace App\Services\Sms;

use App\Contracts\SmsSender;

/**
 * Provider-agnostic outbound SMS. Built by callers (or a notification's
 * toSms()) and handed to an {@see SmsSender} driver.
 */
class SmsMessage
{
    public function __construct(
        public string $to,
        public string $body,
        public ?string $from = null,
    ) {}

    public function to(string $to): static
    {
        $this->to = $to;

        return $this;
    }

    public function body(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function from(?string $from): static
    {
        $this->from = $from;

        return $this;
    }
}
