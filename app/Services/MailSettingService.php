<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * DB-backed mail transport configuration (Administration ▸ Notifications).
 *
 * The .env values act as the *instantiator*: on first render the rows are
 * seeded from the effective config so the interface shows what the install
 * currently uses. From then on the stored rows are the source of truth —
 * `apply()` overlays every non-empty row onto config('mail.*') at boot.
 * Emptying a field falls back to the .env default again.
 */
class MailSettingService
{
    /**
     * Setting row NAME => the config path it overrides.
     *
     * @var array<string,string>
     */
    private const KEYS = [
        'mail_mailer' => 'mail.default',
        'mail_host' => 'mail.mailers.smtp.host',
        'mail_port' => 'mail.mailers.smtp.port',
        'mail_username' => 'mail.mailers.smtp.username',
        'mail_password' => 'mail.mailers.smtp.password',
        'mail_scheme' => 'mail.mailers.smtp.scheme',
        'mail_from_address' => 'mail.from.address',
        'mail_from_name' => 'mail.from.name',
    ];

    /** @var array<string,string>|null */
    private ?array $cache = null;

    /** Setting names, in display order. */
    public static function keys(): array
    {
        return array_keys(self::KEYS);
    }

    /** Raw stored value ('' when missing or unreadable). */
    public function get(string $name): string
    {
        $map = $this->map();

        return trim($map[$name] ?? '');
    }

    /**
     * Overlay every non-empty stored row onto the runtime mail config.
     * Called at boot (Throwable-guarded by the caller) and cheap enough to
     * run on every request thanks to the memoised read.
     */
    public function apply(): void
    {
        foreach (self::KEYS as $name => $configPath) {
            $value = $this->get($name);
            if ($value === '') {
                continue; // empty row → keep the .env default
            }

            if ($name === 'mail_mailer' && ! array_key_exists($value, (array) config('mail.mailers'))) {
                continue; // unknown mailer must not break every send
            }

            Config::set($configPath, $name === 'mail_port' ? (int) $value : $value);
        }
    }

    /**
     * Seed missing rows from the *effective* config so the interface shows
     * the current .env-driven values. Idempotent; never overwrites a stored
     * value. The table's ID column is a non-auto-increment PK, so each new
     * row gets the next free ID.
     */
    public function ensureSeeded(): void
    {
        $existing = DB::table('configuration')
            ->whereIn('NAME', self::keys())
            ->pluck('NAME')
            ->all();

        $missing = array_diff(self::keys(), $existing);
        if ($missing === []) {
            return;
        }

        $nextId = ((int) DB::table('configuration')->max('ID')) + 1;

        foreach ($missing as $name) {
            DB::table('configuration')->insert([
                'ID' => $nextId++,
                'NAME' => $name,
                'VALUE' => (string) config(self::KEYS[$name], ''),
                'DESCRIPTION' => 'Transport email (Notifications)',
                'ORDERING' => 920,
                'HIDDEN' => 1,
                'TAB' => 92,
                'YESNO' => 0,
                'IS_FILE' => 0,
                'CARD_NAME' => 'Notifications',
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
                    ->whereIn('NAME', self::keys())
                    ->pluck('VALUE', 'NAME')
                    ->map(fn ($v) => (string) $v)
                    ->all();
            } catch (Throwable) {
                // No DB (install, tests) → the .env config stands untouched.
                $this->cache = [];
            }
        }

        return $this->cache;
    }
}
