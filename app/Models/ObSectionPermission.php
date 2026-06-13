<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A feature (F_ID) refused by a section's ceiling (deny-list). A row denies the
 * feature for the section and all its descendants; absence of rows = nothing
 * denied. Table: ob_section_permission.
 */
class ObSectionPermission extends Model
{
    protected $table = 'ob_section_permission';

    protected $fillable = ['section_id', 'feature_id'];

    protected $casts = [
        'section_id' => 'integer',
        'feature_id' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'S_ID');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(LegacyFeature::class, 'feature_id', 'F_ID');
    }
}
