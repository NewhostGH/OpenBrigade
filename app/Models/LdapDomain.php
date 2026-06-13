<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $priority
 * @property string $host
 * @property int $port
 * @property string $base_dn
 * @property string|null $username
 * @property string|null $password
 * @property int $timeout
 * @property bool $use_tls
 * @property bool $use_starttls
 * @property string $auth_method
 * @property string|null $upn_suffix
 * @property string $user_filter
 * @property bool $restrict_to_ou
 */
class LdapDomain extends Model
{
    protected $table = 'ldap_domains';

    protected $fillable = [
        'name', 'enabled', 'priority', 'host', 'port', 'base_dn',
        'username', 'password', 'timeout', 'use_tls', 'use_starttls',
        'auth_method', 'upn_suffix', 'user_filter', 'restrict_to_ou',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'use_tls' => 'boolean',
        'use_starttls' => 'boolean',
        'restrict_to_ou' => 'boolean',
        'password' => 'encrypted',
    ];

    public function attributeMaps(): HasMany
    {
        return $this->hasMany(LdapAttributeMap::class, 'ldap_domain_id');
    }

    public function ouRules(): HasMany
    {
        return $this->hasMany(LdapOuRule::class, 'ldap_domain_id')->orderBy('priority');
    }

    public function toConnectionConfig(): array
    {
        return [
            'hosts' => [$this->host],
            'username' => $this->username ?? '',
            'password' => $this->password ?? '',
            'port' => $this->port,
            'base_dn' => $this->base_dn,
            'timeout' => $this->timeout,
            'use_tls' => $this->use_tls,
            'use_starttls' => $this->use_starttls,
            'use_sasl' => false,
        ];
    }
}
