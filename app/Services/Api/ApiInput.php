<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use DateTimeImmutable;

/**
 * Field-by-field reader for the /api/v1 import payloads.
 *
 * Mirrors the legacy api/import checks (same order, same errnum codes) so a
 * client written against eBrigade gets the same answer for the same mistake.
 * Every failure throws an {@see ApiException} with HTTP 422.
 */
class ApiInput
{
    /** @param array<string,mixed> $data */
    public function __construct(private array $data) {}

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data) && $this->data[$key] !== null;
    }

    public function raw(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /** Trimmed string, '' when absent; $missing = errnum to throw when required. */
    public function string(string $key, ?int $missing = null): string
    {
        if (! $this->has($key)) {
            $this->failIf($missing !== null, (int) $missing, "Missing {$key}");

            return '';
        }

        $value = $this->raw($key);
        if (! is_scalar($value)) {
            throw new ApiException((int) ($missing ?? 20), "Invalid {$key}");
        }

        return trim(str_replace('"', '', (string) $value));
    }

    /** Legacy validateString(): letters (accents allowed), - ' . @ ), space; optional digits. */
    public function name(string $key, int $max, int $invalid, ?int $missing = null, bool $digits = false): string
    {
        $value = $this->string($key, $missing);
        $pattern = $digits ? "/^[\\p{L}\\p{N}\\- '.@)]*$/u" : "/^[\\p{L}\\- '.@)]*$/u";

        $this->failIf(mb_strlen($value) > $max || ! preg_match($pattern, $value), $invalid, "Invalid {$key} (maximum {$max} characters)");

        return $value;
    }

    public function int(string $key, ?int $missing = null, int $default = 0): int
    {
        if (! $this->has($key)) {
            $this->failIf($missing !== null, (int) $missing, "Missing {$key}");

            return $default;
        }

        return (int) $this->raw($key);
    }

    /** Date in $format (legacy validateDate), returned unchanged. */
    public function date(string $key, string $format, int $invalid, ?int $missing = null): string
    {
        $value = $this->string($key, $missing);
        if ($value !== '') {
            self::assertDate($value, $format, $invalid, $key);
        }

        return $value;
    }

    public function email(string $key, int $invalid): string
    {
        $value = $this->string($key);
        $this->failIf($value !== '' && (mb_strlen($value) > 60 || ! filter_var($value, FILTER_VALIDATE_EMAIL)), $invalid, "{$key} is invalid {$value}");

        return $value;
    }

    /** Legacy phone check: digits with optional + . - and spaces, at most 20 characters. */
    public function phone(string $key, int $invalid): string
    {
        $value = $this->string($key);
        $this->failIf($value !== '' && (mb_strlen($value) > 20 || ! preg_match('/^\+?\d[\d .\-]*$/', $value)), $invalid, "invalid {$key} {$value}");

        return $value;
    }

    public static function assertDate(string $value, string $format, int $errnum, string $label): void
    {
        $parsed = DateTimeImmutable::createFromFormat('!'.$format, $value);

        if (! $parsed || $parsed->format($format) !== $value) {
            throw new ApiException($errnum, "{$label} is invalid {$value} expected format is '{$format}'");
        }
    }

    public function failIf(bool $condition, int $errnum, string $message, int $status = 422): void
    {
        if ($condition) {
            throw new ApiException($errnum, $message, $status);
        }
    }
}
