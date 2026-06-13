<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Per-group password policies (NCSC / ANSSI-aligned, fully configurable).
 *
 * ob_password_policy holds named policy records. ob_group gets a nullable FK
 * so each group/role can opt in to a specific policy; users whose group has no
 * policy inherit the row flagged is_default = true.
 *
 * NCSC recommendation: length over complexity, no forced rotation, blocklist.
 * ANSSI adds graded combinations (≥12 + 4 types, ≥14 + 3, ≥16 + 2, ≥20 any).
 * Both are achievable by configuring the fields below.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_password_policy', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);

            // Length
            $table->unsignedTinyInteger('min_length')->default(12);

            // Complexity (all off by default — NCSC stance)
            $table->boolean('require_uppercase')->default(false);
            $table->boolean('require_lowercase')->default(false);
            $table->boolean('require_digits')->default(false);
            $table->boolean('require_special')->default(false);

            // Rotation (0 = disabled — NCSC stance)
            $table->unsignedSmallInteger('expiry_days')->default(0)->comment('0 = no forced rotation');

            // Throttle / lockout (0 = disabled)
            $table->unsignedTinyInteger('max_attempts')->default(10)->comment('0 = no lockout');

            // Blocklist (on by default — NCSC stance)
            $table->boolean('blocklist_check')->default(true);

            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Default policy — NCSC stance: length-first, no complexity, no forced rotation.
        DB::table('ob_password_policy')->insert([
            'name' => 'Politique par défaut (NCSC)',
            'min_length' => 12,
            'require_uppercase' => false,
            'require_lowercase' => false,
            'require_digits' => false,
            'require_special' => false,
            'expiry_days' => 0,
            'max_attempts' => 10,
            'blocklist_check' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Admin policy — ANSSI-grade: longer minimum, digits required.
        DB::table('ob_password_policy')->insert([
            'name' => 'Politique administrateurs (ANSSI)',
            'min_length' => 16,
            'require_uppercase' => false,
            'require_lowercase' => false,
            'require_digits' => true,
            'require_special' => false,
            'expiry_days' => 0,
            'max_attempts' => 5,
            'blocklist_check' => true,
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add the FK column to ob_group.
        Schema::table('ob_group', function (Blueprint $table) {
            $table->unsignedBigInteger('password_policy_id')
                ->nullable()
                ->after('is_system')
                ->comment('null = inherit global default policy');

            $table->foreign('password_policy_id')
                ->references('id')
                ->on('ob_password_policy')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ob_group', function (Blueprint $table) {
            $table->dropForeign(['password_policy_id']);
            $table->dropColumn('password_policy_id');
        });

        Schema::dropIfExists('ob_password_policy');
    }
};
