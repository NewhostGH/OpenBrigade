<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Back-fills `ob_feature` from the legacy `configuration` table.
 *
 * Source rows: the toggleable settings of TAB 1 ("Fonctionnalités") and
 * TAB 6 ("Modules") — i.e. YESNO = 1 and HIDDEN = 0. Hidden organisation-type
 * switches (army/sslia/hospital/sdis/syndicate/assoc) are intentionally skipped.
 *
 * Each feature is marked `native` when a Laravel implementation exists, or
 * `wip` otherwise (e.g. Animaux), so the admin screen can surface a WIP marker
 * and the `feature:` gate only ever blocks things that actually exist natively.
 *
 * The `group` column places each feature in its functional domain so the admin
 * screen can group by domain rather than the legacy TAB 1 / TAB 6 split.
 */
return new class extends Migration
{
    /** Feature keys that already have a native Laravel implementation. */
    private const NATIVE = [
    ];

    /** Functional domain per feature key. */
    private const KEY_GROUPS = [
        'logistique' => ['vehicules', 'materiel', 'consommables'],
        'personnel'  => ['competences', 'externes', 'staff_assignment', 'licences', 'grades', 'matricule'],
        'planning'   => ['disponibilites', 'remplacements', 'gardes', 'activités'],
        'operations' => ['main_courante', 'client', 'victime', 'renfort', 'animaux'],
        'finances'   => ['cotisations', 'bank_accounts', 'bilan', 'notes'],
        'geographie' => ['geolocalize_enabled', 'carte'],
        'systeme'    => ['chat', 'multi_site'],
    ];

    /** Optional Font Awesome glyph per feature key (sidebar / admin screen). */
    private const ICONS = [
        'vehicules' => 'truck', 'materiel' => 'toolbox', 'consommables' => 'boxes',
        'activités' => 'calendar-alt', 'competences' => 'certificate',
        'disponibilites' => 'check-square', 'chat' => 'comments',
        'remplacements' => 'exchange-alt', 'cotisations' => 'receipt',
        'externes' => 'user-friends', 'bank_accounts' => 'university',
        'main_courante' => 'ambulance', 'client' => 'user-circle',
        'geolocalize_enabled' => 'map-marker-alt', 'carte' => 'map',
        'animaux' => 'paw', 'bilan' => 'chart-pie', 'notes' => 'file-invoice-dollar',
        'licences' => 'id-card', 'victime' => 'first-aid', 'renfort' => 'people-carry',
        'matricule' => 'hashtag', 'gardes' => 'shield-alt', 'grades' => 'star',
        'multi_site' => 'sitemap', 'staff_assignment' => 'users-cog',
    ];

    public function up(): void
    {
        $now = now();

        $keyToGroup = [];
        foreach (self::KEY_GROUPS as $group => $keys) {
            foreach ($keys as $key) {
                $keyToGroup[$key] = $group;
            }
        }

        $rows = DB::table('configuration')
            ->whereIn('TAB', [1, 6])
            ->where('YESNO', 1)
            ->where('HIDDEN', 0)
            ->get();

        foreach ($rows as $row) {
            $key = trim((string) $row->NAME);
            if ($key === '') {
                continue;
            }

            $name = trim(strip_tags((string) ($row->DISPLAY_NAME ?: $row->CARD_NAME ?: $row->NAME)));

            DB::table('ob_feature')->updateOrInsert(
                ['key' => $key],
                [
                    'name'             => $name,
                    'description'      => $row->DESCRIPTION ?: null,
                    'group'            => $keyToGroup[$key] ?? null,
                    'status'           => in_array($key, self::NATIVE, true) ? 'native' : 'wip',
                    'icon'             => self::ICONS[$key] ?? null,
                    'enabled'          => ((string) $row->VALUE === '1'),
                    'ordering'         => (int) $row->ORDERING,
                    'legacy_config_id' => (int) $row->ID,
                    'updated_at'       => $now,
                    'created_at'       => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('ob_feature')->truncate();
    }
};
