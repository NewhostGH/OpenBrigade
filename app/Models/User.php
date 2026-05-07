<?php

# project: OpenBrigade

# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'pompier';

    protected $primaryKey = 'P_ID';

    public $timestamps = false;

    protected $hidden = [
        'P_MDP',
    ];

    protected $casts = [
        'P_LAST_CONNECT' => 'datetime',
    ];

    public function getAuthIdentifierName(): string
    {
        return 'P_ID';
    }

    public function getAuthPassword(): string
    {
        return (string) $this->P_MDP;
    }

    // Legacy table has no remember_token column yet.
    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value): void
    {
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
