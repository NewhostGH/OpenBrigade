<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Command\Command;

// The command itself decides whether the user-configured schedule (frequency,
// run time, start date, day of week/month — see ob_backup_settings) is due.
Schedule::command('backup:run-scheduled')->everyMinute();

// Trim the observability log to its configured retention window (daily, 03:10).
Schedule::command('ob:logs:prune')->dailyAt('03:10');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('legacy:migration:validate {--table=* : Validate only specific tables} {--strict : Fail on row count mismatches too}', function () {
    $referenceSqlPath = base_path('database/migrations/legacy/reference.sql');

    if (! is_file($referenceSqlPath)) {
        $this->error('Missing reference schema file at database/migrations/legacy/reference.sql');

        return Command::FAILURE;
    }

    $referenceSql = file_get_contents($referenceSqlPath);

    if ($referenceSql === false) {
        $this->error('Unable to read reference SQL file.');

        return Command::FAILURE;
    }

    preg_match_all('/CREATE\\s+TABLE\\s+`?([A-Za-z0-9_]+)`?/i', $referenceSql, $matches);
    $tables = array_values(array_unique($matches[1]));

    if ($tables === []) {
        $this->error('No tables discovered from reference SQL file.');

        return Command::FAILURE;
    }

    $requestedTables = (array) $this->option('table');

    if ($requestedTables !== []) {
        $tables = array_values(array_intersect($tables, $requestedTables));

        if ($tables === []) {
            $this->error('None of the requested tables exist in the reference SQL baseline.');

            return Command::FAILURE;
        }
    }

    $legacyDsn = config('legacy.db.dsn');
    $legacyHost = config('legacy.db.host');
    $legacyPort = config('legacy.db.port', '3306');
    $legacyDatabase = config('legacy.db.database');
    $legacyUser = config('legacy.db.username');
    $legacyPassword = config('legacy.db.password');

    $legacyPdo = null;
    $compareWithLegacy = false;

    if ($legacyDsn || ($legacyHost && $legacyDatabase && $legacyUser !== null)) {
        try {
            $dsn = $legacyDsn ?: sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $legacyHost,
                $legacyPort,
                $legacyDatabase
            );

            $legacyPdo = new PDO($dsn, (string) $legacyUser, (string) $legacyPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $compareWithLegacy = true;
        } catch (Throwable $exception) {
            $this->warn('Could not connect to legacy DB; running structural validation only.');
            $this->line('Reason: '.$exception->getMessage());
        }
    }

    $rows = [];
    $missingTables = [];
    $countMismatches = [];

    foreach ($tables as $table) {
        $exists = Schema::hasTable($table);

        if (! $exists) {
            $rows[] = [$table, 'missing', '-', '-', 'Table missing from OpenBrigade DB'];
            $missingTables[] = $table;

            continue;
        }

        try {
            $newCount = (int) DB::table($table)->count();
        } catch (Throwable $exception) {
            $rows[] = [$table, 'error', '-', '-', 'Count failed: '.$exception->getMessage()];
            $missingTables[] = $table;

            continue;
        }

        if (! $compareWithLegacy) {
            $rows[] = [$table, 'ok', (string) $newCount, 'n/a', 'Structural validation only'];

            continue;
        }

        try {
            $quoted = str_replace('`', '``', $table);
            $legacyCount = (int) $legacyPdo->query("SELECT COUNT(*) AS c FROM `{$quoted}`")->fetch()['c'];
        } catch (Throwable $exception) {
            $rows[] = [$table, 'legacy-error', (string) $newCount, '-', 'Legacy count failed: '.$exception->getMessage()];
            $countMismatches[] = $table;

            continue;
        }

        if ($legacyCount !== $newCount) {
            $rows[] = [$table, 'count-mismatch', (string) $newCount, (string) $legacyCount, 'Row count mismatch'];
            $countMismatches[] = $table;

            continue;
        }

        $rows[] = [$table, 'ok', (string) $newCount, (string) $legacyCount, 'Counts match'];
    }

    $this->table(['table', 'status', 'openbrigade_rows', 'legacy_rows', 'notes'], $rows);

    $this->newLine();
    $this->line('Summary:');
    $this->line('- Tables checked: '.count($tables));
    $this->line('- Missing tables: '.count($missingTables));
    $this->line('- Count mismatches: '.count($countMismatches));
    $this->line('- Legacy compare: '.($compareWithLegacy ? 'enabled' : 'disabled'));

    if ($missingTables !== []) {
        $this->error('Validation failed: one or more tables are missing or unreadable.');

        return Command::FAILURE;
    }

    if ($this->option('strict') && $countMismatches !== []) {
        $this->error('Strict validation failed: row count mismatches detected.');

        return Command::FAILURE;
    }

    $this->info('Validation completed successfully.');

    return Command::SUCCESS;
})->purpose('Validate migrated legacy tables against schema and optional legacy row counts');
