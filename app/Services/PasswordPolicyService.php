<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Services;

use App\Models\ObPasswordPolicy;
use App\Models\User;

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
    /** @var array<int,array{min_length:int,expiry_days:int,max_attempts:int,blocklist_check:bool}> keyed by group id or 0 for global */
    private array $cache = [];

    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Return the resolved policy for a user (group-specific or default).
     *
     * @return array{min_length:int,expiry_days:int,max_attempts:int,blocklist_check:bool}
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
     * @return array{min_length:int,expiry_days:int,max_attempts:int,blocklist_check:bool}
     */
    public function policy(): array
    {
        return $this->defaultPolicy();
    }

    /**
     * Validate a candidate password against a policy.
     * Pass the result of policyForUser() / policy() as $policy.
     * Omit $policy to use the global default.
     *
     * @param  array<string,mixed>|null  $policy
     * @return string|null Error message, or null when valid.
     */
    public function validate(string $password, string $matricule = '', ?array $policy = null): ?string
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

        if (! empty($policy['blocklist_check']) && $this->isBlocklisted($password)) {
            return __('Ce mot de passe est trop commun. Choisissez-en un plus original.');
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
     * @param  array{min_length:int,expiry_days:int,max_attempts:int,blocklist_check:bool}|null  $policy
     * @return string|null ISO date (Y-m-d) or null when expiry is disabled.
     */
    public function nextExpiry(?array $policy = null): ?string
    {
        $days = ($policy ?? $this->defaultPolicy())['expiry_days'];

        return $days > 0 ? now()->addDays($days)->format('Y-m-d') : null;
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /** @return array{min_length:int,expiry_days:int,max_attempts:int,blocklist_check:bool} */
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
     * Returns true if the password is on the common-passwords blocklist.
     * This is a minimal embedded list; extend via storage/app/private/blocklist.txt
     * (one password per line) for organisation-specific additions.
     */
    private function isBlocklisted(string $password): bool
    {
        $lower = strtolower($password);

        // Check the embedded top-100 list first (fast, no I/O).
        if (in_array($lower, self::COMMON_PASSWORDS, true)) {
            return true;
        }

        // Check a custom blocklist file if it exists.
        $path = storage_path('app/private/blocklist.txt');
        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    if (strtolower(trim($line)) === $lower) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /** Top common / easily-guessed passwords (NCSC / HIBP-aligned subset). */
    private const COMMON_PASSWORDS = [
        'password', 'password1', 'password123', '123456', '12345678', '123456789',
        '1234567890', '12345', '1234', '111111', '000000', 'abc123', 'qwerty',
        'qwerty123', 'azerty', 'azerty123', '1q2w3e', '1q2w3e4r', 'iloveyou',
        'letmein', 'monkey', 'dragon', 'master', 'welcome', 'login', 'admin',
        'admin123', 'root', 'pass', 'test', 'guest', 'changeme', 'secret',
        'sunshine', 'princess', 'batman', 'superman', 'football', 'baseball',
        'soccer', 'hockey', 'hunter', 'shadow', 'mustang', 'michael', 'jessica',
        'daniel', 'thomas', 'george', 'jordan', 'harley', 'ranger', 'dakota',
        'trustno1', 'access', 'matrix', 'starwars', 'firewall', 'internet',
        'computer', 'corvette', 'thunder', 'cookie', 'flower', 'hello', 'love',
        'pass123', 'pass1234', '123qwe', 'qazwsx', 'zxcvbn', 'zxcvbnm',
        'asdfgh', 'asdfghjkl', 'qwertyuiop', '1111', '2222', '3333', '4444',
        '5555', '6666', '7777', '8888', '9999', '0000', '11111', '22222',
        '112233', '123123', '121212', '654321', '666666', '987654321',
        'abcd1234', 'abc1234', 'pass@123', 'motdepasse', 'pompier', 'brigade',
    ];
}
