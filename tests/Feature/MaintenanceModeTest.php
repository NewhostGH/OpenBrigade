<?php

use App\Models\User;
use App\Services\GeneralSettingService;
use App\Services\NavigationService;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/** Stub NavigationService so the layout's view composer never hits the DB. */
function maintStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/** Fake user (no DB); $admin controls isSuperAdmin/hasPermission. */
function maintFakeUser(bool $admin): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Test', 'P_PRENOM' => 'User',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('isSuperAdmin')->andReturn($admin);
    $user->shouldReceive('hasPermission')->andReturn($admin);

    return $user;
}

/** Rebind the general settings with maintenance mode on and a custom text. */
function maintEnabled(string $text = 'Retour prévu à 14h.'): void
{
    $general = Mockery::mock(GeneralSettingService::class)->makePartial();
    $general->shouldReceive('get')->andReturnUsing(
        fn (string $name) => match ($name) {
            'maintenance_mode' => '1',
            'maintenance_text' => $text,
            default => '0',
        }
    );
    app()->instance(GeneralSettingService::class, $general);
}

beforeEach(function () {
    maintStubNav();
});

// ── Behaviour ────────────────────────────────────────────────────────────────

test('maintenance mode blocks non-admins with a 503 carrying the text', function () {
    maintEnabled('Retour prévu à 14h.');

    $this->actingAs(maintFakeUser(admin: false))
        ->get('/dashboard')
        ->assertStatus(503)
        ->assertSee('Retour prévu à 14h.');
});

test('legacy br tags in the maintenance text are not rendered as markup', function () {
    maintEnabled('Serveur inaccessible.<br>Maintenance en cours.');

    $response = $this->actingAs(maintFakeUser(admin: false))->get('/dashboard');

    $response->assertStatus(503)
        ->assertSee('Serveur inaccessible.')
        ->assertDontSee('<br>', escape: false);
});

test('administrators bypass maintenance mode', function () {
    maintEnabled();

    // Dashboard needs a DB; any admin page proves the pass-through. Use the
    // legacy bridge redirect route which needs none.
    $this->actingAs(maintFakeUser(admin: true))
        ->get('/legacy/configuration.php')
        ->assertRedirect(route('admin.settings'));
});

test('non-admins can still log out during maintenance', function () {
    maintEnabled();

    $this->actingAs(maintFakeUser(admin: false))
        ->post(route('logout'))
        ->assertRedirect('/login');
});

test('guests see the login page with the maintenance notice', function () {
    maintEnabled('Retour prévu à 14h.');

    $this->get('/login')
        ->assertOk()
        ->assertSee('Retour prévu à 14h.');
});

test('everything passes through when maintenance mode is off', function () {
    // TestCase's default GeneralSettingService mock has the mode disabled.
    $this->actingAs(maintFakeUser(admin: false))
        ->get('/legacy/configuration.php')
        ->assertRedirect(route('admin.settings'));
});
