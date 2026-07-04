<?php

// project: OpenBrigade

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Periodic restore drill: restores the latest backup into a scratch database
 * and asserts it loads, proving backups are actually recoverable. Scheduled
 * from routes/console.php when backup.restore_drill is enabled.
 */
class RunBackupRestoreDrill extends Command
{
    protected $signature = 'backup:restore-drill';

    protected $description = 'Restore the latest backup into a scratch database to verify it is recoverable';

    public function handle(BackupService $backups): int
    {
        [$passed, $message] = $backups->restoreDrill();

        if ($passed) {
            $this->info($message);
            Log::channel(config('logging.default'))->info('Backup restore drill passed', ['detail' => $message]);

            return self::SUCCESS;
        }

        $this->error($message);
        Log::channel(config('logging.default'))->error('Backup restore drill FAILED', ['detail' => $message]);

        return self::FAILURE;
    }
}
