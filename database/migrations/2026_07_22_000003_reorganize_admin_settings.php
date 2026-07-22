<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reorganize the remaining legacy settings:
 *
 * - photo_obligatoire (68) and ameliorations (80) join the Général tab;
 * - the import-API rows (64/65/66) join the Avancé tab with clear names;
 * - the mail/SMS rows (28, 9-12) move OFF the settings page (HIDDEN=1) —
 *   they are rendered by the new Administration ▸ Notifications page;
 * - the maintenance rows (37/41) and auto_optimize (14) also go HIDDEN —
 *   they are rendered by the Administration ▸ Maintenance page.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        $updates = [
            // → Général tab
            68 => ['TAB' => 1, 'ORDERING' => 130],
            80 => ['TAB' => 1, 'ORDERING' => 131, 'DISPLAY_NAME' => 'Aider à améliorer OpenBrigade'],

            // → Avancé tab, renamed
            64 => ['TAB' => 5, 'ORDERING' => 910],
            65 => ['TAB' => 5, 'ORDERING' => 911, 'DISPLAY_NAME' => 'URL de l\'API d\'import'],
            66 => ['TAB' => 5, 'ORDERING' => 912, 'DISPLAY_NAME' => 'Jeton de l\'API d\'import'],

            // → Notifications page (hidden from the settings grid)
            28 => ['HIDDEN' => 1, 'DISPLAY_NAME' => 'Autoriser l\'envoi d\'emails'],
            9 => ['HIDDEN' => 1, 'DISPLAY_NAME' => 'Fournisseur SMS'],
            10 => ['HIDDEN' => 1, 'DISPLAY_NAME' => 'Identifiant du compte SMS'],
            11 => ['HIDDEN' => 1, 'DISPLAY_NAME' => 'Mot de passe / jeton SMS'],
            12 => ['HIDDEN' => 1, 'DISPLAY_NAME' => 'Identifiant d\'appareil (device ID)',
                'DESCRIPTION' => 'Device ID pour smsgateway.me. Inutile pour les autres fournisseurs.'],

            // → Maintenance page (hidden from the settings grid)
            37 => ['HIDDEN' => 1],
            41 => ['HIDDEN' => 1],
            14 => ['HIDDEN' => 1],
        ];

        foreach ($updates as $id => $fields) {
            DB::table('configuration')->where('ID', $id)->update($fields);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        $restore = [
            68 => ['TAB' => 2, 'ORDERING' => 212],
            80 => ['TAB' => 3, 'ORDERING' => 313],
            64 => ['TAB' => 2, 'ORDERING' => 214],
            65 => ['TAB' => 2, 'ORDERING' => 215, 'DISPLAY_NAME' => null],
            66 => ['TAB' => 2, 'ORDERING' => 216, 'DISPLAY_NAME' => null],
            28 => ['HIDDEN' => 0],
            9 => ['HIDDEN' => 0],
            10 => ['HIDDEN' => 0],
            11 => ['HIDDEN' => 0],
            12 => ['HIDDEN' => 0],
            37 => ['HIDDEN' => 0],
            41 => ['HIDDEN' => 0],
            14 => ['HIDDEN' => 0],
        ];

        foreach ($restore as $id => $fields) {
            DB::table('configuration')->where('ID', $id)->update($fields);
        }
    }
};
