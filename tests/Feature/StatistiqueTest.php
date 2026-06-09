<?php

use App\Http\Controllers\StatistiqueController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

function statStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

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

function statStubDashboard(): void
{
    app()->bind(StatistiqueController::class, function () {
        $ctrl = Mockery::mock(StatistiqueController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('statistique.index', [
                'year' => now()->year,
                'years' => [now()->year],
                'sectionId' => 1,
                'eventsData' => array_fill(0, 12, 0),
                'participantData' => array_fill(0, 12, 0),
                'newMembersByYear' => [],
                'topParticipants' => Collection::make([]),
                'eventsByType' => [],
                'totalMembers' => 0,
                'totalEvents' => 0,
                'totalParticipants' => 0,
                'totalHours' => 0,
                'newMembersThisYear' => 0,
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

test('unauthenticated users are redirected from /statistiques/dashboard to login', function () {
    $this->get('/statistiques/dashboard')->assertRedirect('/login');
});

// ── URL structure ─────────────────────────────────────────────────────────────

test('/statistiques redirects to the dashboard URL', function () {
    $this->actingAs(statFakeUser())
        ->get('/statistiques')
        ->assertRedirect(route('statistique.dashboard'));
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy repo_events.php redirects to statistique.dashboard', function () {
    $this->actingAs(statFakeUser())
        ->get('/legacy/repo_events.php')
        ->assertRedirect(route('statistique.index'));
});

// ── Dashboard (stubbed controller) ───────────────────────────────────────────

test('authenticated users can access the statistique dashboard', function () {
    statStubDashboard();
    $this->actingAs(statFakeUser())->get('/statistiques/dashboard')->assertStatus(200);
});

test('dashboard renders the statistique.index view', function () {
    statStubDashboard();
    $this->actingAs(statFakeUser())->get('/statistiques/dashboard')->assertViewIs('statistique.index');
});

test('dashboard passes all required view variables', function () {
    statStubDashboard();
    $this->actingAs(statFakeUser())->get('/statistiques/dashboard')
        ->assertViewHasAll([
            'year', 'years', 'eventsData', 'participantData',
            'newMembersByYear', 'topParticipants',
            'eventsByType', 'totalEvents', 'totalParticipants', 'totalHours',
            'totalMembers', 'newMembersThisYear',
        ]);
});
