@extends('layout.app')

@section('title', e($album->name) . ' — ' . __('photo.title_albums') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('photo.breadcrumb_albums'), 'url' => route('photo.index')],
    ['label' => $album->name],
]"/>

<x-ob-toolbar :title="$album->name" :total="count($photos)" total-label="{{ __('photo.total_label_photo') }}">

    <a href="{{ route('photo.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> {{ __('photo.btn_back_albums') }}
    </a>

    @if (!$photos->isEmpty())
        <a href="{{ route('photo.album.download', $album) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-file-zipper me-1"></i> {{ __('photo.btn_download_album') }}
        </a>
    @endif

    @if ($canManage)
        @if (!$photos->isEmpty())
            <button type="button" class="btn btn-sm btn-outline-secondary" id="selectModeToggle">
                <i class="fas fa-check-square me-1"></i> {{ __('photo.btn_select') }}
            </button>
        @endif
        <button type="button" class="btn btn-sm btn-primary"
                data-bs-toggle="modal" data-bs-target="#photoAddModal">
            <i class="fas fa-plus me-1"></i> {{ __('photo.btn_add_photos') }}
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="modal" data-bs-target="#albumEditModal">
            <i class="fas fa-pen me-1"></i> {{ __('photo.btn_edit_album') }}
        </button>
    @endif
</x-ob-toolbar>

@if ($album->description)
    <p class="mx-3 text-muted mb-3" style="font-size:var(--font-size-sm);">{{ $album->description }}</p>
@endif

@if ($photos->isEmpty())
    <div class="mx-3 mt-4 text-muted text-center py-5">
        <i class="fas fa-camera fa-3x mb-3 d-block text-secondary opacity-50"></i>
        {{ __('photo.empty_album') }}
        @if ($canManage)
            <div class="mt-2">
                <button class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal" data-bs-target="#photoAddModal">
                    {{ __('photo.btn_add_first_photo') }}
                </button>
            </div>
        @endif
    </div>
@else
    <div class="mx-3 ob-photo-grid">
        @foreach ($photos as $photo)
            <div class="ob-photo-item {{ (int) $album->cover_photo_id === (int) $photo->id ? 'ob-photo-item--cover' : '' }}"
                 data-photo-id="{{ $photo->id }}"
                 @if ($canManage) draggable="true" @endif>
                <a href="{{ $photo->url() }}"
                   data-lb-src="{{ $photo->url() }}"
                   data-lb-gallery="album-{{ $album->id }}"
                   data-lb-title="{{ $photo->caption ?? $photo->filename }}"
                   class="ob-photo-thumb-link">
                    <img src="{{ $photo->url() }}" alt="{{ e($photo->caption ?? $photo->filename) }}"
                         class="ob-photo-thumb" loading="lazy">
                </a>

                @if ($photo->caption)
                    <span class="ob-photo-caption-overlay">{{ $photo->caption }}</span>
                @endif

                <div class="ob-photo-actions">
                    <a href="{{ route('photo.download', $photo) }}"
                       class="btn btn-xs btn-light" title="{{ __('photo.photo_download_title') }}">
                        <i class="fas fa-download fa-xs"></i>
                    </a>
                    @if ($canManage)
                        <button type="button" class="btn btn-xs btn-light"
                                title="{{ __('photo.photo_caption_title') }}" data-caption-edit
                                data-id="{{ $photo->id }}"
                                data-caption="{{ $photo->caption }}">
                            <i class="fas fa-quote-right fa-xs"></i>
                        </button>
                        <form method="POST" action="{{ route('photo.cover', $album) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="photo_id" value="{{ $photo->id }}">
                            <button type="submit" class="btn btn-xs btn-light" title="{{ __('photo.photo_cover_title') }}">
                                <i class="fas fa-star fa-xs {{ (int) $album->cover_photo_id === (int) $photo->id ? 'text-warning' : '' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('photo.destroy', $photo) }}"
                              class="d-inline" data-confirm="{{ __('photo.confirm_delete_photo') }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger" title="{{ __('photo.photo_delete_title') }}">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        </form>
                    @endif
                </div>

                @if ((int) $album->cover_photo_id === (int) $photo->id)
                    <span class="ob-photo-cover-badge" title="{{ __('photo.cover_badge_title') }}">
                        <i class="fas fa-star fa-xs"></i>
                    </span>
                @endif

                <span class="ob-photo-select-check" aria-hidden="true">
                    <i class="fas fa-check"></i>
                </span>
            </div>
        @endforeach
    </div>

    @if ($canManage)
        {{-- Floating bulk-action bar (shown in select mode) --}}
        <div id="bulkBar" class="ob-bulk-bar d-none">
            <span id="bulkCount" class="ob-bulk-count">{{ __('photo.bulk_selected', ['count' => 0]) }}</span>
            <span class="d-flex gap-2 ms-auto">
                <button type="button" class="btn btn-sm btn-secondary" id="bulkCancel">{{ __('common.cancel') }}</button>
                <button type="submit" form="bulkDeleteForm" class="btn btn-sm btn-danger" id="bulkDelete" disabled>
                    <i class="fas fa-trash me-1"></i> {{ __('photo.btn_bulk_delete') }}
                </button>
            </span>
        </div>
        <form method="POST" action="{{ route('photo.bulk-destroy', $album) }}"
              id="bulkDeleteForm" data-confirm="{{ __('photo.confirm_bulk_delete') }}">
            @csrf @method('DELETE')
            <div id="bulkHiddenInputs"></div>
        </form>
    @endif
