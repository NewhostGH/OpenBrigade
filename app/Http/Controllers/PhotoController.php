<?php

namespace App\Http\Controllers;

use App\Models\ObPhoto;
use App\Models\ObPhotoAlbum;
use App\Services\PhotoService;
use App\Services\SectionScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /** Serve a photo file — auth + section-scope enforced, no direct URL guessing. */
    public function photoServe(ObPhoto $photo): StreamedResponse
    {
        abort_unless($this->sectionScope->allows((int) $photo->S_ID), 403);
        abort_unless(Storage::disk('local')->exists($photo->diskPath()), 404);

        return Storage::disk('local')->response($photo->diskPath());
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
