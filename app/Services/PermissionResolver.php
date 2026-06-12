<?php

namespace App\Services;

use App\Models\ObGroup;
use App\Models\ObGroupPermission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Full ACL with groups — section-scoped permission resolution with explicit
 * allow *and* deny at every tier.
 *
 * A feature (F_ID) is decided for a user in a section by the first matching
 * rule (most specific wins):
 *
 *   1. user deny        — a per-person override refuses it            → DENY
 *   2. user allow       — a per-person override grants it             → ALLOW
 *   3. section deny     — any section in the chain caps it (ceiling)  → DENY
 *   4. group/role deny  — a held group/role explicitly refuses it     → DENY
 *   5. group/role allow — a held group/role grants it                 → ALLOW
 *   6. (nothing grants it)                                            → DENY
 *
 * "In scope" for a user-override or role row means section_id ≤ 0 (global,
 * inherited everywhere) or a section in the active chain (section + ancestors).
 * Global groups (ob_personnel_group) always apply. Within a tier, deny wins.
 *
 * Registered as a singleton so the per-request lookups below are memoized.
 */
class PermissionResolver
{
    private const ALLOW = 'allow';

    private const DENY = 'deny';

    /** @var array<int,int[]> section id => ancestor chain (self first, root last) */
    private array $chainCache = [];

    /** @var array<int,int[]> section id => F_IDs denied at that section (deny-list) */
    private array $deniedCache = [];

    /** @var array<int,int[]> group id => allow-granted F_IDs */
    private array $groupFeatureCache = [];

    /** @var array<int,int[]> group id => deny-granted F_IDs */
    private array $groupDeniedCache = [];

    /** @var array<int,array<object>> person id => role assignment rows */
    private array $roleCache = [];

    /** @var array<int,array<object>> person id => user-override rows */
    private array $userPermCache = [];

    /**
     * Does the user have feature $fid, evaluated in section $sId under an
     * optional single-role filter? Pure (no session) — used by tests and the
     * "Mes droits" preview.
     */
    public function allows(User $user, int $fid, ?int $sId = null, ?int $roleFilter = null): bool
    {
        if ($this->isBlocked($user)) {
            return false;
        }

        $chain = $this->sectionChain($sId);

        // 1 & 2 — per-person override is the most specific tier.
        $userEffect = $this->userEffect($user, $fid, $chain);
        if ($userEffect === self::DENY) {
            return false;
        }
        if ($userEffect === self::ALLOW) {
            return true;
        }

        // 3 — section ceiling.
        if (! $this->ceilingAllows($fid, $chain)) {
            return false;
        }

        // 4 & 5 — group/role grants (a deny on any held principal wins).
        return $this->groupRoleEffect($user, $fid, $chain, $roleFilter) === self::ALLOW;
    }

    /**
     * Every feature id effective for the user in the given context. Used by the
     * "Mes droits" screen. Resolved through {@see allows()} so the precedence
     * is defined in exactly one place.
     *
     * @return int[]
     */
    public function effectiveFeatureIds(User $user, ?int $sId = null, ?int $roleFilter = null): array
    {
        if ($this->isBlocked($user)) {
            return [];
        }

        $chain = $this->sectionChain($sId);

        // Candidates = anything a held group/role or the user could grant.
        $candidates = [];
        foreach ($this->userGroupIds($user) as $gid) {
            $candidates = array_merge($candidates, $this->groupFeatures($gid));
        }
        foreach ($this->userRoleAssignments($user) as $a) {
            if ($roleFilter !== null && (int) $a->group_id !== $roleFilter) {
                continue;
            }
            if (! $this->appliesInChain($this->rowSection($a), $chain)) {
                continue;
            }
            $candidates = array_merge($candidates, $this->groupFeatures((int) $a->group_id));
        }
        foreach ($this->userPermissionRows($user) as $p) {
            if ($p->effect === self::ALLOW && $this->appliesInChain($this->rowSection($p), $chain)) {
                $candidates[] = (int) $p->feature_id;
            }
        }

        $candidates = array_values(array_unique($candidates));

        return array_values(array_filter($candidates, fn (int $fid) => $this->allows($user, $fid, $sId, $roleFilter)));
    }

