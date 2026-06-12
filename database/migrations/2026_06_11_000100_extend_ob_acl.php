<?php

use App\Services\PermissionResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Full ACL with groups — extends the section-scoped ceiling model into a
 * complete access-control list with explicit allow *and* deny at every tier.
 *
 *  - ob_group_permission.effect : a group/role grant is now allow|deny (was
 *                                 implicitly allow). A deny on a held group/role
 *                                 overrides allows from sibling groups/roles.
 *  - ob_user_permission         : per-person override, section-scoped (section_id
 *                                 0 = global, inherited to descendants like roles).
 *                                 The most specific tier — beats every group/role
 *                                 grant and the section ceiling.
 *
 * Resolution precedence (first match wins), see {@see PermissionResolver}:
 *   user deny > user allow > section deny > group/role deny > group/role allow > deny.
 *
 * Backwards compatible: existing rows default to effect=allow and there are no
 * user-permission rows, so resolution collapses to the previous behaviour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ob_group_permission', function (Blueprint $table) {
            $table->enum('effect', ['allow', 'deny'])->default('allow')->after('feature_id');
        });

        // Existing grants are all positive — make that explicit.
        DB::table('ob_group_permission')->update(['effect' => 'allow']);

        // Per-person override (the most specific ACL tier).
        Schema::create('ob_user_permission', function (Blueprint $table) {
            $table->id();
            $table->integer('person_id');
            $table->smallInteger('section_id')->default(0); // 0 = global, inherited to descendants
            $table->integer('feature_id');
            $table->enum('effect', ['allow', 'deny'])->default('allow');
            $table->timestamps();

            $table->unique(['person_id', 'section_id', 'feature_id']);
            $table->index('person_id');
            $table->index('feature_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ob_user_permission');

        Schema::table('ob_group_permission', function (Blueprint $table) {
            $table->dropColumn('effect');
        });
    }
};
