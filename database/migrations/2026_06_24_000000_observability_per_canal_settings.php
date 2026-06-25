<?php

use App\Services\LoggingSettingService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrate the observability settings from a single global log level to a
 * per-canal model, and add the Sentry DSN as a stored setting.
 *
 * - Drops the obsolete `obs_log_level` row (replaced by `obs_level_<canal>`).
 * - Seeds the new per-canal level keys + `obs_sentry_dsn`, importing the
 *   existing SENTRY_LARAVEL_DSN env value so error tracking keeps working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        DB::table('configuration')->where('NAME', 'obs_log_level')->delete();

        (new LoggingSettingService)->ensureSeeded();

        // Carry the env-configured DSN (if any) into the new setting so existing
        // deployments don't lose their configured target. Read via config so it
        // resolves correctly even when the config cache is warm.
        $envDsn = (string) (config('sentry.dsn') ?: '');
        if ($envDsn !== '') {
            DB::table('configuration')
                ->where('NAME', 'obs_sentry_dsn')
                ->where('VALUE', '')
                ->update(['VALUE' => $envDsn]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('configuration')) {
            return;
        }

        // Remove the per-canal + DSN keys; restore a single obs_log_level row.
        DB::table('configuration')
            ->whereIn('NAME', LoggingSettingService::keys())
            ->delete();
    }
};