    /**
     * Every feature denied at a section once its ancestor chain is taken into
     * account (the union of all deny-list rows from the section up to the root).
     * Used by the admin "Plafonds" screen to lock cells refused by a parent.
     *
     * @return int[]
     */
    public function effectiveDenied(?int $sId): array
    {
        $denied = [];
        foreach ($this->sectionChain($sId) as $node) {
            $denied = array_merge($denied, $this->sectionDenied($node));
        }

        return array_values(array_unique($denied));
    }

    /** Active section id from session, falling back to the user's home section. */
    public function activeSectionId(User $user): ?int
    {
        // When multi-site is off the switcher is hidden; ignore any stale
        // session value so permissions don't unexpectedly narrow mid-request.
        try {
            $multiSite = app(FeatureService::class)->isEnabled('multi_site');
        } catch (\Throwable) {
            $multiSite = true;
        }

        if ($multiSite) {
            $s = session('hab.section');
            if ($s !== null && $s !== '') {
                return (int) $s;
            }
        }

        return $user->P_SECTION !== null ? (int) $user->P_SECTION : null;
    }

    /** Active single-role filter from session, or null for "all roles". */
    public function activeRoleId(User $user): ?int
    {
        $r = session('hab.role');

        return ($r !== null && $r !== '') ? (int) $r : null;
    }

    /**
     * The section the user EXPLICITLY chose via the navbar switcher.
     * Unlike activeSectionId() this never falls back to P_SECTION, so null
     * means "no restriction chosen — show everything accessible."
     */
    public function chosenSectionId(): ?int
    {
        try {
            if (! app(FeatureService::class)->isEnabled('multi_site')) {
                return null;
            }
        } catch (\Throwable) {
        }

        $s = session('hab.section');

        return ($s !== null && $s !== '') ? (int) $s : null;
    }

    /**
     * Roles the user holds in the given section or one of its ancestors, for
     * the navbar role switcher. Each row carries whether it is inherited from a
     * parent section.
     */
    public function userRoles(User $user, ?int $sId): Collection
    {
        return DB::table('ob_user_assignment as a')
            ->join('ob_group as g', 'g.id', '=', 'a.group_id')
            ->where('a.person_id', (int) $user->P_ID)
            ->where('g.kind', ObGroup::KIND_ROLE)
            ->orderBy('g.ordering')
            ->get(['g.id', 'g.name'])
            ->map(function ($r) {
                $r->inherited = false;

                return $r;
            })
            ->unique('id')
            ->values();
    }

    // ── internals ─────────────────────────────────────────────────────────────

    protected function isBlocked(User $user): bool
    {
        return (int) $user->GP_ID === -1 || (int) ($user->GP_ID2 ?? $user->GP_ID) === -1;
    }

