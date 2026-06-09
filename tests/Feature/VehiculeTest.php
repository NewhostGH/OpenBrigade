<?php

use App\Http\Controllers\VehiculeController;
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
 * Bind VehiculeController so index() returns the real view rendered with stub
 * data, keeping the assertion at the HTTP/view level without touching the DB.
 */
function vehiculeStubIndex(): void
{
    app()->bind(VehiculeController::class, function () {
        $ctrl = Mockery::mock(VehiculeController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 30);
        $page->setPath('/vehicules');
        $ctrl->shouldReceive('index')->andReturn(
            view('vehicule.index', [
                'items'    => $page,
                'columns'  => [],
                'search'   => '',
                'filtSect' => 0,
                'status'   => 'all',
                'sections' => Collection::make([]),
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    vehiculeStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /vehicules to login', function () {
    $this->get('/vehicules')->assertRedirect('/login');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy vehicule.php redirects to vehicule.index', function () {
    $this->actingAs(vehiculeFakeUser())
        ->get('/legacy/vehicule.php')
        ->assertRedirect(route('vehicule.index'));
});

test('legacy upd_vehicule.php with vehicule param redirects to vehicule.show', function () {
    $this->actingAs(vehiculeFakeUser())
        ->get('/legacy/upd_vehicule.php?vehicule=7')
        ->assertRedirect(route('vehicule.show', 7));
});

test('legacy upd_vehicule.php without params redirects to vehicule.index', function () {
    $this->actingAs(vehiculeFakeUser())
        ->get('/legacy/upd_vehicule.php')
        ->assertRedirect(route('vehicule.index'));
});

// ── Vehicule index (stubbed controller) ──────────────────────────────────────

test('authenticated users can access the vehicule list', function () {
    vehiculeStubIndex();
    $this->actingAs(vehiculeFakeUser())->get('/vehicules')->assertStatus(200);
});

test('vehicule list renders the vehicule.index view', function () {
    vehiculeStubIndex();
    $this->actingAs(vehiculeFakeUser())->get('/vehicules')->assertViewIs('vehicule.index');
});
