<?php

use App\Services\GeneralSettingService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

function telemetrySetting(bool $on): void
{
    $general = Mockery::mock(GeneralSettingService::class)->makePartial();
    $general->shouldReceive('get')->andReturnUsing(
        fn (string $name) => $name === 'ameliorations' ? ($on ? '1' : '0') : '0'
    );
    app()->instance(GeneralSettingService::class, $general);
}

test('sends nothing when the opt-in is off', function () {
    Http::fake();
    telemetrySetting(false);

    $this->artisan('ob:telemetry:ping')->assertExitCode(0);

    Http::assertNothingSent();
});

test('sends a strictly anonymous payload when opted in', function () {
    Http::fake(['telemetry.openbrigade.fr/*' => Http::response(['ok' => true])]);
    telemetrySetting(true);

    $this->artisan('ob:telemetry:ping')->assertExitCode(0);

    Http::assertSent(function ($request) {
        $data = $request->data();

        // Exactly the documented keys — nothing else may ever leave.
        expect(array_keys($data))->toBe([
            'instance', 'app_version', 'php_version', 'laravel_version', 'org_type', 'members_rounded',
        ])
            ->and($data['instance'])->toHaveLength(16)
            ->and($data['instance'])->not->toBe((string) config('app.key'))
            ->and($data['members_rounded'] % 10)->toBe(0);

        return str_starts_with($request->url(), 'https://telemetry.openbrigade.fr');
    });
});

test('an unreachable endpoint never surfaces as an error', function () {
    Http::fake(fn () => throw new ConnectionException('unreachable'));
    telemetrySetting(true);

    $this->artisan('ob:telemetry:ping')->assertExitCode(0);
});
