<?php

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
        // Trust proxy headers (reverse proxies)
        $middleware->trustProxies(at: '*');

        // Route-level permission guard: Route::middleware('permission:52')
        // Route-level disabled-account guard (replaces legacy GP_ID==-1 inline checks)
        $middleware->alias([
            'permission'  => \App\Http\Middleware\RequirePermission::class,
            'user.active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Automatically applied to all auth-guarded web routes
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureUserIsActive::class);

        // Security headers on every web response
        $middleware->prependToGroup('web', \App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling configuration
    })->create();
