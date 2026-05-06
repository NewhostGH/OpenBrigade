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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legacy table: groupe
 * Primary key: GP_ID
 * Represents a permission group (role/habilitation group) in the legacy system.
 */
class Groupe extends Model
{
    protected $table = 'groupe';

    protected $primaryKey = 'GP_ID';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'GP_ASTREINTE'       => 'boolean',
        'TR_SUB_POSSIBLE'    => 'boolean',
        'TR_ALL_POSSIBLE'    => 'boolean',
        'TR_WIDGET'          => 'boolean',
    ];

    /** The permissions (fonctionnalites) granted to this group. */
    public function fonctionnalites(): BelongsToMany
    {
        return $this->belongsToMany(Fonctionnalite::class, 'habilitation', 'GP_ID', 'F_ID');
    }

    /** All personnel belonging to this group as primary group. */
    public function personnel(): HasMany
    {
        return $this->hasMany(Personnel::class, 'GP_ID', 'GP_ID');
    }
}
