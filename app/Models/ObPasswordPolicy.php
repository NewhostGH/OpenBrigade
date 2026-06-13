<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Named password policy, assigned per ob_group.
 *
 * @property int $id
 * @property string $name
 * @property int $min_length
 * @property bool $require_uppercase
 * @property bool $require_lowercase
 * @property bool $require_digits
 * @property bool $require_special
 * @property int $expiry_days
 * @property int $max_attempts
 * @property bool $blocklist_check
 * @property bool $require_2fa
 * @property bool $is_default
 */
class ObPasswordPolicy extends Model
{
    protected $table = 'ob_password_policy';

    protected $fillable = [
        'name',
        'min_length',
        'require_uppercase',
        'require_lowercase',
        'require_digits',
        'require_special',
        'expiry_days',
        'max_attempts',
        'blocklist_check',
        'require_2fa',
        'is_default',
    ];

    protected $casts = [
        'min_length' => 'integer',
        'require_uppercase' => 'boolean',
        'require_lowercase' => 'boolean',
        'require_digits' => 'boolean',
        'require_special' => 'boolean',
        'expiry_days' => 'integer',
        'max_attempts' => 'integer',
        'blocklist_check' => 'boolean',
        'require_2fa' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function groups(): HasMany
    {
        return $this->hasMany(ObGroup::class, 'password_policy_id');
    }

    /** @return array{min_length:int,require_uppercase:bool,require_lowercase:bool,require_digits:bool,require_special:bool,expiry_days:int,max_attempts:int,blocklist_check:bool,require_2fa:bool} */
    public function toPolicy(): array
    {
        return [
            'min_length' => $this->min_length,
            'require_uppercase' => $this->require_uppercase,
            'require_lowercase' => $this->require_lowercase,
            'require_digits' => $this->require_digits,
            'require_special' => $this->require_special,
            'expiry_days' => $this->expiry_days,
            'max_attempts' => $this->max_attempts,
            'blocklist_check' => $this->blocklist_check,
            'require_2fa' => $this->require_2fa,
        ];
    }
}
