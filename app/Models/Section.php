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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legacy table: section
 * Primary key: S_ID
 */
class Section extends Model
{
    protected $table = 'section';

    protected $primaryKey = 'S_ID';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'S_HIDE'     => 'boolean',
        'S_INACTIVE' => 'boolean',
    ];

    /** Parent section in the hierarchy. */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'S_PARENT', 'S_ID');
    }

    /** Direct child sections. */
    public function children(): HasMany
    {
        return $this->hasMany(Section::class, 'S_PARENT', 'S_ID');
    }

    /** All personnel whose home section is this section. */
    public function personnel(): HasMany
    {
        return $this->hasMany(Personnel::class, 'P_SECTION', 'S_ID');
    }

    /** Personnel holding an explicit role in this section. */
    public function rolePersonnel(): BelongsToMany
    {
        return $this->belongsToMany(Personnel::class, 'section_role', 'S_ID', 'P_ID')
            ->withPivot('GP_ID', 'UPDATE_DATE');
    }

    /** Events belonging to this section. */
    public function evenements(): HasMany
    {
        return $this->hasMany(Evenement::class, 'S_ID', 'S_ID');
    }

    /** Vehicles belonging to this section. */
    public function vehicules(): HasMany
    {
        return $this->hasMany(Vehicule::class, 'S_ID', 'S_ID');
    }

    /** Materiel items belonging to this section. */
    public function materiels(): HasMany
    {
        return $this->hasMany(Materiel::class, 'S_ID', 'S_ID');
    }

    /** Consumable stock items belonging to this section. */
    public function consommables(): HasMany
    {
        return $this->hasMany(Consommable::class, 'S_ID', 'S_ID');
    }
}
