<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A feature (F_ID) granted to a group/role. Replaces the legacy `habilitation`
 * table. Table: ob_group_permission.
 */
class ObGroupPermission extends Model
{
    protected $table = 'ob_group_permission';

    protected $fillable = ['group_id', 'feature_id'];

    protected $casts = [
        'group_id' => 'integer',
        'feature_id' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ObGroup::class, 'group_id');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Fonctionnalite::class, 'feature_id', 'F_ID');
    }
}
