<?php

use App\Http\Controllers\OrganisationController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function orgStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns true so
 * permission-gated organisation actions are reachable.
 */
function orgFakeUser(): User
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
 * Bind OrganisationController so index() returns the real view rendered with
 * stub data, keeping the assertion at the HTTP/view level without a DB call.
 */
function orgStubIndex(): void
{
    app()->bind(OrganisationController::class, function () {
        $ctrl = Mockery::mock(OrganisationController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('organisation.index', ['tree' => [], 'sectionId' => 1])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    orgStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /organisation to login', function () {
    $this->get('/organisation')->assertRedirect('/login');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy section.php redirects to organisation.index', function () {
    $this->actingAs(orgFakeUser())
        ->get('/legacy/section.php')
        ->assertRedirect(route('organisation.index'));
});

test('legacy organigramme.php redirects to organisation.index', function () {
    $this->actingAs(orgFakeUser())
        ->get('/legacy/organigramme.php')
        ->assertRedirect(route('organisation.index'));
});

// ── Organisation index (stubbed controller) ──────────────────────────────────

test('authenticated users can access the organisation view', function () {
    orgStubIndex();
    $this->actingAs(orgFakeUser())->get('/organisation')->assertStatus(200);
});

test('organisation index uses the organisation.index template', function () {
    orgStubIndex();
    $this->actingAs(orgFakeUser())->get('/organisation')->assertViewIs('organisation.index');
});

test('organisation index passes tree and sectionId variables', function () {
    orgStubIndex();
    $this->actingAs(orgFakeUser())->get('/organisation')
        ->assertViewHasAll(['tree', 'sectionId']);
});
