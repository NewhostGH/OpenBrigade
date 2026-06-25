<?php

use App\Services\HealthCheckService;
use App\Services\SecuritySettingService;

function healthService(): HealthCheckService
{
    // ClamAV scan disabled → that probe is skipped (no socket needed).
    $security = new class extends SecuritySettingService
    {
        public function get(string $name): string
        {
            return (string) $this->default($name);
        }
    };

    return new HealthCheckService($security);
}

it('returns a structured report with every probe', function () {
    $report = healthService()->report();

    expect($report)->toHaveKeys(['status', 'version', 'timestamp', 'checks'])
        ->and($report['checks'])->toHaveKeys(['database', 'cache', 'storage', 'disk', 'clamav']);
});

it('reports the database probe as ok against the test connection', function () {
    $report = healthService()->report();

    expect($report['checks']['database']['status'])->toBe('ok')
        ->and($report['checks']['clamav']['status'])->toBe('skipped');
});

it('treats a non-down report as healthy', function () {
    $svc = healthService();
    $report = $svc->report();

    // The test environment has a working DB/cache/storage → never "down".
    expect($svc->isHealthy($report))->toBeTrue()
        ->and($report['status'])->not->toBe('down');
});
