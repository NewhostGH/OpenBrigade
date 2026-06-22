<?php

use App\Services\SecuritySettingService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seed the security-hardening toggles (Administration ▸ Sécurité ▸ Renforcement)
 * as NAME/VALUE rows in the legacy `configuration` table.
 *
 * The table's ID column is a non-auto-increment primary key, so each new row is
 * assigned the next free ID. Rows are HIDDEN and given a distinct TAB so they
 * never surface on the generic settings page — they are edited only through the
 * dedicated security screen. Idempotent: existing rows are left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Seeding logic lives in the service so the admin screen can self-heal too.
        app(SecuritySettingService::class)->ensureSeeded();
    }

    public function down(): void
    {
        DB::table('configuration')
            ->whereIn('NAME', SecuritySettingService::keys())
            ->delete();
    }
};
