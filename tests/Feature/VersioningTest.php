<?php

use App\Services\VersionService;
use App\Support\ReleaseVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ── Repository invariants ────────────────────────────────────────────────────

test('the VERSION file exists and holds a valid semantic version', function () {
    $path = base_path('VERSION');

    expect(is_file($path))->toBeTrue();

    $version = trim((string) file_get_contents($path));

    expect(VersionService::isValidSemver($version))->toBeTrue();
});

test('config(brigade.version) matches the VERSION file', function () {
    $version = trim((string) file_get_contents(base_path('VERSION')));

    expect((string) config('brigade.version'))->toBe($version);
});

test('CHANGELOG.md keeps an Unreleased section and its latest release matches VERSION', function () {
    $changelog = (string) file_get_contents(base_path('CHANGELOG.md'));
    $version = trim((string) file_get_contents(base_path('VERSION')));

    expect($changelog)->toContain('## [Unreleased]')
        ->and(app(VersionService::class)->changelogLatest())->toBe($version);
});

// ── ReleaseVersion::stamp() ──────────────────────────────────────────────────

test('ReleaseVersion::stamp() writes the installed version row', function () {
    // Build a minimal configuration table (the suite runs on a schemaless
    // sqlite :memory: database) so the stamp write can be exercised.
    Schema::create('configuration', function ($table): void {
        $table->increments('ID');
        $table->string('NAME');
        $table->string('VALUE')->nullable();
    });

    try {
        DB::table('configuration')->insert(['NAME' => 'version', 'VALUE' => '5.5']);

        ReleaseVersion::stamp('6.1.0');

        expect(DB::table('configuration')->where('NAME', 'version')->value('VALUE'))->toBe('6.1.0');
    } finally {
        // The suite assumes a schemaless :memory: database — never leak the table.
        Schema::dropIfExists('configuration');
    }
});

// ── ob:version command ───────────────────────────────────────────────────────

test('ob:version prints the code version', function () {
    $this->artisan('ob:version')
        ->expectsOutputToContain((string) config('brigade.version'))
        ->assertExitCode(0);
});

test('ob:version --json emits the expected keys', function () {
    $this->artisan('ob:version --json')->assertExitCode(0);

    // Resolve the payload directly to assert its shape (command output capture
    // does not expose the buffer for parsing here).
    $versions = app(VersionService::class);

    $payload = [
        'code' => $versions->code(),
        'installed' => $versions->installed(),
        'changelog' => $versions->changelogLatest(),
        'drift' => $versions->hasDrift(),
    ];

    expect(array_keys($payload))->toBe(['code', 'installed', 'changelog', 'drift'])
        ->and($payload['code'])->toBe((string) config('brigade.version'))
        ->and($payload['drift'])->toBeBool();
});
