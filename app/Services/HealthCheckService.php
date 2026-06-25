<?php

namespace App\Services;

use App\Support\ClamavScanner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
