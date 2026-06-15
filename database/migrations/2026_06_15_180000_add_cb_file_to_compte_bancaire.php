<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compte_bancaire', function (Blueprint $table) {
            $table->string('CB_FILE', 255)->nullable()->after('UPDATE_DATE');
        });
    }

    public function down(): void
    {
        Schema::table('compte_bancaire', function (Blueprint $table) {
            $table->dropColumn('CB_FILE');
        });
    }
};
