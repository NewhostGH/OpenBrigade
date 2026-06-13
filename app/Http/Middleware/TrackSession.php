<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records session activity in the legacy `audit` table.
 *
 * On first authenticated request the row is inserted; on every subsequent
 * request A_FIN (last activity) is updated. This feeds the connected-users
 * view (`/admin/connected-users`) exactly as the legacy `check_all()` did.
 *
 * The session key `_audit_debut` is written by {@see AuthService::attemptLogin}.
 */
class TrackSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $debut = $request->session()->get('_audit_debut');

            if ($debut !== null) {
                $this->upsertAudit($user->getAuthIdentifier(), $debut, $request);
            }
        }

        return $next($request);
    }

    private function upsertAudit(int $userId, string $debut, Request $request): void
    {
        $ua = $request->userAgent() ?? '';
        $os = $this->detectOs($ua);
        $browser = $this->detectBrowser($ua);
        $ip = (string) $request->ip();
        $now = now()->format('Y-m-d H:i:s');

        DB::table('audit')->upsert(
            [
                'P_ID' => $userId,
                'A_DEBUT' => $debut,
                'A_FIN' => $now,
                'A_OS' => $os,
                'A_BROWSER' => $browser,
                'A_IP' => $ip,
            ],
            ['P_ID', 'A_DEBUT'],
            ['A_FIN'],
        );
    }

    private function detectOs(string $ua): string
    {
        if (str_contains($ua, 'Android')) {
            return 'Android';
        }
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') || str_contains($ua, 'iOS')) {
            return 'iOS';
        }
        if (str_contains($ua, 'Windows')) {
            return 'Windows';
        }
        if (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS')) {
            return 'macOS';
        }
        if (str_contains($ua, 'Linux')) {
            return 'Linux';
        }

        return 'Autre';
    }

    private function detectBrowser(string $ua): string
    {
        if (str_contains($ua, 'Edg/') || str_contains($ua, 'Edge/')) {
            return 'Edge';
        }
        if (str_contains($ua, 'Chrome')) {
            return 'Chrome';
        }
        if (str_contains($ua, 'Firefox')) {
            return 'Firefox';
        }
        if (str_contains($ua, 'Safari')) {
            return 'Safari';
        }

        return 'Autre';
    }
}
