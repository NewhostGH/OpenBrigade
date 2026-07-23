<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Compile every Blade template and PARSE the compiled PHP.
 *
 * Blade compilation never validates its output: a template like
 * `@endif@endfeature` (adjacent directives — the second is silently left
 * uncompiled) produces invalid PHP that `view:cache` happily writes and that
 * only explodes at render time. This command catches that class of bug in
 * pre-commit / CI by token-parsing each compiled file.
 */
class LintViews extends Command
{
    protected $signature = 'ob:views:lint';

    protected $description = 'Compile every Blade template and syntax-check the compiled PHP';

    public function handle(): int
    {
        // Fresh compile of every template (same mechanics as view:cache).
        $this->callSilent('view:clear');
        $this->callSilent('view:cache');

        $files = File::glob(storage_path('framework/views/*.php'));
        $errors = 0;

        foreach ($files as $file) {
            $code = (string) File::get($file);

            try {
                // In-process syntax check — no per-file php -l subprocess.
                $tokens = token_get_all($code, TOKEN_PARSE);
                unset($tokens);
            } catch (\ParseError $e) {
                // Map the compiled file back to its source template.
                $source = preg_match('#/\*\*PATH (.+?) ENDPATH\*\*/#s', $code, $m)
                    ? trim($m[1])
                    : $file;

                $this->error("✗ {$source}");
                $this->line("  {$e->getMessage()} (compiled line {$e->getLine()})");
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
}
