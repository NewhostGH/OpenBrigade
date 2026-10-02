<?php

use App\Services\BackupService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function maintenanceRows(): void
{
    Schema::create('configuration', function (Blueprint $t) {
        $t->integer('ID')->primary();
        $t->string('NAME');
        $t->string('VALUE')->nullable();
    });
    DB::table('configuration')->insert([
        ['ID' => 37, 'NAME' => 'maintenance_mode', 'VALUE' => '0'],
        ['ID' => 41, 'NAME' => 'maintenance_text', 'VALUE' => ''],
    ]);
}

// ── ob:maintenance ───────────────────────────────────────────────────────────

test('ob:maintenance turns the mode on with a banner, then off', function () {
    maintenanceRows();

    $this->artisan('ob:maintenance', ['state' => 'on', '--message' => 'Mise à jour en cours'])->assertSuccessful();

    expect(DB::table('configuration')->where('NAME', 'maintenance_mode')->value('VALUE'))->toBe('1')
        ->and(DB::table('configuration')->where('NAME', 'maintenance_text')->value('VALUE'))->toBe('Mise à jour en cours');

    $this->artisan('ob:maintenance', ['state' => 'off'])->assertSuccessful();

    expect(DB::table('configuration')->where('NAME', 'maintenance_mode')->value('VALUE'))->toBe('0');
});

test('ob:maintenance rejects an unknown state', function () {
    maintenanceRows();

    $this->artisan('ob:maintenance', ['state' => 'maybe'])->assertExitCode(2);
});

test('ob:maintenance fails when the setting row is missing', function () {
    Schema::create('configuration', function (Blueprint $t) {
        $t->integer('ID')->primary();
        $t->string('NAME');
        $t->string('VALUE')->nullable();
    });

    $this->artisan('ob:maintenance', ['state' => 'on'])->assertFailed();
});

// ── backup:now ───────────────────────────────────────────────────────────────

test('backup:now reports the created file or the error', function () {
    $service = Mockery::mock(BackupService::class);
    $service->shouldReceive('createBackup')->once()->andReturn(['backup_x.zip', null]);
    app()->instance(BackupService::class, $service);

    $this->artisan('backup:now')->expectsOutputToContain('backup_x.zip')->assertSuccessful();

    $failing = Mockery::mock(BackupService::class);
    $failing->shouldReceive('createBackup')->once()->andReturn(['', 'mysqldump not found']);
    app()->instance(BackupService::class, $failing);

    $this->artisan('backup:now')->expectsOutputToContain('mysqldump not found')->assertFailed();
});
