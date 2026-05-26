@extends('layout.app')

@section('title', 'Personnel - ' . config('app.name'))

@push('styles')
    <style>
        .breadcrumb {
            margin-bottom: 0;
            background-color: transparent;
        }

        .table-nav {
            padding: 4px 12px 0 12px;
        }

        .buttons-container {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 0;
        }
    </style>
@endpush

@section('content')

    {{-- Breadcrumb --}}
    <div class="table-responsive table-nav noprint" style="border-bottom: solid 1px #dee2e6;" id="breadcrumb">
        <nav aria-label="breadcrumb" id="navbreadcrumb">
            <ol class="breadcrumb noprint">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                        style="color:#666;font-size:0.87rem;font-weight:bold;">Accueil</a></li>
                <li class="breadcrumb-item" style="font-size:0.87rem;font-weight:bold;color:#666;">Personnel</li>
                <li class="breadcrumb-item active" aria-current="page"
                    style="color:#2b224f;font-size:0.87rem;font-weight:bold;">Liste</li>
            </ol>
        </nav>
        <div class="buttons-container noprint">
            <a class="btn btn-default" onclick="window.print();">
                <i class="fas fa-print fa-1x" title="Imprimer le tableau"></i>
            </a>
            <a class="btn btn-default"
                href="{{ route('personnel.index', array_merge(request()->query(), ['export' => 'xls'])) }}">
                <i class="far fa-file-excel fa-1x excel-hover" title="Exporter la liste dans un fichier Excel"></i>
            </a>
            <a class="btn btn-success" href="#" title="Ajouter du personnel"
                onclick="window.location='{{ route('dashboard.legacy') }}?redirect=ins_personnel.php%3Fcategory%3DINT%26suggestedcompany%3D-1';">
                <i class="fa fa-user-plus fa-1x" style="color:white;"></i><span class="hide_mobile"> Ajouter</span>
            </a>
        </div>
    </div>

    {{-- Toolbar / Filters --}}
    <div class="container-fluid noprint" id="toolbar" align="left">
        @if ($hasSubsections)
            <div class="toggle-switch">
                <label for="sub2">Sous-sections</label>
                <label class="switch">
                    <input type="checkbox" name="sub" id="sub2" {{ $subsections ? 'checked' : '' }}
                        onchange="window.location='{{ route('personnel.index') }}?' + new URLSearchParams(Object.assign({}, {section: document.getElementById('sectionFilter').value, subsections: this.checked?1:0, position: '{{ $position }}', category: '{{ $category }}', order: '{{ $order }}'})).toString()">
                    <span class="slider round"></span>
                </label>
            </div>
        @endif

        <select id="sectionFilter" name="filter" class="selectpicker" data-style="btn-default" data-container="body"
            data-live-search="true"
            onchange="window.location='{{ route('personnel.index') }}?' + new URLSearchParams(Object.assign({}, {section: this.value, subsections: {{ $subsections ? 1 : 0 }}, position: '{{ $position }}', category: '{{ $category }}', order: '{{ $order }}'})).toString()">
            <option value="0" {{ $sectionId === 0 ? 'selected' : '' }}>Toutes sections</option>
            @foreach ($sections as $sec)
                <option value="{{ $sec->S_ID }}" {{ $sectionId === (int) $sec->S_ID ? 'selected' : '' }}>{{ $sec->S_CODE }} -
                    {{ $sec->S_DESCRIPTION }}
                </option>
            @endforeach
        </select>

        <select id="category_filter" name="category_filter" title="" class="selectpicker" data-style="btn-default"
            data-container="body"
            onchange="window.location='{{ route('personnel.index') }}?' + new URLSearchParams(Object.assign({}, {section: {{ $sectionId }}, subsections: {{ $subsections ? 1 : 0 }}, position: '{{ $position }}', category: this.value, order: '{{ $order }}'})).toString()">
            <option value="ALL" {{ $category === 'ALL' ? 'selected' : '' }} class="option-ebrigade">Tous</option>
            <option value="INT" {{ $category === 'INT' ? 'selected' : '' }} class="option-ebrigade">Tous sauf externes
            </option>
            @foreach ($categories as $statut)
                <option value="{{ $statut }}" {{ $category === $statut ? 'selected' : '' }} class="option-ebrigade">{{ $statut }}
                </option>
            @endforeach
        </select>

        <select id="position_filter" name="position_filter" title="" class="selectpicker" data-style="btn-default"
            data-container="body"
            onchange="window.location='{{ route('personnel.index') }}?' + new URLSearchParams(Object.assign({}, {section: {{ $sectionId }}, subsections: {{ $subsections ? 1 : 0 }}, position: this.value, category: '{{ $category }}', order: '{{ $order }}'})).toString()">
            <option value="all" {{ $position === 'all' ? 'selected' : '' }} class="option-ebrigade">Tous</option>
            <option value="actif" {{ $position === 'actif' ? 'selected' : '' }} class="option-ebrigade">Actif</option>
            <option value="archive" {{ $position === 'archive' ? 'selected' : '' }} class="option-ebrigade">Archivé</option>
            <option value="bloqued" {{ $position === 'bloqued' ? 'selected' : '' }} class="option-ebrigade">Bloqué</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="container-fluid pl-0 pt-5">
        <table id="table" data-locale="fr-FR" data-toggle="table" data-sort-class="table-active" data-sortable="true"
            data-show-toggle="true" data-show-columns="true" data-search="true" data-search-align="right"
            data-pagination-align="center" data-pagination="true" data-toolbar="#toolbar" data-page-size="100"
            data-pagination-parts='["pageSize","pageList"]' data-page-list='[12,24,48,100,500]'
            data-loading-template="<i class='fa fa-spinner fa-spin fa-fw fa-lg'></i>" class="table-sm table-hover new-table"
            data-sort-name="{{ $order }}" data-sort-order="asc">
            <thead>
                <tr class="widget-title">
                    <th data-field="photo" data-sortable="false">Photo</th>
                    <th data-field="grade" data-sortable="true" class="hide_mobile">Grade</th>
                    <th data-field="lastname" data-sortable="true">Nom Prénom</th>
                    <th data-field="birthdate" data-sortable="true" class="hide_mobile">Date de naissance</th>
                    <th data-field="telephone" data-sortable="true" class="hide_mobile">Téléphone</th>
                    <th data-field="matricule" data-sortable="true" class="hide_mobile">Matricule</th>
                    <th data-field="section" data-sortable="true" class="hide_mobile">Section</th>
                    <th data-field="entree" data-sortable="true" class="hide_mobile">Date d'entrée</th>
                    <th data-field="statut" data-sortable="true" class="hide_mobile">Statut</th>
                    <th data-field="etat" data-sortable="true" class="hide_mobile">Position</th>
                    <th data-field="actions" data-sortable="false"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php
                        $etat = (int) $item->GP_ID === -1 ? 'Bloqué' : ((int) $item->P_OLD_MEMBER > 0 ? 'Archivé' : 'Actif');
                    @endphp
                    <tr style="cursor:pointer;" onclick="window.location='{{ route('personnel.show', $item) }}'">
                        <td onclick="event.stopPropagation()">
                            <img src="{{ route('personnel.photo', $item) }}" alt="Photo"
                                style="height:32px;width:32px;border-radius:4px;object-fit:cover;">
                        </td>
                        <td class="hide_mobile">{{ $item->P_GRADE ?: '-' }}</td>
                        <td>
                            <strong>{{ $item->P_NOM }} {{ $item->P_PRENOM }}</strong>
                            @if ($item->P_PROFESSION)
                                <br><small class="text-muted">{{ $item->P_PROFESSION }}</small>
                            @endif
                        </td>
                        <td class="hide_mobile">{{ $item->P_BIRTHDATE?->format('d/m/Y') ?: '-' }}</td>
                        <td class="hide_mobile">{{ $item->P_PHONE ?: '-' }}</td>
                        <td class="hide_mobile">{{ $item->P_CODE }}</td>
                        <td class="hide_mobile">{{ $item->section?->S_CODE ?: '-' }}</td>
                        <td class="hide_mobile">{{ $item->P_DATE_ENGAGEMENT?->format('d/m/Y') ?: '-' }}</td>
                        <td class="hide_mobile">{{ $item->P_STATUT }}</td>
                        <td class="hide_mobile">{{ $etat }}</td>
                        <td onclick="event.stopPropagation()" class="text-nowrap">
                            <a href="{{ route('personnel.edit', $item) }}" class="btn btn-default btn-sm" title="Modifier"
                                onclick="event.stopPropagation()">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-4">Aucun personnel trouvé</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <span style="height:36px;line-height:30px;color:#333;margin-right:1.6em;float:right;"
            title="Nombre de personnes">{{ $items->total() }} lignes</span>
    </div>

    <style type="text/css">
        table {
            border-collapse: collapse !important;
        }

        .hide_mobile {}

        @media (max-width: 768px) {
            .hide_mobile {
                display: none;
            }
        }
    </style>

@endsection