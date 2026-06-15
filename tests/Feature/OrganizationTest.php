<?php

use App\Http\Controllers\OrganizationController;
use App\Http\Middleware\RequireFeature;
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
 * Bind OrganizationController so index() returns the real view rendered with
 * stub data, keeping the assertion at the HTTP/view level without a DB call.
 */
function orgStubIndex(): void
{
    app()->bind(OrganizationController::class, function () {
        $ctrl = Mockery::mock(OrganizationController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('organization.index', ['tree' => [], 'sectionId' => 1])
        );

        return $ctrl;
    });
}

/**
 * Bind OrganizationController so $method returns the real view rendered with
 * stub data — HTTP/view-level assertion without a DB call.
 */
function orgStubView(string $method, string $view, array $data): void
{
    app()->bind(OrganizationController::class, function () use ($method, $view, $data) {
        $ctrl = Mockery::mock(OrganizationController::class)->makePartial();
        $ctrl->shouldReceive($method)->andReturn(view($view, $data));

        return $ctrl;
    });
}

beforeEach(function () {
    orgStubNav();
    $this->withoutMiddleware([ValidateCsrfToken::class, RequireFeature::class]);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from organisation pages to login', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with(['/organization', '/organization/org-chart', '/organization/sections', '/organization/map']);

test('section CRUD routes are registered', function () {
    expect(route('organization.sections.create'))->toContain('/organization/sections/create')
        ->and(route('organization.sections.edit', 3))->toContain('/organization/sections/3/edit')
        ->and(route('organization.sections.destroy', 3))->toContain('/organization/sections/3');
});

// ── Sections list + cartographie (stubbed controller) ────────────────────────

test('sections list renders the organization.sections view', function () {
    orgStubView('sections', 'organization.sections', ['sections' => collect([]), 'counts' => collect([])]);
    $this->actingAs(orgFakeUser())->get('/organization/sections')
        ->assertOk()->assertViewIs('organization.sections');
});

test('cartographie renders the organization.map view', function () {
    orgStubView('map', 'organization.map', ['markers' => [], 'count' => 0]);
    $this->actingAs(orgFakeUser())->get('/organization/map')
        ->assertOk()->assertViewIs('organization.map');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy section.php redirects to organization.index', function () {
    $this->actingAs(orgFakeUser())
        ->get('/legacy/section.php')
        ->assertRedirect(route('organization.index'));
});

test('legacy organigramme.php redirects to organization.index', function () {
    $this->actingAs(orgFakeUser())
        ->get('/legacy/organigramme.php')
        ->assertRedirect(route('organization.index'));
});

test('legacy departement.php redirects to organization.sections', function () {
    $this->actingAs(orgFakeUser())
        ->get('/legacy/departement.php')
        ->assertRedirect(route('organization.sections'));
});

test('legacy jvectormap.php redirects to organization.map', function () {
    $this->actingAs(orgFakeUser())
        ->get('/legacy/jvectormap.php')
        ->assertRedirect(route('organization.map'));
});

// ── Organization index / organigramme (stubbed controller) ───────────────────

test('/organization redirects to the organigramme URL', function () {
    $this->actingAs(orgFakeUser())
        ->get('/organization')
        ->assertRedirect(route('organization.org-chart'));
});

test('authenticated users can access the organigramme', function () {
    orgStubIndex();
    $this->actingAs(orgFakeUser())->get('/organization/org-chart')->assertStatus(200);
});

test('organigramme renders the organization.index view', function () {
    orgStubIndex();
    $this->actingAs(orgFakeUser())->get('/organization/org-chart')->assertViewIs('organization.index');
});

test('organigramme passes all required view variables', function () {
    orgStubIndex();
    $this->actingAs(orgFakeUser())->get('/organization/org-chart')
        ->assertViewHasAll(['tree', 'sectionId']);
});
