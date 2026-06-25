<?php

use App\Exceptions\UploadRejectedException;
use App\Http\Middleware\AuditRequests;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RequireAuthSetup;
use App\Http\Middleware\RequireCharterAcceptance;
use App\Http\Middleware\RequireFeature;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackPerformance;
use App\Http\Middleware\TrackSession;
use App\Support\Audit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

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

        // Resolve the active UI locale (session override -> app default)
        $middleware->appendToGroup('web', SetLocale::class);

        // Audit every state-changing request to the durable trail (ob_log_entry).
        $middleware->appendToGroup('web', AuditRequests::class);

        // Per-request performance tracking (slow-request logging). Runs last so
        // it times the full middleware + controller stack.
        $middleware->appendToGroup('web', TrackPerformance::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Report uncaught exceptions to Sentry/GlitchTip. Whether anything is
        // actually sent is gated by the obs_error_tracking setting + DSN, which
        // AppServiceProvider clears when disabled (so this is a safe no-op then).
        Integration::handles($exceptions);

        // Bucket every uncaught-exception log record into the `error` canal of
        // ob_log_entry (read from context by DatabaseLogHandler).
        $exceptions->context(fn () => ['ob_channel' => 'error']);

        // A rejected upload (forbidden type, MIME mismatch, malware hit) surfaces
        // as a normal validation error on the upload field, wherever it is thrown.
        // Every rejection is audited here — the single choke point for all reasons.
        $exceptions->render(function (UploadRejectedException $e) {
            Audit::security('upload.rejected', [
                'field' => $e->field(),
                'reason' => $e->getMessage(),
            ], 'warning');

            throw $e->toValidationException();
        });
    })->create();
