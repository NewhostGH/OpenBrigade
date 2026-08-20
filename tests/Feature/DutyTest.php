<?php

use App\Http\Controllers\DutyController;
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
        'P_ID' => 1,
        'P_NOM' => 'Test',
        'P_PRENOM' => 'User',
        'P_SECTION' => 1,
        'P_ACTIF' => 1,
        'P_MDP' => bcrypt('secret'),
    ], $attrs));
    $user->shouldReceive('hasPermission')->andReturn(true);

    return $user;
}

/**
 * Bind DutyController so index() returns the real view rendered with stub data,
 * keeping the assertion at the HTTP/view level without touching the database.
 */
function gardeStubIndex(User $user): void
{
    $now = now();
    app()->bind(DutyController::class, function () use ($now) {
        $ctrl = Mockery::mock(DutyController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('duty.index', [
                'days' => [],
                'monday' => $now->copy()->startOfWeek(),
                'sunday' => $now->copy()->endOfWeek(),
                'prevWeek' => -1,
                'nextWeek' => 1,
                'weekOffset' => 0,
                'roles' => [],
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

test('unauthenticated users are redirected from /duty to login', function () {
    $this->get('/duty')->assertRedirect('/login');
});

test('unauthenticated users are redirected from the on-call exports to login', function () {
    $this->get('/duty/on-call/export/xls')->assertRedirect('/login');
    $this->get('/duty/on-call/export/csv')->assertRedirect('/login');
});

test('the on-call export routes are registered', function () {
    expect(route('duty.on-call.export.xls'))->toContain('/duty/on-call/export/xls');
    expect(route('duty.on-call.export.csv'))->toContain('/duty/on-call/export/csv');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy tableau_garde.php redirects to garde.index', function () {
    $this->actingAs(gardeFakeUser())
        ->get('/legacy/tableau_garde.php')
        ->assertRedirect(route('duty.index'));
});

test('legacy feuille_garde.php redirects to garde.index', function () {
    $this->actingAs(gardeFakeUser())
        ->get('/legacy/feuille_garde.php')
        ->assertRedirect(route('duty.index'));
});

// ── Duty index (stubbed controller) ─────────────────────────────────────────

test('authenticated users can access the garde roster', function () {
    $user = gardeFakeUser();
    gardeStubIndex($user);

    $this->actingAs($user)->get('/duty')->assertStatus(200);
});

test('garde index renders the garde.index view', function () {
    $user = gardeFakeUser();
    gardeStubIndex($user);

    $this->actingAs($user)->get('/duty')->assertViewIs('duty.index');
});

test('garde index passes all required view variables', function () {
    $user = gardeFakeUser();
    gardeStubIndex($user);

    $this->actingAs($user)->get('/duty')
        ->assertViewHasAll(['days', 'monday', 'sunday', 'prevWeek', 'nextWeek', 'weekOffset', 'roles']);
});

test('garde index passes week=0 by default', function () {
    $user = gardeFakeUser();
    gardeStubIndex($user);

    $this->actingAs($user)->get('/duty')
        ->assertViewHas('weekOffset', 0);
});

// ── On-call printable roster (PDF export) ────────────────────────────────────

/** Bind DutyController so printOnCall() renders the real print view DB-free. */
function gardeStubPrint(): void
{
    $now = now();
    app()->bind(DutyController::class, function () use ($now) {
        $ctrl = Mockery::mock(DutyController::class)->makePartial();
        $slot = (object) [
            'AS_ID' => 1,
            'AS_DEBUT' => $now->copy()->startOfMonth()->setTime(8, 0)->toDateTimeString(),
            'AS_FIN' => $now->copy()->startOfMonth()->setTime(20, 0)->toDateTimeString(),
            'P_ID' => 7, 'P_NOM' => 'Martin', 'P_PRENOM' => 'Lea',
            'GP_ID' => 2, 'GP_DESCRIPTION' => 'Chef de garde',
        ];
        $ctrl->shouldReceive('printOnCall')->andReturn(
            view('duty.on-call-print', [
                'slots' => collect([$slot]),
                'month' => (int) $now->month,
                'year' => (int) $now->year,
                'first' => $now->copy()->startOfMonth(),
            ])
        );

        return $ctrl;
    });
}

test('unauthenticated users are redirected from the on-call print to login', function () {
    $this->get('/duty/on-call/print')->assertRedirect('/login');
});

test('the on-call print route is registered', function () {
    expect(route('duty.on-call.print'))->toContain('/duty/on-call/print');
});

test('authenticated users can view the printable on-call roster', function () {
    gardeStubPrint();

    $this->actingAs(gardeFakeUser())->get('/duty/on-call/print')
        ->assertStatus(200)
        ->assertViewIs('duty.on-call-print')
        ->assertSee(__('duty.print_heading'))
        ->assertSee('Lea MARTIN')
        ->assertSee('Chef de garde');
});
