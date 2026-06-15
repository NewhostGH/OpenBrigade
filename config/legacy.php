<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

return [

    /*
    |--------------------------------------------------------------------------
    | Legacy database connection
    |--------------------------------------------------------------------------
    |
    | Optional connection to the previous application's database, used by the
    | `legacy:migration:validate` command to compare row counts against the
    | migrated OpenBrigade tables. When unset, the command runs structural
    | validation only.
    |
    */

    'db' => [
        'dsn' => env('LEGACY_DB_DSN'),
        'host' => env('LEGACY_DB_HOST'),
        'port' => env('LEGACY_DB_PORT', '3306'),
        'database' => env('LEGACY_DB_DATABASE'),
        'username' => env('LEGACY_DB_USERNAME'),
        'password' => env('LEGACY_DB_PASSWORD'),
    ],

];
