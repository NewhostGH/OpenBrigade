<?php

// project: OpenBrigade

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forward-only repair: add the compatibility-window columns to `ob_plugin` on
 * installs whose create-migration ran before those columns existed (the
 * columns are declared in 2026_07_23_000002_create_plugin_tables, but a DB that
 * migrated before that edit never got them — PluginStateService::record() then
 * fails to insert). Guarded so fresh installs, which already have the columns,
 * are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ob_plugin', function (Blueprint $table): void {
            if (! Schema::hasColumn('ob_plugin', 'min_app_version')) {
                $table->string('min_app_version', 20)->default('')->after('version');
            }
            if (! Schema::hasColumn('ob_plugin', 'max_app_version')) {
                $table->string('max_app_version', 20)->default('')->after('min_app_version');
            }
        });
    }

    public function down(): void
    {
        // No-op: dropping the columns would re-break record(); the create
        // migration owns them for fresh installs.
    }
};
