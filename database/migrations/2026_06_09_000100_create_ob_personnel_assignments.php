<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Decoupled personnel assignment tables.
 *
 *  - ob_personnel_section : section memberships (replaces section_id in ob_user_assignment)
 *  - ob_personnel_group   : group memberships (replaces pompier.GP_ID / GP_ID2)
 *
 * ob_user_assignment.section_id gets a DEFAULT 0 so role entries with no
 * section restriction use 0 as the global sentinel (kept NOT NULL so the
 * three-column unique index works reliably in MariaDB/MySQL).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_personnel_section', function (Blueprint $table) {
            $table->id();
            $table->integer('person_id');
            $table->smallInteger('section_id');
            $table->timestamps();

            $table->unique(['person_id', 'section_id']);
            $table->index('person_id');
        });

        Schema::create('ob_personnel_group', function (Blueprint $table) {
            $table->id();
            $table->integer('person_id');
            $table->smallInteger('group_id');
            $table->timestamps();

            $table->unique(['person_id', 'group_id']);
            $table->index('person_id');
        });

        // Ensure section_id defaults to 0 (global sentinel — section-scoped design keeps
        // NOT NULL so the three-column unique index works reliably).
        Schema::table('ob_user_assignment', function (Blueprint $table) {
            $table->smallInteger('section_id')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ob_user_assignment', function (Blueprint $table) {
            $table->smallInteger('section_id')->default(0)->nullable(false)->change();
        });

        Schema::dropIfExists('ob_personnel_group');
        Schema::dropIfExists('ob_personnel_section');
    }
};
