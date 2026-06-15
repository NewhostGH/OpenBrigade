<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\ObPhoto;
use App\Models\ObPhotoAlbum;
use App\Services\DocumentService;
use App\Services\PhotoService;
use App\Services\SectionScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Section photo-album library — browse albums and photos.
 * Business logic lives in {@see PhotoService}; this controller stays thin.
 *
 * Permissions: 44 = view, 47 = manage (create / upload / delete).
 */
class PhotoController extends Controller
{
    public function __construct(
        private readonly PhotoService $photos,
        private readonly SectionScopeService $sectionScope,
        private readonly DocumentService $documentService,
    ) {}

    /** Album grid — one card per album with cover photo and count. */
    public function index(Request $request): View
    {
        $sectionId = $this->resolveSectionId($request);
        $canManage = $request->user()->hasPermission((int) config('photos.feature_manage'));

        return view('photo.index', [
            'albums' => $this->photos->albums($sectionId),
            'sectionId' => $sectionId,
            'canManage' => $canManage,
            'imageFolders' => $canManage ? $this->photos->imageFolders($sectionId) : collect(),
        ]);
    }

    /** Photo grid for one album. */
    public function albumShow(Request $request, ObPhotoAlbum $album): View
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $canManage = $request->user()->hasPermission((int) config('photos.feature_manage'));

