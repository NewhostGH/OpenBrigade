<?php

namespace App\Services\Auth;

use App\Models\LdapDomain;
use App\Models\ObUserAssignment;
use App\Models\User;
use LdapRecord\Connection;
use LdapRecord\Container;
use LdapRecord\LdapRecordException;

/**
 * Delegates password verification to one or more LDAP directories.
 *
 * Domains are configured via the admin UI (ldap_domains table) and tried in
 * priority order. Falls back to the legacy .env-based connection when no DB
 * domains exist and LDAP_ENABLED=true.
 *
 * For each domain, two auth methods are supported:
 *   bind — search for the user DN via the service account, then bind as that
 *           user.  Requires a username / password on the domain record.
 *   upn  — construct the bind DN as "{login}{upn_suffix}" and bind directly.
 *           Typical for Active Directory.
 *
 * After a successful bind the service:
 *   1. Checks OU rules (allow / deny / assign).
 *   2. Applies attribute mappings to the local pompier record.
 */
class LdapAuthService
{
    public function isEnabled(): bool
    {
        if (LdapDomain::where('enabled', true)->exists()) {
            return true;
        }

        return (bool) config('ldap.enabled', false);
    }

    /**
     * Authenticate credentials against configured LDAP domains.
     * Returns true when any domain accepts the credentials (and OU rules allow it).
     * Pass $user so that attribute maps and assignments can be applied.
     */
    public function authenticate(string $login, string $password, ?User $user = null): bool
    {
        $domains = LdapDomain::where('enabled', true)->orderBy('priority')->get();

        if ($domains->isEmpty()) {
            // Legacy fallback: single .env-based domain.
            if (! config('ldap.enabled')) {
                return false;
            }

            return $this->authenticateOnLegacy($login, $password);
        }

        foreach ($domains as $domain) {
            if ($this->authenticateOnDomain($domain, $login, $password, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test connectivity for a specific domain (service-account bind only).
     * Returns null on success, error message on failure.
     */
    public function testDomain(LdapDomain $domain): ?string
    {
        try {
            $conn = $this->registerConnection($domain);
            $conn->connect();

            return null;
        } catch (LdapRecordException $e) {
            return $e->getMessage();
        } catch (\Throwable $e) {
            return 'Erreur inattendue : '.$e->getMessage();
        }
    }

    /**
     * Test the legacy .env-based connection.
     */
    public function testConnection(): ?string
    {
        try {
            Container::getDefaultConnection()->connect();

            return null;
        } catch (LdapRecordException $e) {
            return $e->getMessage();
        } catch (\Throwable $e) {
            return 'Erreur inattendue : '.$e->getMessage();
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function authenticateOnDomain(LdapDomain $domain, string $login, string $password, ?User $user): bool
    {
        try {
            $conn = $this->registerConnection($domain);

            $userDn = $domain->auth_method === 'upn'
                ? $this->bindUpn($conn, $login, $password, $domain)
                : $this->bindSearch($conn, $login, $password, $domain);

            if ($userDn === null) {
                return false;
            }

            if (! $this->passesOuRules($conn, $domain, $userDn, $login)) {
                return false;
            }

            if ($user !== null) {
                $this->applyAttributeMaps($conn, $domain, $userDn, $user);
                $this->applyOuAssignments($conn, $domain, $userDn, $user);
            }

            return true;
        } catch (LdapRecordException $e) {
            logger()->warning('[LDAP] domain auth failed', [
                'domain' => $domain->name,
                'login' => $login,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Returns the user DN on success, null on failure.
     * Tries binding as the service account, searches for the user, then binds as user.
     */
    private function bindSearch(Connection $conn, string $login, string $password, LdapDomain $domain): ?string
    {
        $rawFilter = $domain->user_filter ?: '(&(objectClass=person)(|(uid={login})(mail={login})))';
        $filter = str_replace('{login}', ldap_escape($login, '', LDAP_ESCAPE_FILTER), $rawFilter);

        $results = $conn->query()
            ->rawFilter($filter)
            ->setDn($domain->base_dn)
            ->select(['dn'])
            ->get();

        if (empty($results)) {
            return null;
        }

        $userDn = $results[0]['dn'] ?? null;
        if (! is_string($userDn) || $userDn === '') {
            return null;
        }

        return $conn->auth()->attempt($userDn, $password) ? $userDn : null;
    }

    /**
     * Binds directly with "{login}{upn_suffix}".
     * Returns the constructed bind DN on success, null on failure.
     */
    private function bindUpn(Connection $conn, string $login, string $password, LdapDomain $domain): ?string
    {
        $bindDn = $login.($domain->upn_suffix ?? '');
        if (! $conn->auth()->attempt($bindDn, $password)) {
            return null;
        }

        // Resolve the actual DN for OU/attr checks.
        $filter = str_replace(
            '{login}',
            ldap_escape($login, '', LDAP_ESCAPE_FILTER),
            $domain->user_filter ?: '(&(objectClass=person)(|(uid={login})(mail={login})(userPrincipalName={login})))'
        );
        $results = $conn->query()->rawFilter($filter)->setDn($domain->base_dn)->select(['dn'])->get();

        return (string) ($results[0]['dn'] ?? $bindDn);
    }

    /**
     * Checks whether the user DN passes the domain's OU rules.
     * deny wins over allow; restrict_to_ou=true requires at least one allow match.
     */
    private function passesOuRules(Connection $conn, LdapDomain $domain, string $userDn, string $login): bool
    {
        $rules = $domain->ouRules;
        if ($rules->isEmpty()) {
            return true;
        }

        $matched = $rules->filter(fn ($r) => $this->dnMatchesOu($userDn, $r->ou_dn)
            && $this->matchesExtraFilter($conn, $domain, $userDn, $r->extra_filter));

        // Deny wins.
        if ($matched->where('action', 'deny')->isNotEmpty()) {
            logger()->info('[LDAP] user denied by OU rule', ['dn' => $userDn, 'domain' => $domain->name]);

            return false;
        }

        // If whitelist mode: must have at least one allow or assign match.
        if ($domain->restrict_to_ou) {
            return $matched->whereIn('action', ['allow', 'assign'])->isNotEmpty();
        }

        return true;
    }

    private function applyAttributeMaps(Connection $conn, LdapDomain $domain, string $userDn, User $user): void
    {
        $maps = $domain->attributeMaps;
        if ($maps->isEmpty()) {
            return;
        }

        $attrs = $maps->pluck('ldap_attr')->unique()->values()->toArray();

        try {
            $results = $conn->query()->setDn($userDn)->select($attrs)->get();
            if (empty($results)) {
                return;
            }

            $entry = $results[0];
            $changes = [];

            foreach ($maps as $map) {
                $raw = $entry[strtolower($map->ldap_attr)] ?? null;
                $value = is_array($raw) ? ($raw[0] ?? null) : $raw;

                if ($value === null || $value === '') {
                    continue;
                }

                $current = $user->{$map->local_field};
                if ($map->overwrite || $current === null || $current === '') {
                    $changes[$map->local_field] = $value;
                }
            }

            if (! empty($changes)) {
                $user->forceFill($changes)->save();
            }
        } catch (\Throwable $e) {
            logger()->warning('[LDAP] attribute mapping failed', ['dn' => $userDn, 'error' => $e->getMessage()]);
        }
    }

    private function applyOuAssignments(Connection $conn, LdapDomain $domain, string $userDn, User $user): void
    {
        $assignRules = $domain->ouRules
            ->where('action', 'assign')
            ->filter(fn ($r) => $this->dnMatchesOu($userDn, $r->ou_dn)
                && $this->matchesExtraFilter($conn, $domain, $userDn, $r->extra_filter));

        if ($assignRules->isEmpty()) {
            return;
        }

        $changes = [];
        foreach ($assignRules as $rule) {
            if ($rule->group_id !== null) {
                $changes['GP_ID'] = $rule->group_id;
            }
            if ($rule->section_id !== null) {
                $changes['P_SECTION'] = $rule->section_id;
            }
            if ($rule->role_id !== null) {
                ObUserAssignment::firstOrCreate([
                    'person_id' => $user->P_ID,
                    'group_id' => $rule->role_id,
                    'section_id' => $rule->section_id ?? 0,
                ]);
            }
        }

        if (! empty($changes)) {
            $user->forceFill($changes)->save();
        }
    }

    private function dnMatchesOu(string $userDn, string $ouDn): bool
    {
        $dn = strtolower(trim($userDn));
        $ou = strtolower(trim($ouDn));

        return $dn === $ou
            || str_ends_with($dn, ','.$ou);
    }

    private function matchesExtraFilter(Connection $conn, LdapDomain $domain, string $userDn, ?string $filter): bool
    {
        if ($filter === null || $filter === '') {
            return true;
        }

        try {
            $results = $conn->query()->setDn($userDn)->rawFilter($filter)->select(['dn'])->get();

            return ! empty($results);
        } catch (\Throwable) {
            return true;
        }
    }

    private function registerConnection(LdapDomain $domain): Connection
    {
        $name = 'ob_domain_'.$domain->id;
        if (! Container::hasConnection($name)) {
            Container::addConnection(new Connection($domain->toConnectionConfig()), $name);
        }

        return Container::getConnection($name);
    }

    private function authenticateOnLegacy(string $login, string $password): bool
    {
        try {
            return config('ldap.auth_method') === 'upn'
                ? $this->legacyUpn($login, $password)
                : $this->legacyBind($login, $password);
        } catch (LdapRecordException $e) {
            logger()->warning('[LDAP] legacy auth failed', ['login' => $login, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private function legacyBind(string $login, string $password): bool
    {
        $conn = Container::getDefaultConnection();
        $rawFilter = config('ldap.user_filter', '(&(objectClass=person)(|(uid={login})(mail={login})))');
        $filter = str_replace('{login}', ldap_escape($login, '', LDAP_ESCAPE_FILTER), $rawFilter);

        $results = $conn->query()->rawFilter($filter)->setDn(config('ldap.connections.default.base_dn', ''))->select(['dn'])->get();
        if (empty($results)) {
            return false;
        }

        $userDn = $results[0]['dn'] ?? null;
        if (! is_string($userDn) || $userDn === '') {
            return false;
        }

        return $conn->auth()->attempt($userDn, $password);
    }

    private function legacyUpn(string $login, string $password): bool
    {
        $bindDn = $login.(string) config('ldap.upn_suffix', '');

        return Container::getDefaultConnection()->auth()->attempt($bindDn, $password);
    }
}
