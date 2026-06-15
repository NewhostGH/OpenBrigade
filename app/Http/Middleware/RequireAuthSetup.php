<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\PasswordPolicyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks authenticated users from navigating the app until they have:
 *   1. A non-expired password (P_MDP_EXPIRY in the past → redirect to password tab)
 *   2. TOTP enrolled when their group policy requires it (redirect to 2FA tab)
 *
 * The authentication page itself and its form-action routes are whitelisted
 * so the gate can never cause an infinite redirect loop.
 */
class RequireAuthSetup
{
    private const ALLOWED_ROUTES = [
        'account.auth',
        'account.password.update',
        'totp.confirm',
        'totp.codes.regenerate',
        'totp.disable',
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

        $routeName = $request->route()?->getName();
        if (in_array($routeName, self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        try {
            if ($this->isPasswordExpired($user)) {
                return redirect()->route('account.auth', ['tab' => 'password', 'expired' => 1])
                    ->with('warning', __('Votre mot de passe a expiré. Veuillez en choisir un nouveau.'));
            }
        } catch (\Throwable) {
            // Fail open on DB errors so a schema issue doesn't lock everyone out.
        }

        try {
            $policy = app(PasswordPolicyService::class)->policyForUser($user);
            if (! empty($policy['require_2fa']) && ! $user->hasEnabledTwoFactorAuthentication()) {
                return redirect()->route('account.auth', ['tab' => '2fa'])
                    ->with('warning', __('Votre groupe requiert la double authentification. Veuillez configurer votre application TOTP.'));
            }
        } catch (\Throwable) {
            // Fail open.
        }

        return $next($request);
    }

    private function isPasswordExpired(User $user): bool
    {
        $expiry = $user->P_MDP_EXPIRY;
        if ($expiry === null || $expiry === '') {
            return false;
        }

        return Carbon::parse($expiry)->startOfDay()->lte(now()->startOfDay());
    }
}
