<?php

use App\Services\LoggingSettingService;

it('exposes every observability key, including per-canal levels', function () {
    $keys = LoggingSettingService::keys();

    expect($keys)->toContain('obs_level_activity', 'obs_level_app', 'obs_sentry_dsn', 'obs_log_to_db', 'obs_perf_slow_ms');
    expect($keys)->not->toContain('obs_log_level');
});

it('falls back to typed defaults when no row exists', function () {
    // No `configuration` table in the unit DB → map() swallows the QueryException
    // and every getter returns the shipped default.
    $svc = new LoggingSettingService;

    expect($svc->canalLevel('app'))->toBe('warning')
        ->and($svc->canalLevel('activity'))->toBe('info')
        ->and($svc->canalLevel('error'))->toBe('error')
        ->and($svc->bool('obs_log_to_db'))->toBeTrue()
        ->and($svc->bool('obs_error_tracking'))->toBeFalse()
        ->and($svc->string('obs_sentry_dsn'))->toBe('')
        ->and($svc->int('obs_db_retention_days'))->toBe(90);
});

it('reports the lowest canal level for the file leg', function () {
    // Defaults: error=error, app=warning, activity/audit/.../performance=info.
    expect((new LoggingSettingService)->lowestCanalLevel())->toBe('info');
});

it('treats an unknown canal as always-on (debug)', function () {
    expect((new LoggingSettingService)->canalLevel('does-not-exist'))->toBe('debug');
});
