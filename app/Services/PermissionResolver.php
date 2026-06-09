<?php

namespace App\Services;

use App\Models\ObGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Section-scoped, ceiling-based permission resolution.
 *
 * A permission (F_ID) is effective for a user in a section when:
 *   1. the section ceiling allows it — every section in the chain
 *      (section + all ancestors) that defines an explicit ceiling must
 *      include the feature (parent caps child); AND
 *   2. the user holds a grant for it — either a global group
 *      (pompier.GP_ID / GP_ID2, always applied) or a role assigned in the
 *      active section or one of its ancestors.
 *
 * Registered as a singleton so the per-request lookups below are memoized.
 */
class PermissionResolver
{
    /** @var array<int,int[]> section id => ancestor chain (self first, root last) */
    private array $chainCache = [];

    /** @var array<int,int[]> section id => F_IDs denied at that section (deny-list) */
    private array $deniedCache = [];

    /** @var array<int,int[]> group id => granted F_IDs */
    private array $groupFeatureCache = [];

    /** @var array<int,array<object>> person id => role assignment rows */
    private array $roleCache = [];

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

        if (! $this->ceilingAllows($fid, $chain)) {
            return false;
        }

        // Global groups are always applied (a role filter narrows roles, not groups).
        foreach ($this->userGroupIds($user) as $gid) {
            if (in_array($fid, $this->groupFeatures($gid), true)) {
                return true;
            }
        }

        // Roles held in the active section or an ancestor of it.
        foreach ($this->userRoleAssignments($user) as $a) {
            if ($roleFilter !== null && (int) $a->group_id !== $roleFilter) {
                continue;
            }
            if ($chain !== [] && ! in_array((int) $a->section_id, $chain, true)) {
                continue;
            }
            if (in_array($fid, $this->groupFeatures((int) $a->group_id), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Every feature id effective for the user in the given context. Used by the
     * "Mes droits" screen.
     *
     * @return int[]
     */
    public function effectiveFeatureIds(User $user, ?int $sId = null, ?int $roleFilter = null): array
    {
        if ($this->isBlocked($user)) {
            return [];
        }

        $chain = $this->sectionChain($sId);
        $granted = [];

        foreach ($this->userGroupIds($user) as $gid) {
            $granted = array_merge($granted, $this->groupFeatures($gid));
        }
        foreach ($this->userRoleAssignments($user) as $a) {
            if ($roleFilter !== null && (int) $a->group_id !== $roleFilter) {
                continue;
            }
            if ($chain !== [] && ! in_array((int) $a->section_id, $chain, true)) {
                continue;
            }
            $granted = array_merge($granted, $this->groupFeatures((int) $a->group_id));
        }

        $granted = array_values(array_unique($granted));

        return array_values(array_filter($granted, fn (int $fid) => $this->ceilingAllows($fid, $chain)));
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
        $s = session('hab.section');
        if ($s !== null && $s !== '') {
            return (int) $s;
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
     * Sections the user can act in (home section + every section where they
     * hold a role), for the navbar section switcher.
     */
    public function userSections(User $user): Collection
    {
        $ids = DB::table('ob_user_assignment')
            ->where('person_id', (int) $user->P_ID)
            ->pluck('section_id')
            ->map(fn ($v) => (int) $v);

        if ($user->P_SECTION) {
            $ids->push((int) $user->P_SECTION);
        }

        $ids = $ids->unique()->filter(fn ($v) => $v > 0)->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return DB::table('section')
            ->whereIn('S_ID', $ids->all())
            ->orderBy('S_PARENT')
            ->orderBy('S_DESCRIPTION')
            ->get(['S_ID', 'S_PARENT', 'S_DESCRIPTION'])
            ->each(fn ($s) => $s->S_DESCRIPTION = $s->S_DESCRIPTION ?: 'Section '.$s->S_ID);
    }

    /**
     * Roles the user holds in the given section or one of its ancestors, for
     * the navbar role switcher. Each row carries whether it is inherited from a
     * parent section.
     */
    public function userRoles(User $user, ?int $sId): Collection
    {
        $chain = $this->sectionChain($sId);

        $query = DB::table('ob_user_assignment as a')
            ->join('ob_group as g', 'g.id', '=', 'a.group_id')
            ->where('a.person_id', (int) $user->P_ID)
            ->where('g.kind', ObGroup::KIND_ROLE);

        if ($chain !== []) {
            $query->whereIn('a.section_id', $chain);
        }

        return $query
            ->orderBy('g.ordering')
            ->get(['g.id', 'g.name', 'a.section_id'])
            ->map(function ($r) use ($sId) {
                $r->inherited = (int) $r->section_id !== (int) $sId;

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

    /** @return int[] [GP_ID, GP_ID2] without duplicates */
    protected function userGroupIds(User $user): array
    {
        $gp2 = $user->GP_ID2 ?: $user->GP_ID;

        return array_values(array_unique([(int) $user->GP_ID, (int) $gp2]));
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

    /** A feature is allowed only if no section in the chain denies it. @param int[] $chain */
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

    /** @return int[] */
    protected function groupFeatures(int $groupId): array
    {
        if (isset($this->groupFeatureCache[$groupId])) {
            return $this->groupFeatureCache[$groupId];
        }

        return $this->groupFeatureCache[$groupId] = DB::table('ob_group_permission')
            ->where('group_id', $groupId)
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
}
