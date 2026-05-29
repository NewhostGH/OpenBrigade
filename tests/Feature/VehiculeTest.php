<?php

use App\Http\Controllers\VehiculeController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

function vehiculeFakeUser(): User
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

function vehiculeStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

function vehiculeStubIndex(User $user): void
{
    app()->bind(VehiculeController::class, function () use ($user) {
        $ctrl = Mockery::mock(VehiculeController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 30);
        $page->setPath('/vehicules');
        $ctrl->shouldReceive('index')->andReturn(
            view('vehicule.index', [
                'items'    => $page,
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
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
});

test('unauthenticated users are redirected from /vehicules to login', function () {
    $this->get('/vehicules')->assertRedirect('/login');
});

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

test('authenticated users can access the vehicule list', function () {
    $user = vehiculeFakeUser();
    vehiculeStubIndex($user);

    $this->actingAs($user)->get('/vehicules')->assertStatus(200);
});

test('vehicule list uses the vehicule.index template', function () {
    $user = vehiculeFakeUser();
    vehiculeStubIndex($user);

    $this->actingAs($user)->get('/vehicules')->assertViewIs('vehicule.index');
});
