<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A feature (F_ID) granted to a group/role. Replaces the legacy `habilitation`
 * table. `effect` makes the grant explicit: allow grants the feature, deny
 * refuses it — a deny on any held group/role overrides allows from the others
 * (see {@see App\Services\PermissionResolver}). Table: ob_group_permission.
 */
class ObGroupPermission extends Model
{
    public const EFFECT_ALLOW = 'allow';

    public const EFFECT_DENY = 'deny';

    protected $table = 'ob_group_permission';

    protected $fillable = ['group_id', 'feature_id', 'effect'];

    protected $casts = [
        'group_id' => 'integer',
        'feature_id' => 'integer',
    ];

    protected $attributes = [
        'effect' => self::EFFECT_ALLOW,
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ObGroup::class, 'group_id');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(LegacyFeature::class, 'feature_id', 'F_ID');
    }
}
