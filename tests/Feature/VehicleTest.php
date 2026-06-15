<?php

use App\Http\Controllers\VehicleController;
use App\Http\Middleware\RequireFeature;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function vehiculeStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns true so
 * permission-gated fleet actions are reachable.
 */
function vehiculeFakeUser(): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Test', 'P_PRENOM' => 'User',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('hasPermission')->andReturn(true);

    return $user;
}

/**
 * Bind VehicleController so index() returns the real view rendered with stub
 * data, keeping the assertion at the HTTP/view level without touching the DB.
 */
function vehiculeStubIndex(): void
{
    app()->bind(VehicleController::class, function () {
        $ctrl = Mockery::mock(VehicleController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 30);
        $page->setPath('/vehicles');
        $ctrl->shouldReceive('index')->andReturn(
            view('vehicle.index', [
                'items' => $page,
                'columns' => [],
                'search' => '',
                'filtSect' => 0,
                'status' => 'all',
                'sections' => Collection::make([]),
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    vehiculeStubNav();
    $this->withoutMiddleware([ValidateCsrfToken::class, RequireFeature::class]);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /vehicles to login', function () {
    $this->get('/vehicles')->assertRedirect('/login');
});

test('unauthenticated users are redirected from the vehicle exports to login', function () {
    $this->get('/vehicles/export/xls')->assertRedirect('/login');
    $this->get('/vehicles/export/csv')->assertRedirect('/login');
});

test('the vehicle export routes are registered', function () {
    expect(route('vehicle.export.xls'))->toContain('/vehicles/export/xls');
    expect(route('vehicle.export.csv'))->toContain('/vehicles/export/csv');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy vehicule.php redirects to vehicle.index', function () {
    $this->actingAs(vehiculeFakeUser())
        ->get('/legacy/vehicule.php')
        ->assertRedirect(route('vehicle.index'));
});

test('legacy upd_vehicule.php with vehicule param redirects to vehicle.show', function () {
    $this->actingAs(vehiculeFakeUser())
        ->get('/legacy/upd_vehicule.php?vehicule=7')
        ->assertRedirect(route('vehicle.show', 7));
});

test('legacy upd_vehicule.php without params redirects to vehicle.index', function () {
    $this->actingAs(vehiculeFakeUser())
        ->get('/legacy/upd_vehicule.php')
        ->assertRedirect(route('vehicle.index'));
});

// ── Vehicle index (stubbed controller) ──────────────────────────────────────

test('authenticated users can access the vehicle list', function () {
    vehiculeStubIndex();
    $this->actingAs(vehiculeFakeUser())->get('/vehicles')->assertStatus(200);
});

test('vehicle list renders the vehicle.index view', function () {
    vehiculeStubIndex();
    $this->actingAs(vehiculeFakeUser())->get('/vehicles')->assertViewIs('vehicle.index');
});
