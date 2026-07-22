<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

/**
 * Tiny job dispatched by the scheduler every five minutes. When a worker picks
 * it up it stamps a cache key; the queue health probe reads that stamp to prove
 * the scheduler → queue → worker chain works end to end. A stale stamp means a
 * stalled or dead worker (see HealthCheckService::checkQueue()).
 */
class QueueHeartbeatJob implements ShouldQueue
{
    use Queueable;

    public const CACHE_KEY = 'ob:queue:heartbeat';

    /** A heartbeat is not worth retrying — the next one is 5 minutes away. */
    public int $tries = 1;

    public function handle(): void
    {
        Cache::put(self::CACHE_KEY, now()->getTimestamp(), now()->addDay());
    }
}
