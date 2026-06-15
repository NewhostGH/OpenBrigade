<?php

namespace Database\Seeders;

use App\Support\Habilitations\BaseHabilitations;
use App\Support\Habilitations\SuperAdminProvisioner;
use Illuminate\Database\Seeder;

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
}
