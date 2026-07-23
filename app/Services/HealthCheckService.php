<?php

namespace App\Services;

use App\Jobs\QueueHeartbeatJob;
use App\Models\ObLogEntry;
use App\Support\ClamavScanner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Aggregates the liveness/readiness probes surfaced by the `/health` endpoint
 * and the Journal d'activité ▸ Santé panel.
 *
 * Each probe is isolated: a thrown probe degrades only its own entry, never the
 * whole report. The overall status is the worst individual status
 * (ok < degraded < down); `skipped` probes don't affect it.
 */
class HealthCheckService
{
    public function __construct(
        private readonly SecuritySettingService $security,
    ) {}

    /**
     * Run every probe and return the full report.
     *
     * @return array{status:string,version:string,timestamp:string,checks:array<string,array<string,mixed>>}
     */
    public function report(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'disk' => $this->checkDisk(),
            'clamav' => $this->checkClamav(),
            'redis' => $this->checkRedis(),
            'queue' => $this->checkQueue(),
            'mail' => $this->checkMail(),
        ];

        return [
            'status' => $this->overall($checks),
            'version' => $this->version(),
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ];
    }

    /** True when nothing is `down` — used to pick the HTTP status code. */
    public function isHealthy(array $report): bool
    {
        return $report['status'] !== 'down';
    }

    /** @return array<string,mixed> */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('select 1');
            $ms = (int) round((microtime(true) - $start) * 1000);

            return ['status' => 'ok', 'latency_ms' => $ms];
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /** @return array<string,mixed> */
    private function checkCache(): array
    {
        try {
            Cache::put('ob_health_probe', '1', 5);
            $ok = Cache::get('ob_health_probe') === '1';

            return ['status' => $ok ? 'ok' : 'degraded'];
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /** @return array<string,mixed> */
    private function checkStorage(): array
    {
        try {
            $writable = is_writable(storage_path('logs'))
                && Storage::disk('local')->put('ob_health_probe.txt', 'ok');
            Storage::disk('local')->delete('ob_health_probe.txt');

            return ['status' => $writable ? 'ok' : 'down', 'writable' => (bool) $writable];
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /** @return array<string,mixed> */
    private function checkDisk(): array
    {
        try {
            $free = @disk_free_space(base_path());
            $total = @disk_total_space(base_path());
            if (! $free || ! $total) {
                return ['status' => 'skipped', 'reason' => 'unavailable'];
            }

            $freePct = (int) round($free / $total * 100);
            // Under 10% free is a real operational risk; under 20% is a warning.
            $status = $freePct < 10 ? 'down' : ($freePct < 20 ? 'degraded' : 'ok');

            return ['status' => $status, 'free_pct' => $freePct];
        } catch (Throwable $e) {
            return ['status' => 'skipped', 'reason' => $e->getMessage()];
        }
    }

    /** @return array<string,mixed> */
    private function checkClamav(): array
    {
        try {
            if (! $this->security->bool('sec_upload_scan_enabled')) {
                return ['status' => 'skipped', 'reason' => 'scan disabled'];
            }

            $scanner = new ClamavScanner(
                $this->security->string('sec_clamav_host'),
                $this->security->int('sec_clamav_port'),
            );

            return ['status' => $scanner->ping() ? 'ok' : 'down'];
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /**
     * Redis reachability + latency. Only meaningful when some subsystem
     * actually rides on Redis (queue, cache or sessions) — otherwise skipped.
     *
     * @return array<string,mixed>
     */
    protected function checkRedis(): array
    {
        $drivers = [
            config('queue.default'),
            config('cache.default'),
            config('session.driver'),
        ];
        if (! in_array('redis', $drivers, true)) {
            return ['status' => 'skipped', 'reason' => 'not in use'];
        }

        try {
            $start = microtime(true);
            Redis::connection()->ping();
            $ms = (int) round((microtime(true) - $start) * 1000);

            return ['status' => $ms > 100 ? 'degraded' : 'ok', 'latency_ms' => $ms];
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /**
     * Queue depth, failed jobs and worker liveness. The scheduler dispatches a
     * QueueHeartbeatJob every 5 minutes; a worker stamps a cache key on pickup.
     * A stale/missing stamp therefore flags a broken scheduler → queue → worker
     * chain, which is exactly the silent failure operators need surfaced.
     *
     * @return array<string,mixed>
     */
    protected function checkQueue(): array
    {
        $driver = (string) config('queue.default');
        if ($driver === 'sync') {
            return ['status' => 'skipped', 'reason' => 'sync driver'];
        }

        $check = ['status' => 'ok'];

        try {
            $check['pending'] = $driver === 'database'
                ? (int) DB::table('jobs')->count()
                : (int) Queue::size();
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }

        try {
            $check['failed_24h'] = (int) DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count();
            if ($check['failed_24h'] > 0) {
                $check['status'] = 'degraded';
            }
        } catch (Throwable) {
            // failed_jobs not migrated yet — depth and liveness still stand.
        }

        // Worker liveness via the heartbeat stamp. >15 min without a beat on a
        // 5-minute cadence = at least two missed cycles → the worker is gone.
        try {
            $beat = Cache::get(QueueHeartbeatJob::CACHE_KEY);
            $age = $beat !== null ? now()->getTimestamp() - (int) $beat : null;
            if ($age === null) {
                $check['status'] = 'down';
                $check['worker'] = 'no heartbeat';
            } elseif ($age > 900) {
                $check['status'] = 'down';
                $check['heartbeat_age_s'] = $age;
            } else {
                if ($age > 300 && $check['status'] === 'ok') {
                    $check['status'] = 'degraded';
                }
                $check['heartbeat_age_s'] = $age;
            }
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }

        return $check;
    }

    /**
     * Mail transport reachability + recent delivery failures. The send-safe
     * log/array mailers have nothing to probe; smtp gets a real EHLO handshake.
     *
     * @return array<string,mixed>
     */
    protected function checkMail(): array
    {
        $mailer = (string) config('mail.default');
        $transport = (string) config("mail.mailers.{$mailer}.transport", $mailer);

        if ($transport !== 'smtp') {
            $check = ['status' => 'skipped', 'transport' => $mailer];
        } else {
            try {
                $ok = $this->smtpHandshake(
                    (string) config("mail.mailers.{$mailer}.host"),
                    (int) config("mail.mailers.{$mailer}.port"),
                );
                $check = ['status' => $ok ? 'ok' : 'down', 'transport' => 'smtp'];
            } catch (Throwable $e) {
                $check = ['status' => 'down', 'transport' => 'smtp', 'error' => $e->getMessage()];
            }
        }

        // Delivery failures logged by NotificationService over the last 24 h.
        try {
            $failures = (int) ObLogEntry::query()
                ->where('message', 'NotificationService: email delivery failed')
                ->where('created_at', '>=', now()->subDay())
                ->count();
            $check['failures_24h'] = $failures;
            if ($failures > 0 && $check['status'] === 'ok') {
                $check['status'] = 'degraded';
            }
        } catch (Throwable) {
            // ob_log_entry not migrated yet — transport status still stands.
        }

        return $check;
    }

    /**
     * Minimal SMTP dialogue: banner + EHLO. Overridable seam so tests never
     * open real sockets.
     */
    protected function smtpHandshake(string $host, int $port, float $timeout = 3.0): bool
    {
        $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $error, $timeout);
        if ($socket === false) {
            throw new \RuntimeException($error !== '' ? $error : "connect failed ({$errno})");
        }

        try {
            stream_set_timeout($socket, (int) $timeout);
            $banner = (string) fgets($socket, 512);
            if (! str_starts_with($banner, '220')) {
                return false;
            }

            fwrite($socket, "EHLO openbrigade.health\r\n");
            $reply = (string) fgets($socket, 512);
            fwrite($socket, "QUIT\r\n");

            return str_starts_with($reply, '250');
        } finally {
            fclose($socket);
        }
    }

    private function version(): string
    {
        $name = config('app.name', 'OpenBrigade');
        $version = config('app.version');

        return $version ? "{$name} {$version}" : $name;
    }

    /** @param  array<string,array<string,mixed>>  $checks */
    private function overall(array $checks): string
    {
        $rank = ['ok' => 0, 'skipped' => 0, 'degraded' => 1, 'down' => 2];
        $worst = 0;
        foreach ($checks as $check) {
            $worst = max($worst, $rank[$check['status']] ?? 0);
        }

        return ['ok', 'degraded', 'down'][$worst];
    }
}
