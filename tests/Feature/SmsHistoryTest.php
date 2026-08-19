<?php

use App\Models\User;
use App\Services\NavigationService;
use App\Services\SectionScopeService;
use App\Services\SmsHistoryService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

function smsStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

function smsFakeUser(): User
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

/** One well-formed history row so the controller/view render DB-free. */
function smsFixtureRow(): object
{
    return (object) [
        'P_ID' => 42,
        'P_NOM' => 'Dupont',
        'P_PRENOM' => 'marie',
        'S_DATE' => '2026-03-15 09:30:00',
        'S_NB' => 2,
        'S_TEXTE' => 'Rappel: garde ce soir 20h',
        'recipient_section' => 'CIS-A',
        'account_code' => 'CIS-A',
        'S_PROVIDER' => 'smsmode',
    ];
}

function smsStubServices(): void
{
    $scope = Mockery::mock(SectionScopeService::class);
    $scope->shouldReceive('sectionFilter')->andReturn(null);
    app()->instance(SectionScopeService::class, $scope);

    $page = new LengthAwarePaginator([smsFixtureRow()], 1, 50, 1, ['path' => '/communication/sms-history']);

    $svc = Mockery::mock(SmsHistoryService::class);
    $svc->shouldReceive('history')->andReturn($page);
    app()->instance(SmsHistoryService::class, $svc);
}

beforeEach(function () {
    smsStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from the SMS history to login', function () {
    $this->get('/communication/sms-history')->assertRedirect('/login');
});

// ── Route registration ─────────────────────────────────────────────────────────

test('the SMS history route is registered', function () {
    expect(route('communication.sms-history'))->toContain('/communication/sms-history');
});

// ── Legacy bridge redirect ─────────────────────────────────────────────────────

test('legacy histo_sms.php redirects to the native SMS history', function () {
    $this->actingAs(smsFakeUser())
        ->get('/legacy/histo_sms.php')
        ->assertRedirect(route('communication.sms-history'));
});

// ── Rendering (service stubbed) ────────────────────────────────────────────────

test('authenticated users can view the SMS history', function () {
    smsStubServices();

    $this->actingAs(smsFakeUser())
        ->get('/communication/sms-history')
        ->assertStatus(200)
        ->assertViewIs('communication.sms-history')
        ->assertSee('Historique SMS')
        ->assertSee('DUPONT Marie')
        ->assertSee('Rappel: garde ce soir 20h');
});

test('the SMS history page passes the expected view data', function () {
    smsStubServices();

    $this->actingAs(smsFakeUser())
        ->get('/communication/sms-history')
        ->assertViewHasAll(['items', 'columns', 'from', 'to', 'sectionId']);
});
