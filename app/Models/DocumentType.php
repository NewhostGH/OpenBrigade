<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table: type_document. Primary key: TD_CODE (varchar).
 *
 * A document category. TD_SECURITY is the feature id (F_ID) required to view
 * documents of this type (0 = visible to everyone). TD_SYNDICATE flags
 * union-only types.
 *
 * @property string $TD_CODE
 * @property string $TD_LIBELLE
 * @property int $TD_SECURITY
 * @property int $TD_SYNDICATE
 */
class DocumentType extends Model
{
    protected $table = 'type_document';

    protected $primaryKey = 'TD_CODE';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
