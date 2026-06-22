@extends('layout.app')

@section('title', __('photo.title_albums') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[['label' => __('photo.breadcrumb_albums')]]"/>

<x-ob-toolbar title="{{ __('photo.title_albums') }}" :total="count($albums)" total-label="{{ __('photo.total_label_album') }}"
    filter-action="{{ route('photo.index') }}" filter-id="photoFilter">

    @if ($canManage)
        <button type="button" class="btn btn-sm btn-primary"
                data-bs-toggle="modal" data-bs-target="#albumCreateModal">
            <i class="fas fa-plus me-1"></i> {{ __('photo.btn_new_album') }}
        </button>
        @if ($imageFolders->isNotEmpty())
            <button type="button" class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal" data-bs-target="#autoAlbumModal">
                <i class="fas fa-magic me-1"></i> {{ __('photo.btn_auto_albums') }}
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
        {{ __('photo.empty_albums') }}
        @if ($canManage)
            <div class="mt-2 d-flex gap-2 justify-content-center flex-wrap">
                <button class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#albumCreateModal">
                    {{ __('photo.btn_create_first') }}
                </button>
                @if ($imageFolders->isNotEmpty())
                    <button class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#autoAlbumModal">
                        <i class="fas fa-magic me-1"></i> {{ __('photo.btn_import_library') }}
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
                                        title="{{ __('photo.album_edit_title') }}"
                                        data-album-edit
                                        data-id="{{ $album->id }}"
                                        data-name="{{ $album->name }}"
                                        data-desc="{{ $album->description }}">
                                    <i class="fas fa-pen fa-xs"></i>
                                </button>
                                <form method="POST" action="{{ route('photo.album.destroy', $album) }}"
                                      data-confirm="{{ __('photo.confirm_delete_album', ['name' => $album->name]) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger py-0 px-2"
                                            title="{{ __('photo.album_delete_title') }}">
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
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>{{ __('photo.modal_create_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('photo.modal_close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="albumCreateName">{{ __('photo.label_name') }}</label>
                        <input type="text" id="albumCreateName" name="name" class="form-control"
                               maxlength="100" required autofocus>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="albumCreateDesc">{!! __('photo.label_description_opt') !!}</label>
                        <input type="text" id="albumCreateDesc" name="description" class="form-control" maxlength="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('photo.btn_create') }}</button>
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
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>{{ __('photo.modal_edit_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('photo.modal_close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="albumEditName">{{ __('photo.label_name') }}</label>
                        <input type="text" id="albumEditName" name="name" class="form-control" maxlength="100" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="albumEditDesc">{{ __('photo.label_description') }}</label>
                        <input type="text" id="albumEditDesc" name="description" class="form-control" maxlength="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('photo.btn_save') }}</button>
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
                        <h5 class="modal-title"><i class="fas fa-magic me-2"></i>{{ __('photo.auto_modal_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('photo.modal_close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3" style="font-size:var(--font-size-sm);">
                            {{ __('photo.auto_modal_desc') }}
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
                                            {{ trans_choice('photo.image_count', $folder['image_count'], ['count' => $folder['image_count']]) }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-magic me-1"></i> {{ __('photo.btn_create_albums') }}
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
