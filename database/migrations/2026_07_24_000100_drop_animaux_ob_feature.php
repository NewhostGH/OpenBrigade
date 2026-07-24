<?php

// project: OpenBrigade

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Animaux is no longer a core feature: it ships as an installable plugin from
 * the official registry (slug `animaux`), so enabling/disabling the plugin —
 * not a settings toggle — governs it.
 *
 * Two things must go, or "Activer la gestion des animaux" keeps showing up:
 *  - the `ob_feature` row (Administration ▸ Fonctionnalités), and
 *  - the legacy `configuration` row (id 81) must be HIDDEN, otherwise the
 *    Options screen (AdminController::settings) — which lists visible legacy
 *    rows not mapped to an ob_feature — would surface it again once unmapped.
 *
 * The legacy row's VALUE is left intact (only HIDDEN flips) so the bridge and
 * any not-yet-migrated code keep reading it.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ob_feature')->where('key', 'animaux')->delete();
        DB::table('configuration')->where('NAME', 'animaux')->update(['HIDDEN' => 1]);
    }

    public function down(): void
    {
        DB::table('configuration')->where('NAME', 'animaux')->update(['HIDDEN' => 0]);

        $row = DB::table('configuration')->where('NAME', 'animaux')->first();

        if ($row === null || DB::table('ob_feature')->where('key', 'animaux')->exists()) {
            return;
        }

        $now = now();

        DB::table('ob_feature')->insert([
            'key' => 'animaux',
            'name' => trim(strip_tags((string) ($row->DISPLAY_NAME ?: $row->CARD_NAME ?: $row->NAME))),
            'description' => $row->DESCRIPTION ?: null,
            'group' => 'operations',
            'status' => 'wip',
            'icon' => 'paw',
            'enabled' => ((string) $row->VALUE === '1'),
            'ordering' => (int) $row->ORDERING,
            'legacy_config_id' => (int) $row->ID,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
