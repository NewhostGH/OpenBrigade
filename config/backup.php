<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Storage
    |--------------------------------------------------------------------------
    |
    | The filesystem disk and directory (relative to that disk's root) where
    | database backups are written, listed, downloaded and pruned.
    |
    */

    'disk' => env('BACKUP_DISK', 'local'),
    'path' => env('BACKUP_PATH', 'backups'),

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Number of backup files to keep; older ones are pruned after each backup.
    |
    */

    'keep' => env('BACKUP_KEEP', 30),

    /*
    |--------------------------------------------------------------------------
    | Stored files
    |--------------------------------------------------------------------------
    |
    | When enabled, each backup bundles the DB dump and the whole stored-files
    | tree into a single .zip so a restore recovers both. This covers every
    | user file: profile photos (app/private/profile_pictures), album photos
    | (app/photos), the document library (app/private/documents), section
    | assets & RIB (app/private/sections), the charter, and theme/logo
    | (app/public/theme). The backups directory itself is always excluded.
    | Disable to keep DB-only .sql backups (legacy behaviour).
    |
    */

    'include_files' => (bool) env('BACKUP_INCLUDE_FILES', true),

    // Root of the stored-files tree included in the archive.
    'files_path' => env('BACKUP_FILES_PATH', storage_path('app')),

    /*
    |--------------------------------------------------------------------------
    | Off-site mirror
    |--------------------------------------------------------------------------
    |
    | Optional second disk each backup is copied to right after it is written,
    | so a lost server does not lose the backups with it. Point at any
    | configured filesystem disk (typically the S3-compatible "s3" disk). Leave
    | empty to keep backups on the primary disk only.
    |
    */

    'offsite_disk' => env('BACKUP_OFFSITE_DISK') ?: null,

    'offsite_path' => env('BACKUP_OFFSITE_PATH', 'backups'),

    /*
    |--------------------------------------------------------------------------
    | Restore drill
    |--------------------------------------------------------------------------
    |
    | When enabled, a scheduled drill restores the latest backup into a scratch
    | database and asserts it loads, proving backups are actually recoverable.
    | The drill never touches the live database.
    |
    */

    'restore_drill' => (bool) env('BACKUP_RESTORE_DRILL', true),

    // Scratch database used by the restore drill (created and dropped each run).
    'drill_database' => env('BACKUP_DRILL_DATABASE', 'openbrigade_restore_drill'),

];
