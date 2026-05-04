<?php

/*
|--------------------------------------------------------------------------
| OpenBrigade application constants
|--------------------------------------------------------------------------
|
| Values migrated from the legacy config.php. Version tracking and
| organisation-type definitions live here so they are not scattered
| across procedural include chains.
|
*/

return [

    'version' => env('APP_VERSION', '5.5'),

    /*
     * Maximum file upload size in megabytes.
     * Must align with upload_max_filesize / post_max_size in php.ini.
     */
    'max_upload_mb' => (int) env('MAX_UPLOAD_MB', 20),

    /*
     * Maximum rows returned in list/table views.
     */
    'max_list_rows' => (int) env('MAX_LIST_ROWS', 500),

    /*
     * Available organisation type pre-configurations.
     * Index 0 is "no pre-configuration" and must remain first.
     */
    'organisation_types' => [
        0 => 'Sans préconfiguration',
        1 => 'Association de secourisme',
        2 => "Service d'incendie et Secours",
        3 => 'SDIS',
        4 => 'Armée',
        5 => 'SSLIA',
        6 => 'Hôpital',
    ],

];
