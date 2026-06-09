<?php

namespace App\Http\Middleware;

use App\Services\FeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a route behind a feature/module flag (the `ob_feature` registry).
 *
 * Usage in routes:
 *   Route::get(...)->middleware('feature:vehicules');
 *
 * A disabled feature responds 404 so the screen behaves as if it does not
 * exist. Unknown keys are treated as enabled (see FeatureService::isEnabled).
 */
class RequireFeature
{
    public function __construct(private readonly FeatureService $features) {}

    public function handle(Request $request, Closure $next, string $key): Response
    {
        abort_unless($this->features->isEnabled($key), 404);

        return $next($request);
    }
}
