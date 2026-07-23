<?php

use App\Services\Plugins\InvalidPluginException;
use App\Services\Plugins\PluginManifest;

function validManifestData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Animaux',
        'slug' => 'animaux',
        'version' => '1.0.0',
        'description' => 'Gestion des chiens de recherche.',
        'min_app_version' => '6.0.0',
        'provider' => 'ObPlugin\\Animaux\\AnimauxServiceProvider',
        'authors' => ['OpenBrigade'],
        'autoload' => ['ObPlugin\\Animaux\\' => 'src'],
    ], $overrides);
}

test('parses a valid manifest', function () {
    $m = PluginManifest::fromArray(validManifestData());

    expect($m->slug)->toBe('animaux')
        ->and($m->provider)->toBe('ObPlugin\\Animaux\\AnimauxServiceProvider')
        ->and($m->autoload)->toBe(['ObPlugin\\Animaux\\' => 'src'])
        ->and($m->maxAppVersion)->toBe('');
});

test('parses the optional compatibility cap', function () {
    $m = PluginManifest::fromArray(validManifestData(['max_app_version' => '6.999']));

    expect($m->maxAppVersion)->toBe('6.999')
        ->and(PluginManifest::compatible('6.5', '6.0.0', '6.999'))->toBeTrue()
        ->and(PluginManifest::compatible('7.0', '6.0.0', '6.999'))->toBeFalse()
        ->and(PluginManifest::compatible('7.0', '6.0.0', ''))->toBeTrue();
});

test('rejects a missing required field', function (string $field) {
    PluginManifest::fromArray(validManifestData([$field => '']));
})->with(['name', 'slug', 'version', 'provider', 'min_app_version'])
    ->throws(InvalidPluginException::class);

test('rejects an invalid slug', function (string $slug) {
    PluginManifest::fromArray(validManifestData(['slug' => $slug]));
})->with(['UPPER', 'has space', 'é-accent', '-leading', 'a'])
    ->throws(InvalidPluginException::class);

test('rejects traversal in the autoload map', function () {
    PluginManifest::fromArray(validManifestData(['autoload' => ['X\\' => '../outside']]));
})->throws(InvalidPluginException::class);

test('rejects an absolute autoload directory', function () {
    PluginManifest::fromArray(validManifestData(['autoload' => ['X\\' => '/etc']]));
})->throws(InvalidPluginException::class);
