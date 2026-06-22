<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for the security-hardening toggles shown under
 * Administration ▸ Sécurité ▸ Renforcement.
 *
 * Values live as NAME/VALUE rows in the legacy `configuration` table (so they
 * survive backups and reuse the existing admin plumbing). Reads are memoised
 * per request and fall back to typed defaults, so a missing row never breaks a
 * page — it simply behaves as the default.
 */
class SecuritySettingService
{
    /**
     * Default values for every hardening setting. The `sec_upload_scan_enabled`
     * default is environment-driven: ON inside Docker (where a clamav service is
     * shipped), OFF otherwise.
     *
     * @var array<string,int|string>
     */
    private const DEFAULTS = [
        'sec_hsts_enabled' => 0,
        'sec_hsts_max_age' => 15552000, // 180 days
        'sec_csp_enabled' => 1,
        'sec_csp_report_only' => 0,
        'sec_ratelimit_auth_enabled' => 1,
        'sec_ratelimit_auth_max' => 5,
        'sec_ratelimit_auth_window' => 1,
        'sec_upload_scan_enabled' => 0,
        'sec_clamav_host' => 'clamav',
        'sec_clamav_port' => 3310,
        'sec_upload_mime_hardening' => 1,
    ];

    /** @var array<string,string>|null */
    private ?array $cache = null;

    /** Setting names, in display order — used by the admin tab and the seeder. */
    public static function keys(): array
    {
        return array_keys(self::DEFAULTS);
    }

    /** Default value for a key (resolving the env-driven scan default). */
    public function default(string $name): int|string
    {
        if ($name === 'sec_upload_scan_enabled') {
            return config('uploads.scan_default') ? 1 : 0;
        }

        return self::DEFAULTS[$name] ?? '';
    }

    /** Raw stored value, or the default when no row exists. */
    public function get(string $name): string
    {
        $map = $this->map();

        return $map[$name] ?? (string) $this->default($name);
    }

    public function bool(string $name): bool
    {
        return $this->get($name) === '1';
    }

    public function int(string $name): int
    {
        return (int) $this->get($name);
    }

    public function string(string $name): string
    {
        return $this->get($name);
    }

    /**
     * Ensure every hardening setting has a row in the `configuration` table,
     * creating missing ones with their default value. Idempotent — used by both
     * the seeding migration and the admin screen so the page never 500s on a row
     * that isn't there yet. The table's ID column is a non-auto-increment PK, so
     * each new row gets the next free ID.
     */
    public function ensureSeeded(): void
    {
        $existing = DB::table('configuration')
            ->whereIn('NAME', array_keys(self::DEFAULTS))
            ->pluck('NAME')
            ->all();

        $missing = array_diff(array_keys(self::DEFAULTS), $existing);
        if ($missing === []) {
            return;
        }

        $nextId = ((int) DB::table('configuration')->max('ID')) + 1;

        foreach ($missing as $name) {
            DB::table('configuration')->insert([
                'ID' => $nextId++,
                'NAME' => $name,
                'VALUE' => (string) $this->default($name),
                'DESCRIPTION' => 'Renforcement de la sécurité',
                'ORDERING' => 900,
                'HIDDEN' => 1,
                'TAB' => 90,
                'YESNO' => 0,
                'IS_FILE' => 0,
                'CARD_NAME' => 'Renforcement',
                'DISPLAY_NAME' => null,
            ]);
        }

        $this->cache = null;
    }

    /** @return array<string,string> */
    private function map(): array
    {
        if ($this->cache === null) {
            try {
                $this->cache = DB::table('configuration')
                    ->whereIn('NAME', array_keys(self::DEFAULTS))
                    ->pluck('VALUE', 'NAME')
                    ->map(fn ($v) => (string) $v)
                    ->all();
            } catch (QueryException) {
                // Security headers run on every response (including error pages);
                // a transient DB problem must fall back to safe defaults, not 500.
                $this->cache = [];
            }
        }

        return $this->cache;
    }
}
