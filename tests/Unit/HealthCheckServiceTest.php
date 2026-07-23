<?php

use App\Jobs\QueueHeartbeatJob;
use App\Services\HealthCheckService;
use App\Services\SecuritySettingService;
use Illuminate\Support\Facades\Queue;

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
        ->and($report['checks'])->toHaveKeys([
            'database', 'cache', 'storage', 'disk', 'clamav', 'redis', 'queue', 'mail',
        ]);
});

it('reports the database probe as ok against the test connection', function () {
    $report = healthService()->report();

    expect($report['checks']['database']['status'])->toBe('ok')
        ->and($report['checks']['clamav']['status'])->toBe('skipped');
});

it('skips redis, queue and mail probes on the sync/array test drivers', function () {
    $checks = healthService()->report()['checks'];

    // queue=sync, cache/session=array, mail=array → nothing rides on Redis,
    // no worker exists and there is no transport to probe.
    expect($checks['redis']['status'])->toBe('skipped')
        ->and($checks['queue']['status'])->toBe('skipped')
        ->and($checks['mail']['status'])->toBe('skipped')
        ->and($checks['mail']['transport'])->toBe('array');
});

it('reports the queue down when no worker heartbeat exists', function () {
    config(['queue.default' => 'redis']);
    Queue::fake();

    $queue = healthService()->report()['checks']['queue'];

    expect($queue['status'])->toBe('down')
        ->and($queue['worker'])->toBe('no heartbeat')
        ->and($queue['pending'])->toBe(0);
});

it('reports the queue down on a stale worker heartbeat', function () {
    config(['queue.default' => 'redis']);
    Queue::fake();
    Cache::put(QueueHeartbeatJob::CACHE_KEY, now()->subHour()->getTimestamp());

    $queue = healthService()->report()['checks']['queue'];

    expect($queue['status'])->toBe('down')
        ->and($queue['heartbeat_age_s'])->toBeGreaterThan(900);
});

it('reports the queue ok on a fresh worker heartbeat', function () {
    config(['queue.default' => 'redis']);
    Queue::fake();
    Cache::put(QueueHeartbeatJob::CACHE_KEY, now()->getTimestamp());

    $queue = healthService()->report()['checks']['queue'];

    expect($queue['status'])->toBe('ok')
        ->and($queue['heartbeat_age_s'])->toBeLessThan(300);
});

it('probes smtp through the handshake seam', function () {
    config(['mail.default' => 'smtp']);

    $security = new class extends SecuritySettingService
    {
        public function get(string $name): string
        {
            return (string) $this->default($name);
        }
    };

    $ok = new class($security) extends HealthCheckService
    {
        protected function smtpHandshake(string $host, int $port, float $timeout = 3.0): bool
        {
            return true;
        }
    };
    $dead = new class($security) extends HealthCheckService
    {
        protected function smtpHandshake(string $host, int $port, float $timeout = 3.0): bool
        {
            throw new RuntimeException('connection refused');
        }
    };

    expect($ok->report()['checks']['mail']['status'])->toBe('ok')
        ->and($dead->report()['checks']['mail'])
        ->toMatchArray(['status' => 'down', 'error' => 'connection refused']);
});

it('treats a non-down report as healthy', function () {
    $svc = healthService();
    $report = $svc->report();

    // The test environment has a working DB/cache/storage → never "down".
    expect($svc->isHealthy($report))->toBeTrue()
        ->and($report['status'])->not->toBe('down');
});
