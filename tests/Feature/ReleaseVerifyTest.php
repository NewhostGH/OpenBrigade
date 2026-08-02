<?php

use App\Services\ReleaseVerificationService;
use Mockery\MockInterface;

/**
 * The command's job is to map the report status to an exit code and orchestrate
 * the webhook; the checks themselves are covered by the service unit test. So
 * these tests bind a mock service returning a controlled report.
 */
function fakeVerifier(string $status, bool $webhook = false): MockInterface
{
    $report = [
        'status' => $status,
        'version' => 'OpenBrigade 6.0.0',
        'timestamp' => '2026-01-01T00:00:00+00:00',
        'checks' => ['migrations' => ['status' => $status, 'pending' => 0]],
    ];

    $mock = Mockery::mock(ReleaseVerificationService::class);
    $mock->shouldReceive('verify')->andReturn($report);
    $mock->shouldReceive('passes')->andReturnUsing(
        fn (array $r, bool $strict = false): bool => $r['status'] !== 'down' && ! ($strict && $r['status'] === 'degraded')
    );
    $mock->shouldReceive('notify')->andReturn($webhook)->byDefault();
    app()->instance(ReleaseVerificationService::class, $mock);

    return $mock;
}

test('exits 0 when the release verifies clean', function () {
    fakeVerifier('ok');

    $this->artisan('ob:release:verify')->assertExitCode(0);
});

test('exits non-zero when a check is down', function () {
    fakeVerifier('down');

    $this->artisan('ob:release:verify')->assertExitCode(1);
});

test('degraded passes by default but fails under --strict', function () {
    fakeVerifier('degraded');
    $this->artisan('ob:release:verify')->assertExitCode(0);

    fakeVerifier('degraded');
    $this->artisan('ob:release:verify --strict')->assertExitCode(1);
});

test('emits the raw report under --json', function () {
    fakeVerifier('ok');

    $this->artisan('ob:release:verify --json')
        ->expectsOutputToContain('"status": "ok"')
        ->assertExitCode(0);
});

test('fires the webhook by default and skips it under --no-webhook', function () {
    $mock = fakeVerifier('ok', webhook: true);
    $mock->shouldReceive('notify')->once()->andReturn(true);
    $this->artisan('ob:release:verify')->assertExitCode(0);

    $skip = fakeVerifier('ok');
    $skip->shouldReceive('notify')->never();
    $this->artisan('ob:release:verify --no-webhook')->assertExitCode(0);
});
