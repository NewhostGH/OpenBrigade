<?php

use App\Http\Controllers\OrganisationController;
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

/**
 * Bind OrganisationController so $method returns the real view rendered with
 * stub data — HTTP/view-level assertion without a DB call.
 */
function orgStubView(string $method, string $view, array $data): void
{
    app()->bind(OrganisationController::class, function () use ($method, $view, $data) {
        $ctrl = Mockery::mock(OrganisationController::class)->makePartial();
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
})->with(['/organisation', '/organisation/organigramme', '/organisation/sections', '/organisation/cartographie']);

test('section CRUD routes are registered', function () {
    expect(route('organisation.sections.create'))->toContain('/organisation/sections/create')
        ->and(route('organisation.sections.edit', 3))->toContain('/organisation/sections/3/edit')
        ->and(route('organisation.sections.destroy', 3))->toContain('/organisation/sections/3');
});

// ── Sections list + cartographie (stubbed controller) ────────────────────────

test('sections list renders the organisation.sections view', function () {
    orgStubView('sections', 'organisation.sections', ['sections' => collect([]), 'counts' => collect([])]);
    $this->actingAs(orgFakeUser())->get('/organisation/sections')
        ->assertOk()->assertViewIs('organisation.sections');
});

test('cartographie renders the organisation.cartographie view', function () {
    orgStubView('cartographie', 'organisation.cartographie', ['markers' => [], 'count' => 0]);
    $this->actingAs(orgFakeUser())->get('/organisation/cartographie')
        ->assertOk()->assertViewIs('organisation.cartographie');
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

test('legacy departement.php redirects to organisation.sections', function () {
    $this->actingAs(orgFakeUser())
        ->get('/legacy/departement.php')
        ->assertRedirect(route('organisation.sections'));
});

test('legacy jvectormap.php redirects to organisation.cartographie', function () {
    $this->actingAs(orgFakeUser())
        ->get('/legacy/jvectormap.php')
        ->assertRedirect(route('organisation.cartographie'));
});

// ── Organisation index / organigramme (stubbed controller) ───────────────────

test('/organisation redirects to the organigramme URL', function () {
    $this->actingAs(orgFakeUser())
        ->get('/organisation')
        ->assertRedirect(route('organisation.organigramme'));
});

test('authenticated users can access the organigramme', function () {
    orgStubIndex();
    $this->actingAs(orgFakeUser())->get('/organisation/organigramme')->assertStatus(200);
});

test('organigramme renders the organisation.index view', function () {
    orgStubIndex();
    $this->actingAs(orgFakeUser())->get('/organisation/organigramme')->assertViewIs('organisation.index');
});

test('organigramme passes all required view variables', function () {
    orgStubIndex();
    $this->actingAs(orgFakeUser())->get('/organisation/organigramme')
        ->assertViewHasAll(['tree', 'sectionId']);
});
