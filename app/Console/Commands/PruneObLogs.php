<?php

namespace App\Console\Commands;

use App\Models\ObLogEntry;
use App\Services\LoggingSettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Prune ob_log_entry rows older than the configured retention window
 * (obs_db_retention_days). A retention of 0 disables pruning.
 */
class PruneObLogs extends Command
{
    protected $signature = 'ob:logs:prune';

    protected $description = 'Delete observability log entries older than the configured retention window';

    public function handle(LoggingSettingService $settings): int
    {
        if (! Schema::hasTable('ob_log_entry')) {
            $this->warn('ob_log_entry table not present — nothing to prune.');

            return self::SUCCESS;
        }

        $days = $settings->int('obs_db_retention_days');
        if ($days <= 0) {
            $this->info('DB log retention is unlimited (0) — nothing to prune.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($days);
        $deleted = ObLogEntry::query()->where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} log entries older than {$days} day(s).");

        return self::SUCCESS;
    }
}
