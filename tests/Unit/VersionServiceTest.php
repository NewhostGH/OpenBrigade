<?php

use App\Services\GeneralSettingService;
use App\Services\VersionService;

// A VersionService wired to a stub GeneralSettingService so the installed
// version can be driven without a database.
function versionService(string $installed): VersionService
{
    $settings = new class($installed) extends GeneralSettingService
    {
        public function __construct(private string $stored) {}

        public function appVersion(): string
        {
            return $this->stored;
        }
    };

    return new VersionService($settings);
}

test('code() reads config(brigade.version)', function () {
    config()->set('brigade.version', '6.1.2');

    expect(versionService('')->code())->toBe('6.1.2');
});

test('installed() delegates to the settings service', function () {
    expect(versionService('6.0.0')->installed())->toBe('6.0.0')
        ->and(versionService('')->installed())->toBe('');
});

test('changelogLatest() returns the top released version, skipping Unreleased', function () {
    // Reads the real repository CHANGELOG.md via base_path().
    expect(versionService('')->changelogLatest())->toBe('6.0.0');
});

test('hasDrift() is false when installed is unknown or matches the code', function () {
    config()->set('brigade.version', '6.0.0');

    expect(versionService('')->hasDrift())->toBeFalse()
        ->and(versionService('6.0.0')->hasDrift())->toBeFalse();
});

test('hasDrift() is true when the code and installed versions differ', function () {
    config()->set('brigade.version', '6.1.0');

    expect(versionService('6.0.0')->hasDrift())->toBeTrue();
});

test('isValidSemver() accepts core, pre-release and build metadata', function () {
    expect(VersionService::isValidSemver('6.0.0'))->toBeTrue()
        ->and(VersionService::isValidSemver('6.1.0-rc.1'))->toBeTrue()
        ->and(VersionService::isValidSemver('6.1.0+ci.42'))->toBeTrue()
        ->and(VersionService::isValidSemver('6.1.0-rc.1+ci.42'))->toBeTrue();
});

test('isValidSemver() rejects malformed versions', function () {
    expect(VersionService::isValidSemver('6.0'))->toBeFalse()
        ->and(VersionService::isValidSemver('v6.0.0'))->toBeFalse()
        ->and(VersionService::isValidSemver('6.0.0.0'))->toBeFalse()
        ->and(VersionService::isValidSemver('latest'))->toBeFalse()
        ->and(VersionService::isValidSemver(''))->toBeFalse();
});
