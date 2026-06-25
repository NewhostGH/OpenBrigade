<?php

use App\Services\LoggingSettingService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seed the observability settings (NAME/VALUE rows in `configuration`) so the
 * Journal d'activité ▸ Paramètres tab has an ID to PATCH for each toggle.
 * Idempotent and self-healing — the admin screen also calls ensureSeeded().
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        (new LoggingSettingService)->ensureSeeded();
    }

    public function down(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        DB::table('configuration')
            ->whereIn('NAME', LoggingSettingService::keys())
            ->delete();
    }
};
