<?php

use App\Services\GeneralSettingService;
use App\Support\Money;

test('formats amounts with french separators and the configured symbol', function () {
    // TestCase's default GeneralSettingService mock serves the € symbol.
    expect(Money::format(1234.5))->toBe('1 234,50 €')
        ->and(Money::format(0))->toBe('0,00 €')
        ->and(Money::format('12.345', 1))->toBe('12,3 €');
});

test('uses the administrable currency symbol', function () {
    $svc = Mockery::mock(GeneralSettingService::class)->makePartial();
    $svc->shouldReceive('get')->with('default_money_symbol')->andReturn('CHF');
    app()->instance(GeneralSettingService::class, $svc);

    expect(Money::format(10))->toBe('10,00 CHF')
        ->and(Money::symbol())->toBe('CHF');
});
