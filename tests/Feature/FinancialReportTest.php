<?php

use App\Models\User;
use App\Services\FinancialReportService;
use App\Services\NavigationService;
use App\Services\SectionScopeService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

function finrepStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

function finrepFakeUser(): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 1, 'P_NOM' => 'Test', 'P_PRENOM' => 'User',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'P_MDP' => bcrypt('secret'),
    ]);
    $user->shouldReceive('hasPermission')->andReturn(true);

    return $user;
}

/** A small, well-formed report fixture so the controller/view render DB-free. */
function finrepFixture(): array
{
    return [
        'paymentTypes' => [1 => 'Espèces', 2 => 'Chèque'],
        'sections' => [[
            'S_ID' => 1,
            'label' => 'CIS — Alpha',
            'effectifs' => 3,
            'lines' => [[
                'profession' => 'SPV',
                'amounts' => [1 => 100.0, 2 => 50.0],
                'rejets' => 10.0,
                'total' => 140.0,
            ]],
            'subtotal' => ['amounts' => [1 => 100.0, 2 => 50.0], 'rejets' => 10.0, 'total' => 140.0],
        ]],
        'totals' => ['effectifs' => 3, 'amounts' => [1 => 100.0, 2 => 50.0], 'rejets' => 10.0, 'total' => 140.0],
    ];
}

function finrepStubServices(): void
{
    $scope = Mockery::mock(SectionScopeService::class);
    $scope->shouldReceive('sectionFilter')->andReturn(null);
    app()->instance(SectionScopeService::class, $scope);

    $svc = Mockery::mock(FinancialReportService::class);
    $svc->shouldReceive('report')->andReturn(finrepFixture());
    app()->instance(FinancialReportService::class, $svc);
}

beforeEach(function () {
    finrepStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from the financial report to login', function () {
    $this->get('/statistics/financial-report')->assertRedirect('/login');
});

test('unauthenticated users are redirected from the financial report exports to login', function () {
    $this->get('/statistics/financial-report/export/xls')->assertRedirect('/login');
    $this->get('/statistics/financial-report/export/csv')->assertRedirect('/login');
});

// ── Route registration ───────────────────────────────────────────────────────

test('the financial report routes are registered', function () {
    expect(route('statistics.financial-report'))->toContain('/statistics/financial-report');
    expect(route('statistics.financial-report.export.xls'))->toContain('/statistics/financial-report/export/xls');
    expect(route('statistics.financial-report.export.csv'))->toContain('/statistics/financial-report/export/csv');
});

// ── Legacy bridge redirect ─────────────────────────────────────────────────────

test('legacy report_cotisations.php redirects to the native financial report', function () {
    $this->actingAs(finrepFakeUser())
        ->get('/legacy/report_cotisations.php')
        ->assertRedirect(route('statistics.financial-report'));
});

// ── Rendering (service stubbed) ────────────────────────────────────────────────

test('authenticated users can view the financial report', function () {
    finrepStubServices();

    $this->actingAs(finrepFakeUser())
        ->get('/statistics/financial-report')
        ->assertStatus(200)
        ->assertViewIs('statistics.financial-report')
        ->assertSee('Cotisations par section')
        ->assertSee('CIS — Alpha')
        ->assertSee('SPV');
});

test('the report page passes the aggregated view data', function () {
    finrepStubServices();

    $this->actingAs(finrepFakeUser())
        ->get('/statistics/financial-report')
        ->assertViewHasAll(['paymentTypes', 'sections', 'totals', 'from', 'to', 'sectionId']);
});

test('the xls export streams a download', function () {
    finrepStubServices();

    $this->actingAs(finrepFakeUser())
        ->get('/statistics/financial-report/export/xls')
        ->assertStatus(200);
});

test('the csv export streams a download', function () {
    finrepStubServices();

    $this->actingAs(finrepFakeUser())
        ->get('/statistics/financial-report/export/csv')
        ->assertStatus(200);
});
