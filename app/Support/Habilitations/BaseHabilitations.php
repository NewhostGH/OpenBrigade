<?php

namespace App\Support\Habilitations;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Single source of truth for the canonical habilitation base data.
 *
 * Consumed by BOTH the rebuild migration and the production CoreSeeder so the
 * two never drift. Pure/stateless except for reading the legacy `fonctionnalite`
 * catalog (the permission definitions) to classify it.
 *
 * Permissions are classified on two axes — domain (config|data) and read/write —
 * plus a critical marker (legacy F_FLAG). The classification drives the SEEDED
 * default grants for the four base groups; it is not a runtime enforcement path.
 *
 * @see config/habilitations.php for the reserved ids, classification rules and
 *      the per-organisation-type role definitions.
 */
class BaseHabilitations
{
    /** @var array<int,array{id:int,key:string,label:string,domain:string,is_read:bool,is_critical:bool,category:?string,ordering:int}>|null */
    private ?array $permissionCache = null;

    /**
     * The permission catalog rows (one per legacy fonctionnalite), classified.
     *
     * @return array<int,array{id:int,key:string,label:string,domain:string,is_read:bool,is_critical:bool,category:?string,ordering:int}>
     */
    public function permissions(): array
    {
        if ($this->permissionCache !== null) {
            return $this->permissionCache;
        }

        $configCats = array_map('intval', config('habilitations.config_categories', []));
        $readIds = array_map('intval', config('habilitations.read_features', []));

        $rows = DB::table('fonctionnalite as f')
            ->leftJoin('type_fonctionnalite as tf', 'tf.TF_ID', '=', 'f.TF_ID')
            ->orderBy('f.TF_ID')->orderBy('f.F_ID')
            ->get(['f.F_ID', 'f.F_LIBELLE', 'f.TF_ID', 'f.F_FLAG', 'tf.TF_DESCRIPTION as category']);

        $out = [];
        $usedKeys = [];
        foreach ($rows as $r) {
            $id = (int) $r->F_ID;
            $key = Str::slug((string) $r->F_LIBELLE) ?: 'feature-'.$id;
            if (isset($usedKeys[$key])) {
                $key .= '-'.$id; // guarantee uniqueness for duplicate labels
            }
            $usedKeys[$key] = true;

            $out[$id] = [
                'id' => $id,
                'key' => $key,
                'label' => (string) $r->F_LIBELLE,
                'domain' => in_array((int) $r->TF_ID, $configCats, true) ? 'config' : 'data',
                'is_read' => in_array($id, $readIds, true),
                'is_critical' => (int) $r->F_FLAG === 1,
                'category' => $r->category,
                'ordering' => $id,
            ];
        }

        return $this->permissionCache = $out;
    }

    /**
     * Definitions for the four base groups (id, name, kind, usage, ordering).
     *
     * @return array<int,array{id:int,name:string,usage:string,ordering:int,default:string}>
     */
    public function baseGroups(): array
    {
        $out = [];
        foreach ((array) config('habilitations.base_groups', []) as $id => $def) {
            $out[(int) $id] = [
                'id' => (int) $id,
                'name' => $def['name'],
                'usage' => $def['usage'] ?? 'internes',
                'ordering' => (int) ($def['ordering'] ?? 50),
                'default' => $def['default'],
            ];
        }

        return $out;
    }

    /** @return int[] reserved base-group ids (protected from edit/delete). */
    public function baseGroupIds(): array
    {
        return array_map('intval', array_keys((array) config('habilitations.base_groups', [])));
    }

    /** @return int[] all ids the admin UI must protect (base groups + block sentinel). */
    public function protectedGroupIds(): array
    {
        return array_values(array_unique(array_merge(
            $this->baseGroupIds(),
            [(int) config('habilitations.block_group_id', -1)],
        )));
    }

    /**
     * The seeded default F_IDs granted to a base group, derived from the
     * classification. Admins edit freely afterwards.
     *
     * @return int[]
     */
    public function defaultGrantsFor(string $default): array
    {
        $perms = $this->permissions();

        $pick = fn (callable $keep): array => array_values(array_map(
            fn (array $p) => $p['id'],
            array_filter($perms, $keep),
        ));

        return match ($default) {
            // Everything except critical (delete personnel, security, admin
            // technique, delete data, organigramme…) — critical is super-admin only.
            'admin' => $pick(fn ($p) => ! $p['is_critical']),
            // Look-but-don't-touch: the read-oriented permissions.
            'auditor' => $pick(fn ($p) => $p['is_read']),
            // Operational data domain (no config), non-critical.
            'user' => $pick(fn ($p) => $p['domain'] === 'data' && ! $p['is_critical']),
            // Minimal read of the data domain.
            'guest' => $pick(fn ($p) => $p['domain'] === 'data' && $p['is_read']),
            default => [],
        };
    }

