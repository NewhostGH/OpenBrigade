<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Native photo-album module — replaces the legacy SPGM gallery.
 *
 *  - ob_photo_album : one album per row, scoped to a section.
 *  - ob_photo       : individual photos stored on disk
 *                     (storage/app/public/photos/{S_ID}/{album_id}/{filename}).
 *
 * Images are served directly by the web server via the `storage:link` symlink;
 * access is gate-kept at the album-listing level (permission 44).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_photo_album', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('S_ID')->default(0);
            $table->string('name', 100);
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('cover_photo_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('S_ID');
        });

        Schema::create('ob_photo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('album_id');
            $table->unsignedSmallInteger('S_ID')->default(0);
            $table->string('filename', 255);
            $table->string('caption', 255)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('album_id')->references('id')->on('ob_photo_album')->onDelete('cascade');
            $table->index('album_id');
            $table->index('S_ID');
        });

        // Deferred FK: cover_photo_id added after ob_photo exists.
        Schema::table('ob_photo_album', function (Blueprint $table) {
            $table->foreign('cover_photo_id')->references('id')->on('ob_photo')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('ob_photo_album', function (Blueprint $table) {
            $table->dropForeign(['cover_photo_id']);
        });
        Schema::dropIfExists('ob_photo');
        Schema::dropIfExists('ob_photo_album');
    }
};
