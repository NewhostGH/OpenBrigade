<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Baseline schema migration
|--------------------------------------------------------------------------
|
| This migration documents the pre-existing schema (originally managed
| via raw SQL upgrade scripts in sql/). New schema changes must be
| added as their own numbered migration files.
|
| The legacy sql/reference.sql file remains as the authoritative reference
| for a fresh install until this migration is completed.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        // Laravel's default users table is intentionally NOT created here
        // because the application uses a custom personnel table (personnels).
        // The full baseline schema will be ported from sql/reference.sql
        // in follow-up migration commits, one domain at a time.

        // Placeholder: ensure the migrations table itself existed.
        // Run: php artisan migrate to apply this no-op baseline marker.
    }

    public function down(): void
    {
        //
    }
};
