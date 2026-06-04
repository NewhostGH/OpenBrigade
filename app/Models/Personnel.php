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

use App\Models\Concerns\HasAvatar;
use App\Models\Pivots\EvenementParticipation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legacy table: pompier
 * Primary key: P_ID
 */
class Personnel extends Model
{
    use HasAvatar;
    use HasFactory;

    protected $table = 'pompier';

    protected $primaryKey = 'P_ID';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'P_BIRTHDATE'      => 'date',
        'P_DATE_ENGAGEMENT' => 'date',
        'P_FIN'            => 'date',
        'P_LAST_CONNECT'   => 'datetime',
        'P_CREATE_DATE'    => 'date',
        'P_LICENCE_DATE'   => 'date',
        'P_LICENCE_EXPIRY' => 'date',
        'P_MDP_EXPIRY'     => 'date',
        'P_ACCEPT_DATE'    => 'datetime',
        'P_ACCEPT_DATE2'   => 'datetime',
        'P_HIDE'           => 'boolean',
        'P_NOSPAM'         => 'boolean',
        'NPAI'             => 'boolean',
        'SUSPENDU'         => 'boolean',
        'GP_FLAG1'         => 'boolean',
        'GP_FLAG2'         => 'boolean',
    ];

    /** The section this person is directly attached to. */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'P_SECTION', 'S_ID');
    }

    /** The primary permission group of this person. */
    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, 'GP_ID', 'GP_ID');
    }

    /** The secondary permission group of this person (GP_ID2). */
    public function groupe2(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, 'GP_ID2', 'GP_ID');
    }

    /** All sections this person holds a role in. */
    public function sectionRoles(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'section_role', 'P_ID', 'S_ID')
            ->withPivot('GP_ID', 'UPDATE_DATE');
    }

    /** Events this person participated in. */
    public function evenements(): BelongsToMany
    {
        return $this->belongsToMany(Evenement::class, 'evenement_participation', 'P_ID', 'E_CODE')
            ->using(EvenementParticipation::class)
            ->withPivot([
                'EH_ID', 'EP_DATE', 'EP_BY', 'TP_ID', 'EP_COMMENT',
                'EP_DATE_DEBUT', 'EP_DATE_FIN', 'EP_DEBUT', 'EP_FIN',
                'EP_DUREE', 'EP_ABSENT', 'EP_EXCUSE', 'EP_PAID', 'EP_KM',
                'EE_ID', 'EP_ASTREINTE',
            ]);
    }

    /** Competencies / qualifications of this person. */
    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class, 'P_ID', 'P_ID');
    }

    /** Membership fee records for this person. */
    public function cotisations(): HasMany
    {
        return $this->hasMany(Cotisation::class, 'P_ID', 'P_ID');
    }

    // ── Derived attributes & lookups (SSOT) ──────────────────────────────────

    /** Membership état: Bloqué (GP_ID -1) > Archivé (old member) > Actif. */
    public function getEtatAttribute(): string
    {
        if ((int) $this->GP_ID === -1) {
            return 'Bloqué';
        }

        return (int) $this->P_OLD_MEMBER > 0 ? 'Archivé' : 'Actif';
    }

    /** Net cotisation total (reimbursements counted negative). */
    public function getCotisNetAttribute(): float
    {
        return (float) $this->cotisations->sum(
            fn ($c) => $c->REMBOURSEMENT ? -abs((float) $c->MONTANT) : (float) $c->MONTANT
        );
    }

    /** [label, css-class] for this person's statut badge (config-driven). */
    public function statutBadge(): array
    {
        $labels  = config('personnel.statuts');
        $classes = config('personnel.statut_badge_class');
        $code    = $this->P_STATUT;

        return [$labels[$code] ?? $code, $classes[$code] ?? $classes['INT']];
    }

    /**
     * value => [label, class] map for every styled statut, as expected by
     * <x-ob-table>'s badgeMap. Built from the config labels + classes so
     * labels are never restated.
     */
    public static function statutBadgeMap(): array
    {
        $labels  = config('personnel.statuts');
        $map     = [];
        foreach (config('personnel.statut_badge_class') as $code => $class) {
            $map[$code] = [$labels[$code] ?? $code, $class];
        }

        return $map;
    }

    /** [label, css-class] for this person's état badge (config-driven). */
    public function etatBadge(): array
    {
        return config('personnel.etat_badges')[$this->etat];
    }

    /** Civility display prefix (M. / Mme / …), or empty string. */
    public function civiliteLabel(): string
    {
        return config('personnel.civilites')[(int) $this->P_CIVILITE] ?? '';
    }
}
