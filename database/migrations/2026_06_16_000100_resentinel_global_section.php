<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Re-sentinel the "global / all sections" marker in the habilitation tables
 * from 0 to -1, so that section_id = 0 can become the real organizational root
 * section (S_ID = 0, whose own S_PARENT is the -1 virtual node).
 *
 * Only ob_user_assignment and ob_user_permission used 0 to mean "global".
 * ob_section_permission rows always referenced real sections, so a 0 there now
 * legitimately means a ceiling on the root section and is left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ob_user_assignment')->where('section_id', 0)->update(['section_id' => -1]);
        DB::table('ob_user_permission')->where('section_id', 0)->update(['section_id' => -1]);
    }

    public function down(): void
    {
        DB::table('ob_user_assignment')->where('section_id', -1)->update(['section_id' => 0]);
        DB::table('ob_user_permission')->where('section_id', -1)->update(['section_id' => 0]);
    }
};
