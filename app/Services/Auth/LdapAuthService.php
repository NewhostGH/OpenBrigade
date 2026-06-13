<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Services\Auth;

use LdapRecord\Container;
use LdapRecord\LdapRecordException;

/**
 * Delegates password verification to an LDAP directory.
 *
 * Enabled via LDAP_ENABLED=true in .env (config('ldap.enabled')).
 * Two auth methods are supported, set via LDAP_AUTH_METHOD:
 *
 *   bind (default) — searches for the user DN with the service account, then
 *                    retries the connection as that user. Requires LDAP_USERNAME
 *                    and LDAP_PASSWORD (service account credentials).
 *
 *   upn            — constructs the bind DN as "{login}{LDAP_UPN_SUFFIX}" and
 *                    tries binding directly. Typical for Active Directory where
 *                    the UPN suffix is "@corp.example.com".
 */
class LdapAuthService
{
    public function isEnabled(): bool
    {
        return (bool) config('ldap.enabled', false);
    }

    /**
     * Authenticate credentials against the LDAP directory.
     * Returns true only when the bind succeeds.
     */
    public function authenticate(string $login, string $password): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        try {
            return config('ldap.auth_method') === 'upn'
                ? $this->authenticateViaUpn($login, $password)
                : $this->authenticateViaBind($login, $password);
        } catch (LdapRecordException $e) {
            logger()->warning('[LDAP] authenticate failed', [
                'login' => $login,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verify that the service account can connect.
     * Returns null on success or an error message on failure.
     */
    public function testConnection(): ?string
    {
        try {
            $connection = Container::getDefaultConnection();
            $connection->connect();

            return null;
        } catch (LdapRecordException $e) {
            return $e->getMessage();
        } catch (\Throwable $e) {
            return 'Erreur inattendue : '.$e->getMessage();
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function authenticateViaBind(string $login, string $password): bool
    {
        $connection = Container::getDefaultConnection();

        $rawFilter = config('ldap.user_filter', '(&(objectClass=person)(|(uid={login})(mail={login})))');
        $filter = str_replace('{login}', ldap_escape($login, '', LDAP_ESCAPE_FILTER), $rawFilter);
        $baseDn = config('ldap.connections.default.base_dn', '');

        $results = $connection->query()
            ->rawFilter($filter)
            ->setDn($baseDn)
            ->select(['dn'])
            ->get();

        if (empty($results)) {
            return false;
        }

        $userDn = $results[0]['dn'] ?? null;
        if (! is_string($userDn) || $userDn === '') {
            return false;
        }

        return $connection->auth()->attempt($userDn, $password);
    }

    private function authenticateViaUpn(string $login, string $password): bool
    {
        $suffix = (string) config('ldap.upn_suffix', '');
        $bindDn = $login.$suffix;

        return Container::getDefaultConnection()->auth()->attempt($bindDn, $password);
    }
}
