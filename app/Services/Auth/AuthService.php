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

namespace App\Services\Auth;

use App\Models\User;
use App\Services\PasswordPolicyService;
use App\Services\ServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthService implements ServiceInterface
{
    /**
     * Attempt a login.
     *
     * Returns 'totp_required' when the password is correct but the user has
     * confirmed TOTP enabled — the caller must redirect to the TOTP challenge.
     * Returns true on full success, false on failure.
     *
     * @return bool|'totp_required'
     */
    public function attemptLogin(string $login, string $plainPassword, bool $remember = false): bool|string
    {
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'P_EMAIL' : 'P_CODE';

        $user = User::query()
            ->where($field, $login)
            ->whereNull('P_FIN')
            ->first();

        if (! $user instanceof User) {
            return false;
        }

        // Enforce max_attempts lockout before checking the password.
        $policyService = app(PasswordPolicyService::class);
        $policy = $policyService->policyForUser($user);
        $failures = (int) ($user->P_PASSWORD_FAILURE ?? 0);
        if ($policy['max_attempts'] > 0 && $failures >= $policy['max_attempts']) {
            return false;
        }

        $passwordOk = app(LdapAuthService::class)->isEnabled()
            ? app(LdapAuthService::class)->authenticate($login, $plainPassword)
            : $this->validateLegacyPassword($plainPassword, (string) $user->P_MDP);

        if (! $passwordOk) {
            $this->incrementPasswordFailure($user);

            return false;
        }

        $this->resetPasswordFailure($user);
        if (! app(LdapAuthService::class)->isEnabled()) {
            $this->refreshLegacyHashIfNeeded($user, $plainPassword);
        }

        // TOTP challenge — confirmed enrolment takes priority.
        if ($user->hasEnabledTwoFactorAuthentication()) {
            Session::put('_totp_user_id', $user->P_ID);

            return 'totp_required';
        }

        // Policy mandates 2FA but the user has not enrolled yet.
        $policyService = app(PasswordPolicyService::class);
        if (! empty($policyService->policyForUser($user)['require_2fa'])) {
            $this->completeLogin($user);

            return 'totp_setup_required';
        }

        $this->completeLogin($user);

        return true;
    }

    /**
     * Complete login after a successful TOTP challenge.
     * Looks up the pending user from session, logs them in, clears the session key.
     */
    public function completeTotpLogin(): ?User
    {
        $userId = Session::get('_totp_user_id');
        if (! $userId) {
            return null;
        }

        $user = User::find($userId);
        if (! $user instanceof User) {
            Session::forget('_totp_user_id');

            return null;
        }

        Session::forget('_totp_user_id');
        $this->completeLogin($user);

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }

    /**
     * Check whether $plain matches the stored hash (supports both legacy MD5 and bcrypt).
     */
    public function verifyPassword(string $plain, string $hash): bool
    {
        return $this->validateLegacyPassword($plain, $hash);
    }

    /**
     * Hash $plain with bcrypt and persist it, using the user's resolved policy for expiry.
     */
    public function updatePassword(User $user, string $plain): void
    {
        $policyService = app(PasswordPolicyService::class);
        $expiry = $policyService->nextExpiry($policyService->policyForUser($user));

        $user->forceFill([
            'P_MDP' => password_hash($plain, PASSWORD_DEFAULT),
            'P_PASSWORD_FAILURE' => null,
            'P_MDP_EXPIRY' => $expiry,
        ])->save();
    }

    /**
     * Generate and persist a temporary password for $personId (admin action).
     * Sets P_MDP_EXPIRY = today so the user must change it on first login.
     */
    public function resetPasswordTo(int $personId, string $plainTemp): void
    {
        DB::table('pompier')->where('P_ID', $personId)->update([
            'P_MDP' => password_hash($plainTemp, PASSWORD_DEFAULT),
            'P_PASSWORD_FAILURE' => null,
            'P_MDP_EXPIRY' => now()->format('Y-m-d'),
        ]);
    }

    private function completeLogin(User $user): void
    {
        // Remember-me is intentionally disabled until a dedicated token column is migrated.
        Auth::guard('web')->login($user, false);
        Session::regenerate();

        // Store the session start time for audit tracking (TrackSession middleware).
        Session::put('_audit_debut', now()->format('Y-m-d H:i:s'));

        $user->forceFill([
            'P_LAST_CONNECT' => now(),
            'P_NB_CONNECT' => (int) ($user->P_NB_CONNECT ?? 0) + 1,
        ])->save();
    }

    private function validateLegacyPassword(string $plainPassword, string $hash): bool
    {
        if ($hash === md5($plainPassword)) {
            return true;
        }

        return password_verify($plainPassword, $hash);
    }

    private function refreshLegacyHashIfNeeded(User $user, string $plainPassword): void
    {
        $currentHash = (string) $user->P_MDP;

        if (strlen($currentHash) === 32 && ctype_xdigit($currentHash)) {
            $user->forceFill([
                'P_MDP' => password_hash($plainPassword, PASSWORD_DEFAULT),
            ])->save();
        }
    }

    private function incrementPasswordFailure(User $user): void
    {
        $current = (int) ($user->P_PASSWORD_FAILURE ?? 0);

        $user->forceFill([
            'P_PASSWORD_FAILURE' => $current + 1,
        ])->save();
    }

    private function resetPasswordFailure(User $user): void
    {
        if (! empty($user->P_PASSWORD_FAILURE)) {
            $user->forceFill([
                'P_PASSWORD_FAILURE' => null,
            ])->save();
        }
    }
}
