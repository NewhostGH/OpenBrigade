<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\GeneralSettingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Administrable maintenance mode (configuration rows 37 `maintenance_mode`
 * and 41 `maintenance_text`, managed from Administration ▸ Maintenance).
 *
 * While enabled, only administrators (permission 14 / super admin) can use
 * the app; everyone else gets the 503 page carrying the maintenance text.
 * Guests keep the whole login flow (with a notice banner on the login page)
 * so an administrator can still sign in to turn the mode off — matching the
 * legacy "seul admin peut se connecter" behaviour.
 */
class MaintenanceMode
{
    public function __construct(private readonly GeneralSettingService $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->settings->maintenanceEnabled()) {
            return $next($request);
        }

        $text = $this->text();

        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            // Login flow stays reachable — surface the notice on the page.
            View::share('maintenanceNotice', $text);

            return $next($request);
        }

        if ($user->isSuperAdmin() || $user->hasPermission(14)) {
            return $next($request);
        }

        // Non-admins may still leave.
        if ($request->routeIs('logout') || $request->routeIs('logout.compat')) {
            return $next($request);
        }

        abort(503, $text);
    }

    /** The maintenance text as plain text (legacy rows carry `<br>` HTML). */
    private function text(): string
    {
        $raw = $this->settings->maintenanceText();
        $plain = trim(strip_tags((string) preg_replace('/<br\s*\/?>/i', "\n", $raw)));

        return $plain !== '' ? $plain : __('errors.503.message');
    }
}