        return view('photo.album', [
            'album' => $album,
            'photos' => $album->photos()->orderBy('sort_order')->orderBy('id')->get(),
            'sectionId' => (int) $album->S_ID,
            'canManage' => $canManage,
        ]);
    }

    /** Create a new album. */
    public function albumStore(Request $request): RedirectResponse
    {
        $sectionId = $this->resolveSectionId($request);
        $v = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $album = $this->photos->createAlbum($sectionId, $v['name'], $v['description'] ?? null, (int) $request->user()->P_ID);

        return redirect()->route('photo.album', $album)->with('success', 'Album créé.');
    }

    /** Rename / update description. */
    public function albumUpdate(Request $request, ObPhotoAlbum $album): RedirectResponse
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $v = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->photos->updateAlbum($album, $v['name'], $v['description'] ?? null);

        return back()->with('success', 'Album mis à jour.');
    }

    /** Delete an album and all its photos. */
    public function albumDestroy(ObPhotoAlbum $album): RedirectResponse
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $this->photos->deleteAlbum($album);

        return redirect()->route('photo.index')->with('success', 'Album supprimé.');
    }

    /** Upload one or more photos into an album. */
    public function photoStore(Request $request, ObPhotoAlbum $album): RedirectResponse
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $extensions = implode(',', PhotoService::SUPPORTED_EXTENSIONS);
        $maxKb = PhotoService::MAX_SIZE_MB * 1024;

        $request->validate([
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['image', "mimes:{$extensions}", "max:{$maxKb}"],
        ]);

        foreach ($request->file('photos') as $file) {
            $this->photos->storeUpload((int) $album->S_ID, $album, $file, (int) $request->user()->P_ID);
        }

        return back()->with('success', count($request->file('photos')).' photo(s) ajoutée(s).');
    }

    /** Update a photo caption. */
    public function photoUpdate(Request $request, ObPhoto $photo): RedirectResponse
    {
        abort_unless($this->sectionScope->allows((int) $photo->S_ID), 403);

        $v = $request->validate(['caption' => ['nullable', 'string', 'max:255']]);
        $this->photos->updateCaption($photo, $v['caption'] ?? null);

        return back()->with('success', 'Légende enregistrée.');
    }

    /** Persist a new photo order — expects JSON body {"ids":[…]}. */
    public function reorder(Request $request, ObPhotoAlbum $album): JsonResponse
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $v = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $this->photos->reorder($album, $v['ids']);

        return response()->json(['ok' => true]);
    }

    /** Bulk-delete selected photos from an album (perm 47). */
    public function photoBulkDestroy(Request $request, ObPhotoAlbum $album): RedirectResponse
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $v = $request->validate([
            'photo_ids' => ['required', 'array', 'min:1'],
            'photo_ids.*' => ['integer'],
        ]);

        $deleted = 0;
        foreach ($v['photo_ids'] as $rawId) {
            $photo = ObPhoto::where('id', (int) $rawId)->where('album_id', $album->id)->first();
            if ($photo) {
                $this->photos->deletePhoto($photo);
                $deleted++;
            }
        }

        return back()->with('success', "{$deleted} photo(s) supprimée(s).");
    }

    /** Delete one photo. */
    public function photoDestroy(ObPhoto $photo): RedirectResponse
    {
        abort_unless($this->sectionScope->allows((int) $photo->S_ID), 403);

        $album = $photo->album;
        $this->photos->deletePhoto($photo);

        return redirect()->route('photo.album', $album)->with('success', 'Photo supprimée.');
    }

    /** Set a photo as the album cover. */
    public function setCover(Request $request, ObPhotoAlbum $album): RedirectResponse
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $v = $request->validate(['photo_id' => ['required', 'integer', 'exists:ob_photo,id']]);
        $photo = ObPhoto::findOrFail((int) $v['photo_id']);

        $this->photos->setCover($album, $photo);

        return back()->with('success', 'Couverture mise à jour.');
    }

    /** POST: create albums automatically from selected document-library folders. */
    public function autoAlbumCreate(Request $request): RedirectResponse
    {
        $sectionId = $this->resolveSectionId($request);
        $v = $request->validate([
            'folder_ids' => ['required', 'array', 'min:1'],
            'folder_ids.*' => ['integer'],
        ]);

        $userId = (int) $request->user()->P_ID;
        $created = 0;
        $imported = 0;

        foreach ($v['folder_ids'] as $rawId) {
            $folderId = (int) $rawId;

            $docs = Document::library()
                ->where('S_ID', $sectionId)
                ->where('DF_ID', $folderId)
                ->with('folder')
                ->get()
                ->filter(fn (Document $d) => in_array(
                    strtolower(pathinfo($d->D_NAME, PATHINFO_EXTENSION)),
                    PhotoService::IMAGE_EXTENSIONS,
                    true,
                ));

            if ($docs->isEmpty()) {
                continue;
            }

            $first = $docs->first();
            $folder = $first->folder;
            $albumName = $folder instanceof DocumentFolder ? $folder->DF_NAME : 'Racine';

            $album = $this->photos->createAlbum($sectionId, $albumName, null, $userId);
            $created++;

            foreach ($docs as $doc) {
                $srcPath = $this->documentService->filePath(
                    (int) $doc->S_ID, (int) $doc->DF_ID, $doc->D_NAME
                );

                if (! file_exists($srcPath)) {
                    continue;
                }

                $this->photos->importFromPath($srcPath, $sectionId, $album, $doc->D_NAME, $userId);
                $imported++;
            }
        }

        return redirect()->route('photo.index', ['section' => $sectionId])
            ->with('success', "{$created} album(s) créé(s) avec {$imported} photo(s) importée(s).");
    }

    /** JSON list of image documents available to import from the doc library. */
    public function pickDocuments(ObPhotoAlbum $album): JsonResponse
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $docs = $this->photos->imageDocuments((int) $album->S_ID)
            ->map(function (Document $d): array {
                $folder = $d->folder;

                return [
                    'id' => $d->D_ID,
                    'name' => $d->D_NAME,
                    'folder' => $folder instanceof DocumentFolder ? $folder->DF_NAME : '',
                    'thumb_url' => route('document.download', $d->D_ID),
                ];
            });

        return response()->json($docs->values());
    }

    /** Import selected document-library images into an album. */
    public function storeFromDocuments(Request $request, ObPhotoAlbum $album): RedirectResponse
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $v = $request->validate([
            'doc_ids' => ['required', 'array', 'min:1'],
            'doc_ids.*' => ['integer'],
        ]);

        $sectionId = (int) $album->S_ID;
        $userId = (int) $request->user()->P_ID;
        $count = 0;

        foreach ($v['doc_ids'] as $docId) {
            $doc = Document::library()
                ->where('S_ID', $sectionId)
                ->where('D_ID', (int) $docId)
                ->first();

            if (! $doc) {
                continue;
            }

            $ext = strtolower(pathinfo($doc->D_NAME, PATHINFO_EXTENSION));
            if (! in_array($ext, PhotoService::IMAGE_EXTENSIONS, true)) {
                continue;
            }

            $srcPath = $this->documentService->filePath(
                (int) $doc->S_ID, (int) $doc->DF_ID, $doc->D_NAME
            );

            if (! file_exists($srcPath)) {
                continue;
            }

            $this->photos->importFromPath($srcPath, $sectionId, $album, $doc->D_NAME, $userId);
            $count++;
        }

        return back()->with(
            $count > 0 ? 'success' : 'warning',
            $count > 0
                ? "{$count} photo(s) importée(s) depuis la bibliothèque."
                : 'Aucun fichier importé (fichiers introuvables ou format non supporté).',
        );
    }

    /** Serve a photo file — auth + section-scope enforced, no direct URL guessing. */
    public function photoServe(ObPhoto $photo): StreamedResponse
    {
        abort_unless($this->sectionScope->allows((int) $photo->S_ID), 403);
        abort_unless(Storage::disk('local')->exists($photo->diskPath()), 404);

        return Storage::disk('local')->response($photo->diskPath());
    }

    /** Download a single photo as an attachment. */
    public function photoDownload(ObPhoto $photo): StreamedResponse
    {
        abort_unless($this->sectionScope->allows((int) $photo->S_ID), 403);
        abort_unless(Storage::disk('local')->exists($photo->diskPath()), 404);

        return Storage::disk('local')->download($photo->diskPath(), $photo->filename);
    }

    /** Download all photos in an album as a ZIP archive. */
    public function albumDownload(ObPhotoAlbum $album): StreamedResponse
    {
        abort_unless($this->sectionScope->allows((int) $album->S_ID), 403);

        $photos = $album->photos()->orderBy('sort_order')->orderBy('id')->get();
        abort_if($photos->isEmpty(), 404);

        $zipPath = tempnam(sys_get_temp_dir(), 'ob_album_').'.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $disk = Storage::disk('local');
        $seen = [];
        foreach ($photos as $photo) {
            if (! $disk->exists($photo->diskPath())) {
                continue;
            }
            $name = $photo->filename;
            $base = pathinfo($name, PATHINFO_FILENAME);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $i = 1;
            while (in_array($name, $seen, true)) {
                $name = "{$base}_{$i}.{$ext}";
                $i++;
            }
            $seen[] = $name;
            $zip->addFile($disk->path($photo->diskPath()), $name);
        }
        $zip->close();

        $albumSlug = Str::slug($album->name) ?: 'album';

        return response()->streamDownload(static function () use ($zipPath) {
            readfile($zipPath);
            @unlink($zipPath);
        }, "{$albumSlug}.zip", ['Content-Type' => 'application/zip']);
    }

    private function resolveSectionId(Request $request): int
    {
        $requested = (int) $request->integer('section');
        if ($requested > 0 && $this->sectionScope->canChoose($requested)) {
            return $requested;
        }

        return (int) ($this->sectionScope->defaultSectionId() ?? $request->user()->P_SECTION);
    }
}
