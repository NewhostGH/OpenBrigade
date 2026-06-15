<?php

use App\Support\Habilitations\BaseHabilitations;
use App\Support\Habilitations\SuperAdminProvisioner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rebuild the habilitation base data from scratch (see the design plan).
 *
 *  - Super-admin becomes the account flag `pompier.P_SUPERADMIN` (not a group).
 *  - A canonical, classified permission catalog `ob_permission` (id = legacy
 *    fonctionnalite.F_ID) is back-filled.
 *  - Four clean base groups (Admin/Auditor/User/Guest) replace the legacy
 *    base groups; their grants are seeded from the classification.
 *  - Section roles are seeded per organisation type, ready for a setup wizard.
 *  - Existing memberships are remapped off the legacy base groups, then the
 *    legacy base groups/roles are dropped.
 *  - A dedicated super-admin account is provisioned.
 *
 * All base-data logic is delegated to {@see BaseHabilitations} so the migration
 * and the CoreSeeder build identical data.
 */
return new class extends Migration
{
    public function up(): void
    {
        $base = new BaseHabilitations;

        // ── 1. Schema ────────────────────────────────────────────────────────
        if (! Schema::hasColumn('pompier', 'P_SUPERADMIN')) {
            Schema::table('pompier', function (Blueprint $table) {
                $table->boolean('P_SUPERADMIN')->default(false)->after('GP_ID2');
            });
        }

        if (! Schema::hasColumn('ob_group', 'org_type')) {
            Schema::table('ob_group', function (Blueprint $table) {
                $table->smallInteger('org_type')->nullable()->after('usage');
            });
        }

        if (! Schema::hasTable('ob_permission')) {
            Schema::create('ob_permission', function (Blueprint $table) {
                $table->smallInteger('id')->primary(); // = legacy fonctionnalite.F_ID
                $table->string('key', 80)->unique();
                $table->string('label', 120);
                $table->enum('domain', ['config', 'data'])->default('data');
                $table->boolean('is_read')->default(false);
                $table->boolean('is_critical')->default(false);
                $table->string('category', 40)->nullable();
                $table->unsignedSmallInteger('ordering')->default(50);
                $table->timestamps();

                $table->index('domain');
            });
        }

        // ── 2-4. Canonical base data (catalog, base groups, per-type roles) ──
        // archetypeToId[orgType][archetype] => role id (for the legacy remap).
        $archetypeToId = $base->seed();

        // ── 5. Remap memberships off legacy base groups, then drop them ──────
        foreach ($base->legacyGroupMap() as $from => $to) {
            // Global group memberships.
            DB::table('ob_personnel_group')
                ->where('group_id', $from)
                ->orderBy('id')
                ->each(function ($row) use ($to) {
                    DB::table('ob_personnel_group')->insertOrIgnore([
                        'person_id' => $row->person_id,
                        'group_id' => $to,
                    ]);
                });
            DB::table('ob_personnel_group')->where('group_id', $from)->delete();

            // Legacy single-group columns on the pompier row.
            DB::table('pompier')->where('GP_ID', $from)->update(['GP_ID' => $to]);
            DB::table('pompier')->where('GP_ID2', $from)->update(['GP_ID2' => $to]);

            // Obsolete legacy base group + its grants.
            DB::table('ob_group_permission')->where('group_id', $from)->delete();
            DB::table('ob_group')->where('id', $from)->delete();
        }

        // Legacy roles → the active organisation type's matching new role.
        $activeType = (int) (DB::table('configuration')->where('NAME', 'type_organisation')->value('VALUE') ?? 0);
        $byArchetype = $archetypeToId[$activeType] ?? ($archetypeToId[0] ?? []);
        $legacyRoleMap = [102 => 'chef', 103 => 'adjoint', 110 => 'secretariat'];
        $fallback = $byArchetype['chef'] ?? (reset($byArchetype) ?: null);

        foreach ($legacyRoleMap as $legacyRoleId => $archetype) {
            $target = $byArchetype[$archetype] ?? $fallback;
            if ($target !== null) {
                DB::table('ob_user_assignment')
                    ->where('group_id', $legacyRoleId)
                    ->orderBy('id')
                    ->each(function ($row) use ($target) {
                        DB::table('ob_user_assignment')->insertOrIgnore([
                            'person_id' => $row->person_id,
                            'section_id' => $row->section_id,
                            'group_id' => $target,
                        ]);
                    });
            }
            DB::table('ob_user_assignment')->where('group_id', $legacyRoleId)->delete();
            DB::table('ob_group_permission')->where('group_id', $legacyRoleId)->delete();
            DB::table('ob_group')->where('id', $legacyRoleId)->delete();
        }

        // ── 6. Dedicated super-admin account ─────────────────────────────────
        $result = (new SuperAdminProvisioner)->ensure();
        if ($result['created'] && $result['password'] !== null) {
            echo PHP_EOL.'  ┌─────────────────────────────────────────────────────────┐'.PHP_EOL;
            echo '  │  Super-admin account created.                           │'.PHP_EOL;
            echo "  │  login:    {$result['code']}".str_repeat(' ', max(0, 45 - strlen($result['code']))).'│'.PHP_EOL;
            echo "  │  password: {$result['password']}".str_repeat(' ', max(0, 45 - strlen($result['password']))).'│'.PHP_EOL;
            echo '  │  (must be changed on first login)                       │'.PHP_EOL;
            echo '  └─────────────────────────────────────────────────────────┘'.PHP_EOL.PHP_EOL;
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ob_permission')) {
            Schema::dropIfExists('ob_permission');
        }
        if (Schema::hasColumn('ob_group', 'org_type')) {
            Schema::table('ob_group', function (Blueprint $table) {
                $table->dropColumn('org_type');
            });
        }
        if (Schema::hasColumn('pompier', 'P_SUPERADMIN')) {
            Schema::table('pompier', function (Blueprint $table) {
                $table->dropColumn('P_SUPERADMIN');
            });
        }
    }
};
