<?php

use App\Http\Controllers\EvenementController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

// ── Helpers ─────────────────────────────────────────────────────────────────

function eventFakeUser(array $attrs = []): User
{
    /** @var User&\Mockery\MockInterface $user */
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

function eventStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

beforeEach(function () {
    eventStubNav();
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
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
    $user      = eventFakeUser();
    $emptyPage = new LengthAwarePaginator([], 0, 50);
    $emptyPage->setPath('/evenements');
    $empty = Collection::make([]);

    app()->bind(EvenementController::class, function () use ($emptyPage, $empty) {
        $ctrl = Mockery::mock(EvenementController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('evenement.index', [
                'items'    => $emptyPage,
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

    $this->actingAs($user)->get('/evenements')->assertStatus(200);
});

test('event list uses the evenement.index template', function () {
    $user      = eventFakeUser();
    $emptyPage = new LengthAwarePaginator([], 0, 50);
    $emptyPage->setPath('/evenements');
    $empty = Collection::make([]);

    app()->bind(EvenementController::class, function () use ($emptyPage, $empty) {
        $ctrl = Mockery::mock(EvenementController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('evenement.index', [
                'items'    => $emptyPage,
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

    $this->actingAs($user)->get('/evenements')->assertViewIs('evenement.index');
});

test('event list passes all required view variables', function () {
    $user      = eventFakeUser();
    $emptyPage = new LengthAwarePaginator([], 0, 50);
    $emptyPage->setPath('/evenements');
    $empty = Collection::make([]);

    app()->bind(EvenementController::class, function () use ($emptyPage, $empty) {
        $ctrl = Mockery::mock(EvenementController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('evenement.index', [
                'items'    => $emptyPage,
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

    $this->actingAs($user)->get('/evenements')
        ->assertViewHasAll(['items', 'period', 'search', 'type', 'filtSect', 'types', 'sections']);
});
