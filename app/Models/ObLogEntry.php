<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One row of the unified observability log (ob_log_entry).
 *
 * Written by App\Logging\DatabaseLogHandler; read by the Journal d'activité
 * admin screens. Entries are immutable — only `created_at` is tracked.
 *
 * @property int $id
 * @property string $level
 * @property string $channel
 * @property string $message
 * @property array<string,mixed>|null $context
 * @property string|null $exception_class
 * @property string|null $exception_message
 * @property string|null $exception_trace
 * @property int|null $p_id
 * @property string|null $ip
 * @property string|null $method
 * @property string|null $url
 * @property string|null $user_agent
 * @property int|null $duration_ms
 * @property int|null $memory_mb
 * @property Carbon|null $created_at
 */
class ObLogEntry extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'ob_log_entry';

    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
        'p_id' => 'integer',
        'duration_ms' => 'integer',
        'memory_mb' => 'integer',
    ];

    /** PSR-3 levels in ascending severity — shared by the service and the UI. */
    public const LEVELS = [
        'debug', 'info', 'notice', 'warning',
        'error', 'critical', 'alert', 'emergency',
    ];

    /** The pompier who triggered the entry, if any. */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'p_id', 'P_ID');
    }
}
