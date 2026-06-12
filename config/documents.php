<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document library file storage
    |--------------------------------------------------------------------------
    |
    | Library files live on disk under {legacy_root}/{files_subpath}/{S_ID}/
    | {DF_ID}/{D_NAME}. The legacy root comes from config/legacy_bridge.php.
    |
    | TODO: Migrate code — move this tree under storage/app/documents after the
    | legacy app is decommissioned, and drop the dependency on legacy_root.
    |
    */

    'files_subpath' => 'user-data/files_section',

    /*
    |--------------------------------------------------------------------------
    | Upload constraints (mirror of the legacy config.php whitelist)
    |--------------------------------------------------------------------------
    */

    'supported_extensions' => [
        'doc', 'docx', 'zip', 'pps', 'ppt', 'pptx', 'xls', 'xlsx',
        'pdf', 'jpg', 'jpeg', 'png', 'odt', 'mp3',
    ],

    'max_size_mb' => 20,

    /*
    |--------------------------------------------------------------------------
    | Feature ids (F_ID) gating the library
    |--------------------------------------------------------------------------
    */

    'feature_view' => 44,    // browse the library
    'feature_manage' => 47,  // upload / edit / delete / manage folders

];
