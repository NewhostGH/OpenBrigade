<?php

// project: OpenBrigade

namespace App\Services\Sms;

/**
 * Outcome of an SMS send attempt, returned by every driver so callers get a
 * uniform result regardless of provider.
 */
class SmsResult
{
    public function __construct(
        public bool $success,
        public string $driver,
        public ?string $reference = null,
        public ?string $error = null,
    ) {}

    public static function ok(string $driver, ?string $reference = null): self
    {
        return new self(true, $driver, $reference);
    }

    public static function failed(string $driver, string $error): self
    {
        return new self(false, $driver, null, $error);
    }
}
