<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A role held by a person in a section (section-scoped, inherited to child
 * sections). Global group membership stays on pompier.GP_ID / GP_ID2, so this
 * table holds role assignments only. Table: ob_user_assignment.
 */
class ObUserAssignment extends Model
{
    protected $table = 'ob_user_assignment';

    protected $fillable = ['person_id', 'section_id', 'group_id'];

    protected $casts = [
        'person_id' => 'integer',
        'section_id' => 'integer',
        'group_id' => 'integer',
    ];

    protected $attributes = [
        'section_id' => null,
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'person_id', 'P_ID');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id', 'S_ID');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ObGroup::class, 'group_id');
    }
}
