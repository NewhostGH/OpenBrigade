<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Password policy rules and helpers, reading from the legacy `configuration` table.
 *
 * Config IDs: 15 = password_quality, 16 = password_length, 70 = password_expiry_days
 */
class PasswordPolicyService implements ServiceInterface
{
    /** @var array{quality:int,min_length:int,expiry_days:int}|null */
    private ?array $policy = null;

    /** @return array{quality:int,min_length:int,expiry_days:int} */
    public function policy(): array
    {
        if ($this->policy === null) {
            $rows = DB::table('configuration')
                ->whereIn('NAME', ['password_quality', 'password_length', 'password_expiry_days'])
                ->pluck('VALUE', 'NAME');

            $this->policy = [
                'quality' => (int) ($rows['password_quality'] ?? 0),
                'min_length' => (int) ($rows['password_length'] ?? 0),
                'expiry_days' => (int) ($rows['password_expiry_days'] ?? 0),
            ];
        }

        return $this->policy;
    }

    /**
     * Validate a candidate password against the brigade policy.
     *
     * @return string|null Error message, or null when valid.
     */
    public function validate(string $password, string $matricule = ''): ?string
    {
        if ($password === '') {
            return __('Le nouveau mot de passe ne peut pas être vide.');
        }

        if (preg_match('/["\']/', $password)) {
            return __("Le mot de passe ne doit pas contenir d'apostrophes ou guillemets.");
        }

        $policy = $this->policy();

        if ($policy['min_length'] > 0 && mb_strlen($password) < $policy['min_length']) {
            return __('Le mot de passe est trop court (minimum :n caractères).', ['n' => $policy['min_length']]);
        }

        if ($matricule !== '') {
            if (str_contains($password, $matricule) || substr($password, 0, 2) === substr($matricule, 0, 2)) {
                return __('Le mot de passe ne doit pas être basé sur votre identifiant.');
            }
        }

        if ($policy['quality'] >= 1) {
            if (! preg_match('/[0-9]/', $password)) {
                return __('Le mot de passe doit contenir au moins un chiffre.');
            }
            if (! preg_match('/[a-zA-Z]/', $password)) {
                return __('Le mot de passe doit contenir au moins une lettre.');
            }
        }

        if ($policy['quality'] >= 2 && ! preg_match('/\W/', $password)) {
            return __('Le mot de passe doit contenir au moins un caractère spécial (!, @, #, $, %, …).');
        }

        return null;
    }

    /**
     * Generate a temporary plain-text password long enough to satisfy the minimum
     * length, always mixing letters and digits (meets quality 0 and 1).
     * Quality-2 (special-char) is intentionally skipped for temporary passwords
     * because they expire immediately and the user must change them on first login.
     */
    public function generateTemporaryPassword(): string
    {
        $length = max($this->policy()['min_length'], 8);
        $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';

        // Start with at least 2 random digits so quality-1 is met.
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
     * Compute the next expiry date given the current policy.
     *
     * @return string|null ISO date (Y-m-d) or null when expiry is disabled.
     */
    public function nextExpiry(): ?string
    {
        $days = $this->policy()['expiry_days'];

        return $days > 0 ? now()->addDays($days)->format('Y-m-d') : null;
    }
}
