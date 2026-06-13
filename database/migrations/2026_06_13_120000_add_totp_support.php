<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TOTP / two-factor authentication support.
 *
 * - pompier: three columns for Fortify's TwoFactorAuthenticatable trait.
 * - ob_password_policy: require_2fa flag — when true, users in groups that
 *   carry this policy are redirected to TOTP enrolment after their first
 *   successful password login.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pompier', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('P_MDP');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });

        Schema::table('ob_password_policy', function (Blueprint $table) {
            $table->boolean('require_2fa')
                ->default(false)
                ->after('blocklist_check')
                ->comment('Force TOTP enrolment for users whose group uses this policy');
        });
    }

    public function down(): void
    {
        Schema::table('pompier', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });

        Schema::table('ob_password_policy', function (Blueprint $table) {
            $table->dropColumn('require_2fa');
        });
    }
};
