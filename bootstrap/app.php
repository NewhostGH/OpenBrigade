<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RequireAuthSetup;
use App\Http\Middleware\RequireCharterAcceptance;
use App\Http\Middleware\RequireFeature;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Keep auth redirects relative to avoid host/port/path-info drift under proxies and /index.php/* URLs.
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');

        // Trust proxy headers (reverse proxies)
        $middleware->trustProxies(at: '*');

        // Route-level permission guard: Route::middleware('permission:52')
        // Route-level disabled-account guard (replaces legacy GP_ID==-1 inline checks)
        $middleware->alias([
            'permission' => RequirePermission::class,
            'feature' => RequireFeature::class,
            'user.active' => EnsureUserIsActive::class,
        ]);

        // Automatically applied to all auth-guarded web routes
        $middleware->appendToGroup('web', EnsureUserIsActive::class);
        $middleware->appendToGroup('web', RequireCharterAcceptance::class);
        $middleware->appendToGroup('web', RequireAuthSetup::class);
        $middleware->appendToGroup('web', TrackSession::class);

        // Security headers on every web response
        $middleware->prependToGroup('web', SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling configuration
    })->create();
