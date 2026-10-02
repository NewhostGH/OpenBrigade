<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Services\Api\ApiSettings;
use App\Support\Audit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared-secret guard for the /api/v1 import and export endpoints.
 *
 * Usage: ->middleware('api.token:export') or ('api.token:import'). The token
 * is read from `Authorization: Bearer <token>` first, then from a top-level
 * `token` field in the JSON body (legacy eBrigade clients send it there).
 */
class RequireApiToken
{
    public function __construct(private ApiSettings $settings) {}

    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $expected = $scope === 'import' ? $this->settings->importToken() : $this->settings->exportToken();

        if ($expected === '') {
            throw new ApiException(10, ucfirst($scope).' API not activated', 503);
        }

        // Decoded whatever the Content-Type: legacy clients often post raw JSON
        // with curl's default form header.
        if ($request->json()->all() === []) {
            throw new ApiException(20, 'Incorrect json input', 400);
        }

        $token = (string) ($request->bearerToken() ?? $request->json('token', ''));

        if ($token === '') {
            throw new ApiException(30, 'token not provided', 401);
        }

        if (! hash_equals($expected, $token)) {
            Audit::security('api.token_rejected', ['scope' => $scope]);

            throw new ApiException(40, 'Wrong token, access to this webservice forbidden', 403);
        }

        return $next($request);
    }
}
