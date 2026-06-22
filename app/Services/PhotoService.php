<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFolder;
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

    /** Subset of SUPPORTED_EXTENSIONS used for document-picker filtering. */
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** Maximum upload size in MB. */
    public const MAX_SIZE_MB = 20;

    public function __construct(private readonly UploadSecurityService $uploadSecurity) {}

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
        $this->uploadSecurity->assertSafe(
            $file,
            self::SUPPORTED_EXTENSIONS,
            self::MAX_SIZE_MB * 1024,
            'photos',
        );

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
    /**
     * Persist a new photo order for an album.
     *
     * @param  int[]  $orderedIds  Photo IDs in the desired display order.
     */
    public function reorder(ObPhotoAlbum $album, array $orderedIds): void
    {
        $position = 0;
        foreach ($orderedIds as $id) {
            ObPhoto::where('id', (int) $id)
                ->where('album_id', $album->id)
                ->update(['sort_order' => $position++]);
        }
    }

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

    /**
     * Library image documents (jpg/png/gif/webp) for a section, folder eager-loaded.
     *
     * @return Collection<int, Document>
     */
    public function imageDocuments(int $sectionId): Collection
    {
        return Document::library()
            ->where('S_ID', $sectionId)
            ->with('folder')
            ->get()
            ->filter(fn (Document $d) => in_array(
                strtolower(pathinfo($d->D_NAME, PATHINFO_EXTENSION)),
                self::IMAGE_EXTENSIONS,
                true,
            ))
            ->values();
    }

    /**
     * Document library folders (for this section) that contain at least one
     * image file, sorted alphabetically. Used by the auto-album feature.
     *
     * @return Collection<int, array{id: int, name: string, image_count: int}>
     */
    public function imageFolders(int $sectionId): Collection
    {
        $imageDocs = Document::library()
            ->where('S_ID', $sectionId)
            ->with('folder')
            ->get()
            ->filter(fn (Document $d) => in_array(
                strtolower(pathinfo($d->D_NAME, PATHINFO_EXTENSION)),
                self::IMAGE_EXTENSIONS,
                true,
            ));

        $byFolder = [];
        foreach ($imageDocs as $doc) {
            $fid = (int) $doc->DF_ID;
            if (! isset($byFolder[$fid])) {
                $folder = $doc->folder;
                $byFolder[$fid] = [
                    'id' => $fid,
                    'name' => $folder instanceof DocumentFolder ? $folder->DF_NAME : 'Racine',
                    'image_count' => 0,
                ];
            }
            $byFolder[$fid]['image_count']++;
        }

        usort($byFolder, fn (array $a, array $b) => strcmp($a['name'], $b['name']));

        return collect($byFolder);
    }

    /**
     * Copy a file already on disk (e.g. from the document library) into the
     * photo album storage, create the ObPhoto row, and auto-set the cover.
     */
    public function importFromPath(
        string $sourcePath,
        int $sectionId,
        ObPhotoAlbum $album,
        string $filename,
        int $userId,
    ): ObPhoto {
        $name = $this->sanitizeFilename($filename);
        $destDir = storage_path("app/photos/{$sectionId}/{$album->id}");

        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Avoid overwriting an existing file.
        if (file_exists("{$destDir}/{$name}")) {
            $info = pathinfo($name);
            $name = $info['filename'].'_'.time().'.'.($info['extension'] ?? 'jpg');
        }

        copy($sourcePath, "{$destDir}/{$name}");

        $photo = ObPhoto::create([
            'album_id' => $album->id,
            'S_ID' => $sectionId,
            'filename' => $name,
            'created_by' => $userId,
        ]);

        if ($album->cover_photo_id === null) {
            $album->update(['cover_photo_id' => $photo->id]);
        }

        return $photo;
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
