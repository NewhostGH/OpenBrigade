<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Services;

use App\Models\ObPasswordPolicy;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Password policy rules and helpers.
 *
 * Policy resolution order (first wins):
 *   1. ob_group.password_policy_id for the user's primary group (GP_ID)
 *   2. ob_group.password_policy_id for the user's secondary group (GP_ID2)
 *   3. The row in ob_password_policy where is_default = true
 *   4. Hard-coded NCSC-aligned fallback
 *
 * NCSC guidance applied: min-length over complexity, no forced rotation,
 * blocklist common/known-bad passwords, throttle after repeated failures.
 */
class PasswordPolicyService implements ServiceInterface
{
    /** @var array<int,array{min_length:int,require_uppercase:bool,require_lowercase:bool,require_digits:bool,require_special:bool,expiry_days:int,max_attempts:int,blocklist_check:bool,require_2fa:bool}> keyed by group id or 0 for global */
    private array $cache = [];

    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Return the resolved policy for a user (group-specific or default).
     *
     * @return array{min_length:int,require_uppercase:bool,require_lowercase:bool,require_digits:bool,require_special:bool,expiry_days:int,max_attempts:int,blocklist_check:bool,require_2fa:bool}
     */
    public function policyForUser(?User $user): array
    {
        if ($user === null) {
            return $this->defaultPolicy();
        }

        foreach ([(int) ($user->GP_ID ?? 0), (int) ($user->GP_ID2 ?? 0)] as $groupId) {
            if ($groupId <= 0) {
                continue;
            }

            $cacheKey = $groupId;
            if (! array_key_exists($cacheKey, $this->cache)) {
                $policy = ObPasswordPolicy::query()
                    ->whereHas('groups', fn ($q) => $q->where('id', $groupId))
                    ->first();
                $this->cache[$cacheKey] = $policy?->toPolicy();
            }

            if ($this->cache[$cacheKey] !== null) {
                return $this->cache[$cacheKey];
            }
        }

        return $this->defaultPolicy();
    }

    /**
     * Legacy accessor — returns the global default policy.
     * Callers that do not have a User object should use this.
     *
     * @return array{min_length:int,require_uppercase:bool,require_lowercase:bool,require_digits:bool,require_special:bool,expiry_days:int,max_attempts:int,blocklist_check:bool,require_2fa:bool}
     */
    public function policy(): array
    {
        return $this->defaultPolicy();
    }

    /**
     * Validate a candidate password against a policy.
     *
     * @param  array<string,mixed>|null  $policy
     * @param  bool  $checkHibp  When true, query Have I Been Pwned for breach exposure.
     *                           Pass false for login-time checks to avoid latency.
     * @return string|null Error message, or null when valid.
     */
    public function validate(string $password, string $matricule = '', ?array $policy = null, bool $checkHibp = true): ?string
    {
        $policy ??= $this->defaultPolicy();

        if ($password === '') {
            return __('Le nouveau mot de passe ne peut pas être vide.');
        }

        if ($policy['min_length'] > 0 && mb_strlen($password) < $policy['min_length']) {
            return __('Le mot de passe est trop court (minimum :n caractères).', ['n' => $policy['min_length']]);
        }

        if ($matricule !== '') {
            $lower = strtolower($password);
            $mat = strtolower($matricule);
            if (str_contains($lower, $mat) || substr($lower, 0, 2) === substr($mat, 0, 2)) {
                return __('Le mot de passe ne doit pas être basé sur votre identifiant.');
            }
        }

        if (! empty($policy['require_uppercase']) && ! preg_match('/[A-Z]/', $password)) {
            return __('Le mot de passe doit contenir au moins une lettre majuscule.');
        }

        if (! empty($policy['require_lowercase']) && ! preg_match('/[a-z]/', $password)) {
            return __('Le mot de passe doit contenir au moins une lettre minuscule.');
        }

        if (! empty($policy['require_digits']) && ! preg_match('/[0-9]/', $password)) {
            return __('Le mot de passe doit contenir au moins un chiffre.');
        }

        if (! empty($policy['require_special']) && ! preg_match('/[\W_]/', $password)) {
            return __('Le mot de passe doit contenir au moins un caractère spécial (!, @, #, $, %…).');
        }

        if (! empty($policy['blocklist_check'])) {
            if ($this->isTrivialPattern($password)) {
                return __('Ce mot de passe est trop prévisible (séquence répétée ou consécutive). Choisissez-en un plus original.');
            }

            if ($checkHibp && $this->isCompromised($password)) {
                return __('Ce mot de passe est apparu dans une fuite de données connues. Choisissez-en un autre.');
            }
        }

        return null;
    }

