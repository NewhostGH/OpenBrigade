@extends('layout.app')

@section('title', 'Documents — ' . config('app.name'))

@section('content')

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Bibliothèque de documents</h1>
        @if(auth()->user()->hasPermission(47))
            {{-- TODO: Migrate code --}}
            <a href="{{ url('/legacy/upd_document.php?action=insert') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-upload me-1"></i> Ajouter un document
            </a>
        @endif
    </div>
</div>

<div class="mx-3 mt-3 row g-3">

    {{-- ── Folder tree (left panel) ─────────────────────────────────────────── --}}
    <div class="col-lg-3">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title"><i class="fas fa-folder-open"></i> Dossiers</div>
            </div>
            <div class="ob-widget-card-body p-0">
                <a href="{{ route('document.index') }}"
                   class="d-block px-3 py-2 text-decoration-none {{ $folderId === 0 ? 'fw-semibold' : '' }}"
                   style="font-size:var(--font-size-sm);border-bottom:1px solid var(--component-border)">
                    <i class="fas fa-home fa-xs me-2 text-muted"></i>Racine
                </a>
                @foreach($allFolders->where('DF_PARENT', 0)->merge($allFolders->whereNull('DF_PARENT')) as $folder)
                    <a href="{{ route('document.index', ['folder' => $folder->DF_ID]) }}"
                       class="d-block px-3 py-2 text-decoration-none {{ $folderId === $folder->DF_ID ? 'fw-semibold' : '' }}"
                       style="font-size:var(--font-size-sm);border-bottom:1px solid var(--component-border)">
                        <i class="fas fa-folder fa-xs me-2" style="color:var(--color-folder)"></i>
                        {{ $folder->DF_NAME }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Documents (right panel) ────────────────────────────────────────── --}}
    <div class="col-lg-9">

        {{-- Breadcrumb --}}
        @if(count($breadcrumb) > 0)
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb" style="font-size:var(--font-size-sm)">
                    <li class="breadcrumb-item">
                        <a href="{{ route('document.index') }}">Racine</a>
                    </li>
                    @foreach($breadcrumb as $crumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            @if($loop->last)
                                {{ $crumb['name'] }}
                            @else
                                <a href="{{ route('document.index', ['folder' => $crumb['id']]) }}">
                                    {{ $crumb['name'] }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        {{-- Sub-folder chips --}}
        @if($subFolders->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach($subFolders as $sf)
                    <a href="{{ route('document.index', ['folder' => $sf->DF_ID]) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-folder me-1" style="color:var(--color-folder)"></i>
                        {{ $sf->DF_NAME }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Type filter --}}
        <form method="GET" action="{{ route('document.index') }}" class="d-flex gap-2 mb-3">
            @if($folderId > 0)
                <input type="hidden" name="folder" value="{{ $folderId }}">
            @endif
            <select name="type" class="form-select form-select-sm" style="max-width:200px"
                    onchange="this.form.submit()">
                <option value="ALL" @selected($typeCode === 'ALL')>Tous les types</option>
                @foreach($types as $t)
                    <option value="{{ $t->TD_CODE }}" @selected($typeCode === $t->TD_CODE)>
                        {{ $t->TD_LIBELLE }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- Document list --}}
        @if($documents->isEmpty())
            <div class="text-muted fst-italic p-3">Aucun document dans ce dossier.</div>
        @else
            <div class="ob-widget-card">
                <div class="ob-widget-card-body p-0">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                            <tr>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Ajouté par</th>
                                <th>Date</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr>
                                    <td style="font-size:var(--font-size-sm);font-weight:600">
                                        <i class="fas fa-file fa-xs me-2 text-muted"></i>
                                        {{ $doc->D_NAME }}
                                    </td>
                                    <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                        {{ $doc->TD_LIBELLE ?? $doc->TD_CODE ?? '—' }}
                                    </td>
                                    <td style="font-size:var(--font-size-xs)">{{ $doc->created_by_name ?? '—' }}</td>
                                    <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                        {{ $doc->D_CREATED_DATE ? \Carbon\Carbon::parse($doc->D_CREATED_DATE)->format('d/m/Y') : '—' }}
                                    </td>
                                    <td>
                                        {{-- TODO: Migrate code --}}
                                        <a href="{{ url('/legacy/showfile.php?id=' . $doc->D_ID) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Télécharger">
                                            <i class="fas fa-download fa-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-2">{{ $documents->links() }}</div>
        @endif
    </div>

</div>

@endsection
