<?php

namespace App\Console\Commands;

use App\Http\Controllers\BackupController;
use App\Models\BackupSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunAutomaticBackup extends Command
{
    protected $signature = 'backup:run-scheduled';

    protected $description = 'Run a database backup if the configured automatic-backup schedule is due';

    public function handle(BackupController $backups): int
    {
        $settings = BackupSetting::current();

        if (! $settings->auto_enabled) {
            $this->info('Automatic backups are disabled.');

            return self::SUCCESS;
        }

        if (! $this->isDue($settings)) {
            $this->info('Automatic backup not due yet.');

            return self::SUCCESS;
        }

        [$filename, $error] = $backups->createBackup();

        if ($error !== null) {
            $this->error('Backup failed: '.$error);

            return self::FAILURE;
        }

        $settings->update(['last_auto_backup_at' => now()]);

        $this->info("Backup created: {$filename}");

        return self::SUCCESS;
    }

    /**
     * Mimics a cron entry: runs once per recurrence unit, at the configured
     * time (within the scheduler's one-minute resolution), no earlier than
     * `start_date`. 'hourly' repeats every hour at the configured minute;
     * the other frequencies run at most once per calendar day.
     */
    private function isDue(BackupSetting $settings): bool
    {
        $now = Carbon::now();

        if ($settings->start_date && $now->lt($settings->start_date->startOfDay())) {
            return false;
        }

        if ($this->alreadyRan($settings, $now)) {
            return false;
        }

        if (! $this->matchesSchedule($settings, $now)) {
            return false;
        }

        return $this->matchesRunTime($settings, $now);
    }

    private function alreadyRan(BackupSetting $settings, Carbon $now): bool
    {
        if ($settings->last_auto_backup_at === null) {
            return false;
        }

        return $settings->frequency === 'hourly'
            ? $settings->last_auto_backup_at->isSameHour($now)
            : $settings->last_auto_backup_at->isSameDay($now);
    }

    private function matchesSchedule(BackupSetting $settings, Carbon $now): bool
    {
        return match ($settings->frequency) {
            'weekly' => $now->dayOfWeek === (int) $settings->day_of_week,
            'monthly' => $now->day === min((int) $settings->day_of_month, $now->daysInMonth),
            default => true,
        };
    }

    private function matchesRunTime(BackupSetting $settings, Carbon $now): bool
    {
        $runTime = Carbon::parse($settings->run_time);

        return $settings->frequency === 'hourly'
            ? $now->format('i') === $runTime->format('i')
            : $now->format('H:i') === $runTime->format('H:i');
    }
}
