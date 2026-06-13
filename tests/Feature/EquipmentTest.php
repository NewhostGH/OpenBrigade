<?php

use App\Http\Middleware\RequireFeature;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function equipmentStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

beforeEach(function () {
    equipmentStubNav();
    $this->withoutMiddleware([ValidateCsrfToken::class, RequireFeature::class]);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /equipment to login', function () {
    $this->get('/equipment')->assertRedirect('/login');
});

test('unauthenticated users are redirected from the equipment exports to login', function () {
    $this->get('/equipment/export/xls')->assertRedirect('/login');
    $this->get('/equipment/export/csv')->assertRedirect('/login');
});

// ── Route registration ───────────────────────────────────────────────────────

test('the equipment export routes are registered', function () {
    expect(route('equipment.export.xls'))->toContain('/equipment/export/xls');
    expect(route('equipment.export.csv'))->toContain('/equipment/export/csv');
});
