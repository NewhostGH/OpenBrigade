<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un-retire the application name (38, application_title) and public site URL
 * (7, cisurl) on the Organisation tab. The .env instantiates the value — they
 * were governed by APP_NAME / APP_URL until now, so the env IS the current
 * truth and the stale legacy content is replaced. From then on the stored row
 * is the source of truth (applied at boot; an emptied row reverts to .env).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        DB::table('configuration')->where('ID', 38)->update([
            'VALUE' => config('app.name'),
            'DESCRIPTION' => 'Nom de l\'application. Initialisé depuis APP_NAME (.env) ; la valeur enregistrée ici fait foi.',
        ]);

        DB::table('configuration')->where('ID', 7)->update([
            'VALUE' => config('app.url'),
            'DESCRIPTION' => 'Adresse publique du site (http:// ou https://). Initialisée depuis APP_URL (.env) ; la valeur enregistrée ici fait foi.',
        ]);
    }

    public function down(): void
    {
        // The rows keep their values; the previous release simply ignored them.
    }
};
