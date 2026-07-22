<?php

// project: OpenBrigade

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an admin hide a whole grade category (e.g. "Armée de Terre") from the
 * personnel form without deleting its grades — a hospital shouldn't see
 * military ranks in the assignment dropdown, but the data stays intact.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorie_grade', function (Blueprint $table) {
            $table->tinyInteger('CG_ACTIVE')->default(1)->after('CG_DESCRIPTION');
        });
    }

    public function down(): void
    {
        Schema::table('categorie_grade', function (Blueprint $table) {
            $table->dropColumn('CG_ACTIVE');
        });
    }
};
