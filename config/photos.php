<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Photo album module
    |--------------------------------------------------------------------------
    |
    | feature_view   : F_ID required to browse albums (same as document view)
    | feature_manage : F_ID required to create / upload / delete (same as doc manage)
    |
    */

    'feature_view' => 44,

    'feature_manage' => 47,

    'supported_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],

    'max_size_mb' => 20,

];
