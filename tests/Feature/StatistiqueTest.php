<?php

use App\Http\Controllers\StatistiqueController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Support\Collection;

function statFakeUser(): User
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

function statStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

function statStubIndex(): void
{
    app()->bind(StatistiqueController::class, function () {
        $ctrl = Mockery::mock(StatistiqueController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('statistique.index', [
                'year'               => now()->year,
                'years'              => [now()->year],
                'eventsData'         => array_fill(0, 12, 0),
                'participantData'    => array_fill(0, 12, 0),
                'newMembersByYear'   => [],
                'topParticipants'    => Collection::make([]),
            ])
        );
        return $ctrl;
    });
}

beforeEach(function () {
    statStubNav();
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
});

test('unauthenticated users are redirected from /statistiques to login', function () {
    $this->get('/statistiques')->assertRedirect('/login');
});

test('legacy repo_events.php redirects to statistique.index', function () {
    $this->actingAs(statFakeUser())
        ->get('/legacy/repo_events.php')
        ->assertRedirect(route('statistique.index'));
});

test('authenticated users can access statistics', function () {
    statStubIndex();
    $this->actingAs(statFakeUser())->get('/statistiques')->assertStatus(200);
});

test('statistique index uses the statistique.index template', function () {
    statStubIndex();
    $this->actingAs(statFakeUser())->get('/statistiques')->assertViewIs('statistique.index');
});

test('statistique index passes all required view variables', function () {
    statStubIndex();
    $this->actingAs(statFakeUser())->get('/statistiques')
        ->assertViewHasAll(['year', 'years', 'eventsData', 'participantData', 'newMembersByYear', 'topParticipants']);
});
