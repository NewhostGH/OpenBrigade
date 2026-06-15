<?php

use App\Http\Middleware\RequireFeature;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function replacementStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

beforeEach(function () {
    replacementStubNav();
    $this->withoutMiddleware([ValidateCsrfToken::class, RequireFeature::class]);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /replacements to login', function () {
    $this->get('/replacements')->assertRedirect('/login');
});

test('unauthenticated users are redirected from the replacement exports to login', function () {
    $this->get('/replacements/export/xls')->assertRedirect('/login');
    $this->get('/replacements/export/csv')->assertRedirect('/login');
});

// ── Route registration ───────────────────────────────────────────────────────

test('the replacement export routes are registered', function () {
    expect(route('replacement.export.xls'))->toContain('/replacements/export/xls');
    expect(route('replacement.export.csv'))->toContain('/replacements/export/csv');
});
