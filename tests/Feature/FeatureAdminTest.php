<?php

use App\Models\ObFeature;
use App\Models\User;
use App\Services\FeatureService;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function featureStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Minimal fake user whose hasPermission() returns $can for every feature id,
 * driving both access and permission-denial assertions (mirrors AdminTest).
 */
function featureFakeUser(bool $can = true): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Test', 'P_PRENOM' => 'Admin',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'GP_ID' => 2,
        'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('hasPermission')->andReturn($can);

    return $user;
}

/** Clear the memoised feature map so a fresh request re-reads the DB. */
function forgetFeatureService(): void
{
    app()->forgetInstance(FeatureService::class);
}

beforeEach(function () {
    featureStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);

    // These tests assert real persistence against the ob_feature registry,
    // which is populated by the back-fill migration. Skip cleanly if the
    // registry has not been migrated in this environment.
    if (! Schema::hasTable('ob_feature') || ObFeature::query()->doesntExist()) {
        $this->markTestSkipped('ob_feature registry is not populated.');
    }
});

// ── Index ────────────────────────────────────────────────────────────────────

test('admin can view the fonctionnalités screen', function () {
    $feature = ObFeature::query()->first();

    $this->actingAs(featureFakeUser())
        ->get('/admin/fonctionnalites')
        ->assertOk()
        ->assertViewIs('admin.fonctionnalites')
        ->assertViewHasAll(['features', 'groups'])
        ->assertSee($feature->name);
});

test('a user without permission 14 gets 403', function () {
    $this->actingAs(featureFakeUser(can: false))
        ->get('/admin/fonctionnalites')
        ->assertForbidden();
});

// ── Toggle persistence ───────────────────────────────────────────────────────

test('toggling a feature persists to ob_feature and the legacy configuration row', function () {
    $feature = ObFeature::query()->whereNotNull('legacy_config_id')->first();

    if ($feature === null) {
        $this->markTestSkipped('No feature with a legacy_config_id to assert against.');
    }

    $original = (bool) $feature->enabled;
    $originalConfig = DB::table('configuration')->where('ID', $feature->legacy_config_id)->value('VALUE');

    try {
        $target = ! $original;

        $this->actingAs(featureFakeUser())
            ->patch(route('admin.fonctionnalites.toggle', $feature), [
                'enabled' => $target ? '1' : '0',
            ])
            ->assertRedirect();

        expect((bool) $feature->fresh()->enabled)->toBe($target);
        expect(DB::table('configuration')->where('ID', $feature->legacy_config_id)->value('VALUE'))
            ->toBe($target ? '1' : '0');
    } finally {
        // Restore the registry and legacy row to their original state.
        app(FeatureService::class)->setEnabled($feature->fresh(), $original);
        DB::table('configuration')->where('ID', $feature->legacy_config_id)
            ->update(['VALUE' => $originalConfig]);
    }
});

// ── Runtime gate ─────────────────────────────────────────────────────────────

test('the feature gate returns 404 when disabled and not 404 when enabled', function () {
    $feature = ObFeature::query()->where('key', 'vehicules')->first();

    if ($feature === null) {
        $this->markTestSkipped('The vehicules feature is not registered.');
    }

    $original = (bool) $feature->enabled;

    try {
        // Disabled → the gate hides the route.
        $feature->update(['enabled' => false]);
        forgetFeatureService();

        $this->actingAs(featureFakeUser())
            ->get('/vehicules')
            ->assertNotFound();

        // Enabled → the gate lets the request through (any non-404 status).
        $feature->update(['enabled' => true]);
        forgetFeatureService();

        $status = $this->actingAs(featureFakeUser())->get('/vehicules')->getStatusCode();
        expect($status)->not->toBe(404);
    } finally {
        $feature->update(['enabled' => $original]);
        forgetFeatureService();
    }
});
