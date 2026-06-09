<?php

use App\Http\Controllers\ContextController;
use App\Http\Controllers\HabilitationController;
use App\Http\Controllers\MesDroitsController;
use App\Models\User;
use App\Services\NavigationService;
use App\Services\PermissionResolver;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService and PermissionResolver so the navbar composer (which
 * reads nav groups plus the section/role switchers) never hits the DB.
 */
function habStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);

    // The navbar composer also pulls the section/role switchers from the resolver.
    $resolver = Mockery::mock(PermissionResolver::class)->makePartial();
    $resolver->shouldReceive('activeSectionId')->andReturn(null);
    $resolver->shouldReceive('activeRoleId')->andReturn(null);
    $resolver->shouldReceive('userSections')->andReturn(collect());
    $resolver->shouldReceive('userRoles')->andReturn(collect());
    app()->instance(PermissionResolver::class, $resolver);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns $can for
 * every feature id, so the same helper drives both access and denial tests.
 */
function habFakeUser(bool $can = true): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Test', 'P_PRENOM' => 'Admin',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'GP_ID' => 2,
        'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('hasPermission')->andReturn($can);

    return $user;
}

/**
 * Bind a controller so $method returns the real view rendered with stub data,
 * keeping the assertion at the HTTP/view level without touching the database.
 */
function habStubView(string $controller, string $method, string $view, array $data): void
{
    app()->bind($controller, function () use ($controller, $method, $view, $data) {
        $ctrl = Mockery::mock($controller)->makePartial();
        $ctrl->shouldReceive($method)->andReturn(view($view, $data));

        return $ctrl;
    });
}

/**
 * The full set of view variables the admin habilitations index expects, with
 * the active $tab swapped in. Empty collections keep every partial renderable.
 */
function habAdminStub(string $tab): array
{
    return [
        'tab' => $tab,
        'featuresByCategory' => collect([]),
        'sections' => collect([]),
        'sectionId' => 0,
        'selected' => null,
        'groups' => collect([]),
        'roles' => collect([]),
        'grants' => collect([]),
        'ownDenied' => [],
        'parentDenied' => [],
        'sectionDenied' => [],
    ];
}

beforeEach(function () {
    habStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Authentication ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from habilitation pages to login', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with(['/admin/habilitations', '/mes-droits']);

// ── Permission gating ────────────────────────────────────────────────────────

test('habilitations admin requires permission 9', function () {
    $this->actingAs(habFakeUser(can: false))->get('/admin/habilitations')->assertForbidden();
});

test('mes-droits is reachable by any authenticated user', function () {
    habStubView(MesDroitsController::class, 'index', 'mes-droits.index', [
        'sections' => collect([]), 'sectionId' => 0, 'roles' => collect([]),
        'roleId' => null, 'featuresByCategory' => collect([]), 'origins' => [], 'denied' => [],
    ]);

    $this->actingAs(habFakeUser(can: false))->get('/mes-droits')->assertOk();
});

// ── Admin habilitations tabs ─────────────────────────────────────────────────

test('each admin habilitations tab renders the index view', function (string $tab) {
    habStubView(HabilitationController::class, 'index', 'admin.habilitations.index', habAdminStub($tab));

    $this->actingAs(habFakeUser())->get('/admin/habilitations?tab='.$tab)
        ->assertOk()
        ->assertViewIs('admin.habilitations.index');
})->with(['ceiling', 'groups', 'roles']);

// ── Mes droits ───────────────────────────────────────────────────────────────

test('mes-droits renders the effective-rights view', function () {
    habStubView(MesDroitsController::class, 'index', 'mes-droits.index', [
        'sections' => collect([]), 'sectionId' => 0, 'roles' => collect([]),
        'roleId' => null, 'featuresByCategory' => collect([]), 'origins' => [], 'denied' => [],
    ]);

    $this->actingAs(habFakeUser())->get('/mes-droits')
        ->assertOk()
        ->assertViewIs('mes-droits.index');
});

// ── Context switch routes ────────────────────────────────────────────────────

test('context switch routes redirect back', function (string $path) {
    app()->bind(ContextController::class, function () {
        $ctrl = Mockery::mock(ContextController::class)->makePartial();
        $ctrl->shouldReceive('section')->andReturn(redirect('/dashboard'));
        $ctrl->shouldReceive('role')->andReturn(redirect('/dashboard'));

        return $ctrl;
    });

    $this->actingAs(habFakeUser())->get($path)->assertRedirect('/dashboard');
})->with(['/contexte/section?s=1', '/contexte/role?r=all']);
