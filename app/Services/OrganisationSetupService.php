<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Services;

use App\Support\Habilitations\BaseHabilitations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Owns the first-run / organisation-setup state stored in the `configuration`
 * table and the activation of an organisation type's seeded role set.
 *
 * Canonical keys (matching the legacy schema):
 *   - `already_configured` (ID -1) — the first-run gate: 1 once the wizard ran.
 *   - `type_organisation`  (ID 79) — the active organisation type id.
 *   - identity settings 6/7/8/38/39 — short/url/email/app-title/long name.
 *
 * Persisting the type is non-destructive: roles for every type are already
 * seeded, so activation simply records the choice and the app filters role
 * listings to it. Re-seeding a type's preset roles is an explicit opt-in
 * (`resetRoles()`), never implied by changing the type.
 */
class OrganisationSetupService
{
    /** configuration ID of the first-run gate. */
    private const FLAG_ID = -1;

    private const FLAG_NAME = 'already_configured';

    /** configuration ID of the active organisation type. */
    private const ORG_TYPE_ID = 79;

    private const ORG_TYPE_NAME = 'type_organisation';

    /**
     * Identity fields collected by the wizard: config NAME => ID.
     *
     * The site URL (7) and application title (38) are intentionally excluded —
     * they are obsolete, governed by APP_URL / APP_NAME in .env.
     */
    private const IDENTITY = [
        'cisname' => 6,
        'admin_email' => 8,
        'organisation_name' => 39,
        'association_dept_name' => 40,
    ];

    /** configuration ID of the organisation logo (file, public disk). */
    private const LOGO_ID = 71;

    /** True once the first-run wizard has been completed. */
    public function isCompleted(): bool
    {
        return (int) $this->read(self::FLAG_NAME, '0') === 1;
    }

    /** The active organisation type id (0 when unset / "sans préconfiguration"). */
    public function orgType(): int
    {
        return (int) $this->read(self::ORG_TYPE_NAME, '0');
    }

    /** @return array<int,string> type id => label, from config('brigade.organisation_types'). */
    public function types(): array
    {
        return (array) config('brigade.organisation_types', [0 => 'Sans préconfiguration']);
    }

    /** Human label for the active (or given) organisation type. */
    public function typeLabel(?int $orgType = null): string
    {
        $orgType ??= $this->orgType();

        return $this->types()[$orgType] ?? (string) $orgType;
    }

    /**
     * Org-type ids whose roles are "active" for this install: the chosen type,
     * plus `null` (custom roles created by the admin, not tied to a type).
     *
     * @return array{0:int,1:null}
     */
    public function activeRoleOrgTypes(): array
    {
        return [$this->orgType(), null];
    }

    /**
     * Current identity values keyed by config NAME (empty string when unset).
     *
     * @return array<string,string>
     */
    public function identity(): array
    {
        $rows = DB::table('configuration')
            ->whereIn('ID', array_values(self::IDENTITY))
            ->pluck('VALUE', 'ID');

        $out = [];
        foreach (self::IDENTITY as $name => $id) {
            $out[$name] = (string) ($rows[$id] ?? '');
        }

        return $out;
    }

    /**
     * Persist the identity fields collected by the wizard.
     *
     * @param  array<string,string>  $values  keyed by config NAME
     */
    public function saveIdentity(array $values): void
    {
        foreach (self::IDENTITY as $name => $id) {
            if (! array_key_exists($name, $values)) {
                continue;
            }
            DB::table('configuration')->updateOrInsert(
                ['ID' => $id],
                ['NAME' => $name, 'VALUE' => (string) $values[$name]],
            );
        }
    }

