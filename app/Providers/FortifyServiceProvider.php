<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // We only use Fortify for TOTP / two-factor infrastructure.
        // All other features (login, registration, passwords) are handled by the
        // app's own controllers and are intentionally not wired through Fortify.

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('_totp_user_id'));
        });
        // NB: the 'auth' limiter (login / password-reset throttling) is registered
        // in AppServiceProvider::boot(), which — unlike this provider — is listed
        // in bootstrap/providers.php and therefore always boots.

        // Prevent Fortify from registering its own login / register / etc. views,
        // since we supply all views ourselves.
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('auth.totp-challenge'));
    }
}
