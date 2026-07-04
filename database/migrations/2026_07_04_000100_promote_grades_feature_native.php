<?php

// project: OpenBrigade

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The grade system is now fully migrated (CRUD + reordering + member assignment),
 * so its feature flag becomes a normal gateable toggle instead of a WIP stub.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ob_feature')->where('key', 'grades')->update(['status' => 'native']);
    }

    public function down(): void
    {
        DB::table('ob_feature')->where('key', 'grades')->update(['status' => 'wip']);
    }
};
