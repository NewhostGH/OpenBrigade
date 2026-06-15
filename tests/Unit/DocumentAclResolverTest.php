<?php

use App\Models\ObDocumentAcl as Acl;
use App\Models\User;
use App\Services\DocumentAclService;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Pure-logic test of the per-object ACL resolver. A subclass swaps the DB-backed
 * lookups for in-memory fixtures so inheritance + allow/deny can be exercised
 * without a database.
 *
 * Folder tree:   1 (root) ──▶ 2 ──▶ 3 ;   document 100 lives in folder 3.
 * ACEs:
 *   folder 1   : allow group 10  → READ | DOWNLOAD
 *   folder 3   : deny  user  5   → DOWNLOAD
 *   document100: allow role  20  → WRITE
 * User principals: user 5, group 10, role 20.
 */
function aclResolver()
{
    return new class extends DocumentAclService
    {
        /** @var array<string,array<array{principal_type:string,principal_id:int,effect:string,rights:int}>> */
        public array $aces = [
            'folder:1' => [['principal_type' => 'group', 'principal_id' => 10, 'effect' => 'allow', 'rights' => Acl::RIGHT_READ | Acl::RIGHT_DOWNLOAD]],
            'folder:3' => [['principal_type' => 'user', 'principal_id' => 5, 'effect' => 'deny', 'rights' => Acl::RIGHT_DOWNLOAD]],
            'document:100' => [['principal_type' => 'role', 'principal_id' => 20, 'effect' => 'allow', 'rights' => Acl::RIGHT_WRITE]],
        ];

        public array $tree = [1 => 0, 2 => 1, 3 => 2];

        protected function userPrincipals(User $user): array
        {
            return ['user' => [5], 'group' => [10], 'role' => [20], 'everyone' => [0]];
        }

        protected function folderChain(int $folderId): array
        {
            $chain = [];
            while ($folderId > 0) {
                $chain[] = $folderId;
                $folderId = $this->tree[$folderId] ?? 0;
            }

            return $chain;
        }

        protected function documentFolderId(int $docId): int
        {
            return $docId === 100 ? 3 : 0;
        }

        protected function acesFor(string $resourceType, int $resourceId): array
        {
            return array_map(fn ($r) => (object) $r, $this->aces[$resourceType.':'.$resourceId] ?? []);
        }
    };
}

function aclUser(): User
{
    return (new User)->forceFill(['P_ID' => 5, 'GP_ID' => 0]);
}

// ── Resolution ────────────────────────────────────────────────────────────────

test('returns null when no ACE governs the resource', function () {
    expect(aclResolver()->effectiveRights(aclUser(), 'document', 999))->toBeNull();
});

test('a folder ACE is inherited by a nested document', function () {
    // group-10 allow READ|DOWNLOAD on root folder 1 reaches document 100.
    $r = aclResolver();
    expect($r->can(aclUser(), 'document', 100, Acl::RIGHT_READ))->toBeTrue();
});

test('a deny wins over an inherited allow, per right', function () {
    $r = aclResolver();
    // download allowed at folder 1 but denied at folder 3 → denied for doc 100.
    expect($r->can(aclUser(), 'document', 100, Acl::RIGHT_DOWNLOAD))->toBeFalse();
    // read is only allowed, never denied → still granted.
    expect($r->can(aclUser(), 'document', 100, Acl::RIGHT_READ))->toBeTrue();
});

test("the item's own ACE adds rights on top of inheritance", function () {
    // role-20 allow WRITE lives on the document itself.
    expect(aclResolver()->can(aclUser(), 'document', 100, Acl::RIGHT_WRITE))->toBeTrue();
});

test('effective rights combine inherited allows minus denies', function () {
    // READ + DOWNLOAD (folder1) + WRITE (doc) minus DOWNLOAD (folder3) = READ|WRITE.
    expect(aclResolver()->effectiveRights(aclUser(), 'document', 100))
        ->toBe(Acl::RIGHT_READ | Acl::RIGHT_WRITE);
});

test('a right the user was never granted is not held', function () {
    expect(aclResolver()->can(aclUser(), 'document', 100, Acl::RIGHT_DELETE))->toBeFalse();
});

test('RIGHT_FULL expands to every right', function () {
    $r = aclResolver();
    $r->aces['document:100'] = [['principal_type' => 'user', 'principal_id' => 5, 'effect' => 'allow', 'rights' => Acl::RIGHT_FULL]];
    // full control on the doc grants delete/share too (download still denied by folder 3).
    expect($r->can(aclUser(), 'document', 100, Acl::RIGHT_DELETE))->toBeTrue();
    expect($r->can(aclUser(), 'document', 100, Acl::RIGHT_SHARE))->toBeTrue();
    expect($r->can(aclUser(), 'document', 100, Acl::RIGHT_DOWNLOAD))->toBeFalse();
});

test('an everyone ACE applies to any user', function () {
    $r = aclResolver();
    $r->aces['document:100'] = [['principal_type' => 'everyone', 'principal_id' => 0, 'effect' => 'allow', 'rights' => Acl::RIGHT_DELETE]];
    expect($r->can(aclUser(), 'document', 100, Acl::RIGHT_DELETE))->toBeTrue();
});

test('a folder inherits ACEs from its ancestors', function () {
    // folder 3 inherits folder 1's group-10 READ allow.
    expect(aclResolver()->can(aclUser(), 'folder', 3, Acl::RIGHT_READ))->toBeTrue();
});
