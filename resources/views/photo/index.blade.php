@extends('layout.app')

@section('title', 'Album photos — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[['label' => 'Album photos']]"/>

<x-ob-toolbar title="Album photos" :total="count($albums)" total-label="album"
    filter-action="{{ route('photo.index') }}" filter-id="photoFilter">

    @if ($canManage)
        <button type="button" class="btn btn-sm btn-primary"
                data-bs-toggle="modal" data-bs-target="#albumCreateModal">
            <i class="fas fa-plus me-1"></i> Nouvel album
        </button>
        @if ($imageFolders->isNotEmpty())
            <button type="button" class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal" data-bs-target="#autoAlbumModal">
                <i class="fas fa-magic me-1"></i> Auto-albums
            </button>
        @endif
    @endif

    <x-slot:filters>
        <x-ob-section-select name="section" :selected="$sectionId" auto-submit/>
    </x-slot:filters>
</x-ob-toolbar>

@if ($albums->isEmpty())
    <div class="mx-3 mt-4 text-muted text-center py-5">
        <i class="fas fa-images fa-3x mb-3 d-block text-secondary opacity-50"></i>
        Aucun album photo.
        @if ($canManage)
            <div class="mt-2 d-flex gap-2 justify-content-center flex-wrap">
                <button class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#albumCreateModal">
                    Créer le premier album
                </button>
                @if ($imageFolders->isNotEmpty())
                    <button class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#autoAlbumModal">
                        <i class="fas fa-magic me-1"></i> Importer depuis la bibliothèque
                    </button>
                @endif
            </div>
        @endif
    </div>
@else
    <div class="mx-3 mt-3 row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-3">
        @foreach ($albums as $album)
            <div class="col">
                <div class="ob-photo-album-card card h-100 shadow-sm">
                    {{-- Cover — full clickable block link --}}
                    <a href="{{ route('photo.album', $album) }}" class="ob-photo-album-cover" tabindex="-1">
                        @if ($album->coverPhoto)
                            <img src="{{ $album->coverPhoto->url() }}" alt="{{ e($album->name) }}"
                                 class="card-img-top ob-photo-cover-img">
                        @else
                            <div class="ob-photo-cover-placeholder card-img-top">
                                <i class="fas fa-images fa-2x text-secondary opacity-50"></i>
                            </div>
                        @endif
                    </a>
                    <div class="card-body p-2">
                        {{-- Plain link — NOT stretched-link so footer buttons remain clickable --}}
                        <a href="{{ route('photo.album', $album) }}" class="ob-photo-album-name text-decoration-none">
                            {{ $album->name }}
                        </a>
                        @if ($album->description)
                            <p class="text-muted mb-0 mt-1" style="font-size:var(--font-size-xs);line-height:1.3;">
                                {{ Str::limit($album->description, 60) }}
                            </p>
                        @endif
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center p-2">
                        <span class="ob-badge ob-badge-archive">
                            <i class="fas fa-image fa-xs me-1"></i>{{ $album->photos_count }}
                        </span>
                        @if ($canManage)
                            <span class="ob-photo-album-actions">
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary py-0 px-2"
                                        title="Modifier l'album"
                                        data-album-edit
                                        data-id="{{ $album->id }}"
                                        data-name="{{ $album->name }}"
                                        data-desc="{{ $album->description }}">
                                    <i class="fas fa-pen fa-xs"></i>
                                </button>
                                <form method="POST" action="{{ route('photo.album.destroy', $album) }}"
                                      data-confirm="Supprimer l'album « {{ $album->name }} » et toutes ses photos ?">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger py-0 px-2"
                                            title="Supprimer l'album">
                                        <i class="fas fa-trash fa-xs"></i>
                                    </button>
                                </form>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if ($canManage)
    {{-- Create album --}}
    <div class="modal fade" id="albumCreateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('photo.album.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="section" value="{{ $sectionId }}">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nouvel album</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="albumCreateName">Nom</label>
                        <input type="text" id="albumCreateName" name="name" class="form-control"
                               maxlength="100" required autofocus>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="albumCreateDesc">Description <span class="text-muted">(optionnel)</span></label>
                        <input type="text" id="albumCreateDesc" name="description" class="form-control" maxlength="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit album (filled by JS) --}}
    <div class="modal fade" id="albumEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="albumEditForm" class="modal-content">
                @csrf @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Modifier l'album</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="albumEditName">Nom</label>
                        <input type="text" id="albumEditName" name="name" class="form-control" maxlength="100" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="albumEditDesc">Description</label>
                        <input type="text" id="albumEditDesc" name="description" class="form-control" maxlength="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    @if ($imageFolders->isNotEmpty())
        {{-- Auto-album from document folders --}}
        <div class="modal fade" id="autoAlbumModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form method="POST" action="{{ route('photo.auto-albums') }}" class="modal-content">
                    @csrf
                    <input type="hidden" name="section" value="{{ $sectionId }}">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-magic me-2"></i>Auto-albums depuis la bibliothèque</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
                            Chaque dossier sélectionné sera converti en album photo avec toutes ses images.
                        </p>
                        <div class="list-group">
                            @foreach ($imageFolders as $folder)
                                <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2">
                                    <input class="form-check-input flex-shrink-0 mt-0" type="checkbox"
                                           name="folder_ids[]" value="{{ $folder['id'] }}">
                                    <span>
                                        <i class="fas fa-folder text-warning me-2"></i>
                                        <strong>{{ $folder['name'] }}</strong>
                                        <span class="ms-2 text-muted" style="font-size:var(--font-size-xs);">
                                            {{ $folder['image_count'] }} image{{ $folder['image_count'] > 1 ? 's' : '' }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-magic me-1"></i> Créer les albums
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Album edit modal: fill form from data-* attributes.
    var editModalEl = document.getElementById('albumEditModal');
    var editForm    = document.getElementById('albumEditForm');
    var base        = '{{ url("/photos") }}';

    document.querySelectorAll('[data-album-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            editForm.action = base + '/' + btn.dataset.id;
            document.getElementById('albumEditName').value = btn.dataset.name || '';
            document.getElementById('albumEditDesc').value = btn.dataset.desc  || '';
            bootstrap.Modal.getOrCreateInstance(editModalEl).show();
        });
    });

    // Confirm before delete.
    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm)) { e.preventDefault(); }
        });
    });
});
</script>
@endpush
