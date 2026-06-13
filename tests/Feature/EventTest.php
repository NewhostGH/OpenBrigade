<?php

use App\Http\Controllers\EventController;
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
 * Bind EventController so index() returns the real view rendered with stub
 * data, keeping the assertion at the HTTP/view level without touching the DB.
 */
function eventStubIndex(): void
{
    app()->bind(EventController::class, function () {
        $ctrl = Mockery::mock(EventController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 50);
        $page->setPath('/events');
        $empty = Collection::make([]);
        $ctrl->shouldReceive('index')->andReturn(
            view('event.index', [
                'items' => $page,
                'columns' => [],
                'period' => 'upcoming',
                'search' => '',
                'type' => 'ALL',
                'filtSect' => 0,
                'types' => $empty,
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

test('unauthenticated users are redirected from /events to login', function () {
    $this->get('/events')->assertRedirect('/login');
});

test('unauthenticated users are redirected from event detail to login', function () {
    $this->get('/events/EVT001')->assertRedirect('/login');
});

// ── Legacy bridge redirects ──────────────────────────────────────────────────

test('legacy evenements.php redirects to event.index', function () {
    $this->actingAs(eventFakeUser())
        ->get('/legacy/evenements.php')
        ->assertRedirect(route('event.index'));
});

test('legacy evenement_display.php redirects to event.index', function () {
    $this->actingAs(eventFakeUser())
        ->get('/legacy/evenement_display.php')
        ->assertRedirect(route('event.index'));
});

test('legacy evenement_detail.php with evenement param redirects to show', function () {
    $this->actingAs(eventFakeUser())
        ->get('/legacy/evenement_detail.php?evenement=EVT042')
        ->assertRedirect(route('event.show', 'EVT042'));
});

test('legacy evenement_detail.php without param redirects to index', function () {
    $this->actingAs(eventFakeUser())
        ->get('/legacy/evenement_detail.php')
        ->assertRedirect(route('event.index'));
});

// ── Event index (stubbed controller) ─────────────────────────────────────

test('authenticated users can access the event list', function () {
    eventStubIndex();
    $this->actingAs(eventFakeUser())->get('/events')->assertStatus(200);
});

test('event list renders the event.index view', function () {
    eventStubIndex();
    $this->actingAs(eventFakeUser())->get('/events')->assertViewIs('event.index');
});

test('event list passes all required view variables', function () {
    eventStubIndex();
    $this->actingAs(eventFakeUser())->get('/events')
        ->assertViewHasAll(['items', 'period', 'search', 'type', 'filtSect', 'types', 'sections']);
});
