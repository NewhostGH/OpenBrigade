<?php

use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Mockery\MockInterface;

function maintActStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

function maintActUser(bool $can): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Test', 'P_PRENOM' => 'Admin',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('hasPermission')->andReturn($can);

    return $user;
}

beforeEach(function () {
    maintActStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('maintenance actions are forbidden without permission 14', function (string $route) {
    $this->actingAs(maintActUser(can: false))
        ->post(route($route))
        ->assertForbidden();
})->with([
    'admin.maintenance.clear-caches',
    'admin.maintenance.optimize-db',
    'admin.maintenance.prune-logs',
]);

test('clearing the caches redirects back with a success flash', function () {
    $this->actingAs(maintActUser(can: true))
        ->post(route('admin.maintenance.clear-caches'))
        ->assertRedirect(route('admin.maintenance'))
        ->assertSessionHas('success');
});
