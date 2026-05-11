<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\BrigadeService;
use Illuminate\Support\Facades\Gate;
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
    }
}
