@extends('layout.app')

@section('title', 'Documents — ' . config('app.name'))

@section('content')

@php
    $crumbs = [['label' => 'Documents', 'url' => route('document.index')]];
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
<x-ob-toolbar title="Bibliothèque de documents" :total="$documents->total()"
    filter-action="{{ route('document.index') }}" filter-id="docFilter"
    :columns="$columns" table-id="docTable" :show-card-toggle="true"
    :export-xls-url="route('document.export', ['format' => 'xlsx'] + $exportParams)"
    :export-csv-url="route('document.export', ['format' => 'csv'] + $exportParams)">

    @if ($canManage)
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#folderCreateModal">
            <i class="fas fa-folder-plus me-1"></i> Nouveau dossier
        </button>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#docUploadModal">
            <i class="fas fa-upload me-1"></i> Ajouter
        </button>
        <a href="{{ route('document.types') }}" class="btn btn-sm btn-outline-secondary" title="Types de documents">
            <i class="fas fa-tags"></i>
        </a>
    @endif

    <x-slot:filters>
        <input type="hidden" name="folder" value="{{ $folderId }}">
        <x-ob-section-select name="section" :selected="$sectionId" auto-submit/>
        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="ALL" @selected($typeCode === 'ALL')>Tous les types</option>
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
                <div class="ob-widget-card-title"><i class="fas fa-folder-open me-2"></i>Dossiers</div>
            </div>
            <div class="ob-widget-card-body p-0 ob-doc-tree">
                <div class="ob-doc-folder {{ $folderId === 0 ? 'active' : '' }}">
                    <span class="ob-doc-tree-spacer"></span>
                    <a href="{{ route('document.index', ['section' => $sectionId]) }}" class="ob-doc-folder-link">
                        <i class="fas fa-home fa-fw me-1 text-muted"></i>Racine
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
                empty-text="Ce dossier est vide."/>
        </x-ob-commandbar>

        <div class="mt-2">{{ $documents->links() }}</div>
    </div>

</div>

@if ($canManage)
    {{-- Create folder --}}
    <div class="modal fade" id="folderCreateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('document.folder.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="section_id" value="{{ $sectionId }}">
                <input type="hidden" name="parent_id" value="{{ $folderId }}">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-folder-plus me-2"></i>Nouveau dossier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="folderCreateName">Nom du dossier</label>
                    <input type="text" id="folderCreateName" name="name" class="form-control" maxlength="50" required autofocus>
                    @if ($folderId > 0)
                        <p class="text-muted mt-2 mb-0" style="font-size:var(--font-size-xs);">
                            Créé dans le dossier courant ; il héritera de son type de document.
                        </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Créer</button>
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
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Ajouter un document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="docUploadFiles">Fichier(s)</label>
                        <input type="file" id="docUploadFiles" name="userfile[]" class="form-control" multiple required>
                        <div class="form-text">
                            {{ implode(', ', config('documents.supported_extensions')) }} — max {{ config('documents.max_size_mb') }} Mo.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="docUploadType">Type</label>
                        <select id="docUploadType" name="type" class="form-select" required>
                            @foreach ($types as $t)
                                <option value="{{ $t->TD_CODE }}">{{ $t->TD_LIBELLE }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="docUploadSecurity">Visibilité</label>
                        <select id="docUploadSecurity" name="security" class="form-select" required>
                            @foreach ($securities as $s)
                                <option value="{{ $s->DS_ID }}">{{ $s->DS_LIBELLE }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Envoyer</button>
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
                        <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Modifier le document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="docEditType">Type</label>
                            <select id="docEditType" name="type" class="form-select" required>
                                @foreach ($types as $t)
                                    <option value="{{ $t->TD_CODE }}">{{ $t->TD_LIBELLE }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="docEditSecurity">Visibilité</label>
                            <select id="docEditSecurity" name="security" class="form-select" required>
                                @foreach ($securities as $s)
                                    <option value="{{ $s->DS_ID }}">{{ $s->DS_LIBELLE }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="docEditFolder">Dossier</label>
                            <select id="docEditFolder" name="folder_id" class="form-select">
                                <option value="0">Racine</option>
                                @foreach ($folders as $f)
                                    <option value="{{ $f->DF_ID }}">{{ $f->DF_NAME }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-doc-delete>
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                        <span>
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
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
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Renommer le dossier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="folderEditName">Nom du dossier</label>
                    <input type="text" id="folderEditName" name="name" class="form-control" maxlength="50" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
@endif

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
            if (!window.confirm('Supprimer ce dossier ? Il doit être vide.')) {
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
            docEditForm.querySelector('#docEditType').value = btn.dataset.type || '';
            docEditForm.querySelector('#docEditSecurity').value = btn.dataset.security || '';
            docEditForm.querySelector('#docEditFolder').value = btn.dataset.folder || '0';
            bootstrap.Modal.getOrCreateInstance(docEditModalEl).show();
        });
    });

    var docDeleteBtn = document.querySelector('[data-doc-delete]');
    if (docDeleteBtn) {
        docDeleteBtn.addEventListener('click', function () {
            if (window.confirm('Supprimer définitivement ce document ?')) {
                docDeleteForm.submit();
            }
        });
    }

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
