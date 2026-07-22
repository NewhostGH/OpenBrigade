<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Group the localisation-related settings (timezone, currency, phone format)
 * under their own « Localisation » tab on the settings page instead of the
 * catch-all « Avancé » tab. The timezone description loses its legacy
 * type-the-identifier instructions — the UI now renders a dropdown.
 */
return new class extends Migration
{
    private const LOCALISATION_TAB = 6;

    /** Settings IDs to regroup: timezone, currency name/symbol, phone prefix/length. */
    private const IDS = [76, 98, 99, 100, 101];

    public function up(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        DB::table('configuration')
            ->whereIn('ID', self::IDS)
            ->update(['TAB' => self::LOCALISATION_TAB]);

        DB::table('configuration')
            ->where('ID', 76)
            ->update(['DESCRIPTION' => 'Fuseau horaire de l\'application.']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        DB::table('configuration')
            ->whereIn('ID', self::IDS)
            ->update(['TAB' => 5]);
    }
};
