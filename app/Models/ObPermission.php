<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Canonical permission catalog (table: ob_permission).
 *
 * `id` is preserved from the legacy `fonctionnalite.F_ID`, so the grant tables
 * (ob_group_permission, ob_section_permission, ob_user_permission) keep
 * referencing it unchanged. Each row is classified on two axes — domain
 * (config|data) and read/write — plus a critical marker. The classification is
 * back-filled by the rebuild migration via {@see App\Support\Habilitations\BaseHabilitations}
 * and drives the SEEDED base-group default grants; it is not a runtime
 * enforcement path (that stays {@see App\Services\PermissionResolver}).
 *
 * @property int $id
 * @property string $key
 * @property string $label
 * @property string $domain
 * @property bool $is_read
 * @property bool $is_critical
 * @property string|null $category
 * @property int $ordering
 */
class ObPermission extends Model
{
    public const DOMAIN_CONFIG = 'config';

    public const DOMAIN_DATA = 'data';

    protected $table = 'ob_permission';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'key', 'label', 'domain', 'is_read', 'is_critical', 'category', 'ordering'];

    protected $casts = [
        'is_read' => 'boolean',
        'is_critical' => 'boolean',
        'ordering' => 'integer',
    ];
}
