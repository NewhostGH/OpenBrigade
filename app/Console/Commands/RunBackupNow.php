<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

/**
 * Take a backup immediately, regardless of the automatic schedule: the CLI
 * twin of Configuration ▸ Sauvegarde ▸ « Créer une sauvegarde ». The release
 * runbook runs it before every deploy (docs/admin/release-runbook.md).
 */
class RunBackupNow extends Command
{
    protected $signature = 'backup:now';

    protected $description = 'Create a backup now (database, plus files when BACKUP_INCLUDE_FILES)';

    public function handle(BackupService $backups): int
    {
        [$filename, $error] = $backups->createBackup();

        if ($error !== null) {
            $this->error('Backup failed: '.$error);

            return self::FAILURE;
        }

        $this->info("Backup created: {$filename}");

        return self::SUCCESS;
    }
}
