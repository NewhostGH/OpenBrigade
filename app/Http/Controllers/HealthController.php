<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

/**
 * Public health-check endpoint for uptime probes and load balancers.
 *
 * GET /health → JSON report with a 200 (healthy/degraded) or 503 (down) status
 * code. No authentication: it exposes only service availability, never data.
 */
class HealthController extends Controller
{
    public function __invoke(HealthCheckService $health): JsonResponse
    {
        $report = $health->report();

        return response()->json($report, $health->isHealthy($report) ? 200 : 503);
    }
}