    /**
     * Generate a temporary plain-text password satisfying the default policy.
     * Temporary passwords always expire immediately (P_MDP_EXPIRY = today).
     */
    public function generateTemporaryPassword(): string
    {
        $length = max($this->defaultPolicy()['min_length'], 10);
        $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';

        $pass = '';
        for ($i = 0; $i < 2; $i++) {
            $pass .= $digits[random_int(0, strlen($digits) - 1)];
        }
        for ($i = 2; $i < $length; $i++) {
            $pass .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return str_shuffle($pass);
    }

    /**
     * Compute the next expiry date for a given policy.
     *
     * @param  array{min_length:int,require_uppercase:bool,require_lowercase:bool,require_digits:bool,require_special:bool,expiry_days:int,max_attempts:int,blocklist_check:bool,require_2fa:bool}|null  $policy
     * @return string|null ISO date (Y-m-d) or null when expiry is disabled.
     */
    public function nextExpiry(?array $policy = null): ?string
    {
        $days = ($policy ?? $this->defaultPolicy())['expiry_days'];

        return $days > 0 ? now()->addDays($days)->format('Y-m-d') : null;
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /** @return array{min_length:int,require_uppercase:bool,require_lowercase:bool,require_digits:bool,require_special:bool,expiry_days:int,max_attempts:int,blocklist_check:bool,require_2fa:bool} */
    private function defaultPolicy(): array
    {
        if (! array_key_exists(0, $this->cache)) {
            $row = ObPasswordPolicy::where('is_default', true)->first();
            if ($row) {
                $this->cache[0] = $row->toPolicy();
            } else {
                // Hard-coded NCSC-aligned fallback when no DB row exists.
                $this->cache[0] = [
                    'min_length' => 12,
                    'require_uppercase' => false,
                    'require_lowercase' => false,
                    'require_digits' => false,
                    'require_special' => false,
                    'expiry_days' => 0,
                    'max_attempts' => 10,
                    'blocklist_check' => true,
                    'require_2fa' => false,
                ];
            }
        }

        return $this->cache[0];
    }

    /**
     * Returns true when the password matches a trivially guessable pattern:
     *  - All same character (aaaaaaa, 1111111)
     *  - Consecutive ascending or descending characters (abcdefg, zyxwvu,
     *    123456, 7654321) with at least 5 characters
     */
    private function isTrivialPattern(string $password): bool
    {
        // All same character.
        if (preg_match('/^(.)\1+$/u', $password)) {
            return true;
        }

        // Consecutive sequence.
        if (mb_strlen($password) >= 5 && $this->isConsecutiveSequence(strtolower($password))) {
            return true;
        }

        return false;
    }

    /**
     * Returns true when the password has appeared in a known data breach
     * (checked via the Have I Been Pwned k-anonymity API).
     * Fails open — allows the password when the API is unreachable.
     */
    private function isCompromised(string $password): bool
    {
        try {
            $validator = Validator::make(
                ['p' => $password],
                ['p' => PasswordRule::min(1)->uncompromised()]
            );

            return $validator->fails();
        } catch (\Throwable) {
            return false; // Fail open: API unavailable → let the password through.
        }
    }

    /**
     * Returns true when every character in $s follows the same +1 or -1 Unicode
     * code-point step — e.g. "abcde", "zyxwv", "12345", "9876543".
     */
    private function isConsecutiveSequence(string $s): bool
    {
        $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false || count($chars) < 2) {
            return false;
        }

        $step = mb_ord($chars[1]) - mb_ord($chars[0]);
        if ($step !== 1 && $step !== -1) {
            return false;
        }

        for ($i = 2; $i < count($chars); $i++) {
            if (mb_ord($chars[$i]) - mb_ord($chars[$i - 1]) !== $step) {
                return false;
            }
        }

        return true;
    }
}
