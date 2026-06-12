<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-person ACL override (the most specific habilitation tier). A row grants
 * (effect=allow) or refuses (effect=deny) a feature (F_ID) for one person,
 * optionally scoped to a section (section_id = 0 means global, inherited to
 * descendants like a role). Beats every group/role grant and the section
 * ceiling — see {@see App\Services\PermissionResolver}. Table: ob_user_permission.
 */
class ObUserPermission extends Model
{
    public const EFFECT_ALLOW = 'allow';

    public const EFFECT_DENY = 'deny';

    protected $table = 'ob_user_permission';

    protected $fillable = ['person_id', 'section_id', 'feature_id', 'effect'];

    protected $casts = [
        'person_id' => 'integer',
        'section_id' => 'integer',
        'feature_id' => 'integer',
    ];

    protected $attributes = [
        'section_id' => 0,
        'effect' => self::EFFECT_ALLOW,
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'person_id', 'P_ID');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'S_ID');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Fonctionnalite::class, 'feature_id', 'F_ID');
    }
}
