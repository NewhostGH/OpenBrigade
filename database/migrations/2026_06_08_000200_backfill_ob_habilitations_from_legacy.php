<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Back-fills the ob_ habilitation tables from the legacy schema.
 *
 *  groupe        -> ob_group            (TR_CONFIG 1 => group, 2/3 => role)
 *  habilitation  -> ob_group_permission
 *  section_role  -> ob_user_assignment  (role held by a person in a section)
 *
 * Global group membership (pompier.GP_ID / GP_ID2) is intentionally NOT copied:
 * it stays on the pompier record. No ob_section_permission rows are seeded, so
 * every section is initially unrestricted — behaviour stays backwards-compatible
 * until an admin defines a ceiling.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── groupe -> ob_group ────────────────────────────────────────────────
        $groups = DB::table('groupe')->get();
        foreach ($groups as $g) {
            DB::table('ob_group')->updateOrInsert(
                ['id' => (int) $g->GP_ID],
                [
                    'name' => $g->GP_DESCRIPTION,
                    'kind' => ((int) ($g->TR_CONFIG ?? 1)) >= 2 ? 'role' : 'group',
                    'usage' => $g->GP_USAGE ?: 'internes',
                    'ordering' => (int) ($g->GP_ORDER ?? 50),
                    'is_system' => in_array((int) $g->GP_ID, [-1, 0, 4], true),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // ── habilitation -> ob_group_permission ──────────────────────────────
        DB::table('habilitation')->orderBy('GP_ID')->chunk(500, function ($rows) use ($now) {
            $payload = $rows->map(fn ($r) => [
                'group_id' => (int) $r->GP_ID,
                'feature_id' => (int) $r->F_ID,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();
            DB::table('ob_group_permission')->insertOrIgnore($payload);
        });

        // ── section_role -> ob_user_assignment ───────────────────────────────
        DB::table('section_role')->orderBy('P_ID')->chunk(500, function ($rows) use ($now) {
            $payload = $rows->map(fn ($r) => [
                'person_id' => (int) $r->P_ID,
                'section_id' => (int) $r->S_ID,
                'group_id' => (int) $r->GP_ID,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();
            DB::table('ob_user_assignment')->insertOrIgnore($payload);
        });
    }

    public function down(): void
    {
        DB::table('ob_user_assignment')->truncate();
        DB::table('ob_section_permission')->truncate();
        DB::table('ob_group_permission')->truncate();
        DB::table('ob_group')->truncate();
    }
};
