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

    /*
     * Code version — the single source of truth is the root VERSION file
     * (kept in sync with CHANGELOG.md; see docs/dev/versioning.md). APP_VERSION
     * overrides it when set; '5.5' is the last-resort fallback if the file is
     * unreadable. This is the *code* version; the *installed* version (what the
     * DB has been migrated to) lives in configuration row `version` and overlays
     * this at boot — see App\Services\GeneralSettingService::appVersion().
     */
    'version' => env('APP_VERSION')
        ?: (trim((string) @file_get_contents(dirname(__DIR__).'/VERSION')) ?: '5.5'),

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
     * Geolocation map defaults (Leaflet).
     * center: [lat, lng] fallback when no markers are present.
     * zoom: initial zoom when using center fallback.
     * zoom_fit: zoom used when centering on the first marker before fitBounds.
     */
    'geo' => [
        'center' => [46.5, 2.5],
        'zoom' => 6,
        'zoom_fit' => 8,
    ],

    /*
     * Endpoint receiving the anonymous weekly telemetry ping when the
     * "Aider à améliorer" setting (configuration row 80) is enabled.
     * The payload is strictly anonymous — see ob:telemetry:ping.
     */
    'telemetry_url' => env('OB_TELEMETRY_URL', 'https://telemetry.openbrigade.fr'),

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
