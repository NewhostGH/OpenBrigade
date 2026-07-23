<?php

use App\Services\GeneralSettingService;

// Without a database the service must fall back to its typed defaults —
// several consumers run at boot (timezone) or on every request (maintenance
// mode), so this path is the safety net.

test('falls back to defaults when the configuration table is unreachable', function () {
    // The sqlite :memory: test database has no `configuration` table, so the
    // read throws and the service must serve its defaults.
    $s = new GeneralSettingService;

    expect($s->appVersion())->toBe('')
        ->and($s->timezone())->toBe('Europe/Paris')
        ->and($s->currencyName())->toBe('Euro')
        ->and($s->currencySymbol())->toBe('€')
        ->and($s->phonePrefix())->toBe('+33')
        ->and($s->phoneMinDigits())->toBe(10)
        ->and($s->photoRequired())->toBeFalse()
        ->and($s->maintenanceEnabled())->toBeFalse()
        ->and($s->maintenanceText())->toBe('')
        ->and($s->telemetryEnabled())->toBeFalse()
        ->and($s->autoOptimizeEnabled())->toBeFalse();
});

test('unknown keys resolve to an empty string', function () {
    $s = new GeneralSettingService;

    expect($s->get('nonexistent_setting'))->toBe('');
});

test('typed accessors coerce stored string values', function () {
    $s = new class extends GeneralSettingService
    {
        public function get(string $name): string
        {
            return match ($name) {
                'maintenance_mode' => '1',
                'min_numbers_in_phone' => '9',
                'timezone' => 'Europe/Zurich',
                default => '0',
            };
        }
    };

    expect($s->maintenanceEnabled())->toBeTrue()
        ->and($s->phoneMinDigits())->toBe(9)
        ->and($s->timezone())->toBe('Europe/Zurich')
        ->and($s->photoRequired())->toBeFalse();
});
