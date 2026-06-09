<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Group or role definition (section-scoped habilitation model).
 *
 * Table: ob_group. `id` preserves the legacy GP_ID so that pompier.GP_ID /
 * GP_ID2 (global group membership) keep referencing it.
 */
class ObGroup extends Model
{
    protected $table = 'ob_group';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'name', 'kind', 'usage', 'ordering', 'is_system'];

    protected $casts = [
        'is_system' => 'boolean',
        'ordering' => 'integer',
    ];

    public const KIND_GROUP = 'group';

    public const KIND_ROLE = 'role';

    /** Feature grants attached to this group/role. */
    public function permissions(): HasMany
    {
        return $this->hasMany(ObGroupPermission::class, 'group_id');
    }

    /** @param Builder $query */
    public function scopeGroups($query)
    {
        return $query->where('kind', self::KIND_GROUP);
    }

    /** @param Builder $query */
    public function scopeRoles($query)
    {
        return $query->where('kind', self::KIND_ROLE);
    }

    public function isProtected(): bool
    {
        return $this->is_system || in_array((int) $this->id, [-1, 0, 4], true);
    }
}
