<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ldap_domain_id
 * @property string $ou_dn
 * @property string|null $extra_filter
 * @property string $action allow|deny|assign
 * @property int|null $group_id
 * @property int|null $section_id
 * @property int $priority
 */
class LdapOuRule extends Model
{
    protected $table = 'ldap_ou_rules';

    protected $fillable = [
        'ldap_domain_id', 'ou_dn', 'extra_filter', 'action',
        'group_id', 'role_id', 'section_id', 'priority',
    ];

    protected $casts = ['priority' => 'integer'];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(LdapDomain::class, 'ldap_domain_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ObGroup::class, 'group_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(ObGroup::class, 'role_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'S_ID');
    }
}
