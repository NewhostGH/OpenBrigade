<?php

use App\Http\Controllers\ConsommableController;
use App\Http\Controllers\MaterielController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

// ── Helpers ─────────────────────────────────────────────────────────────────

function inventaireFakeUser(): User
{
    /** @var User&\Mockery\MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Test', 'P_PRENOM' => 'User',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('hasPermission')->andReturn(true);
    return $user;
}

function inventaireStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

function materielStubIndex(): void
{
    app()->bind(MaterielController::class, function () {
        $ctrl = Mockery::mock(MaterielController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 50);
        $page->setPath('/materiels');
        $ctrl->shouldReceive('index')->andReturn(
            view('materiel.index', [
                'items' => $page, 'search' => '', 'filtSect' => 0,
                'sections' => Collection::make([]),
            ])
        );
        return $ctrl;
    });
}

function consommableStubIndex(): void
{
    app()->bind(ConsommableController::class, function () {
        $ctrl = Mockery::mock(ConsommableController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 50);
        $page->setPath('/consommables');
        $ctrl->shouldReceive('index')->andReturn(
            view('consommable.index', [
                'items' => $page, 'search' => '', 'filtSect' => 0,
                'alert' => false, 'sections' => Collection::make([]),
            ])
        );
        return $ctrl;
    });
}

beforeEach(function () {
    inventaireStubNav();
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
});

// ── Matériels ─────────────────────────────────────────────────────────────────

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

test('materiel list uses the materiel.index template', function () {
    materielStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/materiels')->assertViewIs('materiel.index');
});

// ── Consommables ──────────────────────────────────────────────────────────────

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

test('consommable list uses the consommable.index template', function () {
    consommableStubIndex();
    $this->actingAs(inventaireFakeUser())->get('/consommables')->assertViewIs('consommable.index');
});
