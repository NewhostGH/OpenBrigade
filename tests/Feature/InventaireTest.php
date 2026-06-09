<?php

use App\Http\Controllers\ConsommableController;
use App\Http\Controllers\MaterielController;
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
function inventaireStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns true so
 * permission-gated inventory actions are reachable.
 */
function inventaireFakeUser(): User
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
 * Bind MaterielController so index() returns the real view rendered with stub
 * data, keeping the assertion at the HTTP/view level without touching the DB.
 */
function materielStubIndex(): void
{
    app()->bind(MaterielController::class, function () {
        $ctrl = Mockery::mock(MaterielController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 50);
        $page->setPath('/materiels');
        $ctrl->shouldReceive('index')->andReturn(
            view('materiel.index', [
                'items'    => $page,
                'columns'  => [],
                'search'   => '',
                'filtSect' => 0,
                'sections' => Collection::make([]),
            ])
        );

        return $ctrl;
    });
}

/**
 * Bind ConsommableController so index() returns the real view rendered with
 * stub data, keeping the assertion at the HTTP/view level without a DB call.
 */
function consommableStubIndex(): void
{
    app()->bind(ConsommableController::class, function () {
        $ctrl = Mockery::mock(ConsommableController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 50);
        $page->setPath('/consommables');
        $ctrl->shouldReceive('index')->andReturn(
            view('consommable.index', [
                'items'    => $page,
                'columns'  => [],
                'search'   => '',
                'filtSect' => 0,
                'alert'    => false,
                'sections' => Collection::make([]),
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    inventaireStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Matériels ────────────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /materiels to login', function () {
    $this->get('/materiels')->assertRedirect('/login');
});

test('legacy materiel.php redirects to materiel.index', function () {
    $this->actingAs(inventaireFakeUser())
        ->get('/legacy/materiel.php')
        ->assertRedirect(route('materiel.index'));
});

test('authenticated users can access the materiel list', function () {
    materielStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/materiels')->assertStatus(200);
});

test('materiel list renders the materiel.index view', function () {
    materielStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/materiels')->assertViewIs('materiel.index');
});

// ── Consommables ─────────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /consommables to login', function () {
    $this->get('/consommables')->assertRedirect('/login');
});

test('legacy consommable.php redirects to consommable.index', function () {
    $this->actingAs(inventaireFakeUser())
        ->get('/legacy/consommable.php')
        ->assertRedirect(route('consommable.index'));
});

test('authenticated users can access the consommable list', function () {
    consommableStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/consommables')->assertStatus(200);
});

test('consommable list renders the consommable.index view', function () {
    consommableStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/consommables')->assertViewIs('consommable.index');
});
