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
use Illuminate\Support\Carbon;

/**
 * Legacy table: personnel_cotisation
 * Primary key: PC_ID
 * Represents a membership fee (or refund) record for a personnel member.
 *
 * @property int $PC_ID
 * @property int $P_ID
 * @property string $MONTANT
 * @property bool $REMBOURSEMENT
 * @property Carbon|null $PC_DATE
 */
class Dues extends Model
{
    protected $table = 'personnel_cotisation';

    protected $primaryKey = 'PC_ID';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'PC_DATE' => 'date',
        'REMBOURSEMENT' => 'boolean',
        'MONTANT' => 'decimal:2',
    ];

    /** The personnel member this fee belongs to. */
    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'P_ID', 'P_ID');
    }

    /** The payment type (type_paiement). */
    public function typePaiement(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class, 'TP_ID', 'TP_ID');
    }
}
