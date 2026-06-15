<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * CoreSeeder holds the production-canonical data (habilitation base groups,
     * permission catalog, super-admin account) and runs everywhere.
     * DevelopmentDataSeeder adds throwaway fixtures and runs only outside
     * production.
     */
    public function run(): void
    {
        $this->call(CoreSeeder::class);

        if (app()->environment(['local', 'development', 'testing'])) {
            $this->call(DevelopmentDataSeeder::class);
        }
    }
}
