<?php

use App\Models\User;
use App\Services\PermissionResolver;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Pure-logic test of the full-ACL resolver. A subclass replaces the DB-backed
 * lookups with in-memory fixtures so the resolution algorithm can be exercised
 * without a database. Public fixture properties can be mutated per test.
 *
 * Fixture tree:   1 (root) ──▶ 2 ──▶ 3
 * Groups:         user is in global group 10 → allows {3, 53}
 * Roles:          role 100 attached to section 2 → allows {42}
 * Ceilings:       section 1 denies 53 ; section 3 denies 42
 * (group denies and user overrides are empty unless a test sets them)
 */
function fakeResolver()
{
    return new class extends PermissionResolver
    {
        public array $tree = [1 => 0, 2 => 1, 3 => 2];

        public array $denied = [1 => [53], 3 => [42]];

        public array $groupFeat = [10 => [3, 53], 100 => [42]];

        public array $groupDenied = [];

        public array $groupIds = [10];

        public array $roleRows = [['section_id' => 2, 'group_id' => 100]];

        public array $userPerms = [];

        public function sectionChain(?int $sId): array
        {
            $chain = [];
            while ($sId !== null && $sId > 0) {
                $chain[] = $sId;
                $sId = $this->tree[$sId] ?? null;
            }

            return $chain;
        }

        protected function isBlocked(User $user): bool
        {
            return false;
        }

        protected function userGroupIds(User $user): array
        {
            return $this->groupIds;
        }

        protected function sectionDenied(int $sId): array
        {
            return $this->denied[$sId] ?? [];
        }

        protected function groupFeatures(int $groupId): array
        {
            return $this->groupFeat[$groupId] ?? [];
        }

        protected function groupDeniedFeatures(int $groupId): array
        {
            return $this->groupDenied[$groupId] ?? [];
        }

        protected function userRoleAssignments(User $user): array
        {
            return array_map(fn ($r) => (object) $r, $this->roleRows);
        }

        protected function userPermissionRows(User $user): array
        {
            return array_map(fn ($r) => (object) $r, $this->userPerms);
        }
    };
}

/**
 * Build the fixture user — only P_ID and GP_ID matter to the resolver.
 */
function resolverFakeUser(): User
{
    return (new User)->forceFill(['P_ID' => 1, 'GP_ID' => 10]);
}

// ── Grant resolution ─────────────────────────────────────────────────────────

test('a global group grant is effective when no section denies it', function () {
    expect(fakeResolver()->allows(resolverFakeUser(), 3, 2))->toBeTrue();
});

test('a role grant is effective in its attached section', function () {
    expect(fakeResolver()->allows(resolverFakeUser(), 42, 2))->toBeTrue();
});

test('a role grant cascades to a child section', function () {
    // role is attached to section 2; section 3 is a child of 2, but 3 denies 42.
    expect(fakeResolver()->allows(resolverFakeUser(), 42, 3))->toBeFalse();
});

test('a role does not apply when the active section is a parent of its attach section', function () {
    // role attached to section 2 must not grant when acting in the parent (1).
    expect(fakeResolver()->allows(resolverFakeUser(), 42, 1))->toBeFalse();
});

// ── Ceiling deny-lists ───────────────────────────────────────────────────────

test('a section deny-list blocks a grant for that section', function () {
    // section 3 denies feature 42 even though the role would grant it.
    expect(fakeResolver()->allows(resolverFakeUser(), 42, 3))->toBeFalse();
});

test('a root denial cascades to every descendant section', function () {
    // group 10 grants 53, but root (1) denies it → blocked everywhere below.
    expect(fakeResolver()->allows(resolverFakeUser(), 53, 2))->toBeFalse();
    expect(fakeResolver()->allows(resolverFakeUser(), 53, 3))->toBeFalse();
});

// ── Role filtering & aggregation ─────────────────────────────────────────────

