<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObPersonnelGroup extends Model
{
    protected $table = 'ob_personnel_group';

    protected $fillable = ['person_id', 'group_id'];

    protected $casts = [
        'person_id' => 'integer',
        'group_id' => 'integer',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'person_id', 'P_ID');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ObGroup::class, 'group_id');
    }
}
