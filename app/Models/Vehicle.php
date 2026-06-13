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
 * Legacy table: vehicule
 * Primary key: V_ID
 *
 * @property int $V_ID
 * @property string|null $V_INDICATIF
 * @property string|null $V_IMMATRICULATION
 * @property string|null $V_MODELE
 * @property string|null $TV_CODE
 * @property int|null $S_ID
 * @property int|null $VP_ID joined from `vehicule_position` list queries
 * @property int|null $VP_OPERATIONNEL joined from `vehicule_position` list queries
 * @property string|null $TV_LIBELLE joined from `type_vehicule` list queries
 * @property-read Section|null $section
 */
class Vehicle extends Model
{
    protected $table = 'vehicule';

    protected $primaryKey = 'V_ID';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'V_ASS_DATE' => 'date',
        'V_CT_DATE' => 'date',
        'V_REV_DATE' => 'date',
        'V_TITRE_DATE' => 'date',
        'V_UPDATE_DATE' => 'date',
        'V_EXTERNE' => 'boolean',
        'V_FLAG1' => 'boolean',
        'V_FLAG2' => 'boolean',
        'V_FLAG3' => 'boolean',
        'V_FLAG4' => 'boolean',
    ];

    /** The section this vehicle belongs to. */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'S_ID', 'S_ID');
    }

    /** Events this vehicle was deployed on. */
    public function evenements(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'evenement_vehicule', 'V_ID', 'E_CODE')
            ->withPivot([
                'EH_ID', 'EV_KM', 'EV_DATE_DEBUT', 'EV_DATE_FIN',
                'EV_DEBUT', 'EV_FIN', 'EV_DUREE', 'EE_ID', 'TFV_ID',
            ]);
    }

    /** Materials stored on / assigned to this vehicle. */
    public function materiels(): HasMany
    {
        return $this->hasMany(Equipment::class, 'V_ID', 'V_ID');
    }
}
