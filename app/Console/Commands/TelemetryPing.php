<?php

namespace App\Console\Commands;

use App\Services\GeneralSettingService;
use App\Services\OrganisationSetupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Anonymous weekly telemetry ping — the "Aider à améliorer" opt-in
 * (configuration row 80, `ameliorations`).
 *
 * The payload is strictly anonymous and fully listed below: a stable
 * instance hash (derived from the app key — no way back to a URL or an
 * identity), version numbers, the organisation type and a member count
 * rounded to the nearest ten. No names, no emails, no hostnames, no IPs —
 * unlike the legacy push_monitoring_info() this replaces.
 */
class TelemetryPing extends Command
{
    protected $signature = 'ob:telemetry:ping';

    protected $description = 'Send the anonymous weekly telemetry ping (opt-in, setting 80)';

    public function handle(GeneralSettingService $settings, OrganisationSetupService $setup): int
    {
        if (! $settings->telemetryEnabled()) {
            $this->info('Telemetry is disabled (setting 80) — nothing sent.');

            return self::SUCCESS;
        }

        $url = (string) config('brigade.telemetry_url');
        if ($url === '') {
            $this->info('No telemetry endpoint configured — nothing sent.');

            return self::SUCCESS;
        }

        try {
            Http::timeout(5)->post($url, $this->payload($setup));
            $this->info('Telemetry ping sent.');
        } catch (\Throwable $e) {
            // Best-effort by design: the endpoint being unreachable must never
            // surface as an error anywhere.
            Log::debug('TelemetryPing: send failed', ['error' => $e->getMessage()]);
        }

        return self::SUCCESS;
    }

    /** @return array<string,mixed> */
    private function payload(OrganisationSetupService $setup): array
    {
        try {
            $members = (int) DB::table('pompier')->where('P_ACTIF', 1)->count();
        } catch (\Throwable) {
            $members = 0;
        }

        try {
            $orgType = $setup->orgType();
        } catch (\Throwable) {
            $orgType = 0;
        }

        return [
            // Stable, non-reversible instance identifier.
            'instance' => substr(hash('sha256', (string) config('app.key')), 0, 16),
            'app_version' => (string) config('brigade.version'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'org_type' => $orgType,
            'members_rounded' => (int) (round($members / 10) * 10),
        ];
    }
}
