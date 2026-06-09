<?php

namespace App\Models;

use App\Services\FeatureService;
use Illuminate\Database\Eloquent\Model;

/**
 * Canonical feature/module flag. Unifies the legacy `configuration` TAB 1
 * (Fonctionnalités) and TAB 6 (Modules) buckets behind a single registry that
 * drives the Administration ▸ Fonctionnalités screen and the runtime
 * `feature:<key>` gate. Table: ob_feature.
 *
 * @see FeatureService
 */
class ObFeature extends Model
{
    protected $table = 'ob_feature';

    protected $fillable = [
        'key', 'name', 'description', 'group', 'status', 'icon',
        'enabled', 'ordering', 'legacy_config_id',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'ordering' => 'integer',
        'legacy_config_id' => 'integer',
    ];

    /** Not yet migrated to the native app — surfaced with a WIP marker. */
    public function isWip(): bool
    {
        return $this->status === 'wip';
    }
}
