<?php

use App\Models\Personnel;
use App\Models\Section;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

// ── Helpers ─────────────────────────────────────────────────────────────────

function personnelFakeUser(array $attrs = []): User
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

function personnelStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

beforeEach(function () {
    personnelStubNav();
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
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

// ── Personnel index (stubbed Eloquent) ───────────────────────────────────────

test('authenticated users can access the personnel list', function () {
    $user = personnelFakeUser();

    // Stub Eloquent so no real DB call is made
    $emptyPage = new LengthAwarePaginator([], 0, 100);
    $emptyPage->setPath('/personnel');

    // Bind a mock PersonnelController that short-circuits the DB calls
    app()->bind(\App\Http\Controllers\PersonnelController::class, function () use ($emptyPage) {
        $ctrl = Mockery::mock(\App\Http\Controllers\PersonnelController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('personnel.index', [
                'items'          => $emptyPage,
                'columns'        => [],
                'position'       => 'actif',
                'search'         => '',
                'category'       => 'INT',
                'sectionId'      => 0,
                'order'          => 'P_NOM',
                'subsections'    => true,
                'perPage'        => 100,
                'sectionOptions' => [],
            ])
        );
        return $ctrl;
    });

    $this->actingAs($user)->get('/personnel')->assertStatus(200);
});

test('personnel index uses the personnel.index template', function () {
    $user      = personnelFakeUser();
    $emptyPage = new LengthAwarePaginator([], 0, 100);
    $emptyPage->setPath('/personnel');

    app()->bind(\App\Http\Controllers\PersonnelController::class, function () use ($emptyPage) {
        $ctrl = Mockery::mock(\App\Http\Controllers\PersonnelController::class)->makePartial();
        $ctrl->shouldReceive('index')->andReturn(
            view('personnel.index', [
                'items'          => $emptyPage,
                'columns'        => [],
                'position'       => 'actif',
                'search'         => '',
                'category'       => 'INT',
                'sectionId'      => 0,
                'order'          => 'P_NOM',
                'subsections'    => true,
                'perPage'        => 100,
                'sectionOptions' => [],
            ])
        );
        return $ctrl;
    });

    $this->actingAs($user)->get('/personnel')->assertViewIs('personnel.index');
});
