<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Legacy table: document. Primary key: D_ID.
 *
 * A file in the section document library or attached to an entity (event,
 * person, vehicle…). The library shows only rows not attached to any entity —
 * see {@see scopeLibrary()}. Physical files live on disk under
 * files_section/{S_ID}/{DF_ID}/{D_NAME}; the resolver is
 * {@see App\Services\DocumentService::filePath()}.
 *
 * @property int $D_ID
 * @property int $S_ID
 * @property string $D_NAME
 * @property string|null $TD_CODE
 * @property int $DS_ID
 * @property int $D_CREATED_BY
 * @property string|null $D_CREATED_DATE
 * @property int $DF_ID
 * @property int $E_CODE
 * @property int $P_ID
 * @property int $V_ID
 * @property int $M_ID
 * @property int $NF_ID
 * @property int $VI_ID
 * @property int $EL_ID
 */
class Document extends Model
{
    protected $table = 'document';

    protected $primaryKey = 'D_ID';

    public $timestamps = false;

    protected $guarded = [];

    /** Library documents: not attached to any entity (event, person, vehicle…). */
    public function scopeLibrary(Builder $query): Builder
    {
        return $query->where('E_CODE', 0)->where('P_ID', 0)->where('V_ID', 0)
            ->where('M_ID', 0)->where('NF_ID', 0)->where('VI_ID', 0)->where('EL_ID', 0);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeDocument::class, 'TD_CODE', 'TD_CODE');
    }

    public function security(): BelongsTo
    {
        return $this->belongsTo(DocumentSecurity::class, 'DS_ID', 'DS_ID');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'DF_ID', 'DF_ID');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'D_CREATED_BY', 'P_ID');
    }
}
