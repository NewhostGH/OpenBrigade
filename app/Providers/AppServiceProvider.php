<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\BrigadeService;
use App\Services\Legacy\LegacyMenuService;
use Illuminate\Support\Facades\Gate;
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

        $this->app->singleton(LegacyMenuService::class, function ($app) {
            return new LegacyMenuService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set up locale and timezone from config
        date_default_timezone_set(config('app.timezone'));

        // Gate for legacy permission IDs: Gate::allows('feature', 52)
        Gate::define('feature', function (User $user, int $fid): bool {
            return $user->hasPermission($fid);
        });

        View::composer(['layout.navbar', 'layout.sidebar'], function ($view): void {
            /** @var LegacyMenuService $legacyMenu */
            $legacyMenu = app(LegacyMenuService::class);
            /** @var User|null $user */
            $user = auth()->user();

            $view->with('legacyTopGroups', $legacyMenu->getTopGroups($user));
            $view->with('legacyLeftGroups', $legacyMenu->getLeftGroups($user));
        });
    }
}
