<?php

use App\Services\Plugins\InvalidPluginException;
use App\Services\Plugins\PluginInstaller;
use App\Services\Plugins\PluginStateService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/** Build a zip in memory-backed temp storage and return [bytes, sha256]. */
function pluginZip(array $files): array
{
    $path = storage_path('framework/testing/plugin-'.uniqid().'.zip');
    File::ensureDirectoryExists(dirname($path));

    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE);
    foreach ($files as $name => $content) {
        $zip->addFromString($name, $content);
    }
    $zip->close();

    $bytes = (string) file_get_contents($path);
    @unlink($path);

    return [$bytes, hash('sha256', $bytes)];
}

function pluginEntry(string $sha256): array
{
    return [
        'slug' => 'demo-plugin',
        'name' => 'Demo',
        'version' => '1.0.0',
        'description' => 'x',
        'download_url' => 'https://plugins.example.org/demo-plugin.zip',
        'sha256' => $sha256,
        'min_app_version' => '1.0',
    ];
}

function pluginInstaller(): PluginInstaller
{
    $state = Mockery::mock(PluginStateService::class);
    $state->shouldReceive('record');
    $state->shouldIgnoreMissing();

    return new PluginInstaller($state);
}

afterEach(function () {
    File::deleteDirectory(base_path('plugins/demo-plugin'));
});

test('installs a valid package end to end', function () {
    config(['brigade.version' => '6.0.0']);
    [$bytes, $sha] = pluginZip([
        'plugin.json' => json_encode([
            'name' => 'Demo', 'slug' => 'demo-plugin', 'version' => '1.0.0',
            'description' => 'x', 'min_app_version' => '1.0',
            'provider' => 'Demo\\Provider',
        ]),
        'src/Provider.php' => '<?php',
    ]);
    Http::fake(['plugins.example.org/*' => Http::response($bytes)]);

    $manifest = pluginInstaller()->install(pluginEntry($sha));

    expect($manifest->slug)->toBe('demo-plugin')
        ->and(is_file(base_path('plugins/demo-plugin/plugin.json')))->toBeTrue()
        ->and(is_file(base_path('plugins/demo-plugin/src/Provider.php')))->toBeTrue();
});

test('refuses a checksum mismatch', function () {
    [$bytes] = pluginZip(['plugin.json' => '{}']);
    Http::fake(['plugins.example.org/*' => Http::response($bytes)]);

    pluginInstaller()->install(pluginEntry(str_repeat('0', 64)));
})->throws(InvalidPluginException::class);

test('a registry may opt out of checksum verification', function () {
    config(['brigade.version' => '6.0.0']);
    [$bytes] = pluginZip([
        'plugin.json' => json_encode([
            'name' => 'Demo', 'slug' => 'demo-plugin', 'version' => '1.0.0',
            'description' => 'x', 'min_app_version' => '1.0',
            'provider' => 'Demo\\Provider',
        ]),
    ]);
    Http::fake(['plugins.example.org/*' => Http::response($bytes)]);

    $entry = pluginEntry(str_repeat('0', 64)) + ['verify_checksum' => false];

    expect(pluginInstaller()->install($entry)->slug)->toBe('demo-plugin');
});

test('refuses path traversal in the archive', function () {
    [$bytes, $sha] = pluginZip([
        'plugin.json' => '{}',
        '../evil.php' => '<?php',
    ]);
    Http::fake(['plugins.example.org/*' => Http::response($bytes)]);

    pluginInstaller()->install(pluginEntry($sha));
})->throws(InvalidPluginException::class);

test('refuses a manifest whose slug differs from the catalog', function () {
    config(['brigade.version' => '6.0.0']);
    [$bytes, $sha] = pluginZip([
        'plugin.json' => json_encode([
            'name' => 'Demo', 'slug' => 'other-slug', 'version' => '1.0.0',
            'description' => 'x', 'min_app_version' => '1.0',
            'provider' => 'Demo\\Provider',
        ]),
    ]);
    Http::fake(['plugins.example.org/*' => Http::response($bytes)]);

    pluginInstaller()->install(pluginEntry($sha));
})->throws(InvalidPluginException::class);

test('refuses a plugin whose max_app_version is below the running app', function () {
    config(['brigade.version' => '7.0.0']);
    [$bytes, $sha] = pluginZip([
        'plugin.json' => json_encode([
            'name' => 'Demo', 'slug' => 'demo-plugin', 'version' => '1.0.0',
            'description' => 'x', 'min_app_version' => '5.0', 'max_app_version' => '6.999',
            'provider' => 'Demo\\Provider',
        ]),
    ]);
    Http::fake(['plugins.example.org/*' => Http::response($bytes)]);

    pluginInstaller()->install(pluginEntry($sha));
})->throws(InvalidPluginException::class);

test('refuses a plugin requiring a newer app version', function () {
    config(['brigade.version' => '6.0.0']);
    [$bytes, $sha] = pluginZip([
        'plugin.json' => json_encode([
            'name' => 'Demo', 'slug' => 'demo-plugin', 'version' => '1.0.0',
            'description' => 'x', 'min_app_version' => '99.0',
            'provider' => 'Demo\\Provider',
        ]),
    ]);
    Http::fake(['plugins.example.org/*' => Http::response($bytes)]);

    pluginInstaller()->install(pluginEntry($sha));
})->throws(InvalidPluginException::class);
