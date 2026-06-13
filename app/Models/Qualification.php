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
 * Legacy table: qualification
 * Composite primary key: (P_ID, PS_ID)
 * Represents a personnel member's competency/skill record.
 */
class Qualification extends Model
{
    protected $table = 'qualification';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'Q_EXPIRATION' => 'date',
        'Q_UPDATE_DATE' => 'datetime',
    ];

    /** The personnel member who holds this qualification. */
    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'P_ID', 'P_ID');
    }

    /** The competency / skill definition (poste). */
    public function poste(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'PS_ID', 'PS_ID');
    }
}
