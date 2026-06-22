@extends('layout.app')

@section('title', 'Documents — ' . config('app.name'))

@section('content')

@php
    $crumbs = [['label' => __('document.title'), 'url' => route('document.index')]];
    foreach ($breadcrumb as $i => $c) {
        $crumbs[] = $i === array_key_last($breadcrumb)
            ? ['label' => $c['name']]
            : ['label' => $c['name'], 'url' => route('document.index', ['folder' => $c['id'], 'section' => $sectionId])];
    }
@endphp

<x-ob-breadcrumb :items="$crumbs"/>

{{-- ── Page toolbar (full width: title, actions, filters) ──────────────────── --}}
@php
    $exportParams = array_filter([
        'folder' => $folderId ?: null,
        'section' => $sectionId,
        'type' => $typeCode === 'ALL' ? null : $typeCode,
    ], fn ($v) => $v !== null);
@endphp
<x-ob-toolbar title="{{ __('document.title') }}" :total="$documents->total()"
    filter-action="{{ route('document.index') }}" filter-id="docFilter"
    :columns="$columns" table-id="docTable" :show-card-toggle="true"
    :export-xls-url="route('document.export', ['format' => 'xlsx'] + $exportParams)"
    :export-csv-url="route('document.export', ['format' => 'csv'] + $exportParams)">

    @if ($canManage)
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#folderCreateModal">
            <i class="fas fa-folder-plus me-1"></i> {{ __('document.new_folder') }}
        </button>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#docUploadModal">
            <i class="fas fa-upload me-1"></i> {{ __('document.add_doc') }}
        </button>
    @endif
    @if ($canConfig)
        <a href="{{ route('document.types') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('document.doc_types_title') }}">
            <i class="fas fa-tags"></i>
        </a>
    @endif

    <x-slot:filters>
        <input type="hidden" name="folder" value="{{ $folderId }}">
        <x-ob-section-select name="section" :selected="$sectionId" auto-submit/>
        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="ALL" @selected($typeCode === 'ALL')>{{ __('document.filter_all_types') }}</option>
            @foreach ($types as $t)
                <option value="{{ $t->TD_CODE }}" @selected($typeCode === $t->TD_CODE)>{{ $t->TD_LIBELLE }}</option>
            @endforeach
        </select>
    </x-slot:filters>
</x-ob-toolbar>

<div class="row g-3 mx-1">

    {{-- ── Folder tree ─────────────────────────────────────────────────────── --}}
    <div class="col-lg-3">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-folder-open me-2"></i>{{ __('document.folder_tree_heading') }}</div>
            </div>
            <div class="ob-widget-card-body p-0 ob-doc-tree">
                <div class="ob-doc-folder {{ $folderId === 0 ? 'active' : '' }}" style="padding-left: 0.4rem;">
                    <a href="{{ route('document.index', ['section' => $sectionId]) }}" class="ob-doc-folder-link">
                        <i class="fas fa-home fa-fw me-1"></i>{{ __('document.root') }}
                    </a>
                </div>
                @foreach ($tree as $node)
                    @include('document.partials.folder-node', ['node' => $node, 'depth' => 0])
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Documents ───────────────────────────────────────────────────────── --}}
    <div class="col-lg-9">

        <x-ob-commandbar table-id="docTable" :total="$documents->total()" total-label="document">
            <x-ob-table :columns="$columns" :items="$rows" table-id="docTable"
                empty-text="{{ __('document.empty_folder') }}"/>
        </x-ob-commandbar>

        <div class="mt-2">{{ $documents->links() }}</div>
    </div>

</div>

