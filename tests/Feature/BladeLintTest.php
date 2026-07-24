<?php

use Illuminate\Support\Facades\File;

test('every shipped Blade template compiles to valid PHP', function () {
    // Blade never validates its output; ob:views:lint parses every compiled
    // template (regression guard for e.g. adjacent @endif@endfeature).
    $this->artisan('ob:views:lint')->assertExitCode(0);
});

test('scoped mode lints only the given templates', function () {
    // The pre-commit hook passes just the staged *.blade.php files.
    $good = resource_path('views/__lint_good.blade.php');
    $bad = resource_path('views/__lint_bad.blade.php');
    File::put($good, "@if(true)\n<p>ok</p>\n@endif\n");
    File::put($bad, "@if(true)\n<p>unclosed</p>\n");   // no @endif → invalid PHP

    try {
        // A non-Blade / missing path is skipped, not an error.
        $this->artisan('ob:views:lint', ['paths' => [$good, 'composer.json']])
            ->assertExitCode(0);

        $this->artisan('ob:views:lint', ['paths' => [$bad]])
            ->assertExitCode(1);
    } finally {
        File::delete([$good, $bad]);
    }
});
