<?php

use App\Models\User;
use App\Services\PermissionResolver;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * A resolver with the correct sectionChain that includes the org root (S_ID=0).
 *
 * The existing fakeResolver in PermissionResolverTest stops at $sId > 0 and
 * never exercises section 0 — this variant mirrors the real implementation's
 * `$current >= 0` loop so root-section ceiling cascades can be tested.
 *
 * Tree:   0 (org root, S_PARENT=-1) ──▶ 1 ──▶ 2
 * Groups: user is in global group 10 → allows {99, 77}
 */
function rootFakeResolver()
{
    return new class extends PermissionResolver
    {
        // Mirrors the real section table: key = S_ID, value = S_PARENT.
        public array $tree = [0 => -1, 1 => 0, 2 => 1];

        public array $denied = [];

        public array $groupFeat = [10 => [99, 77]];

        public array $groupIds = [10];

        public function sectionChain(?int $sId): array
        {
            if ($sId === null || $sId < 0) {
                return [];
            }

            $chain = [];
            $current = $sId;
            $guard = 0;
            while ($current !== null && $current >= 0 && $guard++ < 50 && ! in_array($current, $chain, true)) {
                $chain[] = $current;
                $parent = $this->tree[$current] ?? null;
                $current = $parent;
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
            return [];
        }

        protected function userRoleAssignments(User $user): array
        {
            return [];
        }

        protected function userPermissionRows(User $user): array
        {
            return [];
        }
    };
}

function rootFakeUser()
{
    return (new User)->forceFill(['P_ID' => 1, 'GP_ID' => 10]);
}

// ── sectionChain with root ─────────────────────────────────────────────────

test('sectionChain for the org root returns [0]', function () {
    expect(rootFakeResolver()->sectionChain(0))->toBe([0]);
});

test('sectionChain for a child section includes the root', function () {
    expect(rootFakeResolver()->sectionChain(1))->toBe([1, 0]);
});

test('sectionChain for a grandchild section includes parent and root', function () {
    expect(rootFakeResolver()->sectionChain(2))->toBe([2, 1, 0]);
});

test('sectionChain returns empty for null or negative values', function () {
    $r = rootFakeResolver();
    expect($r->sectionChain(null))->toBe([]);
    expect($r->sectionChain(-1))->toBe([]);
});

// ── Root ceiling cascade ─────────────────────────────────────────────────────

test('a root ceiling deny blocks the feature in every descendant section', function () {
    $r = rootFakeResolver();
    $r->denied = [0 => [99]];
    $u = rootFakeUser();

    expect($r->allows($u, 99, 0))->toBeFalse();
    expect($r->allows($u, 99, 1))->toBeFalse();
    expect($r->allows($u, 99, 2))->toBeFalse();
    // unrelated feature is still granted
    expect($r->allows($u, 77, 1))->toBeTrue();
});

test('a root ceiling deny does not apply when no section context is given', function () {
    $r = rootFakeResolver();
    $r->denied = [0 => [99]];
    // null section → chain is empty → no ceiling consulted
    expect($r->allows(rootFakeUser(), 99, null))->toBeTrue();
});

test('a child section ceiling deny does not cascade up to the root', function () {
    $r = rootFakeResolver();
    $r->denied = [1 => [99]];
    $u = rootFakeUser();

    expect($r->allows($u, 99, 0))->toBeTrue();  // root: no deny
    expect($r->allows($u, 99, 1))->toBeFalse(); // child: ceiling hit
    expect($r->allows($u, 99, 2))->toBeFalse(); // grandchild: inherits from 1
});

// ── effectiveDenied ───────────────────────────────────────────────────────────

test('effectiveDenied on a child section unions root and own deny-lists', function () {
    $r = rootFakeResolver();
    $r->denied = [0 => [99], 1 => [55]];

    expect($r->effectiveDenied(1))->toEqualCanonicalizing([99, 55]);
});

test('effectiveDenied on the org root returns only root-level entries', function () {
    $r = rootFakeResolver();
    $r->denied = [0 => [99], 1 => [55]]; // section 1 deny is not in root chain

    expect($r->effectiveDenied(0))->toEqualCanonicalizing([99]);
});

test('effectiveDenied for a grandchild unions all three levels', function () {
    $r = rootFakeResolver();
    $r->denied = [0 => [10], 1 => [20], 2 => [30]];

    expect($r->effectiveDenied(2))->toEqualCanonicalizing([10, 20, 30]);
});
