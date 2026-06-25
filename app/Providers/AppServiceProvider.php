<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AppIdentityService;
use App\Services\Auth\AuthService;
use App\Services\BrigadeService;
use App\Services\FeatureService;
use App\Services\LoggingSettingService;
use App\Services\NavigationService;
use App\Services\PermissionResolver;
use App\Services\SectionScopeService;
use App\Services\SecuritySettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService;
        });

        $this->app->singleton(AppIdentityService::class);

        // Register singleton services (instantiated once per container)
        $this->app->singleton(BrigadeService::class, function ($app) {
            return new BrigadeService;
        });

        // Per-request memoized feature/module flags (the ob_feature registry).
        $this->app->singleton(FeatureService::class, function ($app) {
            return new FeatureService;
        });

        // Per-request memoized observability settings — resolved on every logged
        // record for per-canal level checks, so it must be a singleton.
        $this->app->singleton(LoggingSettingService::class);

        $this->app->singleton(NavigationService::class, function ($app) {
            return new NavigationService($app->make(FeatureService::class));
        });

        // Per-request memoized permission resolution (section ceilings + grants).
        $this->app->singleton(PermissionResolver::class, function ($app) {
            return new PermissionResolver;
        });

        // Per-request memoized section data isolation (visible-section set).
        $this->app->singleton(SectionScopeService::class, function ($app) {
            return new SectionScopeService(
                $app->make(FeatureService::class),
                $app->make(PermissionResolver::class),
            );
        });

        // Resolve the Sentry/GlitchTip DSN from the admin setting in the REGISTER
        // phase. The Sentry service provider boots *before* this provider (package
        // providers boot before app providers) and there it eagerly builds its
        // hub and gates event capture on config('sentry.dsn') being set. All
        // register() calls run before any boot(), so setting it here lands before
        // Sentry boots — doing it in boot() would be too late and silently drop
        // every event.
        $this->configureErrorTracking();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set up locale and timezone from config
        date_default_timezone_set(config('app.timezone'));

        // This is a Bootstrap 5 app — render paginators with the Bootstrap view
        // so `$paginator->links()` matches the UI instead of the unstyled
        // Tailwind default (which renders oversized SVG arrows without Tailwind).
        Paginator::useBootstrapFive();

        // Reconcile the logging pipeline with the administrable observability
        // settings (Journal d'activité ▸ Paramètres) before anything logs.
        $this->configureObservability();

        // Force URL root so redirects include the correct host:port when running
        // behind Docker port mappings where internal port differs from external.
        if ($url = config('app.url')) {
            URL::forceRootUrl($url);
            if (str_starts_with($url, 'https://')) {
                URL::forceScheme('https');
            }
        }

        // Gate for legacy permission IDs: Gate::allows('feature', 52)
        Gate::define('feature', function (User $user, int $fid): bool {
            return $user->hasPermission($fid);
        });

        // Named 'auth' rate limiter for the login / password-reset routes. The
        // limit (and whether it applies) is administrable from Sécurité ▸
        // Renforcement, so it is resolved per request. Keyed by client IP.
        RateLimiter::for('auth', function (Request $request) {
            $settings = app(SecuritySettingService::class);

            if (! $settings->bool('sec_ratelimit_auth_enabled')) {
                return Limit::none();
            }

            return Limit::perMinutes(
                max(1, $settings->int('sec_ratelimit_auth_window')),
                max(1, $settings->int('sec_ratelimit_auth_max')),
            )->by($request->ip());
        });

        // @feature('multi_site') … @endfeature — hide UI tied to a disabled
        // feature flag. Fails open (enabled) so a missing ob_feature table
        // never blanks a page (e.g. tests without a database).
        Blade::if('feature', function (string $key): bool {
            try {
                return app(FeatureService::class)->isEnabled($key);
            } catch (\Throwable) {
                return true;
            }
        });

        View::composer('layout.navbar', function ($view): void {
            $nav = app(NavigationService::class);
            $user = auth()->user();
            $view->with('pinnedShortcuts', $nav->getPinnedShortcuts($user));

            // Active section / role context switchers. Non-critical navbar
            // enhancement — never let it break a page render (e.g. before the
            // ob_ tables exist, or in tests without a database).
            $ctx = ['ctxSections' => collect(), 'ctxActiveSection' => null, 'ctxRoles' => collect(), 'ctxActiveRole' => null];
            if ($user !== null) {
                try {
                    $resolver = app(PermissionResolver::class);
                    // The switcher reflects the EXPLICIT navbar choice (null = "Toutes
                    // mes sections"), not activeSectionId() which falls back to the
                    // home section for permission evaluation.
                    $ctx['ctxActiveSection'] = $resolver->chosenSectionId();
                    $ctx['ctxSections'] = app(SectionScopeService::class)->switcherSections();
                    $ctx['ctxRoles'] = $resolver->userRoles($user, $ctx['ctxActiveSection']);
                    $ctx['ctxActiveRole'] = $resolver->activeRoleId($user);
                } catch (\Throwable $e) {
                    // keep defaults
                }
            }
            $view->with($ctx);
        });

        View::composer('layout.sidebar', function ($view): void {
            $nav = app(NavigationService::class);
            $user = auth()->user();
            $view->with('navGroups', $nav->getNavGroups($user));
            $view->with('appIdentity', app(AppIdentityService::class));
        });

        View::composer('auth.login', function ($view): void {
            $view->with('appIdentity', app(AppIdentityService::class));
        });
    }

    /**
     * Apply the runtime observability settings to the log channels and the
     * Sentry/GlitchTip client.
     *
     * Wholly guarded: if the settings can't be read (no DB, fresh install,
     * tests) the shipped config/env defaults stand and logging keeps working.
     */
    private function configureObservability(): void
    {
        try {
            $obs = app(LoggingSettingService::class);

            // The database store keeps records per-canal (filtered in
            // DatabaseLogHandler), so the channel itself accepts everything; the
            // file mirrors at least the lowest canal threshold.
            $fileLevel = $obs->lowestCanalLevel();
            Config::set('logging.channels.daily.level', $fileLevel);
            Config::set('logging.channels.single.level', $fileLevel);
            Config::set('logging.channels.daily.days', max(1, $obs->int('obs_file_retention_days')));

            // Compose the active stack from the output toggles. Falling back to
            // the file leg guarantees we never end up with a no-op logger.
            $stack = [];
            if ($obs->bool('obs_log_to_file')) {
                $stack[] = $obs->string('obs_file_channel') === 'single' ? 'single' : 'daily';
            }
            if ($obs->bool('obs_log_to_db')) {
                $stack[] = 'database';
            }
            Config::set('logging.channels.stack.channels', $stack !== [] ? $stack : ['daily']);
        } catch (\Throwable) {
            // Keep shipped defaults — logging must never break boot.
        }
    }

    /**
     * Resolve the effective Sentry/GlitchTip DSN from the admin settings.
     *
     * Error tracking is opt-in: report only when the `obs_error_tracking` toggle
     * is on AND a DSN is set. The DSN is an admin setting (`obs_sentry_dsn`), with
     * the `SENTRY_LARAVEL_DSN` env (config/sentry.php) as a fallback for existing
     * deployments. When disabled, the DSN is cleared so Sentry stays silent.
     *
     * Must run in register() — see the call site for why.
     */
    private function configureErrorTracking(): void
    {
        try {
            $obs = $this->app->make(LoggingSettingService::class);

            $dsn = $obs->string('obs_sentry_dsn') ?: (string) config('sentry.dsn');

            Config::set('sentry.dsn', $obs->bool('obs_error_tracking') && $dsn !== '' ? $dsn : null);
        } catch (\Throwable) {
            // Keep the shipped config/env DSN — never break boot over a setting.
        }
    }
}
