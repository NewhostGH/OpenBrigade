<?php

use App\Http\Middleware\RequireFeature;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function consumableStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

beforeEach(function () {
    consumableStubNav();
    $this->withoutMiddleware([ValidateCsrfToken::class, RequireFeature::class]);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /consumables to login', function () {
    $this->get('/consumables')->assertRedirect('/login');
});

test('unauthenticated users are redirected from the consumable exports to login', function () {
    $this->get('/consumables/export/xls')->assertRedirect('/login');
    $this->get('/consumables/export/csv')->assertRedirect('/login');
});

// ── Route registration ───────────────────────────────────────────────────────

test('the consumable export routes are registered', function () {
    expect(route('consumable.export.xls'))->toContain('/consumables/export/xls');
    expect(route('consumable.export.csv'))->toContain('/consumables/export/csv');
});
