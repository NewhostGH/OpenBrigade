<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pompier', function (Blueprint $table) {
            $table->unsignedInteger('P_URGENCE_PERSON_ID')->nullable()->after('P_RELATION_MAIL');
        });
    }

    public function down(): void
    {
        Schema::table('pompier', function (Blueprint $table) {
            $table->dropColumn('P_URGENCE_PERSON_ID');
        });
    }
};
