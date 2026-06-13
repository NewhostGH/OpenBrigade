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

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Legacy table: evenement_participation
 * Composite primary key: (E_CODE, EH_ID, P_ID)
 * Tracks a personnel member's participation in an event time-slot.
 */
class EvenementParticipation extends Pivot
{
    protected $table = 'evenement_participation';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'EP_DATE' => 'datetime',
        'EP_DATE_DEBUT' => 'date',
        'EP_DATE_FIN' => 'date',
        'EP_ABSENT' => 'boolean',
        'EP_EXCUSE' => 'boolean',
        'EP_PAID' => 'boolean',
        'EP_FLAG1' => 'boolean',
        'EP_ASA' => 'boolean',
        'EP_DAS' => 'boolean',
        'EP_ASTREINTE' => 'boolean',
        'EP_REMINDER' => 'boolean',
    ];
}
