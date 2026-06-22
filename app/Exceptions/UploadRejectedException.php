<?php

namespace App\Exceptions;

use App\Services\UploadSecurityService;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Thrown by {@see UploadSecurityService} when an uploaded file
 * fails a safety check (forbidden type, MIME mismatch, malware hit, …).
 *
 * Carries the form field name so it can be converted into a normal validation
 * error, keeping the rejection reason visible to the user on the form they
 * submitted rather than surfacing as a 500.
 */
class UploadRejectedException extends RuntimeException
{
    public function __construct(string $message, private readonly string $field = 'file')
    {
        parent::__construct($message);
    }

    public function field(): string
    {
        return $this->field;
    }

    /** Convert to a Laravel ValidationException bound to the offending field. */
    public function toValidationException(): ValidationException
    {
        return ValidationException::withMessages([
            $this->field => [$this->getMessage()],
        ]);
    }
}
