<?php

use App\Http\Controllers\ConsumableController;
use App\Http\Controllers\EquipmentController;
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
 * Bind EquipmentController so index() returns the real view rendered with stub
 * data, keeping the assertion at the HTTP/view level without touching the DB.
 */
function materielStubIndex(): void
{
    app()->bind(EquipmentController::class, function () {
        $ctrl = Mockery::mock(EquipmentController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 50);
        $page->setPath('/equipment');
        $ctrl->shouldReceive('index')->andReturn(
            view('equipment.index', [
                'items' => $page,
                'columns' => [],
                'search' => '',
                'filtSect' => 0,
                'sections' => Collection::make([]),
            ])
        );

        return $ctrl;
    });
}

/**
 * Bind ConsumableController so index() returns the real view rendered with
 * stub data, keeping the assertion at the HTTP/view level without a DB call.
 */
function consommableStubIndex(): void
{
    app()->bind(ConsumableController::class, function () {
        $ctrl = Mockery::mock(ConsumableController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 50);
        $page->setPath('/consumables');
        $ctrl->shouldReceive('index')->andReturn(
            view('consumable.index', [
                'items' => $page,
                'columns' => [],
                'search' => '',
                'filtSect' => 0,
                'alert' => false,
                'sections' => Collection::make([]),
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    inventaireStubNav();
    $this->withoutMiddleware([ValidateCsrfToken::class, RequireFeature::class]);
});

// ── Matériels ────────────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /equipment to login', function () {
    $this->get('/equipment')->assertRedirect('/login');
});

test('legacy materiel.php redirects to equipment.index', function () {
    $this->actingAs(inventaireFakeUser())
        ->get('/legacy/materiel.php')
        ->assertRedirect(route('equipment.index'));
});

test('authenticated users can access the materiel list', function () {
    materielStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/equipment')->assertStatus(200);
});

test('materiel list renders the equipment.index view', function () {
    materielStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/equipment')->assertViewIs('equipment.index');
});

// ── Consumables ─────────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /consumables to login', function () {
    $this->get('/consumables')->assertRedirect('/login');
});

test('legacy consommable.php redirects to consumable.index', function () {
    $this->actingAs(inventaireFakeUser())
        ->get('/legacy/consommable.php')
        ->assertRedirect(route('consumable.index'));
});

test('authenticated users can access the consommable list', function () {
    consommableStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/consumables')->assertStatus(200);
});

test('consommable list renders the consumable.index view', function () {
    consommableStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/consumables')->assertViewIs('consumable.index');
});
