<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One photo album, section-scoped.
 *
 * @property int $id
 * @property int $S_ID
 * @property string $name
 * @property string|null $description
 * @property int|null $cover_photo_id
 * @property int|null $created_by
 */
class ObPhotoAlbum extends Model
{
    protected $table = 'ob_photo_album';

    protected $fillable = ['S_ID', 'name', 'description', 'cover_photo_id', 'created_by'];

    protected $casts = [
        'S_ID' => 'integer',
        'cover_photo_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(ObPhoto::class, 'album_id')->orderBy('sort_order')->orderBy('id');
    }

    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(ObPhoto::class, 'cover_photo_id');
    }
}
