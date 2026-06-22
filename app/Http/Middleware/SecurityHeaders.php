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

use App\Services\SecuritySettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds security response headers to every web response:
 *  - X-Frame-Options              → blocks clickjacking
 *  - X-Content-Type-Options       → blocks MIME sniffing
 *  - Referrer-Policy              → limits referrer leakage
 *  - Permissions-Policy           → disables unused browser features
 *  - Content-Security-Policy      → restricts resource origins (toggleable)
 *  - Strict-Transport-Security    → forces HTTPS (toggleable, HTTPS-only)
 *
 * The CSP and HSTS behaviours are administrable from Administration ▸ Sécurité ▸
 * Renforcement; see {@see SecuritySettingService}.
 */
class SecurityHeaders
{
    public function __construct(private readonly SecuritySettingService $settings) {}

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
        if ($this->settings->bool('sec_csp_enabled')) {
            $header = $this->settings->bool('sec_csp_report_only')
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $response->headers->set(
                $header,
                "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://*.tile.openstreetmap.org; font-src 'self' data:; connect-src 'self' https://*.tile.openstreetmap.org; object-src 'none'; frame-ancestors 'self';"
            );
        }

        // HSTS only over a genuine HTTPS request, so an HTTP-only deployment can never
        // be locked out by flipping the toggle.
        if ($this->settings->bool('sec_hsts_enabled') && $request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age='.$this->settings->int('sec_hsts_max_age').'; includeSubDomains'
            );
        }

        return $response;
    }
}
