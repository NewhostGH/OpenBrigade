<?php

use App\Http\Controllers\GardeController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function gardeStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns true so
 * permission-gated roster actions are reachable.
 */
function gardeFakeUser(array $attrs = []): User
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
    $user->shouldReceive('hasPermission')->andReturn(true);

    return $user;
}

/**
 * Bind GardeController so index() returns the real view rendered with stub data,
 * keeping the assertion at the HTTP/view level without touching the database.
 */
function gardeStubIndex(User $user): void
{
    $now = now();
    app()->bind(GardeController::class, function () use ($now) {
        $ctrl = Mockery::mock(GardeController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('garde.index', [
                'days'       => [],
                'monday'     => $now->copy()->startOfWeek(),
                'sunday'     => $now->copy()->endOfWeek(),
                'prevWeek'   => -1,
                'nextWeek'   => 1,
                'weekOffset' => 0,
                'roles'      => [],
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    gardeStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /garde to login', function () {
    $this->get('/garde')->assertRedirect('/login');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy tableau_garde.php redirects to garde.index', function () {
    $this->actingAs(gardeFakeUser())
        ->get('/legacy/tableau_garde.php')
        ->assertRedirect(route('garde.index'));
});

test('legacy feuille_garde.php redirects to garde.index', function () {
    $this->actingAs(gardeFakeUser())
        ->get('/legacy/feuille_garde.php')
        ->assertRedirect(route('garde.index'));
});

// ── Garde index (stubbed controller) ─────────────────────────────────────────

test('authenticated users can access the garde roster', function () {
    $user = gardeFakeUser();
    gardeStubIndex($user);

    $this->actingAs($user)->get('/garde')->assertStatus(200);
});

test('garde index renders the garde.index view', function () {
    $user = gardeFakeUser();
    gardeStubIndex($user);

    $this->actingAs($user)->get('/garde')->assertViewIs('garde.index');
});

test('garde index passes all required view variables', function () {
    $user = gardeFakeUser();
    gardeStubIndex($user);

    $this->actingAs($user)->get('/garde')
        ->assertViewHasAll(['days', 'monday', 'sunday', 'prevWeek', 'nextWeek', 'weekOffset', 'roles']);
});

test('garde index passes week=0 by default', function () {
    $user = gardeFakeUser();
    gardeStubIndex($user);

    $this->actingAs($user)->get('/garde')
        ->assertViewHas('weekOffset', 0);
});
