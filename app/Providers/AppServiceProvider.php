<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AppIdentityService;
use App\Services\Auth\AuthService;
use App\Services\BrigadeService;
use App\Services\FeatureService;
use App\Services\NavigationService;
use App\Services\PermissionResolver;
use App\Services\SectionScopeService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set up locale and timezone from config
        date_default_timezone_set(config('app.timezone'));

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
}
