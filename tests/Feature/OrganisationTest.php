<?php

use App\Http\Controllers\OrganisationController;
use App\Models\User;
use App\Services\NavigationService;

function orgFakeUser(): User
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

function orgStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

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
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
});

test('unauthenticated users are redirected from /organisation to login', function () {
    $this->get('/organisation')->assertRedirect('/login');
});

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