test('a role filter narrows roles but keeps groups applied', function () {
    $r = fakeResolver();
    // filtering to a different role drops the role-granted feature…
    expect($r->allows(resolverFakeUser(), 42, 2, 999))->toBeFalse();
    // …but the matching role still grants it…
    expect($r->allows(resolverFakeUser(), 42, 2, 100))->toBeTrue();
    // …and a group-granted feature stays effective under any role filter.
    expect($r->allows(resolverFakeUser(), 3, 2, 999))->toBeTrue();
});

test('effectiveDenied unions the whole ancestor chain', function () {
    expect(fakeResolver()->effectiveDenied(3))->toEqualCanonicalizing([53, 42]);
});

test('effectiveFeatureIds returns grants minus ceiling denials', function () {
    // group {3,53} + role {42} in section 2, minus root deny {53} → {3, 42}.
    expect(fakeResolver()->effectiveFeatureIds(resolverFakeUser(), 2))->toEqualCanonicalizing([3, 42]);
});

// ── Group/role explicit deny (tier 4) ─────────────────────────────────────────

test('a group deny overrides an allow from another held group', function () {
    $r = fakeResolver();
    $r->groupIds = [10, 11];        // 10 allows 3 …
    $r->groupDenied = [11 => [3]];  // … 11 denies it
    expect($r->allows(resolverFakeUser(), 3, 2))->toBeFalse();
});

test('a role deny only applies inside its section scope', function () {
    $r = fakeResolver();
    $r->roleRows = [['section_id' => 2, 'group_id' => 101]];
    $r->groupDenied = [101 => [3]];
    // acting in section 2 (role in scope): the role deny beats the group allow.
    expect($r->allows(resolverFakeUser(), 3, 2))->toBeFalse();
    // acting in the parent (1): the section-2 role is out of scope → group allow stands.
    expect($r->allows(resolverFakeUser(), 3, 1))->toBeTrue();
});

// ── Per-person overrides (tiers 1 & 2, most specific) ─────────────────────────

test('a user deny overrides a group allow', function () {
    $r = fakeResolver();
    $r->userPerms = [['section_id' => 0, 'feature_id' => 3, 'effect' => 'deny']];
    expect($r->allows(resolverFakeUser(), 3, 2))->toBeFalse();
});

test('a user allow overrides a section ceiling deny', function () {
    $r = fakeResolver();
    // 53 is denied at the root ceiling, but a global user-allow wins.
    $r->userPerms = [['section_id' => 0, 'feature_id' => 53, 'effect' => 'allow']];
    expect($r->allows(resolverFakeUser(), 53, 2))->toBeTrue();
});

test('a section-scoped user override only applies in that section subtree', function () {
    $r = fakeResolver();
    $r->userPerms = [['section_id' => 3, 'feature_id' => 3, 'effect' => 'deny']];
    // section 2 chain = [2,1] → override (section 3) out of scope → group allow stands.
    expect($r->allows(resolverFakeUser(), 3, 2))->toBeTrue();
    // section 3 chain = [3,2,1] → override in scope → denied.
    expect($r->allows(resolverFakeUser(), 3, 3))->toBeFalse();
});

test('a user deny beats a user allow for the same feature', function () {
    $r = fakeResolver();
    $r->userPerms = [
        ['section_id' => 0, 'feature_id' => 3, 'effect' => 'allow'],
        ['section_id' => 0, 'feature_id' => 3, 'effect' => 'deny'],
    ];
    expect($r->allows(resolverFakeUser(), 3, 2))->toBeFalse();
});

test('effectiveFeatureIds includes a user-allowed feature no group grants', function () {
    $r = fakeResolver();
    $r->userPerms = [['section_id' => 0, 'feature_id' => 77, 'effect' => 'allow']];
    expect($r->effectiveFeatureIds(resolverFakeUser(), 2))->toEqualCanonicalizing([3, 42, 77]);
});
