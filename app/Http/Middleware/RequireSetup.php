<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Middleware;

use App\Services\OrganisationSetupService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * First-run gate (successor to legacy `already_configured`). While the install
 * has not been configured, an authenticated administrator (permission 14) is
 * redirected to the setup wizard; everyone else is left alone.
 *
 * Existing eBrigade databases already have `already_configured = 1`, so this is
 * a no-op there — the wizard only appears on genuinely fresh installs.
 */
class RequireSetup
{
    public function __construct(private readonly OrganisationSetupService $setup) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // On a genuinely fresh install roles/permissions may not be wired yet, so
        // a super-admin must always reach the wizard even without permission 14.
        $mayConfigure = $user !== null && ($user->isSuperAdmin() || $user->hasPermission(14));

        if ($user === null
            || $this->setup->isCompleted()
            || $request->routeIs('setup.*')
            || $request->routeIs('logout')
            || ! $mayConfigure) {
            return $next($request);
        }

        return redirect()->route('setup.show');
    }
}
