<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReferenceController;
use App\Models\BackupSetting;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function adminStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake admin User (no DB required). hasPermission() returns
 * $can for every feature id, so the same helper drives both access and
 * permission-denial assertions.
 */
function adminFakeUser(bool $can = true): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Test', 'P_PRENOM' => 'Admin',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'GP_ID' => 2,
        'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('hasPermission')->andReturn($can);

    return $user;
}

/**
 * Bind a controller so $method returns the real view rendered with the supplied
 * stub data — keeping the assertion at the HTTP/view level without touching the
 * database (mirrors DashboardTest).
 */
function adminStubView(string $controller, string $method, string $view, array $data): void
{
    app()->bind($controller, function () use ($controller, $method, $view, $data) {
        $ctrl = Mockery::mock($controller)->makePartial();
        $ctrl->shouldReceive($method)->andReturn(view($view, $data));

        return $ctrl;
    });
}

beforeEach(function () {
    adminStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Authentication ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from admin pages to login', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with([
    '/admin/settings',
    '/admin/monitoring',
    '/admin/references',
    '/admin/permissions',
    '/admin/backup',
    '/admin/maintenance',
    '/admin/plugins',
]);

// ── Permission gating ────────────────────────────────────────────────────────

test('users without the required permission get 403', function (string $path) {
    $this->actingAs(adminFakeUser(can: false))->get($path)->assertForbidden();
})->with([
    '/admin/settings',
    '/admin/monitoring',
    '/admin/references',
    '/admin/permissions',
    '/admin/backup',
    '/admin/maintenance',
    '/admin/plugins',
]);

// ── Settings ─────────────────────────────────────────────────────────────────

test('settings page renders the admin.settings view', function () {
    adminStubView(AdminController::class, 'settings', 'admin.settings', [
        'grouped' => collect([]),
        'tabs' => [1 => ['label' => 'Fonctionnalités', 'icon' => 'toggle-on']],
        'activeTab' => 0,
        'annotations' => [],
    ]);

    $this->actingAs(adminFakeUser())->get('/admin/settings')
        ->assertOk()
        ->assertViewIs('admin.settings')
        ->assertViewHasAll(['grouped', 'tabs', 'activeTab', 'annotations']);
});

// ── Plugins (WIP) ────────────────────────────────────────────────────────────

test('plugins page renders for an admin and is forbidden otherwise', function () {
    $this->actingAs(adminFakeUser())->get('/admin/plugins')
        ->assertOk()
        ->assertViewIs('admin.plugins')
        ->assertSee('Plugins');

    $this->actingAs(adminFakeUser(can: false))->get('/admin/plugins')
        ->assertForbidden();
});

// ── Monitoring (audit log) ───────────────────────────────────────────────────

test('monitoring page renders the admin.monitoring view', function () {
    adminStubView(AdminController::class, 'monitoring', 'admin.monitoring', [
        'items' => new LengthAwarePaginator([], 0, 50),
        'search' => '',
        'ltCode' => 'ALL',
        'logTypes' => collect([]),
        'columns' => [],
    ]);

    $this->actingAs(adminFakeUser())->get('/admin/monitoring')
        ->assertOk()
        ->assertViewIs('admin.monitoring');
});

// ── Paramétrage ──────────────────────────────────────────────────────────────

test('parametrage index renders the admin.references.index view', function () {
    adminStubView(ReferenceController::class, 'index', 'admin.references.index', [
        'counts' => [
            'type_evenement' => 0,
            'type_participation' => 0,
            'type_materiel' => 0,
            'type_consommable' => 0,
            'grade' => 0,
        ],
    ]);

    $this->actingAs(adminFakeUser())->get('/admin/references')
        ->assertOk()
        ->assertViewIs('admin.references.index');
});

// ── Permissions ────────────────────────────────────────────────────────────

test('habilitations index renders the admin.permissions.index view', function () {
    adminStubView(PermissionController::class, 'index', 'admin.permissions.index', [
        'tab' => 'ceiling',
        'featuresByCategory' => collect([]),
        'sections' => collect([]),
        'sectionId' => 0,
        'selected' => null,
        'groups' => collect([]),
        'roles' => collect([]),
        'grants' => collect([]),
        'ownDenied' => [],
        'parentDenied' => [],
        'sectionDenied' => [],
    ]);

    $this->actingAs(adminFakeUser())->get('/admin/permissions')
        ->assertOk()
        ->assertViewIs('admin.permissions.index');
});

// ── Backup ───────────────────────────────────────────────────────────────────

test('backup index renders the admin.backup.index view', function () {
    adminStubView(BackupController::class, 'index', 'admin.backup.index', [
        'files' => collect([]),
        'settings' => new BackupSetting([
            'auto_enabled' => false,
            'frequency' => 'daily',
            'run_time' => '02:00',
            'retention_count' => 7,
            'naming_pattern' => 'backup_{date}',
            'format' => 'sql',
        ]),
    ]);

    $this->actingAs(adminFakeUser())->get('/admin/backup')
        ->assertOk()
        ->assertViewIs('admin.backup.index');
});

// ── Maintenance ──────────────────────────────────────────────────────────────

test('maintenance index renders the admin.maintenance.index view', function () {
    adminStubView(MaintenanceController::class, 'index', 'admin.maintenance.index', [
        'phpVersion' => PHP_VERSION,
        'laravelVersion' => app()->version(),
        'dbVersion' => '8.0',
        'appVersion' => '1.0.0',
        'env' => 'testing',
        'debugMode' => 'Désactivé',
        'status' => [],
    ]);

    $this->actingAs(adminFakeUser())->get('/admin/maintenance')
        ->assertOk()
        ->assertViewIs('admin.maintenance.index');
});

// ── Legacy bridge redirects (cutover) ────────────────────────────────────────

test('legacy admin pages redirect to their native routes', function (string $legacy, string $route) {
    $this->actingAs(adminFakeUser())
        ->get($legacy)
        ->assertRedirect(route($route));
})->with([
    ['/legacy/configuration.php',              'admin.settings'],
    ['/legacy/save_configuration.php',         'admin.settings'],
    ['/legacy/configuration_theme.php',        'admin.settings'],
    ['/legacy/configuration_icone_grade.php',  'admin.references.grade'],
    ['/legacy/parametrage.php',                'admin.references'],
    ['/legacy/habilitations.php',              'admin.permissions'],
    ['/legacy/save_habilitations.php',         'admin.permissions'],
    ['/legacy/upd_habilitations.php',          'admin.permissions'],
    ['/legacy/audit.php',                      'admin.monitoring'],
    ['/legacy/history.php',                    'admin.monitoring'],
    ['/legacy/backup.php',                     'admin.backup'],
    ['/legacy/upgrade.php',                    'admin.maintenance'],
]);