    /** @return int[] group ids from ob_personnel_group (excludes the blocked sentinel -1) */
    protected function userGroupIds(User $user): array
    {
        return DB::table('ob_personnel_group')
            ->where('person_id', (int) $user->P_ID)
            ->where('group_id', '!=', -1)
            ->pluck('group_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * Ancestor chain for a section (self first, root last). Empty when no
     * section context is given.
     *
     * @return int[]
     */
    public function sectionChain(?int $sId): array
    {
        if ($sId === null || $sId <= 0) {
            return [];
        }
        if (isset($this->chainCache[$sId])) {
            return $this->chainCache[$sId];
        }

        $chain = [];
        $current = $sId;
        $guard = 0;
        while ($current !== null && $current > 0 && $guard++ < 50 && ! in_array($current, $chain, true)) {
            $chain[] = $current;
            $parent = DB::table('section')->where('S_ID', $current)->value('S_PARENT');
            $current = $parent !== null ? (int) $parent : null;
        }

        return $this->chainCache[$sId] = $chain;
    }

    /**
     * Is a section-scoped row (role or user override) active in the chain?
     * section ≤ 0 means global (everywhere); an empty chain means no section
     * context, so every row applies (used by the "all sections" preview).
     *
     * @param  int[]  $chain
     */
    private function appliesInChain(?int $sid, array $chain): bool
    {
        if ($sid === null || $sid <= 0) {
            return true;
        }
        if ($chain === []) {
            return true;
        }

        return in_array($sid, $chain, true);
    }

    /** Read a row's section_id (defaults to global when the column is absent). */
    private function rowSection(object $row): ?int
    {
        return isset($row->section_id) ? (int) $row->section_id : null;
    }

    /**
     * Effect of the per-person override tier for a feature, or null when the
     * user has no in-scope override. Deny wins within the tier.
     */
    private function userEffect(User $user, int $fid, array $chain): ?string
    {
        $allow = false;
        foreach ($this->userPermissionRows($user) as $p) {
            if ((int) $p->feature_id !== $fid || ! $this->appliesInChain($this->rowSection($p), $chain)) {
                continue;
            }
            if ($p->effect === self::DENY) {
                return self::DENY;
            }
            $allow = true;
        }

        return $allow ? self::ALLOW : null;
    }

    /**
     * Effect of the group/role tier for a feature, or null when no held
     * group/role mentions it. Global groups always apply; roles apply in scope.
     * A single deny on any held principal wins over allows from the others.
     *
     * @param  int[]  $chain
     */
    private function groupRoleEffect(User $user, int $fid, array $chain, ?int $roleFilter): ?string
    {
        $allow = false;

        foreach ($this->userGroupIds($user) as $gid) {
            if (in_array($fid, $this->groupDeniedFeatures($gid), true)) {
                return self::DENY;
            }
            if (in_array($fid, $this->groupFeatures($gid), true)) {
                $allow = true;
            }
        }

        foreach ($this->userRoleAssignments($user) as $a) {
            if ($roleFilter !== null && (int) $a->group_id !== $roleFilter) {
                continue;
            }
            if (! $this->appliesInChain($this->rowSection($a), $chain)) {
                continue;
            }
            $gid = (int) $a->group_id;
            if (in_array($fid, $this->groupDeniedFeatures($gid), true)) {
                return self::DENY;
            }
            if (in_array($fid, $this->groupFeatures($gid), true)) {
                $allow = true;
            }
        }

        return $allow ? self::ALLOW : null;
    }

    /** A feature is capped only if no section in the chain denies it. @param int[] $chain */
    protected function ceilingAllows(int $fid, array $chain): bool
    {
        foreach ($chain as $node) {
            if (in_array($fid, $this->sectionDenied($node), true)) {
                return false;
            }
        }

        return true;
    }

    /** Features explicitly denied at one section (deny-list). @return int[] */
    protected function sectionDenied(int $sId): array
    {
        if (isset($this->deniedCache[$sId])) {
            return $this->deniedCache[$sId];
        }

        return $this->deniedCache[$sId] = DB::table('ob_section_permission')
            ->where('section_id', $sId)
            ->pluck('feature_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /** Features a group/role grants (effect = allow). @return int[] */
    protected function groupFeatures(int $groupId): array
    {
        if (isset($this->groupFeatureCache[$groupId])) {
            return $this->groupFeatureCache[$groupId];
        }

        return $this->groupFeatureCache[$groupId] = DB::table('ob_group_permission')
            ->where('group_id', $groupId)
            ->where('effect', ObGroupPermission::EFFECT_ALLOW)
            ->pluck('feature_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /** Features a group/role explicitly refuses (effect = deny). @return int[] */
    protected function groupDeniedFeatures(int $groupId): array
    {
        if (isset($this->groupDeniedCache[$groupId])) {
            return $this->groupDeniedCache[$groupId];
        }

        return $this->groupDeniedCache[$groupId] = DB::table('ob_group_permission')
            ->where('group_id', $groupId)
            ->where('effect', ObGroupPermission::EFFECT_DENY)
            ->pluck('feature_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /** @return array<object> */
    protected function userRoleAssignments(User $user): array
    {
        $pid = (int) $user->P_ID;
        if (isset($this->roleCache[$pid])) {
            return $this->roleCache[$pid];
        }

        return $this->roleCache[$pid] = DB::table('ob_user_assignment')
            ->where('person_id', $pid)
            ->get(['section_id', 'group_id'])
            ->all();
    }

    /** Per-person override rows (section_id, feature_id, effect). @return array<object> */
    protected function userPermissionRows(User $user): array
    {
        $pid = (int) $user->P_ID;
        if (isset($this->userPermCache[$pid])) {
            return $this->userPermCache[$pid];
        }

        return $this->userPermCache[$pid] = DB::table('ob_user_permission')
            ->where('person_id', $pid)
            ->get(['section_id', 'feature_id', 'effect'])
            ->all();
    }
}
