<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upload safety
    |--------------------------------------------------------------------------
    |
    | Shared hardening rules applied by App\Services\UploadSecurityService to
    | every file upload across the app. Per-feature extension whitelists and
    | size limits still live in config/photos.php / config/documents.php and the
    | relevant controllers; this file only carries the cross-cutting defences.
    |
    */

    /*
    | Extensions that must never be accepted, whatever the feature whitelist
    | says — executables, scripts and server-side code. Checked case-insensitively
    | and against every extension part of the filename (e.g. "x.php.png").
    */
    'forbidden_extensions' => [
        'php', 'phar', 'phtml', 'php3', 'php4', 'php5', 'php7', 'pht',
        'exe', 'com', 'bat', 'cmd', 'msi', 'scr', 'cpl', 'jar',
        'sh', 'bash', 'csh', 'ksh', 'zsh', 'ps1', 'psm1', 'vbs', 'vbe',
        'js', 'jse', 'wsf', 'wsh', 'hta', 'reg', 'dll', 'so', 'bin',
        'pl', 'py', 'rb', 'cgi', 'asp', 'aspx', 'jsp', 'htaccess',
    ],

    /*
    | Leading magic bytes (hex) that identify dangerous binary content,
    | regardless of the declared extension. Matched at offset 0.
    */
    'magic_byte_blocklist' => [
        '4d5a',       // MZ — Windows PE / DOS executable
        '7f454c46',   // .ELF — Linux executable
        '23212f',     // #!/ — script shebang
        'cafebabe',   // Java class / Mach-O fat binary
        'feedface',   // Mach-O 32-bit
        'feedfacf',   // Mach-O 64-bit
    ],

    /*
    | When true, the declared extension must be consistent with the real MIME
    | type detected from the file contents (finfo). Surfaced as the
    | sec_upload_mime_hardening toggle; this is just the static fallback default.
    */
    'mime_hardening' => true,

    /*
    | Absolute hard ceiling (KB) applied on top of any per-feature limit, so a
    | mis-configured caller can never accept an unbounded file.
    */
    'absolute_max_kb' => 51200, // 50 MB

    /*
    | ClamAV behaviour. host/port defaults are overridden per-install through the
    | Sécurité ▸ Renforcement settings; timeout and fail_open are static.
    */
    // Default state of the malware-scan toggle when no configuration row exists
    // yet. ON inside Docker (a clamav service ships in docker-compose), OFF
    // otherwise. The admin toggle in Sécurité ▸ Renforcement overrides this.
    'scan_default' => (bool) env('SECURITY_UPLOAD_SCAN', false),

    'clamav' => [
        'timeout' => 30,
        // When the daemon is unreachable, let the upload through (logged) rather
        // than blocking all uploads. Set false to fail closed.
        'fail_open' => true,
    ],

];
