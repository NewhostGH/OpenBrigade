<?php

namespace App\Http\Middleware;

use App\Support\Audit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records every state-changing request (POST/PUT/PATCH/DELETE) to the audit
 * trail, in one place — so the whole backend is instrumented without touching
 * each controller. Read requests (GET/HEAD/OPTIONS) are ignored to keep the
 * trail signal-dense.
 *
 * The acting pompier, IP and URL are attached automatically by the logging
 * processor; here we add the route name, HTTP method and response status. The
 * request body is never logged (it may contain passwords / personal data).
 */
class AuditRequests
{
    /** Methods that mutate state and are therefore worth auditing. */
    private const AUDITED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->getMethod(), self::AUDITED_METHODS, true)) {
            return $response;
        }

        $route = $request->route();

        Audit::action('request', [
            'route' => $route?->getName() ?? $request->path(),
            'http_method' => $request->getMethod(),
            'status' => $response->getStatusCode(),
        ], $response->getStatusCode() >= 400 ? 'warning' : 'info');

        return $response;
    }
}
