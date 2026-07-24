<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * DB-backed SMS gateway configuration (Administration ▸ Notifications).
 *
 * The legacy rows sms_provider / sms_user / sms_password / sms_api_id drive
 * the provider-agnostic SMS layer, with config/sms.php (env) as the fallback
 * for anything left empty — so an install configured through .env keeps
 * working untouched.
 *
 * Provider mapping: `log` and `null` pass through; every real gateway reads
 * its secret from sms_password. `smsgatewayme` also takes device_id from
 * sms_api_id, `smseagle` takes its host from sms_api_id, and `smsmode` takes
 * an optional sender id from sms_user. An unknown provider resolves to the
 * null driver with a one-time warning.
 */
class SmsSettingService
{
    private const KEYS = ['sms_provider', 'sms_user', 'sms_password', 'sms_api_id'];

    /** @var array<string,string>|null */
    private ?array $cache = null;

    private bool $warned = false;

    /** Effective driver name: DB row first, env config as fallback. */
    public function driver(): string
    {
        $stored = strtolower(trim($this->get('sms_provider')));

        if ($stored === '') {
            return (string) config('sms.driver', 'log');
        }

        return match ($stored) {
            'log' => 'log',
            'null', 'none', 'aucun' => 'null',
            'smsgatewayme', 'smsgateway.me', 'smsgateway' => 'smsgatewayme',
            'smsmode', 'sms mode' => 'smsmode',
            'clickatell' => 'clickatell',
            'smseagle' => 'smseagle',
            'http' => 'http',
            default => $this->unknown($stored),
        };
    }

    /**
     * Driver options for the smsgateway.me sender: stored credentials first,
     * env config for anything left empty.
     *
     * @return array<string,mixed>
     */
    public function smsGatewayMeOptions(): array
    {
        $config = (array) config('sms.drivers.smsgatewayme');

        return [
            'token' => $this->get('sms_password') !== '' ? $this->get('sms_password') : ($config['token'] ?? ''),
            'device_id' => $this->get('sms_api_id') !== '' ? $this->get('sms_api_id') : ($config['device_id'] ?? ''),
            'endpoint' => $config['endpoint'] ?? 'https://smsgateway.me/api/v4',
        ];
    }

    /**
     * Driver options for the smsmode sender: api key from sms_password (env
     * fallback), optional sender id from sms_user, endpoint from config.
     *
     * @return array<string,mixed>
     */
    public function smsModeOptions(): array
    {
        $config = (array) config('sms.drivers.smsmode');

        return [
            'api_key' => $this->stored('sms_password', $config['api_key'] ?? ''),
            'endpoint' => $config['endpoint'] ?? 'https://rest.smsmode.com',
        ];
    }

    /**
     * Driver options for the Clickatell sender: api key from sms_password
     * (env fallback), endpoint from config.
     *
     * @return array<string,mixed>
     */
    public function clickatellOptions(): array
    {
        $config = (array) config('sms.drivers.clickatell');

        return [
            'api_key' => $this->stored('sms_password', $config['api_key'] ?? ''),
            'endpoint' => $config['endpoint'] ?? 'https://platform.clickatell.com',
        ];
    }

    /**
     * Driver options for the SMSEagle sender: host from sms_api_id and access
     * token from sms_password (env fallback for both).
     *
     * @return array<string,mixed>
     */
    public function smsEagleOptions(): array
    {
        $config = (array) config('sms.drivers.smseagle');

        return [
            'host' => $this->stored('sms_api_id', $config['host'] ?? ''),
            'token' => $this->stored('sms_password', $config['token'] ?? ''),
        ];
    }

    /**
     * Driver options for the generic HTTP sender: URL template and method from
     * config, secret token from sms_password (env fallback).
     *
     * @return array<string,mixed>
     */
    public function httpOptions(): array
    {
        $config = (array) config('sms.drivers.http');

        return [
            'url' => $config['url'] ?? '',
            'method' => $config['method'] ?? 'GET',
            'token' => $this->stored('sms_password', $config['token'] ?? ''),
        ];
    }

    /** Stored value for a key, falling back to the given default when empty. */
    private function stored(string $key, mixed $fallback): mixed
    {
        return $this->get($key) !== '' ? $this->get($key) : $fallback;
    }

    /** Raw stored value ('' when missing or unreadable). */
    public function get(string $name): string
    {
        $map = $this->map();

        return trim($map[$name] ?? '');
    }

    /**
     * Instantiate empty rows from the .env-driven config so the interface
     * shows what the install currently uses (the .env is the "default
     * option"; a stored value is the source of truth). Never overwrites a
     * non-empty value.
     */
    public function seedFromEnv(): void
    {
        $config = (array) config('sms.drivers.smsgatewayme');
        $seed = [
            'sms_provider' => (string) config('sms.driver', 'log'),
            'sms_password' => (string) ($config['token'] ?? ''),
            'sms_api_id' => (string) ($config['device_id'] ?? ''),
        ];

        foreach ($seed as $name => $value) {
            if ($value === '' || $this->get($name) !== '') {
                continue;
            }

            DB::table('configuration')->where('NAME', $name)->update(['VALUE' => $value]);
        }

        $this->cache = null;
    }

    private function unknown(string $stored): string
    {
        if (! $this->warned) {
            $this->warned = true;
            Log::warning('SmsSettingService: unknown SMS provider configured, SMS disabled', [
                'sms_provider' => $stored,
            ]);
        }

        return 'null';
    }

    /** @return array<string,string> */
    private function map(): array
    {
        if ($this->cache === null) {
            try {
                $this->cache = DB::table('configuration')
                    ->whereIn('NAME', self::KEYS)
                    ->pluck('VALUE', 'NAME')
                    ->map(fn ($v) => (string) $v)
                    ->all();
            } catch (Throwable) {
                // No DB (install, tests) → env config drives everything.
                $this->cache = [];
            }
        }

        return $this->cache;
    }
}
