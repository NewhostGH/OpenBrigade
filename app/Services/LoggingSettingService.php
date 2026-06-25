<?php

namespace App\Services;

use App\Logging\DatabaseLogHandler;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for the observability settings shown under
 * Administration ▸ Journal d'activité ▸ Paramètres.
 *
 * Mirrors {@see SecuritySettingService}: values live as NAME/VALUE rows in the
 * legacy `configuration` table, reads are memoised per request and fall back to
 * typed defaults, and a missing row (or table) never breaks a page — the logging
 * pipeline simply behaves as the defaults.
 *
 * Logging is organised into logical **canaux** (channels), each with its own
 * minimum level so an admin can, say, capture every `activity`/`auth` event
 * while keeping `app` at warning. The per-canal threshold is enforced in
 * {@see DatabaseLogHandler}.
 */
class LoggingSettingService
{
    /**
     * Logical canaux and their default minimum level. The order is the display
     * order in the Paramètres tab and the filter dropdown.
     *
     * @var array<string,string>
     */
    public const CANALS = [
        'activity' => 'info',      // business activity (consolidated log_history)
        'audit' => 'info',         // state-changing HTTP requests
        'auth' => 'info',          // authentication / session events
        'security' => 'info',      // security events (upload rejects, denials)
        'app' => 'warning',        // general application logs
        'error' => 'error',        // uncaught exceptions
        'performance' => 'info',   // slow-request samples
    ];

    /**
     * Non-canal settings and their defaults. Per-canal level keys
     * (obs_level_<canal>) are merged in by {@see allDefaults()}.
     *
     * @var array<string,int|string>
     */
    private const DEFAULTS = [
        'obs_log_to_db' => 1,             // write to ob_log_entry
        'obs_log_to_file' => 1,           // write to storage/logs
        'obs_file_channel' => 'daily',    // single | daily
        'obs_file_retention_days' => 14,  // daily channel retention
        'obs_db_retention_days' => 90,    // prune ob_log_entry older than this (0 = keep)
        'obs_error_tracking' => 0,        // report exceptions to Sentry/GlitchTip
        'obs_sentry_dsn' => '',           // Sentry/GlitchTip DSN (was a .env var)
        'obs_perf_enabled' => 1,          // track request duration / memory
        'obs_perf_slow_ms' => 1000,       // log requests slower than this
    ];

    /** @var array<string,string>|null */
    private ?array $cache = null;

    /** The setting key holding a canal's minimum level. */
    public static function canalLevelKey(string $canal): string
    {
        return 'obs_level_'.$canal;
    }

    /**
     * Every default keyed by setting name, including the per-canal level keys.
     *
     * @return array<string,int|string>
     */
    public static function allDefaults(): array
    {
        $defaults = self::DEFAULTS;
        foreach (self::CANALS as $canal => $level) {
            $defaults[self::canalLevelKey($canal)] = $level;
        }

        return $defaults;
    }

    /** Setting names, in display order — used by the admin tab and the seeder. */
    public static function keys(): array
    {
        return array_keys(self::allDefaults());
    }

    public function default(string $name): int|string
    {
        return self::allDefaults()[$name] ?? '';
    }

    /** Raw stored value, or the default when no row exists. */
    public function get(string $name): string
    {
        return $this->map()[$name] ?? (string) $this->default($name);
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

    /** Configured minimum level for a canal (falls back to its default). */
    public function canalLevel(string $canal): string
    {
        if (! array_key_exists($canal, self::CANALS)) {
            return 'debug'; // unknown canal → never silently dropped
        }

        return $this->get(self::canalLevelKey($canal));
    }

    /**
     * The lowest configured canal level (used to set the file channel so the
     * file captures at least everything the database store keeps).
     */
    public function lowestCanalLevel(): string
    {
        $rank = ['debug' => 0, 'info' => 1, 'notice' => 2, 'warning' => 3, 'error' => 4, 'critical' => 5, 'alert' => 6, 'emergency' => 7];
        $lowest = 'emergency';
        foreach (array_keys(self::CANALS) as $canal) {
            $level = $this->canalLevel($canal);
            if (($rank[$level] ?? 7) < ($rank[$lowest] ?? 7)) {
                $lowest = $level;
            }
        }

        return $lowest;
    }

    /**
     * Ensure every observability setting has a row in `configuration`, creating
     * missing ones with their default. Idempotent — used by both the seeding
     * migration and the admin screen so the page never 500s on an absent row.
     */
    public function ensureSeeded(): void
    {
        $names = array_keys(self::allDefaults());

        $existing = DB::table('configuration')
            ->whereIn('NAME', $names)
            ->pluck('NAME')
            ->all();

        $missing = array_diff($names, $existing);
        if ($missing === []) {
            return;
        }

        $nextId = ((int) DB::table('configuration')->max('ID')) + 1;

        foreach ($missing as $name) {
            DB::table('configuration')->insert([
                'ID' => $nextId++,
                'NAME' => $name,
                'VALUE' => (string) $this->default($name),
                'DESCRIPTION' => 'Observabilité',
                'ORDERING' => 910,
                'HIDDEN' => 1,
                'TAB' => 91,
                'YESNO' => 0,
                'IS_FILE' => 0,
                'CARD_NAME' => 'Observabilité',
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
                    ->whereIn('NAME', array_keys(self::allDefaults()))
                    ->pluck('VALUE', 'NAME')
                    ->map(fn ($v) => (string) $v)
                    ->all();
            } catch (QueryException) {
                // The logging pipeline boots on every request (incl. error
                // pages); a DB problem must fall back to defaults, not recurse.
                $this->cache = [];
            }
        }

        return $this->cache;
    }
}
