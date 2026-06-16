<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A role held by a person, optionally scoped to a section (inherited to child
 * sections). section_id = -1 (SectionScopeService::ALL) means global (no section
 * restriction); 0 is the real root section. Unique on
 * (person_id, section_id, group_id). Table: ob_user_assignment.
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
        'section_id' => -1, // global by default (SectionScopeService::ALL)
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