@endif

@if ($canManage)
    {{-- Combined "Ajouter des photos" modal — two tabs: Upload + Doc library --}}
    <div class="modal fade" id="photoAddModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <ul class="nav nav-tabs" id="photoAddTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-upload" data-bs-toggle="tab"
                                    data-bs-target="#pane-upload" type="button" role="tab">
                                <i class="fas fa-upload me-1"></i> {{ __('photo.tab_upload') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-library" data-bs-toggle="tab"
                                    data-bs-target="#pane-library" type="button" role="tab"
                                    data-load-url="{{ route('photo.pick-docs', $album) }}">
                                <i class="fas fa-folder-open me-1"></i> {{ __('photo.tab_library') }}
                            </button>
                        </li>
                    </ul>
                    <button type="button" class="btn-close ms-auto mb-auto" data-bs-dismiss="modal" aria-label="{{ __('photo.modal_close') }}"></button>
                </div>

                <div class="tab-content modal-body pt-3">
                    {{-- ── Tab 1: Upload ── --}}
                    <div class="tab-pane fade show active" id="pane-upload" role="tabpanel">
                        <form method="POST" action="{{ route('photo.store', $album) }}"
                              enctype="multipart/form-data" id="uploadForm">
                            @csrf
                            <div class="ob-drop-zone" id="dropZone">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-secondary opacity-50"></i>
                                <p class="mb-1">{{ __('photo.drop_hint') }}</p>
                                <label class="btn btn-sm btn-outline-primary mb-0" for="photoFiles">
                                    {{ __('photo.btn_choose_files') }}
                                </label>
                                <input type="file" id="photoFiles" name="photos[]" class="d-none"
                                       multiple accept="{{ implode(',', array_map(fn($e) => '.'.$e, config('photos.supported_extensions'))) }}">
                                <p class="mt-2 mb-0 text-muted" style="font-size:var(--font-size-xs);">
                                    {{ implode(', ', config('photos.supported_extensions')) }} · {{ __('photo.max_size_suffix', ['size' => config('photos.max_size_mb')]) }}
                                </p>
                            </div>
                            <div id="uploadPreview" class="ob-upload-preview mt-3 d-none"></div>
                            <div class="d-flex justify-content-end mt-3 gap-2">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                                <button type="submit" id="uploadSubmit" class="btn btn-sm btn-primary" disabled>
                                    <i class="fas fa-upload me-1"></i> <span id="uploadLabel">{{ __('photo.btn_send') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ── Tab 2: Document library picker ── --}}
                    <div class="tab-pane fade" id="pane-library" role="tabpanel">
                        <div id="docPickerState">
                            <div class="text-center py-4 text-muted" id="docPickerLoading">
                                <div class="spinner-border spinner-border-sm me-2"></div> {{ __('common.loading') }}
                            </div>
                            <div id="docPickerEmpty" class="text-center py-4 text-muted d-none">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                {{ __('photo.doc_picker_empty') }}
                            </div>
                            <div id="docPickerGrid" class="ob-doc-picker-grid d-none"></div>
                        </div>
                        <form method="POST" action="{{ route('photo.from-docs', $album) }}" id="docPickerForm">
                            @csrf
                            <div id="docPickerHiddenInputs"></div>
                            <div class="d-flex justify-content-between align-items-center mt-3 gap-2">
                                <span class="text-muted" style="font-size:var(--font-size-xs);" id="docPickerCount">{{ __('photo.no_selection') }}</span>
                                <span class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                                    <button type="submit" id="docPickerSubmit" class="btn btn-sm btn-primary" disabled>
                                        <i class="fas fa-file-import me-1"></i> {{ __('photo.btn_import') }}
                                    </button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit album --}}
    <div class="modal fade" id="albumEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('photo.album.update', $album) }}" class="modal-content">
                @csrf @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>{{ __('photo.modal_edit_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('photo.modal_close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="albumName">{{ __('photo.label_name') }}</label>
                        <input type="text" id="albumName" name="name" class="form-control"
                               maxlength="100" value="{{ $album->name }}" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="albumDesc">{{ __('photo.label_description') }}</label>
                        <input type="text" id="albumDesc" name="description" class="form-control"
                               maxlength="500" value="{{ $album->description }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('photo.btn_save') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Caption modal (filled by JS) --}}
    <div class="modal fade" id="captionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <form method="POST" id="captionForm" class="modal-content">
                @csrf @method('PATCH')
                <div class="modal-header py-2">
                    <h5 class="modal-title" style="font-size:var(--font-size-sm);">{{ __('photo.caption_modal_title') }}</h5>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="{{ __('photo.modal_close') }}"></button>
                </div>
                <div class="modal-body py-2">
                    <input type="text" id="captionInput" name="caption" class="form-control form-control-sm"
                           maxlength="255" placeholder="{{ __('photo.caption_placeholder') }}">
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('photo.btn_save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Caption modal ──────────────────────────────────────────────────────────
    var captionModalEl = document.getElementById('captionModal');
    var captionForm    = document.getElementById('captionForm');
    var captionInput   = document.getElementById('captionInput');
    var photoBase      = '{{ url("/photo") }}';

    if (captionModalEl) {
        document.querySelectorAll('[data-caption-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                captionForm.action = photoBase + '/' + btn.dataset.id;
                captionInput.value = btn.dataset.caption || '';
                bootstrap.Modal.getOrCreateInstance(captionModalEl).show();
            });
        });
    }

    // ── Confirm before delete ──────────────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm)) { e.preventDefault(); }
        });
    });

    // ── Upload tab: drag-and-drop + file preview ───────────────────────────────
    var dropZone     = document.getElementById('dropZone');
    var photoFiles   = document.getElementById('photoFiles');
    var uploadPreview = document.getElementById('uploadPreview');
    var uploadSubmit = document.getElementById('uploadSubmit');
    var uploadLabel  = document.getElementById('uploadLabel');

    if (dropZone) {
        ['dragenter', 'dragover'].forEach(function (evt) {
            dropZone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropZone.classList.add('ob-drop-zone--over');
            });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            dropZone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropZone.classList.remove('ob-drop-zone--over');
            });
        });
        dropZone.addEventListener('drop', function (e) {
            photoFiles.files = e.dataTransfer.files;
            handleFileSelection(photoFiles.files);
        });
    }

    if (photoFiles) {
        photoFiles.addEventListener('change', function () {
            handleFileSelection(photoFiles.files);
        });
    }

    function handleFileSelection(files) {
        if (!files || files.length === 0) {
            uploadPreview.classList.add('d-none');
            uploadPreview.innerHTML = '';
            uploadSubmit.disabled = true;
            return;
        }
        uploadPreview.innerHTML = '';
        Array.from(files).forEach(function (f) {
            if (!f.type.startsWith('image/')) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                var div = document.createElement('div');
                div.className = 'ob-upload-thumb';
                var img = document.createElement('img');
                img.src = e.target.result;
                img.alt = f.name;
                var label = document.createElement('span');
                label.textContent = f.name.length > 18 ? f.name.slice(0, 15) + '…' : f.name;
                div.appendChild(img);
                div.appendChild(label);
                uploadPreview.appendChild(div);
            };
            reader.readAsDataURL(f);
        });
        uploadPreview.classList.remove('d-none');
        var n = files.length;
        uploadLabel.textContent = 'Envoyer ' + n + ' photo' + (n > 1 ? 's' : '');
        uploadSubmit.disabled = false;
    }

    // ── Doc picker tab: lazy-load on first open ────────────────────────────────
    var libTab       = document.getElementById('tab-library');
    var docGrid      = document.getElementById('docPickerGrid');
    var docLoading   = document.getElementById('docPickerLoading');
    var docEmpty     = document.getElementById('docPickerEmpty');
    var docCount     = document.getElementById('docPickerCount');
    var docSubmit    = document.getElementById('docPickerSubmit');
    var docHiddens   = document.getElementById('docPickerHiddenInputs');
    var docsLoaded   = false;
    var selectedIds  = new Set();

    if (libTab) {
        libTab.addEventListener('shown.bs.tab', function () {
            if (docsLoaded) return;
            docsLoaded = true;
            fetch(libTab.dataset.loadUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (docs) {
                docLoading.classList.add('d-none');
                if (!docs || docs.length === 0) {
                    docEmpty.classList.remove('d-none');
                    return;
                }
                docs.forEach(function (doc) {
                    var item = document.createElement('div');
                    item.className = 'ob-doc-item';
                    item.dataset.id = doc.id;
                    item.innerHTML =
                        '<div class="ob-doc-thumb">' +
                          '<img src="' + doc.thumb_url + '" alt="' + escHtml(doc.name) + '" loading="lazy">' +
                        '</div>' +
                        '<div class="ob-doc-info">' +
                          '<span class="ob-doc-name" title="' + escHtml(doc.name) + '">' + escHtml(doc.name) + '</span>' +
                          (doc.folder ? '<span class="ob-doc-folder">' + escHtml(doc.folder) + '</span>' : '') +
                        '</div>' +
                        '<div class="ob-doc-check"><i class="fas fa-check"></i></div>';
                    item.addEventListener('click', function () {
                        toggleDoc(item, doc.id);
                    });
                    docGrid.appendChild(item);
                });
                docGrid.classList.remove('d-none');
            })
            .catch(function () {
                docLoading.innerHTML = '<span class="text-danger">Erreur lors du chargement.</span>';
            });
        });
    }

    function toggleDoc(item, id) {
        if (selectedIds.has(id)) {
            selectedIds.delete(id);
            item.classList.remove('ob-doc-item--selected');
        } else {
            selectedIds.add(id);
            item.classList.add('ob-doc-item--selected');
        }
        updateDocSelection();
    }

    function updateDocSelection() {
        var n = selectedIds.size;
        docCount.textContent = n > 0 ? n + ' fichier' + (n > 1 ? 's' : '') + ' sélectionné' + (n > 1 ? 's' : '') : 'Aucune sélection';
        docSubmit.disabled = n === 0;
        docHiddens.innerHTML = '';
        selectedIds.forEach(function (id) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'doc_ids[]';
            inp.value = id;
            docHiddens.appendChild(inp);
        });
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Reset state when the main modal closes.
    var addModal = document.getElementById('photoAddModal');
    if (addModal) {
        addModal.addEventListener('hidden.bs.modal', function () {
            if (photoFiles) { photoFiles.value = ''; }
            if (uploadPreview) { uploadPreview.innerHTML = ''; uploadPreview.classList.add('d-none'); }
            if (uploadSubmit) { uploadSubmit.disabled = true; }
            if (uploadLabel)  { uploadLabel.textContent = 'Envoyer'; }
        });
    }

    // ── Select / bulk-delete mode ──────────────────────────────────────────────
    var selectToggle  = document.getElementById('selectModeToggle');
    var bulkBar       = document.getElementById('bulkBar');
    var bulkCount     = document.getElementById('bulkCount');
    var bulkDelete    = document.getElementById('bulkDelete');
    var bulkCancel    = document.getElementById('bulkCancel');
    var bulkHiddens   = document.getElementById('bulkHiddenInputs');
    var bulkForm      = document.getElementById('bulkDeleteForm');
    var photoGrid     = document.querySelector('.ob-photo-grid');
    var inSelectMode  = false;
    var selectedPhotoIds = new Set();

    function enterSelectMode() {
        inSelectMode = true;
        if (photoGrid) photoGrid.classList.add('ob-photo-grid--select');
        if (bulkBar)   bulkBar.classList.remove('d-none');
        if (selectToggle) {
            selectToggle.innerHTML = '<i class="fas fa-times me-1"></i> Annuler';
            selectToggle.classList.replace('btn-outline-secondary', 'btn-warning');
        }
        selectedPhotoIds.clear();
        updateBulkBar();
    }

    function exitSelectMode() {
        inSelectMode = false;
        if (photoGrid) photoGrid.classList.remove('ob-photo-grid--select');
        if (bulkBar)   bulkBar.classList.add('d-none');
        if (selectToggle) {
            selectToggle.innerHTML = '<i class="fas fa-check-square me-1"></i> Sélectionner';
            selectToggle.classList.replace('btn-warning', 'btn-outline-secondary');
        }
        document.querySelectorAll('.ob-photo-item--selected').forEach(function (el) {
            el.classList.remove('ob-photo-item--selected');
        });
        selectedPhotoIds.clear();
        updateBulkBar();
    }

    function updateBulkBar() {
        var n = selectedPhotoIds.size;
        if (bulkCount) bulkCount.textContent = n + ' sélectionnée' + (n > 1 ? 's' : '');
        if (bulkDelete) bulkDelete.disabled = n === 0;
        if (bulkHiddens) {
            bulkHiddens.innerHTML = '';
            selectedPhotoIds.forEach(function (id) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'photo_ids[]';
                inp.value = id;
                bulkHiddens.appendChild(inp);
            });
        }
    }

    if (selectToggle) {
        selectToggle.addEventListener('click', function () {
            inSelectMode ? exitSelectMode() : enterSelectMode();
        });
    }
    if (bulkCancel) {
        bulkCancel.addEventListener('click', exitSelectMode);
    }
    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            if (selectedPhotoIds.size === 0) { e.preventDefault(); return; }
            if (!window.confirm(bulkForm.dataset.confirm || 'Supprimer les photos sélectionnées ?')) {
                e.preventDefault();
            }
        });
    }

    var allSelectableCards = Array.from(document.querySelectorAll('.ob-photo-item'));
    var lastSelectedIndex = null;

    function setCardSelected(card, on) {
        var id = parseInt(card.dataset.photoId, 10);
        if (on) {
            selectedPhotoIds.add(id);
            card.classList.add('ob-photo-item--selected');
        } else {
            selectedPhotoIds.delete(id);
            card.classList.remove('ob-photo-item--selected');
        }
    }

    allSelectableCards.forEach(function (card, index) {
        card.addEventListener('click', function (e) {
            if (!inSelectMode) return;
            // Prevent lightbox or other link activation.
            e.preventDefault();
            e.stopPropagation();

            // Shift-click selects the whole range from the last clicked card.
            if (e.shiftKey && lastSelectedIndex !== null) {
                var start = Math.min(lastSelectedIndex, index);
                var end = Math.max(lastSelectedIndex, index);
                for (var i = start; i <= end; i++) {
                    setCardSelected(allSelectableCards[i], true);
                }
            } else {
                var id = parseInt(card.dataset.photoId, 10);
                setCardSelected(card, !selectedPhotoIds.has(id));
            }
            lastSelectedIndex = index;
            updateBulkBar();
        }, true);
    });

    // ── Drag-and-drop photo reorder (managers only) ───────────────────────────
    @if ($canManage)
    var reorderUrl = '{{ route('photo.reorder', $album) }}';
    var csrfToken  = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : '{{ csrf_token() }}';

    var dragSrc = null;

    function getDraggableCards() {
        return photoGrid ? Array.from(photoGrid.querySelectorAll('.ob-photo-item[draggable]')) : [];
    }

    function initDragAndDrop() {
        getDraggableCards().forEach(function (card) {
            card.addEventListener('dragstart', function (e) {
                if (inSelectMode) { e.preventDefault(); return; }
                dragSrc = card;
                e.dataTransfer.effectAllowed = 'move';
                card.classList.add('ob-photo-dragging');
            });
            card.addEventListener('dragend', function () {
                card.classList.remove('ob-photo-dragging');
                getDraggableCards().forEach(function (c) { c.classList.remove('ob-photo-dragover'); });
                dragSrc = null;
            });
            card.addEventListener('dragover', function (e) {
                if (!dragSrc || dragSrc === card || inSelectMode) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                getDraggableCards().forEach(function (c) { c.classList.remove('ob-photo-dragover'); });
                card.classList.add('ob-photo-dragover');
            });
            card.addEventListener('dragleave', function () {
                card.classList.remove('ob-photo-dragover');
            });
            card.addEventListener('drop', function (e) {
                if (!dragSrc || dragSrc === card || inSelectMode) return;
                e.preventDefault();
                card.classList.remove('ob-photo-dragover');

                // Re-order DOM: insert dragSrc before the drop target.
                var cards = getDraggableCards();
                var srcIdx = cards.indexOf(dragSrc);
                var dstIdx = cards.indexOf(card);
                if (srcIdx < dstIdx) {
                    card.after(dragSrc);
                } else {
                    card.before(dragSrc);
                }

                // Persist new order.
                var ids = getDraggableCards().map(function (c) {
                    return parseInt(c.dataset.photoId, 10);
                });
                fetch(reorderUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids: ids }),
                });
            });
        });
    }

    if (photoGrid) {
        initDragAndDrop();
    }
    @endif
});
</script>
@endpush
