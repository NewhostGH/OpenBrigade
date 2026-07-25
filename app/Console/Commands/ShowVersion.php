<?php

namespace App\Console\Commands;

use App\Services\VersionService;
use Illuminate\Console\Command;

/**
 * Report the application's version state — the code version (VERSION file),
 * the installed version (database) and the latest CHANGELOG entry — and warn
 * when they drift (code deployed but its release migration not yet applied).
 *
 * Handy in deploy scripts and release verification: `php artisan ob:version`
 * for humans, `--json` for machines.
 */
class ShowVersion extends Command
{
    protected $signature = 'ob:version {--json : Output the version state as JSON}';

    protected $description = 'Show the code / installed / changelog versions and any drift';

    public function handle(VersionService $versions): int
    {
        $installed = $versions->installed();
        $changelog = $versions->changelogLatest();
        $drift = $versions->hasDrift();

        if ($this->option('json')) {
            $this->line((string) json_encode([
                'code' => $versions->code(),
                'installed' => $installed,
                'changelog' => $changelog,
                'drift' => $drift,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->twoColumnDetail('Code (VERSION)', $versions->code());
        $this->components->twoColumnDetail('Installed (database)', $installed !== '' ? $installed : '<unknown>');
        $this->components->twoColumnDetail('Changelog (latest)', $changelog !== '' ? $changelog : '<none>');

        if ($drift) {
            $this->newLine();
            $this->components->warn(sprintf(
                'Version drift: code is %s but the database is stamped %s. Run `php artisan migrate`.',
                $versions->code(),
                $installed,
            ));
        }

        return self::SUCCESS;
    }
}