    /**
     * Store an uploaded organisation logo on the public disk and record its path
     * (config ID 71), replacing any previous logo. Caller must have validated
     * the file (type/size/malware) first.
     */
    public function saveLogo(UploadedFile $file): void
    {
        $old = (string) DB::table('configuration')->where('ID', self::LOGO_ID)->value('VALUE');
        if ($old !== '' && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        $path = $file->store('theme', 'public');

        DB::table('configuration')->updateOrInsert(
            ['ID' => self::LOGO_ID],
            ['NAME' => 'logo', 'VALUE' => $path],
        );
    }

    /** Record the active organisation type (non-destructive). */
    public function setOrgType(int $orgType): void
    {
        DB::table('configuration')->updateOrInsert(
            ['ID' => self::ORG_TYPE_ID],
            ['NAME' => self::ORG_TYPE_NAME, 'VALUE' => (string) $orgType],
        );
    }

    /** Flip the first-run gate to done. */
    public function markCompleted(): void
    {
        DB::table('configuration')->updateOrInsert(
            ['ID' => self::FLAG_ID],
            ['NAME' => self::FLAG_NAME, 'VALUE' => '1'],
        );
    }

    /**
     * Destructive: reset the given type's preset roles and their grants back to
     * the seeded defaults. Only touches this type's system roles — custom roles
     * and other types are untouched. Explicit admin action only.
     */
    public function resetRoles(int $orgType): void
    {
        (new BaseHabilitations)->seedRolesForType($orgType);
    }

    /**
     * Custom (admin-created) roles: kind = role, not tied to an org type, each
     * with the number of member assignments so the UI can warn before deletion.
     *
     * @return Collection<int,\stdClass>
     */
    public function customRoles(): Collection
    {
        return DB::table('ob_group')
            ->where('kind', 'role')
            ->whereNull('org_type')
            ->where('is_system', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function ($r): \stdClass {
                $o = new \stdClass;
                $o->id = (int) $r->id;
                $o->name = (string) $r->name;
                $o->members = (int) DB::table('ob_user_assignment')->where('group_id', $r->id)->count();

                return $o;
            });
    }

    /**
     * The preset roles of the given (or active) org type — the valid remap
     * targets when deleting a custom role.
     *
     * @return Collection<int,\stdClass>
     */
    public function presetRoles(?int $orgType = null): Collection
    {
        $orgType ??= $this->orgType();

        return DB::table('ob_group')
            ->where('kind', 'role')
            ->where('org_type', $orgType)
            ->orderBy('ordering')->orderBy('id')
            ->get(['id', 'name'])
            ->map(function ($r): \stdClass {
                $o = new \stdClass;
                $o->id = (int) $r->id;
                $o->name = (string) $r->name;

                return $o;
            });
    }

    /**
     * Destructive: delete custom roles, remapping each one's member assignments
     * to a chosen preset role first (or dropping them when the target is null).
     * Never touches system/preset roles.
     *
     * @param  array<int,int|null>  $map  customRoleId => targetPresetRoleId|null
     */
    public function deleteCustomRoles(array $map): void
    {
        $deletable = $this->customRoles()->keyBy('id');

        DB::transaction(function () use ($map, $deletable) {
            foreach ($map as $roleId => $target) {
                $roleId = (int) $roleId;
                if (! $deletable->has($roleId)) {
                    continue; // Only ever delete genuine custom roles.
                }

                if ($target !== null) {
                    // Remap assignments to the target preset role, de-duplicating
                    // against rows the member may already hold there.
                    DB::table('ob_user_assignment')
                        ->where('group_id', $roleId)
                        ->orderBy('id')
                        ->each(function ($row) use ($target) {
                            DB::table('ob_user_assignment')->insertOrIgnore([
                                'person_id' => $row->person_id,
                                'section_id' => $row->section_id,
                                'group_id' => $target,
                            ]);
                        });
                }

                DB::table('ob_user_assignment')->where('group_id', $roleId)->delete();
                DB::table('ob_group_permission')->where('group_id', $roleId)->delete();
                DB::table('ob_group')->where('id', $roleId)->delete();
            }
        });
    }

    private function read(string $name, string $default): string
    {
        try {
            $value = DB::table('configuration')->where('NAME', $name)->value('VALUE');
        } catch (\Throwable) {
            // During install / migration the table may not exist yet.
            return $default;
        }

        return $value === null ? $default : (string) $value;
    }
}
