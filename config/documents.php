<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document library file storage
    |--------------------------------------------------------------------------
    |
    | Canonical location: storage/app/private/{storage_subpath}/{S_ID}/{DF_ID}/
    | {D_NAME} — organised per section (section 0 = root / no section), with a
    | sub-folder segment only for documents inside a folder.
    |
    | legacy_subpath is the old location ({legacy_root}/user-data/files_section,
    | see config/legacy_bridge.php) kept as a read-only fallback so files
    | uploaded before the move still download; the migrate-storage command and
    | folder moves relocate them into the canonical tree.
    |
    */

    'storage_subpath' => 'documents',

    'legacy_subpath' => 'user-data/files_section',

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
