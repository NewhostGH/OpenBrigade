<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legacy table: document_folder. Primary key: DF_ID.
 *
 * A folder in a section's document library. DF_PARENT = 0 for a root folder.
 * Unique on (S_ID, DF_PARENT, DF_NAME).
 *
 * @property int $DF_ID
 * @property int $S_ID
 * @property int $DF_PARENT
 * @property string $DF_NAME
 * @property string|null $TD_CODE
 * @property int $DF_CREATED_BY
 * @property string $DF_CREATED_DATE
 */
class DocumentFolder extends Model
{
    protected $table = 'document_folder';

    protected $primaryKey = 'DF_ID';

    public $timestamps = false;

    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'DF_PARENT', 'DF_ID');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'DF_PARENT', 'DF_ID');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeDocument::class, 'TD_CODE', 'TD_CODE');
    }
}
