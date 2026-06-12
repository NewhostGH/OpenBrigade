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
    :columns="$columns" table-id="docTable">

    @if ($canManage)
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
            <div class="ob-widget-card-body p-0">
                <a href="{{ route('document.index', ['section' => $sectionId]) }}"
                   class="ob-doc-folder {{ $folderId === 0 ? 'active' : '' }}">
                    <i class="fas fa-home fa-fw me-2 text-muted"></i>Racine
                </a>
                @foreach ($rootFolders as $folder)
                    <a href="{{ route('document.index', ['folder' => $folder->DF_ID, 'section' => $sectionId]) }}"
                       class="ob-doc-folder {{ $folderId === (int) $folder->DF_ID ? 'active' : '' }}">
                        <i class="fas fa-folder fa-fw me-2" style="color:var(--color-folder)"></i>{{ $folder->DF_NAME }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Documents ───────────────────────────────────────────────────────── --}}
    <div class="col-lg-9">

        {{-- Sub-folder chips --}}
        @if ($subFolders->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mb-2">
                @foreach ($subFolders as $sf)
                    <a href="{{ route('document.index', ['folder' => $sf->DF_ID, 'section' => $sectionId]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-folder me-1" style="color:var(--color-folder)"></i>{{ $sf->DF_NAME }}
                    </a>
                @endforeach
            </div>
        @endif

        <x-ob-commandbar table-id="docTable" :total="$documents->total()" total-label="document">
            <x-ob-table :columns="$columns" :items="$documents" table-id="docTable"
                empty-text="Aucun document dans ce dossier."/>
        </x-ob-commandbar>

        <div class="mt-2">{{ $documents->links() }}</div>
    </div>

</div>

@endsection
