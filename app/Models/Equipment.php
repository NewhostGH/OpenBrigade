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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legacy table: materiel
 * Primary key: MA_ID
 */
class Equipment extends Model
{
    protected $table = 'materiel';

    protected $primaryKey = 'MA_ID';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'MA_REV_DATE' => 'date',
        'MA_UPDATE_DATE' => 'date',
        'MA_ADDED' => 'datetime',
        'MA_EXTERNE' => 'boolean',
    ];

    /** The section this item belongs to. */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'S_ID', 'S_ID');
    }

    /** Vehicle this item is stored on / assigned to (optional). */
    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'V_ID', 'V_ID');
    }

    /** Parent item (for kits/sub-items). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'MA_PARENT', 'MA_ID');
    }

    /** Child items. */
    public function children(): HasMany
    {
        return $this->hasMany(Equipment::class, 'MA_PARENT', 'MA_ID');
    }

    /** Events where this item was deployed. */
    public function evenements(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'evenement_materiel', 'MA_ID', 'E_CODE')
            ->withPivot('EM_NB', 'EE_ID');
    }
}
