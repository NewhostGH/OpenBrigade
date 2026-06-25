<?php

use App\Http\Middleware\AuditRequests;
use App\Models\ObLogEntry;
use App\Services\LoggingSettingService;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    Schema::create('ob_log_entry', function ($table) {
        $table->id();
        $table->string('level', 12);
        $table->string('channel', 40)->default('app');
        $table->text('message');
        $table->json('context')->nullable();
        $table->string('exception_class')->nullable();
        $table->text('exception_message')->nullable();
        $table->longText('exception_trace')->nullable();
        $table->unsignedInteger('p_id')->nullable();
        $table->string('ip', 45)->nullable();
        $table->string('method', 10)->nullable();
        $table->string('url', 2048)->nullable();
        $table->string('user_agent', 512)->nullable();
        $table->unsignedInteger('duration_ms')->nullable();
        $table->unsignedInteger('memory_mb')->nullable();
        $table->timestamp('created_at')->nullable();
    });
});

afterEach(fn () => Schema::dropIfExists('ob_log_entry'));

it('writes audit-class events to ob_log_entry with the right channel', function () {
    Audit::auth('login.success', ['login' => 'jdoe']);
    Audit::action('event.deleted', ['event_id' => 7]);
    Audit::security('upload.rejected', ['reason' => 'malware'], 'warning');

    $rows = ObLogEntry::query()->orderBy('id')->get();

    expect($rows)->toHaveCount(3)
        ->and($rows[0]->channel)->toBe('auth')
        ->and($rows[0]->level)->toBe('info')
        ->and($rows[0]->message)->toBe('login.success')
        ->and($rows[0]->context)->toMatchArray(['login' => 'jdoe'])
        ->and($rows[1]->channel)->toBe('audit')
        ->and($rows[2]->channel)->toBe('security')
        ->and($rows[2]->level)->toBe('warning');
});

it('drops events below the per-canal configured level', function () {
    // Raise the `audit` canal to error: an info event is dropped, an error kept.
    app()->instance(LoggingSettingService::class, new class extends LoggingSettingService
    {
        public function canalLevel(string $canal): string
        {
            return $canal === 'audit' ? 'error' : parent::canalLevel($canal);
        }
    });

    Audit::action('below.threshold');               // info → dropped
    Audit::action('at.threshold', [], 'error');     // error → kept

    $rows = ObLogEntry::query()->get();
    expect($rows)->toHaveCount(1)
        ->and($rows[0]->message)->toBe('at.threshold');
});

it('audits state-changing requests but ignores reads', function () {
    $mw = new AuditRequests;
    $next = fn ($req) => new Response('ok', 200);

    $mw->handle(Request::create('/events/5', 'DELETE'), $next);
    $mw->handle(Request::create('/events', 'GET'), $next);

    $rows = ObLogEntry::query()->where('channel', 'audit')->get();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]->message)->toBe('request')
        ->and($rows[0]->context)->toMatchArray(['http_method' => 'DELETE', 'status' => 200]);
});

it('flags failed state-changing requests at warning level', function () {
    $mw = new AuditRequests;
    $mw->handle(Request::create('/events', 'POST'), fn ($req) => new Response('nope', 422));

    expect(ObLogEntry::query()->first()->level)->toBe('warning');
});
