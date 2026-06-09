<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObPersonnelSection extends Model
{
    protected $table = 'ob_personnel_section';

    protected $fillable = ['person_id', 'section_id'];

    protected $casts = [
        'person_id' => 'integer',
        'section_id' => 'integer',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'person_id', 'P_ID');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'S_ID');
    }
}