@if ($canManage || $canEditAny)
    {{-- Create folder --}}
    <div class="modal fade" id="folderCreateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('document.folder.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="section_id" value="{{ $sectionId }}">
                <input type="hidden" name="parent_id" value="{{ $folderId }}">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-folder-plus me-2"></i>{{ __('document.modal_create_folder_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="folderCreateName">{{ __('document.folder_name_label') }}</label>
                    <input type="text" id="folderCreateName" name="name" class="form-control" maxlength="50" required autofocus>
                    @if ($folderId > 0)
                        <p class="text-muted mt-2 mb-0" style="font-size:var(--font-size-xs);">
                            {{ __('document.folder_inherit_note') }}
                        </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('common.create') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Upload document(s) --}}
    <div class="modal fade" id="docUploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('document.store') }}" enctype="multipart/form-data" class="modal-content">
                @csrf
                <input type="hidden" name="section_id" value="{{ $sectionId }}">
                <input type="hidden" name="folder_id" value="{{ $folderId }}">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i>{{ __('document.modal_upload_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="docUploadFiles">{{ __('document.upload_files_label') }}</label>
                        <input type="file" id="docUploadFiles" name="userfile[]" class="form-control" multiple required>
                        <div class="form-text">
                            {{ __('document.upload_hint', ['exts' => implode(', ', config('documents.supported_extensions')), 'max' => config('documents.max_size_mb')]) }}
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="docUploadType">{{ __('document.upload_type_label') }}</label>
                        <select id="docUploadType" name="type" class="form-select" required>
                            @foreach ($types as $t)
                                <option value="{{ $t->TD_CODE }}">{{ $t->TD_LIBELLE }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('document.upload_visibility_note') }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('document.btn_send') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit document (action + fields filled by JS from the clicked row) --}}
    <div class="modal fade" id="docEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="docEditForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-pen me-2"></i>{{ __('document.modal_edit_doc_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="docEditName">{{ __('document.doc_name_label') }}</label>
                            <input type="text" id="docEditName" name="name" class="form-control" maxlength="120" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="docEditType">{{ __('document.doc_type_label') }}</label>
                            <select id="docEditType" name="type" class="form-select" required>
                                @foreach ($types as $t)
                                    <option value="{{ $t->TD_CODE }}">{{ $t->TD_LIBELLE }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="docEditFolder">{{ __('document.doc_folder_label') }}</label>
                            <select id="docEditFolder" name="folder_id" class="form-select">
                                <option value="0">{{ __('document.root') }}</option>
                                @foreach ($folders as $f)
                                    <option value="{{ $f->DF_ID }}">{{ $f->DF_NAME }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-doc-delete>
                            <i class="fas fa-trash me-1"></i>{{ __('document.btn_delete_doc') }}
                        </button>
                        <span>
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                            <button type="submit" class="btn btn-sm btn-primary">{{ __('common.save') }}</button>
                        </span>
                    </div>
                </form>
                <form method="POST" id="docDeleteForm" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>

    {{-- Rename folder (action + name filled by JS from the clicked button) --}}
    <div class="modal fade" id="folderEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="folderEditForm" class="modal-content">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>{{ __('document.modal_rename_folder_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="folderEditName">{{ __('document.folder_name_label') }}</label>
                    <input type="text" id="folderEditName" name="name" class="form-control" maxlength="50" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- ACL ("Partager") modal — content loaded into the iframe on demand. --}}
<div class="modal fade" id="aclModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title"><i class="fas fa-user-lock me-2"></i>{{ __('document.modal_acl_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="aclFrame" src="" style="width:100%;height:580px;border:none;" title="{{ __('document.modal_acl_title') }}"></iframe>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModalEl = document.getElementById('folderEditModal');
    var editForm = document.getElementById('folderEditForm');
    var editName = document.getElementById('folderEditName');
    var base = "{{ url('/documents/folders') }}";

    document.querySelectorAll('[data-folder-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            editForm.setAttribute('action', base + '/' + btn.dataset.id);
            editName.value = btn.dataset.name;
            bootstrap.Modal.getOrCreateInstance(editModalEl).show();
        });
    });

    document.querySelectorAll('[data-folder-delete]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm('{{ __('document.confirm_delete_folder') }}')) {
                e.preventDefault();
            }
        });
    });

    // Document edit modal: fill the form action + fields from the clicked row.
    var docEditModalEl = document.getElementById('docEditModal');
    var docEditForm = document.getElementById('docEditForm');
    var docDeleteForm = document.getElementById('docDeleteForm');
    var docBase = "{{ url('/documents') }}";

    document.querySelectorAll('[data-doc-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            docEditForm.setAttribute('action', docBase + '/' + btn.dataset.id);
            docDeleteForm.setAttribute('action', docBase + '/' + btn.dataset.id);
            docEditForm.querySelector('#docEditName').value = btn.dataset.name || '';
            docEditForm.querySelector('#docEditType').value = btn.dataset.type || '';
            docEditForm.querySelector('#docEditFolder').value = btn.dataset.folder || '0';
            bootstrap.Modal.getOrCreateInstance(docEditModalEl).show();
        });
    });

    var docDeleteBtn = document.querySelector('[data-doc-delete]');
    if (docDeleteBtn) {
        docDeleteBtn.addEventListener('click', function () {
            if (window.confirm('{{ __('document.confirm_delete_doc') }}')) {
                docDeleteForm.submit();
            }
        });
    }

    // Open the "Partager" (ACL) page in an inline modal iframe.
    var aclModalEl = document.getElementById('aclModal');
    var aclFrame = document.getElementById('aclFrame');
    document.querySelectorAll('[data-acl-window]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            aclFrame.src = link.getAttribute('href');
            bootstrap.Modal.getOrCreateInstance(aclModalEl).show();
        });
    });
    aclModalEl.addEventListener('hidden.bs.modal', function () { aclFrame.src = ''; });
    window.addEventListener('message', function (e) {
        if (e.data === 'acl:close') { bootstrap.Modal.getInstance(aclModalEl)?.hide(); }
    });

    // Generic confirm-before-submit for inline delete forms in the table.
    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm)) { e.preventDefault(); }
        });
    });

    // Collapsible folder tree: a chevron toggles its node's children.
    document.querySelectorAll('[data-tree-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var node = btn.closest('.ob-doc-tree-node');
            var children = node ? node.querySelector(':scope > .ob-doc-tree-children') : null;
            if (children) {
                children.classList.toggle('d-none');
                btn.classList.toggle('open');
            }
        });
    });
});
</script>
@endpush
