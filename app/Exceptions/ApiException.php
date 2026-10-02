<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * A refused /api/v1 call, rendered in the legacy eBrigade envelope:
 * {"status":"error","errnum":<code>,"message":"..."}.
 *
 * The numeric code is kept identical to the legacy api/ scripts so existing
 * consumers that branch on `errnum` keep working; the HTTP status is new.
 */
class ApiException extends RuntimeException
{
    public function __construct(public readonly int $errnum, string $message, public readonly int $status = 422)
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'errnum' => $this->errnum,
            'message' => $this->getMessage(),
        ], $this->status);
    }
}
