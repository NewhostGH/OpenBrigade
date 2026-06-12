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
<x-ob-toolbar title="Bibliothèque de documents" :total="$documents->total()"
    filter-action="{{ route('document.index') }}" filter-id="docFilter"
    :columns="$columns" table-id="docTable" :show-card-toggle="true">

    @if ($canManage)
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#folderCreateModal">
            <i class="fas fa-folder-plus me-1"></i> Nouveau dossier
        </button>
        {{-- TODO: Migrate code — replaced by the native upload modal in the next step --}}
        <a href="{{ url('/legacy/upd_document.php?action=insert') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-upload me-1"></i> Ajouter
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
