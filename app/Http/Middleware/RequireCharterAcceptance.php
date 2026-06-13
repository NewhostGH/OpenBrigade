<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces charter acceptance when `charte_active = 1` (configuration).
 *
 * Authenticated users who have not yet accepted are locked to the charter
 * page until they accept or explicitly reject (which logs them out). The
 * charter routes themselves are whitelisted so the redirect doesn't loop.
 */
class RequireCharterAcceptance
{
    /** Routes that must remain accessible while charter gate is enforced. */
    private const ALLOWED_ROUTES = [
        'account.charter',
        'account.charter.accept',
        'account.charter.reject',
        'logout',
        'logout.compat',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        if (! $this->charteActive()) {
            return $next($request);
        }

        $accepted = DB::table('pompier')
            ->where('P_ID', $user->P_ID)
            ->whereNotNull('P_ACCEPT_DATE')
            ->exists();

        if (! $accepted) {
            return redirect()->route('account.charter');
        }

        return $next($request);
    }

    private function charteActive(): bool
    {
        try {
            return (bool) DB::table('configuration')->where('NAME', 'charte_active')->value('VALUE');
        } catch (\Throwable) {
            return false;
        }
    }
}
