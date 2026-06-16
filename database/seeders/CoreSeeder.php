<?php

namespace Database\Seeders;

use App\Support\Habilitations\BaseHabilitations;
use App\Support\Habilitations\SuperAdminProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Production-safe, idempotent canonical data.
 *
 * Rebuilds the habilitation base data (permission catalog, the four base
 * groups + their default grants, the per-organisation-type roles) and
 * guarantees a super-admin account exists. Shares {@see BaseHabilitations} with
 * the rebuild migration so the two never drift; safe to run on any environment.
 *
 * It does NOT seed dev fixtures — see {@see DevelopmentDataSeeder}.
 */
class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRootSection();

        (new BaseHabilitations)->seed();

        $result = (new SuperAdminProvisioner)->ensure();
        if ($result['created'] && $result['password'] !== null) {
            $this->command->warn('Super-admin account created.');
            $this->command->line("  login:    {$result['code']}");
            $this->command->line("  password: {$result['password']}");
            $this->command->line('  (must be changed on first login)');
        } else {
            $this->command->info('Super-admin account already present.');
        }
    }

    /**
     * Ensure the organizational root section (S_ID = 0, S_PARENT = -1) exists.
     * Legacy-imported DBs already have it; this covers fresh installs so that
     * SuperAdminProvisioner::rootSectionId() returns 0 and the navbar switcher
     * can show it. Uses insertOrIgnore — safe to call on every seeder run.
     */
    private function seedRootSection(): void
    {
        DB::table('section')->insertOrIgnore([
            'S_ID' => 0,
            'S_PARENT' => -1,
            'S_CODE' => 'ORG',
            'S_DESCRIPTION' => 'Organisation',
            'S_HIDE' => 0,
            'S_INACTIVE' => 0,
            'S_ORDER' => 0,
        ]);
    }
}
