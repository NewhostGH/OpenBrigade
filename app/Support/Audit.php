<?php

namespace App\Support;

use App\Logging\DatabaseLogHandler;
use App\Logging\RequestContextProcessor;
use Illuminate\Support\Facades\Log;

/**
 * Ergonomic entry point for backend logging, bucketed by logical canal.
 *
 * Every call goes through the default `stack` logger, so the record is written
 * to BOTH the rotating file and `ob_log_entry`. The first argument selects the
 * canal (activity | audit | auth | security | …); whether the record is kept in
 * the database store is decided per-canal by its `obs_level_<canal>` setting
 * (enforced in {@see DatabaseLogHandler}). This keeps "log this"
 * a readable one-liner anywhere in the backend:
 *
 *   Audit::activity('password.changed', ['target' => $id]);
 *   Audit::action('event.deleted', ['event_id' => $id]);
 *   Audit::auth('login.failed', ['login' => $login], 'warning');
 *   Audit::security('upload.rejected', ['reason' => $msg], 'warning');
 *
 * Actor (p_id), IP, method and URL are added automatically by
 * {@see RequestContextProcessor} — never pass them by hand.
 */
class Audit
{
    /** Business activity event (consolidated successor to log_history). */
    public static function activity(string $event, array $context = [], string $level = 'info'): void
    {
        self::write('activity', $event, $context, $level);
    }

    /** Generic state change (used by the request-audit middleware). */
    public static function action(string $event, array $context = [], string $level = 'info'): void
    {
        self::write('audit', $event, $context, $level);
    }

    /** Authentication / session event (login, logout, failures). */
    public static function auth(string $event, array $context = [], string $level = 'info'): void
    {
        self::write('auth', $event, $context, $level);
    }

    /** Security-relevant event (rejected upload, permission denial, throttle). */
    public static function security(string $event, array $context = [], string $level = 'warning'): void
    {
        self::write('security', $event, $context, $level);
    }

    /**
     * Low-level escape hatch: log `$event` to an arbitrary canal.
     *
     * Guarded so a logging failure can never bubble into the caller.
     */
    public static function write(string $channel, string $event, array $context = [], string $level = 'info'): void
    {
        try {
            Log::log($level, $event, ['ob_channel' => $channel] + $context);
        } catch (\Throwable) {
            // Logging must never break the caller.
        }
    }
}
