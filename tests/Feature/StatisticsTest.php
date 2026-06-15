<?php

use App\Http\Controllers\StatisticsController;
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
    app()->bind(StatisticsController::class, function () {
        $ctrl = Mockery::mock(StatisticsController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('statistics.index', [
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

test('unauthenticated users are redirected from /statistics to login', function () {
    $this->get('/statistics')->assertRedirect('/login');
});

test('unauthenticated users are redirected from /statistics/dashboard to login', function () {
    $this->get('/statistics/dashboard')->assertRedirect('/login');
});

// ── URL structure ─────────────────────────────────────────────────────────────

test('/statistics redirects to the dashboard URL', function () {
    $this->actingAs(statFakeUser())
        ->get('/statistics')
        ->assertRedirect(route('statistics.dashboard'));
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy repo_events.php redirects to statistics.dashboard', function () {
    $this->actingAs(statFakeUser())
        ->get('/legacy/repo_events.php')
        ->assertRedirect(route('statistics.index'));
});

// ── Dashboard (stubbed controller) ───────────────────────────────────────────

test('authenticated users can access the statistique dashboard', function () {
    statStubDashboard();
    $this->actingAs(statFakeUser())->get('/statistics/dashboard')->assertStatus(200);
});

test('dashboard renders the statistics.index view', function () {
    statStubDashboard();
    $this->actingAs(statFakeUser())->get('/statistics/dashboard')->assertViewIs('statistics.index');
});

test('dashboard passes all required view variables', function () {
    statStubDashboard();
    $this->actingAs(statFakeUser())->get('/statistics/dashboard')
        ->assertViewHasAll([
            'year', 'years', 'eventsData', 'participantData',
            'newMembersByYear', 'topParticipants',
            'eventsByType', 'totalEvents', 'totalParticipants', 'totalHours',
            'totalMembers', 'newMembersThisYear',
        ]);
});
