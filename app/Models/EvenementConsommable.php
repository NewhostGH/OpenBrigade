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

/**
 * Legacy table: evenement_consommable
 * Primary key: EC_ID
 * Tracks consumable usage per event.
 */
class EvenementConsommable extends Model
{
    protected $table = 'evenement_consommable';

    protected $primaryKey = 'EC_ID';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'EC_DATE_CONSO' => 'date',
    ];

    /** The event this consumption record belongs to. */
    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'E_CODE', 'E_CODE');
    }

    /** The consumable item used. */
    public function consommable(): BelongsTo
    {
        return $this->belongsTo(Consommable::class, 'C_ID', 'C_ID');
    }
}
