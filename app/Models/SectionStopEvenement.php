<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A section event interdiction (legacy `section_stop.php`): blocks an event
 * type (or all types) for a section over a date range. `TE_CODE = 'ALL'` means
 * every type.
 *
 * @property int $SSE_ID
 * @property int $S_ID
 * @property string $TE_CODE
 * @property string|null $START_DATE
 * @property string|null $END_DATE
 * @property string|null $SSE_COMMENT
 * @property int $SSE_ACTIVE
 * @property int|null $SSE_BY
 * @property string|null $SSE_WHEN
 */
class SectionStopEvenement extends Model
{
    protected $table = 'section_stop_evenement';

    protected $primaryKey = 'SSE_ID';

    public $timestamps = false;

    protected $fillable = [
        'S_ID', 'TE_CODE', 'START_DATE', 'END_DATE',
        'SSE_COMMENT', 'SSE_ACTIVE', 'SSE_BY', 'SSE_WHEN',
    ];

    protected $casts = [
        'SSE_ID' => 'integer',
        'S_ID' => 'integer',
        'SSE_ACTIVE' => 'integer',
        'SSE_BY' => 'integer',
        'START_DATE' => 'date',
        'END_DATE' => 'date',
        'SSE_WHEN' => 'datetime',
    ];
}
