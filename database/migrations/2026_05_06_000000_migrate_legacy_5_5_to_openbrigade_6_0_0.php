<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Legacy schema baseline migration (5.5 -> 6.0.0)
|--------------------------------------------------------------------------
|
| This migration imports the legacy eBrigade 5.5 SQL schema into the new
| Laravel-based OpenBrigade 6.0.0 application.
|
| The SQL source is database/migrations/legacy/reference.sql. New OpenBrigade
| schema changes must
| be introduced using dedicated incremental Laravel migration files.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->splitSqlStatements($this->loadReferenceSql()) as $statement) {
            DB::unprepared($statement);
        }
    }

    public function down(): void
    {
        $tables = [];

        foreach ($this->splitSqlStatements($this->loadReferenceSql()) as $statement) {
            if (preg_match('/^\s*CREATE\s+TABLE\s+`?([A-Za-z0-9_]+)`?/i', $statement, $matches) === 1) {
                $tables[] = $matches[1];
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        foreach (array_reverse(array_unique($tables)) as $table) {
            DB::statement(sprintf('DROP TABLE IF EXISTS `%s`', str_replace('`', '``', $table)));
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function loadReferenceSql(): string
    {
        $path = base_path('database/migrations/legacy/reference.sql');

        if (! is_file($path)) {
            throw new RuntimeException('Missing legacy schema file: '.$path);
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException('Unable to read legacy schema file: '.$path);
        }

        return $content;
    }

    /**
     * @return list<string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $length = strlen($sql);

        for ($index = 0; $index < $length; $index++) {
            $char = $sql[$index];
            $next = $index + 1 < $length ? $sql[$index + 1] : '';

            if (! $inSingleQuote && ! $inDoubleQuote && $char === '#') {
                while ($index < $length && $sql[$index] !== "\n") {
                    $index++;
                }

                continue;
            }

            if (! $inSingleQuote && ! $inDoubleQuote && $char === '-' && $next === '-') {
                $afterNext = $index + 2 < $length ? $sql[$index + 2] : '';

                if ($afterNext === ' ' || $afterNext === "\t") {
                    while ($index < $length && $sql[$index] !== "\n") {
                        $index++;
                    }

                    continue;
                }
            }

            if ($char === "'" && ! $inDoubleQuote && ($index === 0 || $sql[$index - 1] !== '\\')) {
                $inSingleQuote = ! $inSingleQuote;
            } elseif ($char === '"' && ! $inSingleQuote && ($index === 0 || $sql[$index - 1] !== '\\')) {
                $inDoubleQuote = ! $inDoubleQuote;
            }

            if (! $inSingleQuote && ! $inDoubleQuote && $char === ';') {
                $statement = trim($buffer);

                if ($statement !== '') {
                    $statements[] = $statement;
                }

                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $tail = trim($buffer);

        if ($tail !== '') {
            $statements[] = $tail;
        }

        return $statements;
    }
};