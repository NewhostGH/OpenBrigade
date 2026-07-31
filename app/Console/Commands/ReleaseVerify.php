<?php

namespace App\Console\Commands;

use App\Services\ReleaseVerificationService;
use Illuminate\Console\Command;

/**
 * Post-deploy release verification gate.
 *
 * Runs the smoke checks in {@see ReleaseVerificationService} and exits non-zero
 * when the release is not serving correctly, so a CD pipeline (issue #73) can
 * gate a deploy on it. Fires the optional monitoring webhook with the result.
 */
class ReleaseVerify extends Command
{
    protected $signature = 'ob:release:verify
        {--strict : Treat degraded checks as a failure, not just down}
        {--json : Output the raw JSON report instead of a table}
        {--no-webhook : Do not fire the monitoring webhook}';

    protected $description = 'Run post-deploy smoke checks and gate the release';

    public function handle(ReleaseVerificationService $service): int
    {
        $report = $service->verify();
        $strict = (bool) $this->option('strict') || (bool) config('release.strict', false);

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->renderReport($report, $strict);
        }

        if (! $this->option('no-webhook') && $service->notify($report)) {
            $this->line('  Monitoring webhook notified.');
        }

        return $service->passes($report, $strict) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  array{status:string,version:string,timestamp:string,checks:array<string,array<string,mixed>>}  $report
     */
    private function renderReport(array $report, bool $strict): void
    {
        $this->info("Release verification — {$report['version']}");
        $this->newLine();

        $rows = [];
        foreach ($report['checks'] as $name => $check) {
            $detail = array_filter($check, fn ($key) => $key !== 'status', ARRAY_FILTER_USE_KEY);
            $rows[] = [$name, $this->badge((string) $check['status']), $this->stringify($detail)];
        }
        $this->table(['Check', 'Status', 'Detail'], $rows);

        $line = 'Overall: '.strtoupper($report['status']).($strict ? ' (strict)' : '');
        match ($report['status']) {
            'ok' => $this->info($line),
            'down' => $this->error($line),
            default => $this->warn($line),
        };
    }

    private function badge(string $status): string
    {
        return match ($status) {
            'ok' => '<fg=green>ok</>',
            'degraded' => '<fg=yellow>degraded</>',
            'down' => '<fg=red>down</>',
            default => "<fg=gray>{$status}</>",
        };
    }

    /** @param  array<string,mixed>  $detail */
    private function stringify(array $detail): string
    {
        $parts = [];
        foreach ($detail as $key => $value) {
            $parts[] = $key.'='.$this->scalarize($value);
        }

        return implode('  ', $parts);
    }

    private function scalarize(mixed $value): string
    {
        return match (true) {
            is_array($value) => (string) json_encode($value, JSON_UNESCAPED_SLASHES),
            is_bool($value) => $value ? 'true' : 'false',
            $value === null => 'null',
            default => (string) $value,
        };
    }
}
