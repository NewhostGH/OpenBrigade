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

/**
 * Legacy table: consommable
 * Primary key: C_ID
 */
class Consumable extends Model
{
    protected $table = 'consommable';

    protected $primaryKey = 'C_ID';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'C_DATE_ACHAT' => 'date',
        'C_DATE_PEREMPTION' => 'date',
    ];

    /** The section this consumable stock belongs to. */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'S_ID', 'S_ID');
    }

    /** Material item this consumable is associated with (optional). */
    public function materiel(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'MA_PARENT', 'MA_ID');
    }
}
