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

/**
 * Legacy table: type_evenement
 * Primary key: TE_CODE (string)
 *
 * Reference catalogue of event types (formation, intervention, …). Read-only
 * lookup data mirrored from the legacy schema.
 *
 * @property string $TE_CODE
 * @property string|null $TE_LIBELLE
 * @property string|null $TE_ICON
 */
class EventType extends Model
{
    protected $table = 'type_evenement';

    protected $primaryKey = 'TE_CODE';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
