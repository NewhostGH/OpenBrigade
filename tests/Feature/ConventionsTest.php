<?php

// ── Convention enforcement tests ──────────────────────────────────────────────
//
// These tests catch the three most common migration mistakes without running
// the application. They operate on the source files directly (no HTTP, no DB).
//
// Run with: php artisan test --filter ConventionsTest
//           or: ./vendor/bin/pest tests/Feature/ConventionsTest.php

// ── 1. No inline <style> blocks in Blade views ────────────────────────────────
//
// All CSS must live in resources/css/<module>.css and be bundled via Vite.
// Rule: Convention §3 — "No <style> blocks in Blade views."
test('no inline style blocks in blade views', function () {
    $views = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(resource_path('views'))
    );

    $violations = [];
    foreach ($views as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $rel  = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
        $lines = file($file->getRealPath());
        foreach ($lines as $n => $line) {
            // Allow @vite directives and HTML comments, flag actual <style> tags
            if (preg_match('/<style[\s>]/i', $line) && !str_contains($line, '{{--')) {
                $violations[] = "$rel:" . ($n + 1) . " — " . trim($line);
            }
        }
    }

    expect($violations)
        ->toBeEmpty(
            "Inline <style> blocks found in Blade views (Convention §3).\n"
            . "Move CSS to resources/css/<module>.css and bundle via Vite.\n\n"
            . implode("\n", $violations)
        );
});

// ── 2. No unflagged legacy URL references ─────────────────────────────────────
//
// Every line referencing a /legacy/*.php URL, a raw *.php? query string, or an
// archive/legacy_app path must have "TODO: Migrate code" on the same line or
// the immediately preceding non-blank line.
//
// Rule: Convention §5 — "Legacy references must be flagged."
test('all legacy references are flagged with TODO: Migrate code', function () {
    $dirs = [
        resource_path('views'),
        app_path('Http/Controllers'),
        app_path('Services'),
        app_path('Http/Requests'),
    ];

    $legacyPattern = '/[\'"]\/legacy\/|archive\/legacy_app\//';

    // Files where legacy strings are structural (detection logic, bridge controller itself)
    // rather than navigational links that need migration.
    $exclude = [
        'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Legacy',
        'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'AuthController.php',
    ];

    $violations = [];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iter as $file) {
            if (!in_array($file->getExtension(), ['php'], true)) {
                continue;
            }
            $rel = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());

            // Skip excluded files/directories
            $skip = false;
            foreach ($exclude as $ex) {
                if (str_contains($rel, $ex)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }

            $lines = file($file->getRealPath());

            for ($i = 0; $i < count($lines); $i++) {
                $line = $lines[$i];
                if (!preg_match($legacyPattern, $line)) {
                    continue;
                }
                // Already flagged on the same line?
                if (str_contains($line, 'TODO: Migrate code')) {
                    continue;
                }
                // Check the preceding 5 non-blank lines — covers multi-line HTML
                // attributes where the <a> tag and the href: are on separate lines,
                // and PHP arrays where the comment precedes the opening bracket.
                $found = false;
                $seen  = 0;
                for ($j = $i - 1; $j >= 0 && $seen < 5; $j--) {
                    $candidate = trim($lines[$j]);
                    if ($candidate === '') {
                        continue;
                    }
                    $seen++;
                    if (str_contains($candidate, 'TODO: Migrate code')) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    continue;
                }
                $violations[] = "$rel:" . ($i + 1) . " — " . trim($line);
            }
        }
    }

    expect($violations)
        ->toBeEmpty(
            "Legacy references without TODO: Migrate code marker (Convention §5).\n"
            . "Add {{-- TODO: Migrate code --}} (Blade) or // TODO: Migrate code (PHP)\n"
            . "on the line immediately before the reference.\n\n"
            . implode("\n", $violations)
        );
});

// ── 3. No bare legacy PHP links missing the /legacy/ prefix ──────────────────
//
// A link like url('/ins_personnel.php') routes to nowhere — the file is only
// reachable under /legacy/. This was the navbar quick-add bug.
//
// Rule: Convention §5 — "A legacy URL without /legacy/ prefix is a routing bug."
test('no legacy php files referenced without /legacy/ prefix', function () {
    $views = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(resource_path('views'))
    );

    // Legacy PHP filenames that must always appear under /legacy/
    $barePattern = '/[\'"]\/(?!legacy\/)(?:ins_|upd_|del_|save_|ins|upd|del)[a-z_]+\.php/';

    $violations = [];
    foreach ($views as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $rel   = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
        $lines = file($file->getRealPath());
        foreach ($lines as $n => $line) {
            if (preg_match($barePattern, $line)) {
                $violations[] = "$rel:" . ($n + 1) . " — " . trim($line);
            }
        }
    }

    expect($violations)
        ->toBeEmpty(
            "Legacy PHP files referenced without /legacy/ prefix (Convention §5).\n"
            . "Change url('/ins_foo.php') → url('/legacy/ins_foo.php').\n\n"
            . implode("\n", $violations)
        );
});
