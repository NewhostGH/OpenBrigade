<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace App\Models;

use App\Models\Concerns\HasAvatar;
use App\Services\PermissionResolver;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasAvatar;

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

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return '';
    }

    /**
     * Check whether this user has the given permission (F_ID) in their active
     * section context. Global groups (GP_ID / GP_ID2) always apply; roles apply
     * within the active section and its ancestors; the section ceiling caps
     * everything. See {@see PermissionResolver}.
     */
    public function hasPermission(int $fid): bool
    {
        $resolver = app(PermissionResolver::class);

        return $resolver->allows(
            $this,
            $fid,
            $resolver->activeSectionId($this),
            $resolver->activeRoleId($this),
        );
    }

    /** Check a permission in an explicit section, ignoring the active-role filter. */
    public function hasPermissionInSection(int $fid, ?int $sectionId): bool
    {
        return app(PermissionResolver::class)->allows($this, $fid, $sectionId);
    }

    /**
     * Returns true if the user belongs to the super-admin group (GP_ID = -1 is blocked;
     * GP_ID = 1 is conventionally the admin group in the legacy schema).
     */
    public function isAdmin(): bool
    {
        return $this->hasPermission(52); // 52 = habilitations management
    }
}
