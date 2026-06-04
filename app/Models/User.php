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
use Illuminate\Support\Facades\DB;

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

    /**
     * Check whether this user has the given legacy permission (F_ID).
     * Covers GP_ID and GP_ID2 group memberships and section-role grants.
     */
    public function hasPermission(int $fid): bool
    {
        if ((int) $this->GP_ID === -1 || (int) ($this->GP_ID2 ?? $this->GP_ID) === -1) {
            return false;
        }

        $gp2 = $this->GP_ID2 ?: $this->GP_ID;
        $groups = array_unique([(int) $this->GP_ID, (int) $gp2]);

        // Primary check: group habilitation
        if (DB::table('habilitation')->whereIn('GP_ID', $groups)->where('F_ID', $fid)->exists()) {
            return true;
        }

        // Section-role check: roles held in any section
        return DB::table('habilitation')
            ->join('section_role', 'habilitation.GP_ID', '=', 'section_role.GP_ID')
            ->where('habilitation.F_ID', $fid)
            ->where('section_role.P_ID', $this->P_ID)
            ->exists();
    }

    /**
     * Returns true if the user belongs to the super-admin group (GP_ID = -1 is blocked;
     * GP_ID = 1 is conventionally the admin group in the legacy schema).
     */
    public function isAdmin(): bool
    {
        return $this->hasPermission(52); // 52 = habilitations management
    }

    /**
    * Returns the URL of the user's avatar, or a default image if not set.
    */
    public function getAvatarUrl(): string
    {
        $userPhoto    = auth()->user()->P_PHOTO ?? '';
        $userCivilite = (int) (auth()->user()->P_CIVILITE ?? 1);
        if ($userPhoto !== '') {
            $avatarSrc = route('personnel.photo', auth()->user())
                        . '?v=' . substr(md5($userPhoto), 0, 8);
        } elseif ($userCivilite === 2) {
            $avatarSrc = asset('images/girl.png');
        } else {
            $avatarSrc = asset('images/boy.png');
        }
        return $avatarSrc;
    }
}
