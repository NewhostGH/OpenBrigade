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
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legacy table: poste
 * Primary key: PS_ID
 * Represents a competency / skill / role definition.
 */
class Position extends Model
{
    protected $table = 'poste';

    protected $primaryKey = 'PS_ID';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'PS_FORMATION' => 'boolean',
        'PS_EXPIRABLE' => 'boolean',
        'PS_AUDIT' => 'boolean',
        'PS_DIPLOMA' => 'boolean',
        'PS_NUMERO' => 'boolean',
        'PS_RECYCLE' => 'boolean',
        'PS_USER_MODIFIABLE' => 'boolean',
        'PS_PRINTABLE' => 'boolean',
        'PS_PRINT_IMAGE' => 'boolean',
        'PS_NATIONAL' => 'boolean',
        'PS_SECOURISME' => 'boolean',
    ];

    /** Qualification records for this skill definition. */
    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class, 'PS_ID', 'PS_ID');
    }
}
