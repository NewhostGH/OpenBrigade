<?php

use App\Http\Controllers\AccountController;
use App\Models\User;
use App\Services\NavigationService;
use App\Services\OrganisationSetupService;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/** Stub NavigationService so the layout's view composer never hits the DB. */
function setupStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/** Fake super-admin (no DB) — always allowed to configure the install. */
function setupFakeAdmin(): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Super', 'P_PRENOM' => 'Admin',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('isSuperAdmin')->andReturn(true);
    $user->shouldReceive('hasPermission')->andReturn(true);

    return $user;
}

/** Rebind the first-run flag to "not configured yet". */
function setupNotCompleted(): void
{
    $setup = Mockery::mock(OrganisationSetupService::class)->makePartial();
    $setup->shouldReceive('isCompleted')->andReturn(false);
    app()->instance(OrganisationSetupService::class, $setup);
}

beforeEach(function () {
    setupStubNav();
});

// ── First-run gate ───────────────────────────────────────────────────────────

test('a fresh install redirects an admin to the setup wizard', function () {
    setupNotCompleted();

    $this->actingAs(setupFakeAdmin())
        ->get('/dashboard')
        ->assertRedirect(route('setup.show'));
});

test('the authentication page stays reachable on a fresh install', function () {
    // Regression: with an expired super-admin password, RequireAuthSetup sends
    // every request to account.auth while RequireSetup sent account.auth to
    // /setup — an infinite redirect loop on a freshly created install.
    setupNotCompleted();

    app()->bind(AccountController::class, function () {
        $ctrl = Mockery::mock(AccountController::class)->makePartial();
        $ctrl->shouldReceive('showAuth')->andReturn(view('auth.login'));

        return $ctrl;
    });

    $this->actingAs(setupFakeAdmin())
        ->get('/account/authentification')
        ->assertOk();
});
