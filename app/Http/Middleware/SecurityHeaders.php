<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds security response headers to every web response:
 *  - X-Frame-Options          → blocks clickjacking
 *  - X-Content-Type-Options   → blocks MIME sniffing
 *  - Referrer-Policy          → limits referrer leakage
 *  - Content-Security-Policy  → restricts resource origins
 *  - Permissions-Policy       → disables unused browser features
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // CSP: allow self, Bootstrap/FA CDN fallback, no inline scripts except those using nonce
        // Kept permissive for legacy-migrated pages that may use inline JS; tighten per-domain during migration.
        // img-src / connect-src include OpenStreetMap tile servers used by the Leaflet map (géolocalisation page).
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://*.tile.openstreetmap.org; font-src 'self' data:; connect-src 'self' https://*.tile.openstreetmap.org; object-src 'none'; frame-ancestors 'self';"
        );

        return $response;
    }
}
