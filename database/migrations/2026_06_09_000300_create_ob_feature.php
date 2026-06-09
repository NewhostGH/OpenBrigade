<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canonical feature/module registry.
 *
 * Unifies the two legacy `configuration` buckets — TAB 1 ("Fonctionnalités")
 * and TAB 6 ("Modules") — into a single `ob_feature` table that drives the
 * Administration ▸ Fonctionnalités screen and the runtime `feature:<key>`
 * gate (route middleware + sidebar visibility).
 *
 *  - key             : stable identifier, mirrors the legacy configuration.NAME.
 *  - group           : functional domain (logistique, personnel, planning, …).
 *  - status          : 'native' (migrated, gateable) | 'wip' (not yet
 *                      transitioned — surfaced with a WIP marker, never gated).
 *  - enabled         : runtime on/off, kept in sync with the legacy
 *                      configuration row so un-migrated code keeps working.
 *  - legacy_config_id: the source configuration.ID, for two-way sync.
 *
 * The legacy `configuration` rows are left intact and back-filled by the
 * companion data migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_feature', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('group', 40)->nullable();
            $table->enum('status', ['native', 'wip'])->default('native');
            $table->string('icon', 40)->nullable();
            $table->boolean('enabled')->default(false);
            $table->unsignedSmallInteger('ordering')->default(50);
            $table->unsignedInteger('legacy_config_id')->nullable();
            $table->timestamps();

            $table->index('group');
            $table->index('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ob_feature');
    }
};
