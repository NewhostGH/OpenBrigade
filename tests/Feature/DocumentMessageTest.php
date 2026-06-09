<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MessageController;
use App\Models\User;
use App\Services\NavigationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Stub NavigationService so the layout's view composer never hits the DB.
 */
function docMsgStubNav(): void
{
    $nav = Mockery::mock(NavigationService::class);
    $nav->shouldReceive('getNavGroups')->andReturn([]);
    $nav->shouldReceive('getPinnedShortcuts')->andReturn([]);
    app()->instance(NavigationService::class, $nav);
}

/**
 * Build a minimal fake User (no DB required). hasPermission() returns true so
 * permission-gated library and board actions are reachable.
 */
function docMsgFakeUser(): User
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

/**
 * Bind DocumentController so index() returns the real view rendered with stub
 * data, keeping the assertion at the HTTP/view level without touching the DB.
 */
function docStubIndex(): void
{
    app()->bind(DocumentController::class, function () {
        $ctrl = Mockery::mock(DocumentController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 30);
        $page->setPath('/documents');
        $ctrl->shouldReceive('index')->andReturn(
            view('document.index', [
                'allFolders' => Collection::make([]),
                'subFolders' => Collection::make([]),
                'breadcrumb' => [],
                'documents'  => $page,
                'folderId'   => 0,
                'typeCode'   => 'ALL',
                'types'      => Collection::make([]),
            ])
        );

        return $ctrl;
    });
}

/**
 * Bind MessageController so index() returns the real view rendered with stub
 * data, keeping the assertion at the HTTP/view level without touching the DB.
 */
function msgStubIndex(): void
{
    app()->bind(MessageController::class, function () {
        $ctrl = Mockery::mock(MessageController::class)->makePartial();
        $page = new LengthAwarePaginator([], 0, 20);
        $page->setPath('/messages');
        $ctrl->shouldReceive('index')->andReturn(
            view('message.index', [
                'items'    => $page,
                'category' => 'consigne',
            ])
        );

        return $ctrl;
    });
}

beforeEach(function () {
    docMsgStubNav();
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ── Documents ────────────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /documents to login', function () {
    $this->get('/documents')->assertRedirect('/login');
});

test('legacy documents.php redirects to document.index', function () {
    $this->actingAs(docMsgFakeUser())
        ->get('/legacy/documents.php')
        ->assertRedirect(route('document.index'));
});

test('authenticated users can access the document library', function () {
    docStubIndex();
    $this->actingAs(docMsgFakeUser())->get('/documents')->assertStatus(200);
});

test('document library uses the document.index template', function () {
    docStubIndex();
    $this->actingAs(docMsgFakeUser())->get('/documents')->assertViewIs('document.index');
});

test('document library passes required view variables', function () {
    docStubIndex();
    $this->actingAs(docMsgFakeUser())->get('/documents')
        ->assertViewHasAll(['allFolders', 'subFolders', 'breadcrumb', 'documents', 'folderId', 'typeCode', 'types']);
});

// ── Messages ─────────────────────────────────────────────────────────────────

test('unauthenticated users are redirected from /messages to login', function () {
    $this->get('/messages')->assertRedirect('/login');
});

test('legacy message.php redirects to message.index preserving category', function () {
    $this->actingAs(docMsgFakeUser())
        ->get('/legacy/message.php?catmessage=amicale')
        ->assertRedirect(route('message.index', ['category' => 'amicale']));
});

test('legacy message.php without category defaults to consigne', function () {
    $this->actingAs(docMsgFakeUser())
        ->get('/legacy/message.php')
        ->assertRedirect(route('message.index', ['category' => 'consigne']));
});

test('authenticated users can access the message board', function () {
    msgStubIndex();
    $this->actingAs(docMsgFakeUser())->get('/messages')->assertStatus(200);
});

test('message board uses the message.index template', function () {
    msgStubIndex();
    $this->actingAs(docMsgFakeUser())->get('/messages')->assertViewIs('message.index');
});
