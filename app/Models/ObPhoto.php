<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * One photo inside an album. The file lives at
 * storage/app/public/photos/{S_ID}/{album_id}/{filename}.
 *
 * @property int $id
 * @property int $album_id
 * @property int $S_ID
 * @property string $filename
 * @property string|null $caption
 * @property int $sort_order
 * @property int|null $created_by
 */
class ObPhoto extends Model
{
    protected $table = 'ob_photo';

    protected $fillable = ['album_id', 'S_ID', 'filename', 'caption', 'sort_order', 'created_by'];

    protected $casts = [
        'album_id' => 'integer',
        'S_ID' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(ObPhotoAlbum::class, 'album_id');
    }

    /** Public URL served via the storage:link symlink. */
    public function url(): string
    {
        return Storage::url("photos/{$this->S_ID}/{$this->album_id}/{$this->filename}");
    }

    /** Relative disk path under storage/app/public. */
    public function diskPath(): string
    {
        return "photos/{$this->S_ID}/{$this->album_id}/{$this->filename}";
    }
}
