<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legacy table: type_paiement
 * Primary key: TP_ID
 * Represents a payment method (chèque, virement, espèces…).
 */
class PaymentType extends Model
{
    protected $table = 'type_paiement';

    protected $primaryKey = 'TP_ID';

    public $timestamps = false;

    protected $guarded = [];

    public function cotisations(): HasMany
    {
        return $this->hasMany(Dues::class, 'TP_ID', 'TP_ID');
    }
}
