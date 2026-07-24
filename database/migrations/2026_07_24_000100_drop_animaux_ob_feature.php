<?php

// project: OpenBrigade

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Animaux is no longer a core feature: it ships as an installable plugin from
 * the official registry (slug `animaux`), so it must not appear on the
 * Administration ▸ Fonctionnalités screen as a WIP core toggle.
 *
 * We only remove the `ob_feature` registry row — the legacy `configuration`
 * row (id 81, "Activer la gestion des animaux") is left untouched so the
 * legacy bridge and any not-yet-migrated code keep reading it. down() restores
 * the feature row from that legacy row, mirroring the original backfill.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ob_feature')->where('key', 'animaux')->delete();
    }

    public function down(): void
    {
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
