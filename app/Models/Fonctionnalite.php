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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Legacy table: fonctionnalite
 * Primary key: F_ID
 * Represents a discrete application permission/feature flag.
 */
class Fonctionnalite extends Model
{
    use HasFactory;

    protected $table = 'fonctionnalite';

    protected $primaryKey = 'F_ID';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'F_FLAG' => 'boolean',
    ];

    /** Groups that have been granted this permission. */
    public function groupes(): BelongsToMany
    {
        return $this->belongsToMany(Groupe::class, 'habilitation', 'F_ID', 'GP_ID');
    }
}
