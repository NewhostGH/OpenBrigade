<?php

use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\NavigationService;

// Disable CSRF verification for all tests in this file — we test the form
// logic, not token handling (that's covered by the middleware unit tests).
beforeEach(function () {
    // Laravel 12 uses ValidateCsrfToken (not the legacy VerifyCsrfToken class).
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
});

// ── Helpers ─────────────────────────────────────────────────────────────────

function authFakeUser(array $attrs = []): User
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
    $user->shouldReceive('hasPermission')->andReturn(false);
    return $user;
}

function bindStubAuthNavigation(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

// ── GET /login ───────────────────────────────────────────────────────────────

test('login page is accessible to guests', function () {
    $this->get('/login')->assertStatus(200);
});

test('login page shows the sign-in form', function () {
    $this->get('/login')
        ->assertSee('Se connecter')
        ->assertSee('Identifiant ou adresse e-mail')
        ->assertSee('Mot de passe');
});

test('authenticated users are redirected from /login to dashboard', function () {
    bindStubAuthNavigation();
    $user = authFakeUser();

    $this->actingAs($user)->get('/login')
        ->assertRedirect(route('dashboard'));
});

// ── POST /login — validation ─────────────────────────────────────────────────

test('login fails when login field is missing', function () {
    $this->post('/login', ['password' => 'secret'])
        ->assertSessionHasErrors('login');
});

test('login fails when password field is missing', function () {
    $this->post('/login', ['login' => 'testuser'])
        ->assertSessionHasErrors('password');
});

test('login fails when both fields are missing', function () {
    $this->post('/login', [])
        ->assertSessionHasErrors(['login', 'password']);
});

// ── POST /login — authentication ─────────────────────────────────────────────

test('login fails with wrong credentials and shows error', function () {
    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('attemptLogin')
        ->with('wronguser', 'wrongpass', false)
        ->andReturn(false);
    app()->instance(AuthService::class, $authService);

    $this->post('/login', ['login' => 'wronguser', 'password' => 'wrongpass'])
        ->assertSessionHasErrors('login')
        ->assertRedirect();
});

test('login succeeds with correct credentials and redirects to dashboard', function () {
    $user        = authFakeUser();
    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('attemptLogin')
        ->with('testuser', 'secret', false)
        ->andReturnUsing(function () use ($user) {
            // Simulate the service logging the user in
            \Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
            return true;
        });
    app()->instance(AuthService::class, $authService);

    $this->post('/login', ['login' => 'testuser', 'password' => 'secret'])
        ->assertRedirect(route('dashboard'));
});

test('login input is repopulated after failed attempt', function () {
    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('attemptLogin')->andReturn(false);
    app()->instance(AuthService::class, $authService);

    $this->post('/login', ['login' => 'someone', 'password' => 'wrong'])
        ->assertRedirect();

    $this->get('/login')->assertSee('someone');
});

// ── POST /logout ──────────────────────────────────────────────────────────────

test('logout redirects unauthenticated users to login', function () {
    $this->post('/logout')->assertRedirect('/login');
});

test('logout signs out the user and redirects to login', function () {
    $user        = authFakeUser();
    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('logout')->once();
    app()->instance(AuthService::class, $authService);

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('login'));
});

test('logout flash message confirms disconnection', function () {
    $user        = authFakeUser();
    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('logout');
    app()->instance(AuthService::class, $authService);

    $this->actingAs($user)
        ->post('/logout')
        ->assertSessionHas('success');
});

// ── Post-login redirect normalization ────────────────────────────────────────

test('login normalizes legacy index_d.php intended URL to dashboard', function () {
    $user        = authFakeUser();
    $authService = Mockery::mock(AuthService::class);
    $authService->shouldReceive('attemptLogin')->andReturnUsing(function () use ($user) {
        \Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
        return true;
    });
    app()->instance(AuthService::class, $authService);

    // Simulate the auth middleware having stored a legacy intended URL
    session(['url.intended' => '/legacy/index_d.php']);

    $this->post('/login', ['login' => 'u', 'password' => 'p'])
        ->assertRedirect(route('dashboard'));
});
