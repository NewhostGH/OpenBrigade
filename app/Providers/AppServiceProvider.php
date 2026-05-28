<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\BrigadeService;
use App\Services\NavigationService;
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
            return new AuthService();
        });

        // Register singleton services (instantiated once per container)
        $this->app->singleton(BrigadeService::class, function ($app) {
            return new BrigadeService();
        });

        $this->app->singleton(NavigationService::class, function ($app) {
            return new NavigationService();
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

        View::composer('layout.navbar', function ($view): void {
            $nav = app(NavigationService::class);
            $user = auth()->user();
            $view->with('pinnedShortcuts', $nav->getPinnedShortcuts($user));
        });

        View::composer('layout.sidebar', function ($view): void {
            $nav = app(NavigationService::class);
            $user = auth()->user();
            $view->with('navGroups', $nav->getNavGroups($user));
        });
    }
}
