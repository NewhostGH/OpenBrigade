<?php

namespace App\Rules;

use App\Services\GeneralSettingService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Phone-number validation driven by the administrable settings: the default
 * international prefix (`phone_prefix`, ID 100) and the minimum number of
 * digits (`min_numbers_in_phone`, ID 101). Ports the legacy checkPhone()
 * behaviour: strip formatting, drop the country prefix, count digits.
 */
class Phone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return; // presence is the caller's concern (nullable fields)
        }

        // Resolve lazily so constructing the rule never touches the container.
        try {
            $settings = app(GeneralSettingService::class);
            $prefix = $settings->phonePrefix();
            $min = $settings->phoneMinDigits();
        } catch (\Throwable) {
            $prefix = '+33';
            $min = 10;
        }

        $digits = (string) preg_replace('/\D/', '', $value);
        $prefixDigits = (string) preg_replace('/\D/', '', $prefix);

        // A number given with the international prefix still needs the national
        // length after it: +33 6 12 34 56 78 → count the 9 national digits as
        // if the leading 0 were there.
        if ($prefixDigits !== '' && str_starts_with($digits, $prefixDigits)) {
            $digits = '0'.substr($digits, strlen($prefixDigits));
        }

        if (strlen($digits) < $min) {
            $fail('validation.custom.phone.min_digits')->translate(['min' => $min]);
        }
    }
}
