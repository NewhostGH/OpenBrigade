<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The mandatory-photo rule only blocks SELF-registration — a manager
 * registering someone else is never blocked. Say so in the setting's
 * description (shown as the row tooltip on the settings page).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        DB::table('configuration')->where('ID', 68)->update([
            'DESCRIPTION' => 'Le personnel doit obligatoirement mettre une photo, sinon il ne pourra plus s\'inscrire lui-même aux activités. L\'inscription par un responsable reste possible.',
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        DB::table('configuration')->where('ID', 68)->update([
            'DESCRIPTION' => 'Le personnel doit obligatoirement mettre une photo, sinon il ne pourra plus s\'inscrire aux activités',
        ]);
    }
};
