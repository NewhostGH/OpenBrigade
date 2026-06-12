<?php

namespace App\Services;

use App\Models\ObDocumentAcl;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Per-object ACL resolution for the document library.
 *
 * The effective rights of a user on a folder or document are the union of the
 * ACEs on the item itself and on every ancestor folder (inheritance), filtered
 * to the ACEs whose principal matches the user (the user, one of their groups
 * or roles, or "everyone"). Within that set an allow grants rights and a deny
 * removes them — **deny wins per-right**. RIGHT_FULL expands to every right.
 *
 * When no ACE applies anywhere in the chain, {@see effectiveRights()} returns
 * null so the caller falls back to the legacy section/type security — making
 * the ACL a strictly additive, backwards-compatible overlay.
 *
 * Registered as a singleton so the per-request lookups below are memoised. The
 * protected DB-access methods are overridable, so the resolver can be unit
 * tested without a database (see DocumentAclResolverTest).
 */
class DocumentAclService implements ServiceInterface
{
    /** @var array<int,array<string,int[]>> person id => principal sets */
    private array $principalCache = [];

    /** @var array<int,int[]> folder id => ancestor chain (self first, root last) */
    private array $chainCache = [];

    /** @var array<string,array<object>> "type:id" => ACE rows */
    private array $aceCache = [];

    /**
     * Effective rights bitmask for a user on a resource, or null when no ACE
     * governs it (caller then falls back to the legacy security).
     */
    public function effectiveRights(User $user, string $resourceType, int $resourceId): ?int
    {
        $aces = $this->chainAces($resourceType, $resourceId);
        if ($aces === []) {
            return null;
        }

        $principals = $this->userPrincipals($user);
        $allow = 0;
        $deny = 0;
        foreach ($aces as $ace) {
            if (! $this->applies($ace, $principals)) {
                continue;
            }
            $mask = ObDocumentAcl::expand((int) $ace->rights);
            if ($ace->effect === ObDocumentAcl::EFFECT_DENY) {
                $deny |= $mask;
            } else {
                $allow |= $mask;
            }
        }

        return $allow & ~$deny;
    }

    /**
     * Warm the ACE cache for many resources in one query (avoids N+1 when the
     * listing resolves rights for every row). Resources with no row are cached
     * as empty so {@see acesFor()} won't re-query them.
     *
     * @param  array<array{0:string,1:int}>  $resources
     */
    public function preload(array $resources): void
    {
        $byType = [];
        foreach ($resources as [$type, $id]) {
            $byType[$type][] = (int) $id;
        }

        foreach ($byType as $type => $ids) {
            $ids = array_values(array_unique($ids));
            foreach ($ids as $id) {
                $this->aceCache[$type.':'.$id] ??= [];
            }
            DB::table('ob_document_acl')
                ->where('resource_type', $type)
                ->whereIn('resource_id', $ids)
                ->get(['resource_id', 'principal_type', 'principal_id', 'effect', 'rights'])
                ->each(function ($r) use ($type) {
                    $this->aceCache[$type.':'.(int) $r->resource_id][] = (object) [
                        'principal_type' => $r->principal_type,
                        'principal_id' => $r->principal_id,
                        'effect' => $r->effect,
                        'rights' => $r->rights,
                    ];
                });
        }
    }

    /**
     * Does the user hold $right on the resource? null = no ACL defined (the
     * caller decides via the legacy rules).
     */
    public function can(User $user, string $resourceType, int $resourceId, int $right): ?bool
    {
        $effective = $this->effectiveRights($user, $resourceType, $resourceId);

        return $effective === null ? null : (($effective & $right) !== 0);
    }

    /**
     * Resources whose ACEs govern (type,id): the item itself plus its folder
     * chain (a document inherits its folder's chain; a folder its ancestors).
     *
     * @return array<array{0:string,1:int}>
     */
    public function inheritanceChain(string $resourceType, int $resourceId): array
    {
        $chain = [];

        if ($resourceType === ObDocumentAcl::TYPE_DOCUMENT) {
            $chain[] = [ObDocumentAcl::TYPE_DOCUMENT, $resourceId];
            $folderId = $this->documentFolderId($resourceId);
        } else {
            $folderId = $resourceId;
        }

        foreach ($this->folderChain($folderId) as $f) {
            $chain[] = [ObDocumentAcl::TYPE_FOLDER, $f];
        }

        return $chain;
    }

    // ── internals ─────────────────────────────────────────────────────────────

    /** @return array<object> all ACEs governing the resource (own + inherited). */
    private function chainAces(string $resourceType, int $resourceId): array
    {
        $aces = [];
        foreach ($this->inheritanceChain($resourceType, $resourceId) as [$type, $id]) {
            foreach ($this->acesFor($type, $id) as $ace) {
                $aces[] = $ace;
            }
        }

        return $aces;
    }

    /** @param array<string,int[]> $principals */
    protected function applies(object $ace, array $principals): bool
    {
        if ($ace->principal_type === 'everyone') {
            return true;
        }

        return in_array((int) $ace->principal_id, $principals[$ace->principal_type] ?? [], true);
    }

    /** @return array<string,int[]> principal type => ids the user satisfies */
    protected function userPrincipals(User $user): array
    {
        $pid = (int) $user->P_ID;
        if (isset($this->principalCache[$pid])) {
            return $this->principalCache[$pid];
        }

        $groups = DB::table('ob_personnel_group')
            ->where('person_id', $pid)->where('group_id', '!=', -1)
            ->pluck('group_id')->map(fn ($v) => (int) $v)->all();
        foreach ([(int) $user->GP_ID, (int) ($user->GP_ID2 ?? 0)] as $g) {
            if ($g > 0) {
                $groups[] = $g;
            }
        }

        $roles = DB::table('ob_user_assignment')
            ->where('person_id', $pid)
            ->pluck('group_id')->map(fn ($v) => (int) $v)->all();

        return $this->principalCache[$pid] = [
            'user' => [$pid],
            'group' => array_values(array_unique($groups)),
            'role' => array_values(array_unique($roles)),
            'everyone' => [0],
        ];
    }

    /** @return int[] folder id chain (self first, root last); empty for 0. */
    protected function folderChain(int $folderId): array
    {
        if ($folderId <= 0) {
            return [];
        }
        if (isset($this->chainCache[$folderId])) {
            return $this->chainCache[$folderId];
        }

        $chain = [];
        $current = $folderId;
        $guard = 0;
        while ($current > 0 && $guard++ < 50 && ! in_array($current, $chain, true)) {
            $chain[] = $current;
            $parent = DB::table('document_folder')->where('DF_ID', $current)->value('DF_PARENT');
            $current = $parent !== null ? (int) $parent : 0;
        }

        return $this->chainCache[$folderId] = $chain;
    }

    protected function documentFolderId(int $docId): int
    {
        return (int) (DB::table('document')->where('D_ID', $docId)->value('DF_ID') ?? 0);
    }

    /** @return array<object> ACE rows for a single resource. */
    protected function acesFor(string $resourceType, int $resourceId): array
    {
        $key = $resourceType.':'.$resourceId;
        if (isset($this->aceCache[$key])) {
            return $this->aceCache[$key];
        }

        return $this->aceCache[$key] = DB::table('ob_document_acl')
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->get(['principal_type', 'principal_id', 'effect', 'rights'])
            ->all();
    }
}
