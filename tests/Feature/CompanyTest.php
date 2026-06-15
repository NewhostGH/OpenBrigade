<?php

use App\Http\Middleware\RequireFeature;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function companyStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

beforeEach(function () {
    companyStubNav();
    $this->withoutMiddleware([ValidateCsrfToken::class, RequireFeature::class]);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /companies to login', function () {
    $this->get('/companies')->assertRedirect('/login');
});

test('unauthenticated users are redirected from the company exports to login', function () {
    $this->get('/companies/export/xls')->assertRedirect('/login');
    $this->get('/companies/export/csv')->assertRedirect('/login');
});

// ── Route registration ───────────────────────────────────────────────────────

test('the company export routes are registered', function () {
    expect(route('company.export.xls'))->toContain('/companies/export/xls');
    expect(route('company.export.csv'))->toContain('/companies/export/csv');
});
