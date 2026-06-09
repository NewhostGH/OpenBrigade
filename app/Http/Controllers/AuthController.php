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

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $ok = $this->authService->attemptLogin(
            $validated['login'],
            $validated['password'],
            (bool) ($validated['remember'] ?? false)
        );

        if (! $ok) {
            return back()
                ->withErrors(['login' => __('Identifiant ou mot de passe incorrect.')])
                ->withInput($request->safe()->except('password'));
        }

        $intended = (string) $request->session()->pull('url.intended', '');
        if ($intended !== '') {
            // Collapse double index.php prefix produced by some legacy redirects.
            $normalizedIntended = str_replace('/index.php/index.php/', '/index.php/', $intended);
            $normalizedIntended = str_replace('index.php/index.php/', '/index.php/', $normalizedIntended);

            // Legacy root (was index_d.php) — send straight to the dashboard.
            if (in_array($normalizedIntended, ['/index.php/index.php', '/index.php/index_d.php', '/legacy/index_d.php'], true)) {
                return redirect()->route('dashboard');
            }

            if (! str_starts_with($normalizedIntended, '/')
                && ! str_starts_with($normalizedIntended, 'http://')
                && ! str_starts_with($normalizedIntended, 'https://')) {
                $normalizedIntended = '/'.ltrim($normalizedIntended, '/');
            }

            return redirect()->to($normalizedIntended);
        }

        return redirect()->route('dashboard');
    }

    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('login')->with('success', __('Vous êtes déconnecté.'));
    }
}
