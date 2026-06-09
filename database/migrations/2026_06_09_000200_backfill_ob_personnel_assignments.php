<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill ob_personnel_section and ob_personnel_group from legacy data.
 *
 * - pompier.GP_ID / GP_ID2  → ob_personnel_group (one row per non-null value)
 * - ob_user_assignment       → ob_personnel_section (distinct section per person)
 *
 * ob_user_assignment rows are preserved as section-scoped role entries.
 * section_id = 0 is the global sentinel (no section restriction).
 * The unique key stays (person_id, section_id, group_id) so the same role
 * can be assigned in different sections.
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

        // Existing rows already carry their original section_id from the legacy data.
        // Nothing to collapse — the three-column unique (person_id, section_id, group_id)
        // remains and supports section-scoped roles.
    }

    public function down(): void
    {
        // Not reversible — original GP_ID/GP_ID2 values still exist on pompier.
        DB::table('ob_personnel_group')->delete();
        DB::table('ob_personnel_section')->delete();
    }
};
