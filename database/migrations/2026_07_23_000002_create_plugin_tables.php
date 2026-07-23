<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Plugin marketplace storage:
 *
 * - ob_plugin — one row per installed plugin (code lives in plugins/<slug>).
 * - ob_plugin_registry — KASM-style registry list: admins can add third-party
 *   registry URLs serving a registry.json catalog; seeded with the official
 *   OpenBrigade registry (not removable from the UI).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_plugin', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->string('version', 20);
            // Compatibility window captured from the manifest at install time,
            // so an app upgrade can flag now-incompatible plugins.
            $table->string('min_app_version', 20)->default('');
            $table->string('max_app_version', 20)->default('');
            $table->boolean('enabled')->default(false);
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ob_plugin_registry', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('url', 500);
            $table->boolean('enabled')->default(true);
            $table->boolean('is_default')->default(false);
            // Per-registry escape hatches (dev / intercepting proxies): TLS
            // peer verification and package checksum verification.
            $table->boolean('verify_ssl')->default(true);
            $table->boolean('verify_checksum')->default(true);
            $table->timestamps();
        });

        DB::table('ob_plugin_registry')->insert([
            'name' => 'Dépôt officiel OpenBrigade',
            'url' => 'https://raw.githubusercontent.com/NewhostGH/openbrigade-plugins/main/registry.json',
            'enabled' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ob_plugin_registry');
        Schema::dropIfExists('ob_plugin');
    }
};
