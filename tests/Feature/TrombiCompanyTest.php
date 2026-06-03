<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PersonnelController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

function tcFakeUser(): User
{
    /** @var User&\Mockery\MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Test', 'P_PRENOM' => 'User',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('hasPermission')->andReturn(true);
    return $user;
}

function tcStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

beforeEach(function () {
    tcStubNav();
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
});

// ── Trombinoscope ─────────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /trombinoscope to login', function () {
    $this->get('/trombinoscope')->assertRedirect('/login');
});

test('legacy trombinoscope.php redirects to personnel.trombinoscope', function () {
    $this->actingAs(tcFakeUser())
        ->get('/legacy/trombinoscope.php')
        ->assertRedirect(route('personnel.trombinoscope'));
});

test('authenticated users can access the trombinoscope', function () {
    app()->bind(PersonnelController::class, function () {
        $ctrl = Mockery::mock(PersonnelController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 48);
        $page->setPath('/trombinoscope');
        $ctrl->shouldReceive('trombinoscope')->andReturn(
            view('personnel.trombinoscope', [
                'items' => $page, 'search' => '', 'sectionId' => 0,
                'sections' => Collection::make([]),
            ])
        );
        return $ctrl;
    });
    $this->actingAs(tcFakeUser())->get('/trombinoscope')->assertStatus(200);
});

// ── Company / Clients ────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /clients to login', function () {
    $this->get('/clients')->assertRedirect('/login');
});

test('legacy company.php redirects to company.index', function () {
    $this->actingAs(tcFakeUser())
        ->get('/legacy/company.php')
        ->assertRedirect(route('company.index'));
});

test('authenticated users can access the company list', function () {
    app()->bind(CompanyController::class, function () {
        $ctrl = Mockery::mock(CompanyController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 50);
        $page->setPath('/clients');
        $ctrl->shouldReceive('index')->andReturn(
            view('company.index', [
                'items' => $page, 'columns' => [], 'search' => '', 'type' => 'ALL',
                'types' => Collection::make([]),
            ])
        );
        return $ctrl;
    });
    $this->actingAs(tcFakeUser())->get('/clients')->assertStatus(200);
});
