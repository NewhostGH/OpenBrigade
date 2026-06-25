<?php

use App\Exceptions\UploadRejectedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Sentry / GlitchTip configuration.
 *
 * GlitchTip is Sentry-API compatible, so the official sentry/sentry-laravel SDK
 * reports to it unchanged — point SENTRY_LARAVEL_DSN at the GlitchTip project
 * DSN (see docker-compose.yml and .env.example).
 *
 * Reporting is gated at runtime by the `obs_error_tracking` setting
 * (Administration ▸ Journal d'activité ▸ Paramètres): when it is off, or no DSN
 * is set, App\Providers\AppServiceProvider clears the DSN so nothing is sent.
 */
return [

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    // Where the events come from, shown in the GlitchTip/Sentry UI.
    'release' => env('SENTRY_RELEASE', env('APP_VERSION')),

    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),

    // Capture the authenticated user's id/ip — never the request body by default.
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    // Performance tracing. Off by default (0.0); raise the sample rate to profile.
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE') === null
        ? null
        : (float) env('SENTRY_TRACES_SAMPLE_RATE'),

    // Don't report the framework noise that is handled elsewhere / not actionable.
    'ignore_exceptions' => [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ValidationException::class,
        UploadRejectedException::class,
    ],

    'breadcrumbs' => [
        'logs' => true,
        'cache' => true,
        'sql_queries' => true,
        'sql_bindings' => false,
    ],

    'tracing' => [
        'queue_job_transactions' => env('SENTRY_TRACE_QUEUE_ENABLED', false),
        'sql_queries' => true,
        'views' => true,
        'default_integrations' => true,
    ],
];
