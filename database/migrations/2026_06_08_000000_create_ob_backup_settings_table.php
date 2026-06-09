<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_backup_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('retention_count')->default(30);
            $table->boolean('auto_enabled')->default(false);

            // Cron-like schedule for automatic backups (evaluated by RunAutomaticBackup::isDue):
            // 'frequency' picks the recurrence unit, 'run_time' the time of day (for 'hourly'
            // only its minute is used), 'start_date' the first day the schedule is active,
            // 'day_of_week' (0=dimanche..6=samedi) / 'day_of_month' (1-31) narrow weekly/monthly runs.
            $table->enum('frequency', ['hourly', 'daily', 'weekly', 'monthly'])->default('daily');
            $table->time('run_time')->default('03:00:00');
            $table->date('start_date')->nullable();
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->unsignedTinyInteger('day_of_month')->nullable();

            // Filename pattern for generated dumps (both manual and automatic), e.g.
            // 'backup_{date}_{time}'. See BackupController::buildFilename for tokens.
            $table->string('naming_pattern', 100)->default('backup_{date}_{time}');

            $table->timestamp('last_auto_backup_at')->nullable();
            $table->timestamps();
        });

        DB::table('ob_backup_settings')->insert([
            'retention_count' => 30,
            'auto_enabled' => false,
            'frequency' => 'daily',
            'run_time' => '03:00:00',
            'start_date' => now()->toDateString(),
            'day_of_week' => 1,
            'day_of_month' => 1,
            'naming_pattern' => 'backup_{date}_{time}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ob_backup_settings');
    }
};
