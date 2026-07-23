<?php

namespace App\Support;

use App\Services\GeneralSettingService;

/**
 * Money formatting fed by the administrable currency settings (configuration
 * rows `default_money` / `default_money_symbol`). French-style separators to
 * match the rest of the UI; falls back to € when the settings are unreadable.
 */
class Money
{
    public static function format(int|float|string $amount, int $decimals = 2): string
    {
        return number_format((float) $amount, $decimals, ',', ' ').' '.self::symbol();
    }

    public static function symbol(): string
    {
        try {
            return app(GeneralSettingService::class)->currencySymbol();
        } catch (\Throwable) {
            return '€';
        }
    }
}
