<?php

use App\Services\BackupService;
use Illuminate\Support\Facades\Storage;

/**
 * DB-free coverage of the backup engine's file surface: which files land in the
 * archive (and which are excluded), and the off-site mirror. The mysqldump /
 * mysql paths need a live database and are exercised manually / in the drill.
 */

// ── File collection ──────────────────────────────────────────────────────────

it('maps stored files to their storage/app archive paths', function () {
    $root = storage_path('app/backup_test_'.uniqid());
    @mkdir($root.'/private/profile_pictures', 0777, true);
    file_put_contents($root.'/private/profile_pictures/42.jpg', 'x');

    try {
        $entries = (new BackupService)->filesToArchive($root, $root.'/backups');

        $names = array_values($entries);
        expect($names)->toHaveCount(1);
        // Entry name mirrors the real path relative to storage_path().
        expect($names[0])->toEndWith('/private/profile_pictures/42.jpg')
            ->and($names[0])->toStartWith('storage/app/backup_test_');
    } finally {
        exec('rm -rf '.escapeshellarg($root));
    }
});

it('excludes the backups directory from the archive', function () {
    $root = storage_path('app/backup_test_'.uniqid());
    @mkdir($root.'/photos', 0777, true);
    @mkdir($root.'/backups', 0777, true);
    file_put_contents($root.'/photos/keep.jpg', 'x');
    file_put_contents($root.'/backups/old.zip', 'should-not-be-archived');

    try {
        $entries = (new BackupService)->filesToArchive($root, $root.'/backups');
        $names = array_values($entries);

        expect($names)->toHaveCount(1)
            ->and($names[0])->toEndWith('/photos/keep.jpg');
        expect(implode('|', $names))->not->toContain('old.zip');
    } finally {
        exec('rm -rf '.escapeshellarg($root));
    }
});

it('returns nothing when the files root does not exist', function () {
    expect((new BackupService)->filesToArchive(storage_path('app/does_not_exist_'.uniqid()), '/nope'))
        ->toBe([]);
});

// ── Off-site mirror ──────────────────────────────────────────────────────────

it('copies a backup to the off-site disk', function () {
    Storage::fake('local');
    Storage::fake('s3');
    config(['backup.disk' => 'local', 'backup.offsite_disk' => 's3', 'backup.offsite_path' => 'backups']);

    Storage::disk('local')->put('backups/backup.zip', 'ARCHIVE');

    $error = (new BackupService)->mirrorOffsite('backups/backup.zip', 'backup.zip');

    expect($error)->toBeNull();
    Storage::disk('s3')->assertExists('backups/backup.zip');
    expect(Storage::disk('s3')->get('backups/backup.zip'))->toBe('ARCHIVE');
});

it('is a no-op when no off-site disk is configured', function () {
    Storage::fake('local');
    config(['backup.disk' => 'local', 'backup.offsite_disk' => null]);
    Storage::disk('local')->put('backups/backup.zip', 'ARCHIVE');

    expect((new BackupService)->mirrorOffsite('backups/backup.zip', 'backup.zip'))->toBeNull();
});

// ── Restore drill guard ──────────────────────────────────────────────────────

it('reports no backup to verify when the disk is empty', function () {
    Storage::fake('local');
    config(['backup.disk' => 'local', 'backup.path' => 'backups']);

    [$passed, $message] = (new BackupService)->restoreDrill();

    expect($passed)->toBeFalse()
        ->and($message)->toContain('Aucune sauvegarde');
});
