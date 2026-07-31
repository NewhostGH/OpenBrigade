<?php

namespace App\Services;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Post-deploy release verification: the smoke checks that answer "is this
 * release actually serving correctly?" right after a deploy.
 *
 * It composes three layers, each reported as one entry:
 *   1. Infrastructure liveness — delegated to {@see HealthCheckService} (DB,
 *      cache, storage, queue, mail, …) and folded in as a single
 *      `infrastructure` check so the release gate fails when a backing service
 *      is down.
 *   2. Release integrity — checks that only make sense at deploy time: pending
 *      migrations, built front-end assets, production configuration sanity and
 *      the installed-version SSOT.
 *   3. Critical workflows — the named routes the app cannot serve without.
 *
 * Each check is isolated (a thrown check degrades only its own entry) and
 * returns `{status, ...detail}` with status in ok|degraded|down|skipped — the
 * same vocabulary and worst-wins aggregation as HealthCheckService — so the
 * report drops straight into the CD pipeline gate (issue #73) and the optional
 * monitoring webhook.
 */
class ReleaseVerificationService implements ServiceInterface
{
    /** Named routes the application must resolve to be considered live. */
    private const CRITICAL_ROUTES = ['login', 'health', 'dashboard'];

    public function __construct(
        private readonly HealthCheckService $health,
        private readonly GeneralSettingService $settings,
    ) {}

    /**
     * Run every verification check and return the full report.
     *
     * @return array{status:string,version:string,timestamp:string,checks:array<string,array<string,mixed>>}
     */
    public function verify(): array
    {
        $checks = [
            'infrastructure' => $this->checkInfrastructure(),
            'migrations' => $this->checkMigrations(),
            'assets' => $this->checkAssets(),
            'configuration' => $this->checkConfiguration(),
            'version' => $this->checkVersion(),
            'routes' => $this->checkRoutes(),
        ];

        return [
            'status' => $this->overall($checks),
            'version' => $this->versionLabel(),
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ];
    }

    /**
     * A report passes the release gate when nothing is `down`, and — in strict
     * mode — nothing is `degraded` either.
     *
     * @param  array{status:string}  $report
     */
    public function passes(array $report, bool $strict = false): bool
    {
        if ($report['status'] === 'down') {
            return false;
        }

        return ! ($strict && $report['status'] === 'degraded');
    }

    /**
     * Fire the optional monitoring / deploy webhook with the report. Best-effort
     * by design: a broken hook must never fail a deploy that verified fine, and
     * the external URL lives in config (SSOT §1). Returns whether a ping was
     * actually sent.
     *
     * @param  array{status:string,version:string,timestamp:string,checks:array<string,array<string,mixed>>}  $report
     */
    public function notify(array $report): bool
    {
        $url = (string) config('release.webhook', '');
        if ($url === '') {
            return false;
        }

        try {
            Http::timeout((int) config('release.webhook_timeout', 5))->post($url, [
                'event' => 'release.verified',
                'status' => $report['status'],
                'version' => $report['version'],
                'timestamp' => $report['timestamp'],
                'checks' => $this->summarise($report['checks']),
            ]);

            return true;
        } catch (Throwable $e) {
            Log::warning('ReleaseVerification: webhook ping failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Backing-service liveness, folded from the shared health report so the
     * release gate reuses the exact probes the /health endpoint exposes.
     *
     * @return array<string,mixed>
     */
    private function checkInfrastructure(): array
    {
        try {
            $report = $this->health->report();

            return [
                'status' => $report['status'],
                'services' => $this->summarise($report['checks']),
            ];
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /**
     * Every migration on disk must be applied for the code and schema to match.
     *
     * @return array<string,mixed>
     */
    private function checkMigrations(): array
    {
        try {
            $pending = $this->pendingMigrations();

            return ['status' => $pending > 0 ? 'down' : 'ok', 'pending' => $pending];
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /**
     * Front-end assets must be built for this release. A missing Vite manifest
     * means `npm run build` never ran.
     *
     * @return array<string,mixed>
     */
    private function checkAssets(): array
    {
        $path = $this->manifestPath();
        if ($path === null || $path === '') {
            return ['status' => 'down', 'error' => 'no manifest path configured'];
        }

        // Newer laravel-vite-plugin nests the manifest under a .vite/ folder.
        $nested = dirname($path).DIRECTORY_SEPARATOR.'.vite'.DIRECTORY_SEPARATOR.basename($path);
        foreach ([$path, $nested] as $candidate) {
            if (is_file($candidate)) {
                return ['status' => 'ok', 'manifest' => $this->relative($candidate)];
            }
        }

        return ['status' => 'down', 'error' => 'built asset manifest not found', 'expected' => $this->relative($path)];
    }

    /**
     * Production configuration sanity. A missing app key or debug-on-in-prod is
     * a hard failure; anything else is a warning.
     *
     * @return array<string,mixed>
     */
    private function checkConfiguration(): array
    {
        $issues = [];

        $keyMissing = (string) config('app.key', '') === '';
        if ($keyMissing) {
            $issues[] = 'APP_KEY missing';
        }

        $debugInProd = false;
        if (app()->environment('production')) {
            if ((bool) config('app.debug')) {
                $debugInProd = true;
                $issues[] = 'APP_DEBUG enabled in production';
            }
            if ((string) config('app.env') !== 'production') {
                $issues[] = 'APP_ENV is not "production"';
            }
        }

        if ($issues !== []) {
            return ['status' => $keyMissing || $debugInProd ? 'down' : 'degraded', 'issues' => $issues];
        }

        return ['status' => 'ok', 'environment' => (string) config('app.env')];
    }

    /**
     * The deployed version — the installed-version SSOT — must match the version
     * being released when the pipeline pins one.
     *
     * @return array<string,mixed>
     */
    private function checkVersion(): array
    {
        try {
            $installed = $this->version();
        } catch (Throwable $e) {
            return ['status' => 'degraded', 'error' => $e->getMessage()];
        }

        $expected = (string) config('release.expected_version', '');
        if ($expected === '') {
            return ['status' => 'ok', 'installed' => $installed, 'expected' => null];
        }

        return [
            'status' => $expected === $installed ? 'ok' : 'down',
            'installed' => $installed,
            'expected' => $expected,
        ];
    }

    /**
     * The critical named routes the application cannot serve without must be
     * registered (a stale route cache or a broken provider drops them).
     *
     * @return array<string,mixed>
     */
    private function checkRoutes(): array
    {
        try {
            $routes = app(Router::class)->getRoutes();
            $missing = [];
            foreach (self::CRITICAL_ROUTES as $name) {
                if ($routes->getByName($name) === null) {
                    $missing[] = $name;
                }
            }

            if ($missing !== []) {
                return ['status' => 'down', 'missing' => $missing];
            }

            return ['status' => 'ok', 'verified' => count(self::CRITICAL_ROUTES)];
        } catch (Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /**
     * Count migrations present on disk but not yet run. A protected seam so
     * tests need no migrated database (mirrors HealthCheckService::smtpHandshake).
     */
    protected function pendingMigrations(): int
    {
        $migrator = app(Migrator::class);
        $repository = $migrator->getRepository();
        if (! $repository->repositoryExists()) {
            throw new \RuntimeException('migration repository not installed');
        }

        $ran = $repository->getRan();
        $files = $migrator->getMigrationFiles(
            array_merge([database_path('migrations')], $migrator->paths())
        );

        return count(array_diff(array_keys($files), $ran));
    }

    /** Configured Vite manifest path. Protected seam for tests. */
    protected function manifestPath(): ?string
    {
        $path = config('release.manifest_path');

        return $path !== null ? (string) $path : null;
    }

    /** The installed-version SSOT (configuration row 1) with the config fallback. */
    private function version(): string
    {
        $installed = $this->settings->appVersion();

        return $installed !== '' ? $installed : (string) config('brigade.version');
    }

    /** Human-friendly "Name x.y.z" label for the report header. */
    private function versionLabel(): string
    {
        try {
            $version = $this->version();
        } catch (Throwable) {
            $version = (string) config('brigade.version');
        }

        return trim((string) config('app.name', 'OpenBrigade').' '.$version);
    }

    /**
     * Reduce a check map to `{name: status}` for compact webhook / nested output.
     *
     * @param  array<string,array<string,mixed>>  $checks
     * @return array<string,string>
     */
    private function summarise(array $checks): array
    {
        return array_map(fn (array $c): string => (string) $c['status'], $checks);
    }

    /** Trim the base path off an absolute path for readable output. */
    private function relative(string $path): string
    {
        return str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
    }

    /**
     * Overall status = the worst individual status (skipped never worsens it).
     *
     * @param  array<string,array<string,mixed>>  $checks
     */
    private function overall(array $checks): string
    {
        $rank = ['ok' => 0, 'skipped' => 0, 'degraded' => 1, 'down' => 2];
        $worst = 0;
        foreach ($checks as $check) {
            $worst = max($worst, $rank[$check['status']] ?? 0);
        }

        return ['ok', 'degraded', 'down'][$worst];
    }
}
