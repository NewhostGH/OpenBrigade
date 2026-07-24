<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Compile Blade templates and PARSE the compiled PHP.
 *
 * Blade compilation never validates its output: a template like
 * `@endif@endfeature` (adjacent directives — the second is silently left
 * uncompiled) produces invalid PHP that `view:cache` happily writes and that
 * only explodes at render time. This command catches that class of bug in
 * pre-commit / CI by token-parsing the compiled output.
 *
 * With no arguments it sweeps every template (the CI path — see
 * tests/Feature/BladeLintTest.php). Given one or more paths it lints only
 * those files, so the pre-commit hook can check just the staged templates.
 */
class LintViews extends Command
{
    protected $signature = 'ob:views:lint {paths?* : Blade files to lint; omit to lint every template}';

    protected $description = 'Compile Blade templates and syntax-check the compiled PHP';

    public function handle(): int
    {
        /** @var array<int,string> $paths */
        $paths = $this->argument('paths');

        return $paths === []
            ? $this->lintAll()
            : $this->lintPaths($paths);
    }

    /**
     * Full sweep: compile every template (same mechanics as view:cache) and
     * parse each compiled file.
     */
    private function lintAll(): int
    {
        $this->callSilent('view:clear');
        $this->callSilent('view:cache');

        $files = File::glob(storage_path('framework/views/*.php'));
        $errors = 0;

        foreach ($files as $file) {
            $code = (string) File::get($file);

            // Map the compiled file back to its source template for reporting.
            $source = preg_match('#/\*\*PATH (.+?) ENDPATH\*\*/#s', $code, $m)
                ? trim($m[1])
                : $file;

            if (! $this->parses($code, $source)) {
                $errors++;
            }
        }

        // Never leave a lint-time cache behind — runtime recompiles on demand.
        $this->callSilent('view:clear');

        if ($errors > 0) {
            $this->error("blade-lint: {$errors} template(s) compile to invalid PHP");

            return self::FAILURE;
        }

        $this->info('blade-lint: '.count($files).' compiled templates parsed OK');

        return self::SUCCESS;
    }

    /**
     * Scoped mode: compile each given Blade file in isolation and parse it.
     * Non-Blade / missing paths are skipped so the hook can pass a mixed list.
     *
     * @param  array<int,string>  $paths
     */
    private function lintPaths(array $paths): int
    {
        $compiler = app('blade.compiler');
        $checked = 0;
        $errors = 0;

        foreach ($paths as $path) {
            if (! str_ends_with($path, '.blade.php') || ! File::isFile($path)) {
                continue;
            }

            $checked++;
            $compiled = $compiler->compileString((string) File::get($path));

            if (! $this->parses($compiled, $path)) {
                $errors++;
            }
        }

        if ($errors > 0) {
            $this->error("blade-lint: {$errors} template(s) compile to invalid PHP");

            return self::FAILURE;
        }

        $this->info("blade-lint: {$checked} template(s) parsed OK");

        return self::SUCCESS;
    }

    /** Token-parse compiled Blade; report and return false on a syntax error. */
    private function parses(string $code, string $source): bool
    {
        try {
            // In-process syntax check — no per-file php -l subprocess.
            $tokens = token_get_all($code, TOKEN_PARSE);
            unset($tokens);

            return true;
        } catch (\ParseError $e) {
            $this->error("✗ {$source}");
            $this->line("  {$e->getMessage()} (compiled line {$e->getLine()})");

            return false;
        }
    }
}
