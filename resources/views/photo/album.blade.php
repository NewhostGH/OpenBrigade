@extends('layout.app')

@section('title', e($album->name) . ' — Album photos — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Album photos', 'url' => route('photo.index')],
    ['label' => $album->name],
]"/>

<x-ob-toolbar :title="$album->name" :total="count($photos)" total-label="photo">

    <a href="{{ route('photo.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Albums
    </a>

    @if ($canManage)
        <button type="button" class="btn btn-sm btn-primary"
                data-bs-toggle="modal" data-bs-target="#photoUploadModal">
            <i class="fas fa-upload me-1"></i> Ajouter
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="modal" data-bs-target="#albumEditModal">
            <i class="fas fa-pen me-1"></i> Modifier
        </button>
    @endif
</x-ob-toolbar>

@if ($album->description)
    <p class="mx-3 text-muted mb-3" style="font-size:var(--font-size-sm);">{{ $album->description }}</p>
@endif

@if ($photos->isEmpty())
    <div class="mx-3 mt-4 text-muted text-center py-5">
        <i class="fas fa-camera fa-3x mb-3 d-block text-secondary opacity-50"></i>
        Cet album est vide.
        @if ($canManage)
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#photoUploadModal">
                    Ajouter des photos
                </button>
            </div>
        @endif
    </div>
@else
    <div class="mx-3 ob-photo-grid">
        @foreach ($photos as $photo)
            <div class="ob-photo-item" data-photo-id="{{ $photo->id }}">
                <a href="{{ $photo->url() }}"
                   data-toggle="lightbox"
                   data-gallery="album-{{ $album->id }}"
                   data-title="{{ e($photo->caption ?? '') }}"
                   class="ob-photo-thumb-link">
                    <img src="{{ $photo->url() }}" alt="{{ e($photo->caption ?? $photo->filename) }}"
                         class="ob-photo-thumb" loading="lazy">
                    @if ($photo->caption)
                        <span class="ob-photo-caption-overlay">{{ $photo->caption }}</span>
                    @endif
                </a>

                @if ($canManage)
                    <div class="ob-photo-actions">
                        <button type="button" class="btn btn-xs btn-light"
                                title="Légende" data-caption-edit
                                data-id="{{ $photo->id }}"
                                data-caption="{{ $photo->caption }}">
                            <i class="fas fa-quote-right fa-xs"></i>
                        </button>
                        <form method="POST" action="{{ route('photo.cover', $album) }}"
                              class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="photo_id" value="{{ $photo->id }}">
                            <button type="submit" class="btn btn-xs btn-light" title="Définir comme couverture">
                                <i class="fas fa-star fa-xs {{ (int) $album->cover_photo_id === (int) $photo->id ? 'text-warning' : '' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('photo.destroy', $photo) }}"
                              class="d-inline" data-confirm="Supprimer cette photo ?">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif

@if ($canManage)
    {{-- Upload photos --}}
    <div class="modal fade" id="photoUploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('photo.store', $album) }}"
                  enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Ajouter des photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="photoFiles">Fichier(s)</label>
                    <input type="file" id="photoFiles" name="photos[]" class="form-control"
                           multiple accept="{{ implode(',', array_map(fn($e) => '.'.$e, config('photos.supported_extensions'))) }}" required>
                    <div class="form-text">
                        {{ implode(', ', config('photos.supported_extensions')) }} — max {{ config('photos.max_size_mb') }} Mo par fichier.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Envoyer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit album --}}
    <div class="modal fade" id="albumEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('photo.album.update', $album) }}" class="modal-content">
                @csrf @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Modifier l'album</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="albumName">Nom</label>
                        <input type="text" id="albumName" name="name" class="form-control"
                               maxlength="100" value="{{ $album->name }}" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="albumDesc">Description</label>
                        <input type="text" id="albumDesc" name="description" class="form-control"
                               maxlength="500" value="{{ $album->description }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit caption (filled by JS) --}}
    <div class="modal fade" id="captionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <form method="POST" id="captionForm" class="modal-content">
                @csrf @method('PATCH')
                <div class="modal-header py-2">
                    <h5 class="modal-title" style="font-size:var(--font-size-sm);">Légende</h5>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body py-2">
                    <input type="text" id="captionInput" name="caption" class="form-control form-control-sm"
                           maxlength="255" placeholder="Légende de la photo…">
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script type="module">
import Lightbox from 'bs5-lightbox';
const options = { keyboard: true, size: 'xl' };
document.querySelectorAll('[data-toggle="lightbox"]').forEach(function (el) {
    el.addEventListener('click', function (e) {
        e.preventDefault();
        new Lightbox(el, options).show();
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Caption edit modal.
    var captionModalEl = document.getElementById('captionModal');
    var captionForm    = document.getElementById('captionForm');
    var captionInput   = document.getElementById('captionInput');
    var base           = '{{ url("/photo") }}';

    if (captionModalEl) {
        document.querySelectorAll('[data-caption-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                captionForm.action = base + '/' + btn.dataset.id;
                captionInput.value = btn.dataset.caption || '';
                bootstrap.Modal.getOrCreateInstance(captionModalEl).show();
            });
        });
    }

    // Confirm before delete.
    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm)) { e.preventDefault(); }
        });
    });
});
</script>
@endpush
