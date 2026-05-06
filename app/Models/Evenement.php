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

use App\Models\Pivots\EvenementParticipation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legacy table: evenement
 * Primary key: E_CODE
 */
class Evenement extends Model
{
    use HasFactory;

    protected $table = 'evenement';

    protected $primaryKey = 'E_CODE';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'E_CREATE_DATE'          => 'datetime',
        'E_DATE_ENVOI_CONVENTION' => 'date',
        'E_CLOSED'               => 'boolean',
        'E_CANCELED'             => 'boolean',
        'E_OPEN_TO_EXT'          => 'boolean',
        'E_ALLOW_REINFORCEMENT'  => 'boolean',
        'E_VISIBLE_INSIDE'       => 'boolean',
        'E_VISIBLE_OUTSIDE'      => 'boolean',
        'E_EXTERIEUR'            => 'boolean',
        'E_COLONNE_RENFORT'      => 'boolean',
        'E_ANOMALIE'             => 'boolean',
        'E_REPAS'                => 'boolean',
        'E_TRANSPORT'            => 'boolean',
        'E_FLAG1'                => 'boolean',
    ];

    /** The section this event belongs to. */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'S_ID', 'S_ID');
    }

    /** The primary lead person for this event. */
    public function chef(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'E_CHEF', 'P_ID');
    }

    /** Parent event (for child events). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'E_PARENT', 'E_CODE');
    }

    /** Child events. */
    public function children(): HasMany
    {
        return $this->hasMany(Evenement::class, 'E_PARENT', 'E_CODE');
    }

    /** Personnel participating in this event. */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Personnel::class, 'evenement_participation', 'E_CODE', 'P_ID')
            ->using(EvenementParticipation::class)
            ->withPivot([
                'EH_ID', 'EP_DATE', 'EP_BY', 'TP_ID', 'EP_COMMENT',
                'EP_DATE_DEBUT', 'EP_DATE_FIN', 'EP_DEBUT', 'EP_FIN',
                'EP_DUREE', 'EP_ABSENT', 'EP_EXCUSE', 'EP_PAID', 'EP_KM',
                'EE_ID', 'EP_ASTREINTE',
            ]);
    }

    /** Vehicles deployed on this event. */
    public function vehicules(): BelongsToMany
    {
        return $this->belongsToMany(Vehicule::class, 'evenement_vehicule', 'E_CODE', 'V_ID')
            ->withPivot([
                'EH_ID', 'EV_KM', 'EV_DATE_DEBUT', 'EV_DATE_FIN',
                'EV_DEBUT', 'EV_FIN', 'EV_DUREE', 'EE_ID', 'TFV_ID',
            ]);
    }

    /** Materials used in this event. */
    public function materiels(): BelongsToMany
    {
        return $this->belongsToMany(Materiel::class, 'evenement_materiel', 'E_CODE', 'MA_ID')
            ->withPivot('EM_NB', 'EE_ID');
    }

    /** Time slots (horaires) for this event. */
    public function horaires(): HasMany
    {
        return $this->hasMany(EvenementHoraire::class, 'E_CODE', 'E_CODE');
    }

    /** Consumables used during this event. */
    public function consommables(): HasMany
    {
        return $this->hasMany(EvenementConsommable::class, 'E_CODE', 'E_CODE');
    }

    /** Co-leads of this event. */
    public function chefs(): BelongsToMany
    {
        return $this->belongsToMany(Personnel::class, 'evenement_chef', 'E_CODE', 'E_CHEF');
    }
}
