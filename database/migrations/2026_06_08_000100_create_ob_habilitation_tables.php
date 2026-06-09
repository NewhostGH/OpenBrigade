<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Section-scoped, ceiling-based habilitation model.
 *
 * Replaces the legacy `groupe` / `habilitation` / `section_role` permission
 * tables with four `ob_` tables:
 *
 *  - ob_group            : group & role definitions (kind = group|role).
 *                          `id` preserves the legacy GP_ID so pompier.GP_ID /
 *                          GP_ID2 (global group membership) keep mapping.
 *  - ob_group_permission : which features a group/role grants (replaces habilitation).
 *  - ob_section_permission : per-section deny-list. A row = a feature refused at
 *                          that section and all its descendants; no rows = no cap.
 *  - ob_user_assignment  : a person holds a ROLE in a section (section-scoped,
 *                          inherited to child sections). Global group membership
 *                          stays on pompier.GP_ID / GP_ID2.
 *
 * Legacy tables are left intact as reference data and back-filled by the
 * companion data migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Group & role definitions. `id` is assigned manually to preserve the
        // legacy GP_ID (which can be negative, e.g. -1 = "accès interdit").
        Schema::create('ob_group', function (Blueprint $table) {
            $table->smallInteger('id')->primary();
            $table->string('name', 60);
            $table->enum('kind', ['group', 'role'])->default('group');
            $table->string('usage', 10)->default('internes');
            $table->unsignedTinyInteger('ordering')->default(50);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index('kind');
        });

        // group/role -> feature grant (replaces `habilitation`).
        Schema::create('ob_group_permission', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('group_id');
            $table->integer('feature_id');
            $table->timestamps();

            $table->unique(['group_id', 'feature_id']);
            $table->index('feature_id');
        });

        // Per-section deny-list. A row = a feature refused at that section (and
        // cascaded to its descendants). No rows = nothing refused.
        Schema::create('ob_section_permission', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('section_id');
            $table->integer('feature_id');
            $table->timestamps();

            $table->unique(['section_id', 'feature_id']);
            $table->index('section_id');
        });

        // person holds a role in a section (section-scoped, inherited downward).
        Schema::create('ob_user_assignment', function (Blueprint $table) {
            $table->id();
            $table->integer('person_id');
            $table->smallInteger('section_id');
            $table->smallInteger('group_id');
            $table->timestamps();

            $table->unique(['person_id', 'section_id', 'group_id']);
            $table->index('person_id');
            $table->index(['section_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ob_user_assignment');
        Schema::dropIfExists('ob_section_permission');
        Schema::dropIfExists('ob_group_permission');
        Schema::dropIfExists('ob_group');
    }
};
