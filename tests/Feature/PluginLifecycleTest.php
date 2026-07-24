<?php

use App\Models\Plugin;
use App\Services\Plugins\InvalidPluginException;
use App\Services\Plugins\PluginInstaller;
use App\Services\Plugins\PluginLoader;
use App\Services\Plugins\PluginStateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// The full migration set can't run on the sqlite test DB (a MySQL-only
// `SET sql_mode` migration), so the repo avoids RefreshDatabase — we create
// just the ob_plugin table this suite needs.
beforeEach(function () {
    Schema::dropIfExists('ob_plugin');
    Schema::create('ob_plugin', function (Blueprint $t) {
        $t->id();
        $t->string('slug')->unique();
        $t->string('name');
        $t->string('version');
        $t->string('min_app_version')->default('');
        $t->string('max_app_version')->default('');
        $t->boolean('enabled')->default(false);
        $t->timestamp('installed_at')->nullable();
        $t->timestamps();
    });
});

/** Write a minimal on-disk plugin under plugins/<slug>. */
function writePlugin(string $slug, string $namespace, string $providerBody, ?string $migration = null): void
{
    $dir = base_path('plugins/'.$slug);
    File::ensureDirectoryExists($dir.'/src');

    File::put($dir.'/plugin.json', json_encode([
        'name' => $slug,
        'slug' => $slug,
        'version' => '1.0.0',
        'description' => 'x',
        'min_app_version' => '1.0',
        'provider' => $namespace.'\\Provider',
        'authors' => ['t'],
        'autoload' => [$namespace.'\\' => 'src'],
    ]));

    File::put($dir.'/src/Provider.php',
        "<?php\nnamespace {$namespace};\nuse Illuminate\\Support\\ServiceProvider;\n"
        ."class Provider extends ServiceProvider { {$providerBody} }");

    if ($migration !== null) {
        File::ensureDirectoryExists($dir.'/database/migrations');
        File::put($dir.'/database/migrations/2026_01_01_000000_create.php', $migration);
    }
}

function recordPlugin(string $slug, bool $enabled): void
{
    Plugin::create([
        'slug' => $slug, 'name' => $slug, 'version' => '1.0.0',
        'min_app_version' => '1.0', 'max_app_version' => '',
        'enabled' => $enabled, 'installed_at' => now(),
    ]);
}

afterEach(function () {
    foreach (['life-ok', 'life-boom', 'life-off', 'life-mig', 'life-unins'] as $slug) {
        File::deleteDirectory(base_path('plugins/'.$slug));
    }
    Schema::dropIfExists('ob_plugin_life_mig');
    Schema::dropIfExists('ob_plugin');
});

test('enabled plugins boot and a broken one is isolated', function () {
    writePlugin('life-ok', 'ObPlugin\\LifeOk',
        'public function register(): void { app()->instance("life-ok-booted", true); }');
    writePlugin('life-boom', 'ObPlugin\\LifeBoom',
        'public function register(): void { throw new \\RuntimeException("boom"); }');
    recordPlugin('life-ok', enabled: true);
    recordPlugin('life-boom', enabled: true);

    $loader = new PluginLoader(new PluginStateService);
    $loader->boot(app());

    // The healthy plugin booted; the broken one is quarantined, not fatal.
    expect(app()->bound('life-ok-booted'))->toBeTrue()
        ->and($loader->loadFailures())->toHaveKey('life-boom')
        ->and($loader->loadFailures()['life-boom'])->toContain('boom');
});

test('a disabled plugin is never booted', function () {
    writePlugin('life-off', 'ObPlugin\\LifeOff',
        'public function register(): void { app()->instance("life-off-booted", true); }');
    recordPlugin('life-off', enabled: false);

    (new PluginLoader(new PluginStateService))->boot(app());

    expect(app()->bound('life-off-booted'))->toBeFalse();
});

test('enabling runs the plugin migrations and disabling keeps the table', function () {
    $migration = <<<'PHP'
    <?php
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    return new class extends Migration {
        public function up(): void { Schema::create('ob_plugin_life_mig', fn (Blueprint $t) => $t->id()); }
        public function down(): void { Schema::dropIfExists('ob_plugin_life_mig'); }
    };
    PHP;
    writePlugin('life-mig', 'ObPlugin\\LifeMig', 'public function register(): void {}', $migration);
    recordPlugin('life-mig', enabled: false);

    $installer = app(PluginInstaller::class);
    $installer->enable('life-mig');

    expect(Schema::hasTable('ob_plugin_life_mig'))->toBeTrue()
        ->and(Plugin::where('slug', 'life-mig')->first()->enabled)->toBeTrue();

    // Disabling stops booting but never rolls migrations back (no data loss).
    $installer->disable('life-mig');

    expect(Schema::hasTable('ob_plugin_life_mig'))->toBeTrue()
        ->and(Plugin::where('slug', 'life-mig')->first()->enabled)->toBeFalse();
});

test('uninstall is refused while enabled and removes files+row once disabled', function () {
    writePlugin('life-unins', 'ObPlugin\\LifeUnins', 'public function register(): void {}');
    recordPlugin('life-unins', enabled: true);
    $installer = app(PluginInstaller::class);

    expect(fn () => $installer->uninstall('life-unins'))
        ->toThrow(InvalidPluginException::class);
    expect(File::isDirectory(base_path('plugins/life-unins')))->toBeTrue();

    $installer->disable('life-unins');
    $installer->uninstall('life-unins');

    expect(File::isDirectory(base_path('plugins/life-unins')))->toBeFalse()
        ->and(Plugin::where('slug', 'life-unins')->exists())->toBeFalse();
});
