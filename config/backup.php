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

];
