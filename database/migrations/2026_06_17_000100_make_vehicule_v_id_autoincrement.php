<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The legacy `vehicule` table imported `V_ID` as a plain INT primary key with a
 * default of 0 (no AUTO_INCREMENT), but the Vehicle model treats it as an
 * incrementing key. As a result `Vehicle::create()` omitted V_ID and the DB
 * inserted 0 every time — colliding with the existing placeholder row on the
 * second insert ("Duplicate entry '0' for key 'PRIMARY'"). Bringing the column
 * in line with the model (and with pompier.P_ID, which is already
 * AUTO_INCREMENT) fixes vehicle creation.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `vehicule` MODIFY `V_ID` INT(11) NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `vehicule` MODIFY `V_ID` INT(11) NOT NULL DEFAULT 0');
    }
};
