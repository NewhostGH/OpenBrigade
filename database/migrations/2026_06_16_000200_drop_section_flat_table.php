<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('section_flat');
    }

    public function down(): void
    {
        // section_flat was a denormalized cache rebuilt by rebuild_section_flat.php.
        // Depth ordering is now derived from the section tree at query time.
        // Recreating this table is not supported; restore from a backup if needed.
    }
};
