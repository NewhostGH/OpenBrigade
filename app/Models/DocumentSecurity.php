<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy table: document_security. Primary key: DS_ID.
 *
 * A per-document access level. F_ID is the feature id required to view a
 * document carrying this level (0 = public). Reference data — read-only in the
 * native app.
 *
 * @property int $DS_ID
 * @property string $DS_LIBELLE
 * @property int $F_ID
 */
class DocumentSecurity extends Model
{
    protected $table = 'document_security';

    protected $primaryKey = 'DS_ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}
