<?php

namespace App\Logging;

use Illuminate\Http\Request;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Throwable;

/**
 * Monolog processor that enriches every record with the acting pompier and
 * request metadata (IP, method, URL, user-agent), stored in `extra` so the
 * {@see DatabaseLogHandler} can map them to columns and the file formatter can
 * print them.
 *
 * Resolution is best-effort and fully guarded: outside an HTTP request (console,
 * queue) or before the auth/session is booted, the fields are simply omitted.
 */
class RequestContextProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;

        try {
            if (app()->runningInConsole()) {
                return $record;
            }

            /** @var Request $request */
            $request = request();

            $extra['ip'] ??= $request->ip();
            $extra['method'] ??= $request->method();
            $extra['url'] ??= $request->fullUrl();
            $extra['user_agent'] ??= substr((string) $request->userAgent(), 0, 512);

            if (! isset($extra['p_id']) && ($user = $request->user()) !== null) {
                // The app's user model is keyed by P_ID (legacy pompier table).
                $extra['p_id'] = $user->getAuthIdentifier();
            }
        } catch (Throwable) {
            // No request context available — leave extra as-is.
        }

        return $record->with(extra: $extra);
    }
}
