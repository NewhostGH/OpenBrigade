<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill ob_personnel_section and ob_personnel_group from legacy data.
 *
 * - pompier.GP_ID / GP_ID2  → ob_personnel_group (one row per non-null value)
 * - ob_user_assignment       → ob_personnel_section (distinct section per person)
 *                            → ob_user_assignment rows kept but section_id nulled
 *                              and de-duped to unique (person_id, group_id) role entries
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Groups from GP_ID / GP_ID2 ────────────────────────────────────────
        $personnel = DB::table('pompier')
            ->whereNotNull('P_ID')
            ->get(['P_ID', 'GP_ID', 'GP_ID2']);

        foreach ($personnel as $p) {
            $inserted = [];
            foreach ([(int) $p->GP_ID, (int) ($p->GP_ID2 ?? 0)] as $gid) {
                if ($gid === 0 || in_array($gid, $inserted, true)) {
                    continue;
                }
                $exists = DB::table('ob_group')->where('id', $gid)->exists();
                if (! $exists) {
                    continue;
                }
                DB::table('ob_personnel_group')->insertOrIgnore([
                    'person_id' => (int) $p->P_ID,
                    'group_id' => $gid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted[] = $gid;
            }
        }

        // ── Sections from ob_user_assignment ──────────────────────────────────
        $assignments = DB::table('ob_user_assignment')
            ->whereNotNull('section_id')
            ->get(['person_id', 'section_id', 'group_id']);

        foreach ($assignments as $a) {
            DB::table('ob_personnel_section')->insertOrIgnore([
                'person_id' => (int) $a->person_id,
                'section_id' => (int) $a->section_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Convert ob_user_assignment to global (section-less) role entries —
        // keep unique (person_id, group_id) pairs, nulling section_id.
        DB::statement('DELETE a1 FROM ob_user_assignment a1
            INNER JOIN ob_user_assignment a2
            ON a1.person_id = a2.person_id AND a1.group_id = a2.group_id AND a1.id > a2.id');

        DB::table('ob_user_assignment')->update(['section_id' => null]);

        // Drop the old unique constraint that included section_id and add the new one.
        // (Schema::table inside a data migration would require Doctrine — use raw SQL instead.)
        try {
            DB::statement('ALTER TABLE ob_user_assignment DROP INDEX ob_user_assignment_person_id_section_id_group_id_unique');
        } catch (Throwable) {
        }
        try {
            DB::statement('ALTER TABLE ob_user_assignment ADD UNIQUE KEY ob_user_assignment_person_group_unique (person_id, group_id)');
        } catch (Throwable) {
        }
    }

    public function down(): void
    {
        // Not reversible — original GP_ID/GP_ID2 values still exist on pompier.
        DB::table('ob_personnel_group')->delete();
        DB::table('ob_personnel_section')->delete();

        // Restore unique constraint
        try {
            DB::statement('ALTER TABLE ob_user_assignment DROP INDEX ob_user_assignment_person_group_unique');
        } catch (Throwable) {
        }
    }
};
