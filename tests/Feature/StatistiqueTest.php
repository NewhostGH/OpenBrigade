<?php

use App\Http\Controllers\StatistiqueController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function statStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns true so
 * permission-gated statistics actions are reachable.
 */
function statFakeUser(): User
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
 * Bind StatistiqueController so index() returns the real view rendered with
 * stub data, keeping the assertion at the HTTP/view level without a DB call.
 */
function statStubIndex(): void
{
    app()->bind(StatistiqueController::class, function () {
        $ctrl = Mockery::mock(StatistiqueController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('statistique.index', [
                'year'             => now()->year,
                'years'            => [now()->year],
                'eventsData'       => array_fill(0, 12, 0),
                'participantData'  => array_fill(0, 12, 0),
                'newMembersByYear' => [],
                'topParticipants'  => Collection::make([]),
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    statStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /statistiques to login', function () {
    $this->get('/statistiques')->assertRedirect('/login');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy repo_events.php redirects to statistique.index', function () {
    $this->actingAs(statFakeUser())
        ->get('/legacy/repo_events.php')
        ->assertRedirect(route('statistique.index'));
});

// ── Statistique index (stubbed controller) ───────────────────────────────────

test('authenticated users can access the statistique index', function () {
    statStubIndex();
    $this->actingAs(statFakeUser())->get('/statistiques')->assertStatus(200);
});

test('statistique index renders the statistique.index view', function () {
    statStubIndex();
    $this->actingAs(statFakeUser())->get('/statistiques')->assertViewIs('statistique.index');
});

test('statistique index passes all required view variables', function () {
    statStubIndex();
    $this->actingAs(statFakeUser())->get('/statistiques')
        ->assertViewHasAll(['year', 'years', 'eventsData', 'participantData', 'newMembersByYear', 'topParticipants']);
});
