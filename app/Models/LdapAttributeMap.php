<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ldap_domain_id
 * @property string $ldap_attr
 * @property string $local_field
 * @property bool $overwrite
 */
class LdapAttributeMap extends Model
{
    protected $table = 'ldap_attribute_maps';

    protected $fillable = ['ldap_domain_id', 'ldap_attr', 'local_field', 'overwrite'];

    protected $casts = ['overwrite' => 'boolean'];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(LdapDomain::class, 'ldap_domain_id');
    }
}
