<?php

use App\Services\GeneralSettingService;
use App\Services\HealthCheckService;
use App\Services\ReleaseVerificationService;
use App\Services\SecuritySettingService;
use Illuminate\Support\Facades\Http;

/**
 * Build a service with the migration and manifest seams overridden, so the
 * suite needs neither a migrated database nor a built front-end (mirrors the
 * smtpHandshake seam in HealthCheckServiceTest).
 */
function releaseService(int $pending = 0, ?string $manifest = null): ReleaseVerificationService
{
    // ClamAV disabled → that infra probe is skipped (no socket needed).
    $security = new class extends SecuritySettingService
    {
        public function get(string $name): string
        {
            return (string) $this->default($name);
        }
    };

    return new class(new HealthCheckService($security), app(GeneralSettingService::class), $pending, $manifest) extends ReleaseVerificationService
    {
        public function __construct(
            HealthCheckService $health,
            GeneralSettingService $settings,
            private int $pending,
            private ?string $manifest,
        ) {
            parent::__construct($health, $settings);
        }

        protected function pendingMigrations(): int
        {
            return $this->pending;
        }

        protected function manifestPath(): ?string
        {
            return $this->manifest;
        }
    };
}

it('returns a structured report with every check', function () {
    $report = releaseService()->verify();

    expect($report)->toHaveKeys(['status', 'version', 'timestamp', 'checks'])
        ->and($report['checks'])->toHaveKeys([
            'infrastructure', 'migrations', 'assets', 'configuration', 'version', 'routes',
        ]);
});

it('passes migrations when nothing is pending and fails when some are', function () {
    expect(releaseService(pending: 0)->verify()['checks']['migrations'])
        ->toMatchArray(['status' => 'ok', 'pending' => 0]);

    $report = releaseService(pending: 3)->verify();
    expect($report['checks']['migrations'])->toMatchArray(['status' => 'down', 'pending' => 3])
        ->and($report['status'])->toBe('down');
});

it('detects a built asset manifest and flags a missing one', function () {
    $tmp = (string) tempnam(sys_get_temp_dir(), 'ob_manifest');
    file_put_contents($tmp, '{}');

    expect(releaseService(manifest: $tmp)->verify()['checks']['assets']['status'])->toBe('ok');

    unlink($tmp);
    expect(releaseService(manifest: $tmp)->verify()['checks']['assets']['status'])->toBe('down');
});

it('verifies the critical named routes are registered', function () {
    $routes = releaseService()->verify()['checks']['routes'];

    expect($routes)->toMatchArray(['status' => 'ok', 'verified' => 3]);
});

it('asserts the deployed version against the expected one', function () {
    config(['brigade.version' => '6.0.0']);

    config(['release.expected_version' => '6.0.0']);
    expect(releaseService()->verify()['checks']['version']['status'])->toBe('ok');

    config(['release.expected_version' => '6.1.0']);
    expect(releaseService()->verify()['checks']['version'])
        ->toMatchArray(['status' => 'down', 'installed' => '6.0.0', 'expected' => '6.1.0']);
});

it('flags debug mode enabled in production as a hard failure', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['app.env' => 'production', 'app.debug' => true, 'app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);

    $config = releaseService()->verify()['checks']['configuration'];

    expect($config['status'])->toBe('down')
        ->and($config['issues'])->toContain('APP_DEBUG enabled in production');
});

it('applies strict mode only to degraded reports', function () {
    $svc = releaseService();

    expect($svc->passes(['status' => 'ok'], strict: true))->toBeTrue()
        ->and($svc->passes(['status' => 'degraded'], strict: false))->toBeTrue()
        ->and($svc->passes(['status' => 'degraded'], strict: true))->toBeFalse()
        ->and($svc->passes(['status' => 'down'], strict: false))->toBeFalse();
});

it('fires the monitoring webhook when configured', function () {
    config(['release.webhook' => 'https://deploy.example.test/hook']);
    Http::fake();

    $sent = releaseService()->notify([
        'status' => 'ok', 'version' => 'OpenBrigade 6.0.0', 'timestamp' => 't',
        'checks' => ['migrations' => ['status' => 'ok', 'pending' => 0]],
    ]);

    expect($sent)->toBeTrue();
    Http::assertSent(fn ($request) => $request->url() === 'https://deploy.example.test/hook'
        && $request['event'] === 'release.verified'
        && $request['status'] === 'ok'
        && $request['checks'] === ['migrations' => 'ok']);
});

it('does nothing when no webhook is configured', function () {
    config(['release.webhook' => '']);
    Http::fake();

    expect(releaseService()->notify(['status' => 'ok', 'version' => 'x', 'timestamp' => 't', 'checks' => []]))
        ->toBeFalse();
    Http::assertNothingSent();
});
