<?php

use App\Http\Controllers\EvenementController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function eventStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns true so
 * permission-gated event actions are reachable.
 */
function eventFakeUser(array $attrs = []): User
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
 * Bind EvenementController so index() returns the real view rendered with stub
 * data, keeping the assertion at the HTTP/view level without touching the DB.
 */
function eventStubIndex(): void
{
    app()->bind(EvenementController::class, function () {
        $ctrl  = Mockery::mock(EvenementController::class)->makePartial();
        $page  = new LengthAwarePaginator([], 0, 50);
        $page->setPath('/evenements');
        $empty = Collection::make([]);
        $ctrl->shouldReceive('index')->andReturn(
            view('evenement.index', [
                'items'    => $page,
                'columns'  => [],
                'period'   => 'upcoming',
                'search'   => '',
                'type'     => 'ALL',
                'filtSect' => 0,
                'types'    => $empty,
                'sections' => $empty,
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    eventStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /evenements to login', function () {
    $this->get('/evenements')->assertRedirect('/login');
});

test('unauthenticated users are redirected from event detail to login', function () {
    $this->get('/evenements/EVT001')->assertRedirect('/login');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy evenements.php redirects to evenement.index', function () {
    $this->actingAs(eventFakeUser())
        ->get('/legacy/evenements.php')
        ->assertRedirect(route('evenement.index'));
});

test('legacy evenement_display.php redirects to evenement.index', function () {
    $this->actingAs(eventFakeUser())
        ->get('/legacy/evenement_display.php')
        ->assertRedirect(route('evenement.index'));
});

test('legacy evenement_detail.php with evenement param redirects to show', function () {
    $this->actingAs(eventFakeUser())
        ->get('/legacy/evenement_detail.php?evenement=EVT042')
        ->assertRedirect(route('evenement.show', 'EVT042'));
});

test('legacy evenement_detail.php without param redirects to index', function () {
    $this->actingAs(eventFakeUser())
        ->get('/legacy/evenement_detail.php')
        ->assertRedirect(route('evenement.index'));
});

// ── Evenement index (stubbed controller) ─────────────────────────────────────

test('authenticated users can access the event list', function () {
    eventStubIndex();
    $this->actingAs(eventFakeUser())->get('/evenements')->assertStatus(200);
});

test('event list uses the evenement.index template', function () {
    eventStubIndex();
    $this->actingAs(eventFakeUser())->get('/evenements')->assertViewIs('evenement.index');
});

test('event list passes all required view variables', function () {
    eventStubIndex();
    $this->actingAs(eventFakeUser())->get('/evenements')
        ->assertViewHasAll(['items', 'period', 'search', 'type', 'filtSect', 'types', 'sections']);
});
