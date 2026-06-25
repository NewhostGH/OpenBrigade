<?php

use App\Logging\DatabaseLogger;
use App\Models\ObLogEntry;
use Illuminate\Support\Facades\Schema;
use Monolog\Level;

beforeEach(function () {
    // Build just the ob_log_entry table on the in-memory test connection.
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

afterEach(function () {
    Schema::dropIfExists('ob_log_entry');
});

it('persists a log record to ob_log_entry', function () {
    $logger = (new DatabaseLogger)(['level' => 'debug']);

    $logger->warning('Something noteworthy', ['ob_channel' => 'app', 'foo' => 'bar']);

    $row = ObLogEntry::query()->first();

    expect($row)->not->toBeNull()
        ->and($row->level)->toBe('warning')
        ->and($row->channel)->toBe('app')
        ->and($row->message)->toBe('Something noteworthy')
        ->and($row->context)->toMatchArray(['foo' => 'bar']);
});

it('extracts an exception into the dedicated trace columns', function () {
    $logger = (new DatabaseLogger)(['level' => 'debug']);

    $logger->error('Boom', ['exception' => new RuntimeException('kaboom')]);

    $row = ObLogEntry::query()->first();

    expect($row->exception_class)->toBe(RuntimeException::class)
        ->and($row->exception_message)->toBe('kaboom')
        ->and($row->exception_trace)->not->toBeNull()
        ->and($row->context)->toBeNull();
});

it('honours the channel minimum level', function () {
    $logger = (new DatabaseLogger)(['level' => 'error']);

    $logger->warning('Below threshold — dropped');
    $logger->error('At threshold — kept');

    expect(ObLogEntry::query()->count())->toBe(1)
        ->and(ObLogEntry::query()->first()->message)->toBe('At threshold — kept');
})->skip(fn () => ! class_exists(Level::class), 'Monolog Level enum required');
