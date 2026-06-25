<?php

namespace App\Http\Middleware;

use App\Services\LoggingSettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Records per-request duration and peak memory, and logs requests slower than
 * the configured threshold to the `performance` channel (ob_log_entry).
 *
 * Driven by the observability settings (obs_perf_enabled / obs_perf_slow_ms).
 * Fully guarded so monitoring can never affect the response itself.
 */
class TrackPerformance
{
    public function __construct(
        private readonly LoggingSettingService $settings,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        try {
            if (! $this->settings->bool('obs_perf_enabled')) {
                return $response;
            }

            $durationMs = (int) round((microtime(true) - $start) * 1000);
            $threshold = max(1, $this->settings->int('obs_perf_slow_ms'));

            if ($durationMs >= $threshold) {
                $memoryMb = (int) round(memory_get_peak_usage(true) / 1048576);

                logger()->channel('database')->warning('Slow request', [
                    'ob_channel' => 'performance',
                    'duration_ms' => $durationMs,
                    'memory_mb' => $memoryMb,
                    'status' => $response->getStatusCode(),
                    'route' => optional($request->route())->getName(),
                ]);
            }
        } catch (Throwable) {
            // Never let performance tracking interfere with the response.
        }

        return $response;
    }
}
