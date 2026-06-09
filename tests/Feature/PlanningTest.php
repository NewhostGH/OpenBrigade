<?php

use App\Http\Controllers\PlanningController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function planningStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns false so
 * Blade templates render without touching the database.
 */
function planningFakeUser(array $attrs = []): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill(array_merge([
        'P_ID'      => 1,
        'P_NOM'     => 'Test',
        'P_PRENOM'  => 'User',
        'P_SECTION' => 1,
        'P_ACTIF'   => 1,
        'P_MDP'     => bcrypt('secret'),
    ], $attrs));
    $user->shouldReceive('hasPermission')->andReturn(false);

    return $user;
}

/**
 * Bind PlanningController so index() returns the real view rendered with stub
 * data, keeping the assertion at the HTTP/view level without touching the DB.
 */
function planningStubIndex(User $user): void
{
    $now = now();
    app()->bind(PlanningController::class, function () use ($now) {
        $ctrl = Mockery::mock(PlanningController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('planning.index', [
                'weeks'     => [],
                'year'      => $now->year,
                'month'     => $now->month,
                'first'     => $now->copy()->startOfMonth(),
                'prevYear'  => $now->year,
                'prevMonth' => $now->month - 1 ?: 12,
                'nextYear'  => $now->year,
                'nextMonth' => $now->month + 1 > 12 ? 1 : $now->month + 1,
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    planningStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /planning to login', function () {
    $this->get('/planning')->assertRedirect('/login');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy calendar.php redirects to planning.index', function () {
    $this->actingAs(planningFakeUser())
        ->get('/legacy/calendar.php')
        ->assertRedirect(route('planning.index'));
});

test('legacy myagenda.php redirects to planning.index', function () {
    $this->actingAs(planningFakeUser())
        ->get('/legacy/myagenda.php')
        ->assertRedirect(route('planning.index'));
});

// ── Planning index (stubbed controller) ──────────────────────────────────────

test('authenticated users can access the planning', function () {
    $user = planningFakeUser();
    planningStubIndex($user);

    $this->actingAs($user)->get('/planning')->assertStatus(200);
});

test('planning index renders the planning.index view', function () {
    $user = planningFakeUser();
    planningStubIndex($user);

    $this->actingAs($user)->get('/planning')->assertViewIs('planning.index');
});

test('planning index passes all required view variables', function () {
    $user = planningFakeUser();
    planningStubIndex($user);

    $this->actingAs($user)->get('/planning')
        ->assertViewHasAll(['weeks', 'year', 'month', 'first', 'prevYear', 'prevMonth', 'nextYear', 'nextMonth']);
});
