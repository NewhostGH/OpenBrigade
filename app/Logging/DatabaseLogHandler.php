<?php

namespace App\Logging;

use App\Models\ObLogEntry;
use App\Services\LoggingSettingService;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

/**
 * Monolog handler that persists records to the unified `ob_log_entry` table.
 *
 * Wired through the custom `database` channel (config/logging.php), which
 * accepts every record — the effective minimum level is enforced here PER CANAL
 * (obs_level_<canal>) so an admin can tune each logical channel independently.
 *
 * The handler is deliberately defensive: anything that goes wrong while logging
 * (table not migrated yet, DB outage) is swallowed so logging can never become
 * the cause of a request failure. Request/actor enrichment is added by
 * {@see RequestContextProcessor}; this handler maps a record to a row.
 */
class DatabaseLogHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        try {
            $extra = $record->extra;
            $context = $record->context;

            // Auto-enriched request/actor fields live in `extra`; caller-supplied
            // overrides (logical channel, perf metrics) may arrive in `context`.
            $canal = $context['ob_channel'] ?? $extra['ob_channel'] ?? $record->channel;

            // Per-canal level gate: drop records below the canal's configured
            // minimum. Resolved from the (singleton, request-cached) settings.
            if ($this->belowCanalLevel($record->level, (string) $canal)) {
                return;
            }

            $row = [
                'level' => strtolower($record->level->getName()),
                'channel' => $canal,
                'message' => $record->message,
                'p_id' => $extra['p_id'] ?? null,
                'ip' => $extra['ip'] ?? null,
                'method' => $extra['method'] ?? null,
                'url' => isset($extra['url']) ? mb_substr((string) $extra['url'], 0, 2048) : null,
                'user_agent' => isset($extra['user_agent']) ? mb_substr((string) $extra['user_agent'], 0, 512) : null,
                'duration_ms' => $context['duration_ms'] ?? $extra['duration_ms'] ?? null,
                'memory_mb' => $context['memory_mb'] ?? $extra['memory_mb'] ?? null,
                'created_at' => $record->datetime,
            ];

            // These were promoted to columns — don't duplicate them in context.
            unset($context['duration_ms'], $context['memory_mb']);

            // Pull a captured exception out of context into the dedicated columns.
            $exception = $context['exception'] ?? null;
            if ($exception instanceof Throwable) {
                $row['exception_class'] = get_class($exception);
                $row['exception_message'] = $exception->getMessage();
                $row['exception_trace'] = mb_substr($exception->getTraceAsString(), 0, 60000);
                unset($context['exception']);
            }

            // Keep only JSON-serialisable, non-internal context.
            unset($context['ob_channel']);
            $row['context'] = $context !== [] ? $this->sanitize($context) : null;

            ObLogEntry::query()->create($row);
        } catch (Throwable) {
            // Never let logging break the request.
        }
    }

    /**
     * True when the record is less severe than the canal's configured minimum
     * level. On any failure (settings unreadable, bad level name) it returns
     * false — i.e. keep the record rather than silently drop the trail.
     */
    private function belowCanalLevel(Level $level, string $canal): bool
    {
        try {
            $min = app(LoggingSettingService::class)->canalLevel($canal);

            return $level->value < Level::fromName(ucfirst($min))->value;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Best-effort reduction of arbitrary context to a JSON-safe array.
     *
     * @param  array<mixed>  $context
     * @return array<mixed>
     */
    private function sanitize(array $context): array
    {
        $clean = [];
        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value === null || is_array($value)) {
                $clean[$key] = $value;
            } elseif ($value instanceof Throwable) {
                $clean[$key] = $value->getMessage();
            } else {
                $clean[$key] = is_object($value) ? get_class($value) : gettype($value);
            }
        }

        return $clean;
    }
}
