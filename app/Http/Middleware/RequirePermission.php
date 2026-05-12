<?php

# project: OpenBrigade

# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replaces the legacy check_all($fid) inline guard.
 *
 * Usage in routes:
 *   Route::get(...)->middleware('permission:52');
 *
 * Usage in controllers:
 *   $this->middleware('permission:52');
 */
class RequirePermission
{
    public function handle(Request $request, Closure $next, int $fid): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        abort_if($user === null, 401);
        abort_unless($user->hasPermission($fid), 403, 'Permission refusée (F_ID: '.$fid.').');

        return $next($request);
    }
}
