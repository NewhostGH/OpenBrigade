<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Legacy table: element_facturable
 * Primary key: EF_ID
 * A section's catalogue entry for invoicing (prestation, frais km…) with a
 * default unit price. Invoice lines copy the values, so rows can be deleted
 * without breaking existing invoices.
 *
 * @property int $EF_ID
 * @property string $TEF_CODE
 * @property int $S_ID
 * @property string $EF_NAME
 * @property float $EF_PRICE
 */
class BillableElement extends Model
{
    protected $table = 'element_facturable';

    protected $primaryKey = 'EF_ID';

    public $timestamps = false;

    protected $fillable = ['TEF_CODE', 'S_ID', 'EF_NAME', 'EF_PRICE'];

    protected $casts = [
        'S_ID' => 'integer',
        'EF_PRICE' => 'float',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(BillableElementType::class, 'TEF_CODE', 'TEF_CODE');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'S_ID', 'S_ID');
    }
}
