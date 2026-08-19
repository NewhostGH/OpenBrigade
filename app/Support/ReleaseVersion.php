<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Stamps the installed application version into the database.
 *
 * The installed version is the single source of truth for "what schema/behaviour
 * is this instance actually running" — it lives in the legacy `configuration`
 * table (row NAME 'version') and overlays config('brigade.version') /
 * config('app.version') at boot (see App\Services\GeneralSettingService and
 * App\Providers\AppServiceProvider::configureAppIdentity).
 *
 * Every release that ships schema or behaviour changes carries a Laravel
 * migration whose only job — beyond the schema change itself — is to call
 * ReleaseVersion::stamp() with the new semantic version, so the DB records the
 * version it was migrated to. Keeping the write here (not inlined in each
 * migration) is the SSOT for the stamp itself.
 */
final class ReleaseVersion
{
    /**
     * Update the installed-version row (`configuration.version`) to $version.
     *
     * The row ships with the reference schema, so this is an in-place update:
     * on a fresh install the baseline migration loads reference.sql first, then
     * stamps the current version. A no-op if the row is somehow absent.
     */
    public static function stamp(string $version): void
    {
        DB::table('configuration')
            ->where('NAME', 'version')
            ->update(['VALUE' => $version]);
    }
}
