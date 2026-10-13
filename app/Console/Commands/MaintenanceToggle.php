<?php

namespace App\Console\Commands;

use App\Support\Audit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Shell-side switch for the administrable maintenance mode (configuration
 * rows 37 `maintenance_mode` and 41 `maintenance_text`), the same flag as
 * Administration ▸ Maintenance. Used by the release runbook and CD pipeline
 * to fence a deploy (docs/admin/release-runbook.md).
 */
class MaintenanceToggle extends Command
{
    protected $signature = 'ob:maintenance
        {state : on or off}
        {--message= : Banner text shown while maintenance is on}';

    protected $description = 'Turn the administrable maintenance mode on or off';

    public function handle(): int
    {
        $state = strtolower((string) $this->argument('state'));

        if (! in_array($state, ['on', 'off'], true)) {
            $this->error('State must be "on" or "off".');

            return self::INVALID;
        }

        $updated = DB::table('configuration')->where('NAME', 'maintenance_mode')
            ->update(['VALUE' => $state === 'on' ? '1' : '0']);

        if ($updated === 0 && ! DB::table('configuration')->where('NAME', 'maintenance_mode')->exists()) {
            $this->error('The maintenance_mode setting row is missing: run the migrations first.');

            return self::FAILURE;
        }

        $message = $this->option('message');
        if (is_string($message) && $message !== '') {
            DB::table('configuration')->where('NAME', 'maintenance_text')->update(['VALUE' => $message]);
        }

        Audit::action('maintenance.mode_'.$state, ['source' => 'cli']);
        $this->info("Maintenance mode {$state}.");

        return self::SUCCESS;
    }
}
