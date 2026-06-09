<?php

use App\Http\Controllers\PersonnelController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function personnelStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns true so
 * permission-gated personnel actions are reachable.
 */
function personnelFakeUser(array $attrs = []): User
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
 * Bind PersonnelController so index() returns the real view rendered with stub
 * data, short-circuiting the Eloquent calls so no real DB query is made.
 */
function personnelStubIndex(): void
{
    app()->bind(PersonnelController::class, function () {
        $ctrl = Mockery::mock(PersonnelController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 100);
        $page->setPath('/personnel');
        $ctrl->shouldReceive('index')->andReturn(
            view('personnel.index', [
                'items' => $page,
                'columns' => [],
                'position' => 'actif',
                'search' => '',
                'category' => 'INT',
                'sectionId' => 0,
                'order' => 'P_NOM',
                'subsections' => true,
                'perPage' => 100,
                'sectionOptions' => [],
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    personnelStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /personnel to login', function () {
    $this->get('/personnel')->assertRedirect('/login');
});

test('unauthenticated users are redirected from personnel show to login', function () {
    $this->get('/personnel/1')->assertRedirect('/login');
});

test('unauthenticated users are redirected from personnel edit to login', function () {
    $this->get('/personnel/1/edit')->assertRedirect('/login');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy personnel.php redirects to personnel.index', function () {
    $this->actingAs(personnelFakeUser())
        ->get('/legacy/personnel.php')
        ->assertRedirect(route('personnel.index'));
});

test('legacy upd_personnel.php with pompier param redirects to personnel.show', function () {
    $this->actingAs(personnelFakeUser())
        ->get('/legacy/upd_personnel.php?pompier=42')
        ->assertRedirect(route('personnel.show', 42));
});

test('legacy upd_personnel.php with id param redirects to personnel.show', function () {
    $this->actingAs(personnelFakeUser())
        ->get('/legacy/upd_personnel.php?id=99')
        ->assertRedirect(route('personnel.show', 99));
});

test('legacy upd_personnel.php without params redirects to personnel.index', function () {
    $this->actingAs(personnelFakeUser())
        ->get('/legacy/upd_personnel.php')
        ->assertRedirect(route('personnel.index'));
});

// ── Personnel index (stubbed controller) ─────────────────────────────────────

test('authenticated users can access the personnel list', function () {
    personnelStubIndex();
    $this->actingAs(personnelFakeUser())->get('/personnel')->assertStatus(200);
});

test('personnel index uses the personnel.index template', function () {
    personnelStubIndex();
    $this->actingAs(personnelFakeUser())->get('/personnel')->assertViewIs('personnel.index');
});

// ── Section-scoped role assignment routes (ob_user_assignment) ───────────────

test('role assignment routes are registered', function () {
    expect(route('personnel.role.store', 1))->toContain('/personnel/1/roles')
        ->and(route('personnel.role.destroy', [1, 5]))->toContain('/personnel/1/roles/5');
});

test('unauthenticated users cannot assign or remove roles', function () {
    $this->post('/personnel/1/roles', ['section_id' => 1, 'group_id' => 100])->assertRedirect('/login');
    $this->delete('/personnel/1/roles/5')->assertRedirect('/login');
});
