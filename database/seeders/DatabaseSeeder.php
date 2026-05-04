<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Reference data (organisation types, grades, etc.) is migrated
     * from sql/specific/ into individual seeder classes incrementally.
     */
    public function run(): void
    {
        // $this->call(OrganisationTypeSeeder::class);
    }
}
