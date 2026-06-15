<?php

namespace App\Services;

use App\Models\ObPhoto;
use App\Models\ObPhotoAlbum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Photo-album business logic — single source of truth for album/photo
 * listing, file storage, and deletion. Keeps {@see PhotoController} thin.
 *
 * Files are stored in the public disk under photos/{S_ID}/{album_id}/{name}
 * and served directly by the web server via the storage:link symlink.
 */
class PhotoService implements ServiceInterface
{
    /** Supported upload extensions (lower-case). */
    public const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** Maximum upload size in MB. */
    public const MAX_SIZE_MB = 20;

    /** All albums for a section, newest first, with cover-photo eager-loaded. */
    public function albums(int $sectionId): Collection
    {
        return ObPhotoAlbum::with('coverPhoto')
            ->withCount('photos')
            ->where('S_ID', $sectionId)
            ->orderByDesc('created_at')
            ->get();
    }

    /** Create a new empty album. */
    public function createAlbum(int $sectionId, string $name, ?string $description, int $userId): ObPhotoAlbum
    {
        return ObPhotoAlbum::create([
            'S_ID' => $sectionId,
            'name' => trim($name),
            'description' => $description ? trim($description) : null,
            'created_by' => $userId,
        ]);
    }

    /** Rename / update description of an album. */
    public function updateAlbum(ObPhotoAlbum $album, string $name, ?string $description): void
    {
        $album->update([
            'name' => trim($name),
            'description' => $description ? trim($description) : null,
        ]);
    }

    /** Delete an album and all its photos (files + rows). */
    public function deleteAlbum(ObPhotoAlbum $album): void
    {
        foreach ($album->photos as $photo) {
            /** @var ObPhoto $photo */
            $this->deletePhotoFile($photo);
        }
        // Rows cascade via FK; but we may need to clear cover_photo_id first
        // to avoid FK constraint issue on some engines.
        $album->update(['cover_photo_id' => null]);
        $album->delete();
    }

    /** Store one uploaded image and create the photo row. */
    public function storeUpload(int $sectionId, ObPhotoAlbum $album, UploadedFile $file, int $userId): ObPhoto
    {
        $name = $this->sanitizeFilename($file->getClientOriginalName());
        $path = "photos/{$sectionId}/{$album->id}";
        Storage::disk('local')->putFileAs($path, $file, $name);

        $photo = ObPhoto::create([
            'album_id' => $album->id,
            'S_ID' => $sectionId,
            'filename' => $name,
            'created_by' => $userId,
        ]);

        // Auto-set first uploaded photo as the album cover.
        if ($album->cover_photo_id === null) {
            $album->update(['cover_photo_id' => $photo->id]);
        }

        return $photo;
    }

    /** Update a photo's caption. */
    public function updateCaption(ObPhoto $photo, ?string $caption): void
    {
        $photo->update(['caption' => $caption ? trim($caption) : null]);
    }

    /** Set the album cover to the given photo. */
    public function setCover(ObPhotoAlbum $album, ObPhoto $photo): void
    {
        $album->update(['cover_photo_id' => $photo->id]);
    }

    /** Delete one photo's file and row; fix the album cover if needed. */
    public function deletePhoto(ObPhoto $photo): void
    {
        $album = $photo->album;
        $this->deletePhotoFile($photo);
        $photo->delete();

        // If this was the cover, promote the next available photo.
        if ($album instanceof ObPhotoAlbum && (int) $album->cover_photo_id === (int) $photo->id) {
            $next = $album->photos()->first();
            $album->update(['cover_photo_id' => $next instanceof ObPhoto ? $next->id : null]);
        }
    }

    /** Sanitise an uploaded filename: strip unsafe chars, keep extension. */
    public function sanitizeFilename(string $name): string
    {
        $name = basename(str_replace('\\', '', $name));

        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $name) ?: 'photo';
    }

    private function deletePhotoFile(ObPhoto $photo): void
    {
        if (Storage::disk('local')->exists($photo->diskPath())) {
            Storage::disk('local')->delete($photo->diskPath());
        }
    }
}
