<?php

/*
|--------------------------------------------------------------------------
| Release verification
|--------------------------------------------------------------------------
|
| Configuration for the post-deploy smoke-check gate (`ob:release:verify`,
| App\Services\ReleaseVerificationService). These are the "release
| verification" knobs of the RELEASE epic — a CD pipeline (issue #73) runs the
| command right after a deploy and gates the release on its exit code.
|
*/

return [

    /*
     * Treat `degraded` checks as a failure, not just `down`. The CLI --strict
     * flag forces this on for a single run regardless of the default here.
     */
    'strict' => env('RELEASE_VERIFY_STRICT', false),

    /*
     * Built front-end asset manifest. The default is where laravel-vite-plugin
     * writes it; a missing manifest means `npm run build` never ran for this
     * release. The `.vite/manifest.json` sibling is also accepted for newer
     * plugin versions.
     */
    'manifest_path' => public_path('build/manifest.json'),

    /*
     * Optional expected deployed version. When set — e.g. the pipeline passes
     * the tag being released — verification fails if it does not match the
     * installed-version SSOT (configuration row 1, see config('brigade.version')).
     * Empty skips the assertion.
     */
    'expected_version' => env('RELEASE_EXPECTED_VERSION', ''),

    /*
     * Optional monitoring / deploy webhook. When set, the report is POSTed here
     * after every verification (best-effort — a failed ping never fails the
     * deploy). Point it at a deploy tracker, uptime service or chat webhook.
     */
    'webhook' => env('RELEASE_VERIFY_WEBHOOK_URL', ''),

    'webhook_timeout' => (int) env('RELEASE_VERIFY_WEBHOOK_TIMEOUT', 5),

];
