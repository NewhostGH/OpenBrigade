<?php

namespace App\Console\Commands;

use App\Services\GeneralSettingService;
use App\Support\Audit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * OPTIMIZE TABLE over every table — the successor of the legacy daily
 * database_optimize(). Scheduled weekly (Sundays 04:30), gated on the
 * auto_optimize setting (configuration row 14, Administration ▸
 * Maintenance); --force bypasses the gate for the manual button.
 */
class DbOptimize extends Command
{
    protected $signature = 'ob:db:optimize {--force : Run even when the auto_optimize setting is off}';

    protected $description = 'Optimize every database table (gated on the auto_optimize setting)';

    public function handle(GeneralSettingService $settings): int
    {
        if (! $this->option('force') && ! $settings->autoOptimizeEnabled()) {
            $this->info('Automatic optimization is disabled (setting 14) — skipped.');

            return self::SUCCESS;
        }

        try {
            $tables = array_map(
                fn ($row) => (string) current((array) $row),
                DB::select('SHOW TABLES'),
            );
        } catch (\Throwable $e) {
            $this->error('Cannot list tables: '.$e->getMessage());

            return self::FAILURE;
        }

        $ok = 0;
        $failed = [];

        foreach ($tables as $table) {
            try {
                // Isolated per table: one locked/broken table must not stop the run.
                DB::statement('OPTIMIZE TABLE `'.str_replace('`', '``', $table).'`');
                $ok++;
            } catch (\Throwable $e) {
                $failed[] = $table;
                $this->warn("✗ {$table}: {$e->getMessage()}");
            }
        }

        Audit::action('maintenance.db_optimized', [
            'tables_ok' => $ok,
            'tables_failed' => $failed,
            'forced' => (bool) $this->option('force'),
        ]);

        $this->info("Optimized {$ok}/".count($tables).' table(s).');

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }
}
