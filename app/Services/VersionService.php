<?php

namespace App\Services;

/**
 * Read-side single source of truth for the application's version state.
 *
 * Three versions coexist and this service is the one place that names them:
 *
 * - **code**       — the checked-out source version (root VERSION file, surfaced
 *                    as config('brigade.version')).
 * - **installed**  — what the running instance has been migrated to
 *                    (`configuration.version`, via GeneralSettingService).
 * - **changelog**  — the latest released version documented in CHANGELOG.md.
 *
 * On a correctly released instance code === installed === changelog. Drift
 * (code ≠ installed) means the code was deployed but its release migration has
 * not run yet — surfaced by `ob:version` and available for health/monitoring.
 */
class VersionService implements ServiceInterface
{
    public function __construct(private GeneralSettingService $settings) {}

    /** Version of the checked-out code (VERSION file → config('brigade.version')). */
    public function code(): string
    {
        return (string) config('brigade.version');
    }

    /** Version the database has been migrated to ('' when unknown/unstamped). */
    public function installed(): string
    {
        return $this->settings->appVersion();
    }

    /**
     * Latest released version documented in CHANGELOG.md ('' when none or the
     * file is unreadable). The `[Unreleased]` heading is skipped by design —
     * only a concrete semantic version matches.
     */
    public function changelogLatest(): string
    {
        $path = base_path('CHANGELOG.md');

        if (! is_file($path)) {
            return '';
        }

        $content = (string) file_get_contents($path);

        if (preg_match('/^##\s*\[(\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?)\]/m', $content, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    /**
     * True when the code and installed versions disagree — i.e. the code was
     * deployed but its release migration has not been applied. Never reports
     * drift when the installed version is unknown ('').
     */
    public function hasDrift(): bool
    {
        $installed = $this->installed();

        return $installed !== '' && $installed !== $this->code();
    }

    /** Validate a string against the SemVer 2.0.0 grammar (core + pre-release + build). */
    public static function isValidSemver(string $version): bool
    {
        return preg_match(
            '/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?(?:\+[0-9A-Za-z.-]+)?$/',
            $version
        ) === 1;
    }
}
