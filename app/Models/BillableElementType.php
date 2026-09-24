<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table: type_element_facturable
 * Primary key: TEF_CODE (string)
 * Reference list of billable element types (Prestation, Frais Km…).
 */
class BillableElementType extends Model
{
    protected $table = 'type_element_facturable';

    protected $primaryKey = 'TEF_CODE';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