    /**
     * Section roles for one organisation type, with their resolved F_ID grants.
     *
     * @return array<int,array{name:string,org_type:int,feature_ids:int[]}>
     */
    public function rolesForType(int $orgType): array
    {
        $archetypes = (array) config('habilitations.role_archetypes', []);
        $defs = config("habilitations.roles_by_org_type.{$orgType}")
            ?? config('habilitations.roles_by_org_type.0', []);

        $out = [];
        foreach ($defs as $def) {
            $ids = array_map('intval', $archetypes[$def['archetype']] ?? []);
            $out[] = [
                'name' => $def['name'],
                'org_type' => $orgType,
                'feature_ids' => array_values(array_unique($ids)),
            ];
        }

        return $out;
    }

    /** @return array<int,int> legacy base-group id => new base-group id. */
    public function legacyGroupMap(): array
    {
        $out = [];
        foreach ((array) config('habilitations.legacy_group_map', []) as $from => $to) {
            $out[(int) $from] = (int) $to;
        }

        return $out;
    }

    /** Deterministic, stable id for a seeded role (non-colliding with base groups / legacy ≤ 900). */
    public function roleId(int $orgType, int $index): int
    {
        return 1000 + $orgType * 20 + $index;
    }

    /**
     * Write the canonical base data (permission catalog, base groups + default
     * grants, per-organisation-type roles). Idempotent — safe to re-run. Shared
     * by the rebuild migration and the production CoreSeeder.
     *
     * @return array<int,array<string,int>> orgType => [archetype => role id],
     *                                      used by the migration to remap legacy roles.
     */
    public function seed(): array
    {
        $now = now();

        // Permission catalog.
        foreach ($this->permissions() as $p) {
            DB::table('ob_permission')->updateOrInsert(
                ['id' => $p['id']],
                [
                    'key' => $p['key'],
                    'label' => $p['label'],
                    'domain' => $p['domain'],
                    'is_read' => $p['is_read'],
                    'is_critical' => $p['is_critical'],
                    'category' => $p['category'],
                    'ordering' => $p['ordering'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }

        // Base groups + seeded default grants.
        foreach ($this->baseGroups() as $g) {
            DB::table('ob_group')->updateOrInsert(
                ['id' => $g['id']],
                [
                    'name' => $g['name'],
                    'kind' => 'group',
                    'usage' => $g['usage'],
                    'org_type' => null,
                    'ordering' => $g['ordering'],
                    'is_system' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
            $this->replaceGrants($g['id'], $this->defaultGrantsFor($g['default']), $now);
        }

        // Section roles per organisation type.
        $archetypeToId = [];
        $orgTypes = array_keys((array) config('brigade.organisation_types', [0 => null]));
        foreach ($orgTypes as $orgType) {
            $orgType = (int) $orgType;
            $defs = config("habilitations.roles_by_org_type.{$orgType}")
                ?? config('habilitations.roles_by_org_type.0', []);
            $resolved = $this->rolesForType($orgType);

            foreach ($defs as $index => $def) {
                $rid = $this->roleId($orgType, $index);
                $archetypeToId[$orgType][$def['archetype']] = $rid;

                DB::table('ob_group')->updateOrInsert(
                    ['id' => $rid],
                    [
                        'name' => $def['name'],
                        'kind' => 'role',
                        'usage' => 'internes',
                        'org_type' => $orgType,
                        'ordering' => 50,
                        'is_system' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
                $this->replaceGrants($rid, $resolved[$index]['feature_ids'], $now);
            }
        }

        return $archetypeToId;
    }

    /**
     * Replace a group's allow-grants with the given feature ids.
     *
     * @param  int[]  $featureIds
     */
    private function replaceGrants(int $groupId, array $featureIds, \DateTimeInterface $now): void
    {
        DB::table('ob_group_permission')->where('group_id', $groupId)->delete();

        $rows = array_map(fn (int $fid) => [
            'group_id' => $groupId,
            'feature_id' => $fid,
            'effect' => 'allow',
            'created_at' => $now,
            'updated_at' => $now,
        ], $featureIds);

        if ($rows !== []) {
            DB::table('ob_group_permission')->insertOrIgnore($rows);
        }
    }
}
