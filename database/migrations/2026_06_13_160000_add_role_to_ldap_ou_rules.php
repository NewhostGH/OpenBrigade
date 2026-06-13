<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ldap_ou_rules', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id')->nullable()->after('group_id');
        });
    }

    public function down(): void
    {
        Schema::table('ldap_ou_rules', function (Blueprint $table): void {
            $table->dropColumn('role_id');
        });
    }
};
