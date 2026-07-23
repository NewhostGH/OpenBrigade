<?php

use App\Models\PluginRegistry;
use App\Services\Plugins\PluginRegistryService;
use Illuminate\Support\Facades\Http;

/** Registry service with a fixed (unsaved) registry list — no DB. */
function registryService(array $registries): PluginRegistryService
{
    return new class($registries) extends PluginRegistryService
    {
        public function __construct(private array $fixed) {}

        protected function registries(): iterable
        {
            return $this->fixed;
        }
    };
}

function fakeRegistry(int $id, string $name, string $url): PluginRegistry
{
    $r = new PluginRegistry(['name' => $name, 'url' => $url, 'enabled' => true]);
    $r->id = $id;

    return $r;
}

function registryPayload(array $plugins): array
{
    return ['name' => 'Test', 'plugins' => $plugins];
}

function registryPlugin(string $slug, string $version = '1.0.0', string $min = '6.0', string $max = ''): array
{
    return [
        'slug' => $slug, 'name' => ucfirst($slug), 'version' => $version,
        'description' => 'x', 'download_url' => "https://dl.example.org/{$slug}.zip",
        'sha256' => str_repeat('a', 64), 'min_app_version' => $min, 'max_app_version' => $max,
    ];
}

test('merges catalogs with first-registry-wins on slug collisions', function () {
    Http::fake([
        'one.example.org/*' => Http::response(registryPayload([registryPlugin('alpha', '2.0.0'), registryPlugin('beta')])),
        'two.example.org/*' => Http::response(registryPayload([registryPlugin('alpha', '9.9.9'), registryPlugin('gamma')])),
    ]);

    $catalog = registryService([
        fakeRegistry(1, 'One', 'https://one.example.org/registry.json'),
        fakeRegistry(2, 'Two', 'https://two.example.org/registry.json'),
    ])->catalog();

    expect(array_keys($catalog['plugins']))->toBe(['alpha', 'beta', 'gamma'])
        ->and($catalog['plugins']['alpha']['version'])->toBe('2.0.0')
        ->and($catalog['plugins']['alpha']['registry'])->toBe('One')
        ->and($catalog['plugins']['gamma']['registry'])->toBe('Two')
        ->and($catalog['errors'])->toBe([]);
});

test('a broken registry degrades only itself', function () {
    Http::fake([
        'one.example.org/*' => Http::response(registryPayload([registryPlugin('alpha')])),
        'two.example.org/*' => Http::response('not json at all', 500),
    ]);

    $catalog = registryService([
        fakeRegistry(1, 'One', 'https://one.example.org/registry.json'),
        fakeRegistry(2, 'Two', 'https://two.example.org/registry.json'),
    ])->catalog();

    expect(array_keys($catalog['plugins']))->toBe(['alpha'])
        ->and($catalog['errors'])->toHaveKey('Two');
});

test('a slug may ship one track per app line — the compatible one wins', function () {
    config(['brigade.version' => '6.0.0']);
    Http::fake([
        'one.example.org/*' => Http::response(registryPayload([
            registryPlugin('alpha', '1.5.0', '5.0', '6.999'),
            registryPlugin('alpha', '2.0.0', '7.0'),
        ])),
    ]);

    $alpha = registryService([
        fakeRegistry(1, 'One', 'https://one.example.org/registry.json'),
    ])->catalog()['plugins']['alpha'];

    // The 7.x-only track must never surface on a 6.x install.
    expect($alpha['version'])->toBe('1.5.0')
        ->and($alpha['compatible'])->toBeTrue();
});

test('a newer app picks the newer track of the same slug', function () {
    config(['brigade.version' => '7.1.0']);
    Http::fake([
        'one.example.org/*' => Http::response(registryPayload([
            registryPlugin('alpha', '1.5.0', '5.0', '6.999'),
            registryPlugin('alpha', '2.0.0', '7.0'),
        ])),
    ]);

    $alpha = registryService([
        fakeRegistry(1, 'One', 'https://one.example.org/registry.json'),
    ])->catalog()['plugins']['alpha'];

    expect($alpha['version'])->toBe('2.0.0')
        ->and($alpha['compatible'])->toBeTrue();
});

test('a slug with no compatible track is surfaced as incompatible', function () {
    config(['brigade.version' => '6.0.0']);
    Http::fake([
        'one.example.org/*' => Http::response(registryPayload([
            registryPlugin('alpha', '2.0.0', '7.0'),
        ])),
    ]);

    $alpha = registryService([
        fakeRegistry(1, 'One', 'https://one.example.org/registry.json'),
    ])->catalog()['plugins']['alpha'];

    expect($alpha['compatible'])->toBeFalse()
        ->and($alpha['version'])->toBe('2.0.0');
});

test('optional presentation fields are sanitized', function () {
    Http::fake([
        'one.example.org/*' => Http::response(registryPayload([
            registryPlugin('alpha') + [
                'category' => '  Opérationnel  ',
                'icon' => 'https://cdn.example.org/alpha.png',
                'homepage' => 'javascript:alert(1)',
                'screenshots' => ['https://cdn.example.org/1.png', 'ftp://nope', 42],
            ],
        ])),
    ]);

    $alpha = registryService([
        fakeRegistry(1, 'One', 'https://one.example.org/registry.json'),
    ])->catalog()['plugins']['alpha'];

    expect($alpha['category'])->toBe('Opérationnel')
        ->and($alpha['icon'])->toBe('https://cdn.example.org/alpha.png')
        ->and($alpha['homepage'])->toBeNull()
        ->and($alpha['screenshots'])->toBe(['https://cdn.example.org/1.png']);
});

test('a site URL is normalised to its registry.json', function () {
    Http::fake(fn ($request) => str_ends_with($request->url(), '/registry.json')
        ? Http::response(registryPayload([registryPlugin('alpha')]))
        : Http::response('<!DOCTYPE html><title>catalog viewer</title>'));

    $catalog = registryService([
        fakeRegistry(1, 'Slash', 'https://one.example.org/catalog/'),
        fakeRegistry(2, 'NoSlash', 'https://two.example.org/catalog'),
    ])->catalog();

    // Both variants fetch …/catalog/registry.json instead of the HTML page.
    expect(array_keys($catalog['plugins']))->toBe(['alpha'])
        ->and($catalog['errors'])->toBe([]);

    Http::assertSent(fn ($request) => $request->url() === 'https://one.example.org/catalog/registry.json');
    Http::assertSent(fn ($request) => $request->url() === 'https://two.example.org/catalog/registry.json');
});

test('entries missing required fields are dropped', function () {
    Http::fake([
        'one.example.org/*' => Http::response(registryPayload([
            registryPlugin('alpha'),
            ['slug' => 'no-sha', 'name' => 'x', 'version' => '1', 'description' => 'x', 'download_url' => 'https://x', 'min_app_version' => '6.0'],
            ['slug' => 'BAD SLUG'] + registryPlugin('ignored'),
        ])),
    ]);

    $catalog = registryService([
        fakeRegistry(1, 'One', 'https://one.example.org/registry.json'),
    ])->catalog();

    expect(array_keys($catalog['plugins']))->toBe(['alpha']);
});
