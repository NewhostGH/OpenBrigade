@extends('layout.app')

@section('title', 'Personnel — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Personnel'],
]"/>

{{-- ── Toolbar ─────────────────────────────────────────────────────────────── --}}
<x-ob-toolbar
    title="Personnel"
    :total="$items->total()"
    filter-cols="2fr 1.4fr 1fr 2fr auto"
    filter-action="{{ route('personnel.index') }}"
    filter-id="filterForm"
    :columns="$columns"
    table-id="personnelTable"
    :export-xls-url="route('personnel.export.xls', request()->query())"
    :export-csv-url="route('personnel.export.csv', request()->query())"
    :show-card-toggle="true">

    {{-- Header actions --}}
    <button class="btn btn-sm btn-light" onclick="window.print()" title="Imprimer">
        <i class="fas fa-print"></i>
    </button>
    <a class="btn btn-sm btn-success"
       href="{{ route('personnel.create') }}"
       title="Ajouter du personnel">
        <i class="fa fa-user-plus"></i>
        <span class="d-none d-sm-inline ms-1">Ajouter</span>
    </a>

    {{-- Filters (rendered inside the form by ob-toolbar) --}}
    <x-slot:filters>
        <input type="hidden" name="order"       value="{{ $order }}">
        <input type="hidden" name="perPage"     value="{{ $perPage }}">
        <input type="hidden" name="subsections" value="{{ $subsections ? 1 : 0 }}">

        <select name="section" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="0" {{ $sectionId === 0 ? 'selected' : '' }}>Toutes sections</option>
            @foreach ($sectionOptions as $opt)
                @php
                    $depth = $opt['depth'];
                    $bgs   = ['#FFCC33','#FFFF99','#B7D8FB','#D4F1C0','#F0E6FF'];
                    $bg    = $bgs[min($depth, count($bgs) - 1)];
                    $pad   = round(1.2 + $depth * 0.5, 1);
                    $lbl   = $opt['S_CODE'] . ($opt['S_DESCRIPTION']
                        ? ' — ' . \Illuminate\Support\Str::limit($opt['S_DESCRIPTION'], 22) : '');
                @endphp
                <option value="{{ $opt['S_ID'] }}"
                        style="padding-left:{{ $pad }}rem; background:{{ $bg }};"
                        {{ $sectionId === $opt['S_ID'] ? 'selected' : '' }}>
                    {{ $lbl }}
                </option>
            @endforeach
        </select>

        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="ALL"  {{ $category === 'ALL'  ? 'selected' : '' }}>Tous</option>
            <option value="INT"  {{ $category === 'INT'  ? 'selected' : '' }}>Sauf externes</option>
            <option value="BEN"  {{ $category === 'BEN'  ? 'selected' : '' }}>Bénévoles</option>
            <option value="EXT"  {{ $category === 'EXT'  ? 'selected' : '' }}>Externes</option>
            <option value="PRES" {{ $category === 'PRES' ? 'selected' : '' }}>Prestataires</option>
        </select>

        <select name="position" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all"     {{ $position === 'all'     ? 'selected' : '' }}>Tous</option>
            <option value="actif"   {{ $position === 'actif'   ? 'selected' : '' }}>Actif</option>
            <option value="archive" {{ $position === 'archive' ? 'selected' : '' }}>Archivé</option>
            <option value="bloqued" {{ $position === 'bloqued' ? 'selected' : '' }}>Bloqué</option>
        </select>

        <input type="search" name="q"
               class="form-control form-control-sm"
               placeholder="Rechercher…"
               value="{{ $search }}"
               data-ob-search>

        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="fas fa-filter me-1"></i> Filtrer
        </button>
    </x-slot:filters>

    {{-- Secondary controls (left side of secondary row) --}}
    <x-slot:secondary>
        @if ($sectionId > 0)
            <div class="ob-toggle-switch">
                <label for="subsToggle">Sous-sections</label>
                <label class="ob-switch">
                    <input type="checkbox" id="subsToggle" {{ $subsections ? 'checked' : '' }}
                           onchange="updateParam('subsections', this.checked ? 1 : 0)">
                    <span class="slider"></span>
                </label>
            </div>
            <span class="text-muted">|</span>
        @endif

        <select class="form-select form-select-sm" style="width:auto;"
                onchange="updateParam('perPage', this.value)">
            @foreach ([12, 24, 48, 100, 500] as $ps)
                <option value="{{ $ps }}" {{ $perPage == $ps ? 'selected' : '' }}>
                    {{ $ps }} / page
                </option>
            @endforeach
        </select>

        @if ($search)
            <a href="{{ route('personnel.index', array_filter(request()->query(), fn($k) => $k !== 'q', ARRAY_FILTER_USE_KEY)) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times me-1"></i> "{{ $search }}"
            </a>
        @endif
    </x-slot:secondary>

</x-ob-toolbar>

{{-- ── Table + commandbar ──────────────────────────────────────────────────── --}}
<x-ob-commandbar table-id="personnelTable" :total="$items->total()" total-label="personne">

    <x-ob-table
        :columns="$columns"
        :items="$items"
        storage-key="personnelColsV2"
        :current-order="$order"
        row-url-pattern="/personnel/{P_ID}"
        :row-actions="[
            ['url' => '/personnel/{P_ID}/edit', 'icon' => 'fas fa-edit', 'title' => 'Modifier'],
        ]"
        :show-select="true"
        select-id-field="P_ID"
        select-email-field="P_EMAIL"
        table-id="personnelTable"
    />

    {{-- Bulk action buttons --}}
    <x-slot:actions>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelAction('mail')" title="Envoyer un message">
            <i class="fas fa-envelope me-1"></i> Envoyer
        </button>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelAction('badge')" title="Badges PDF">
            <i class="fas fa-id-badge me-1"></i> Badges
        </button>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelMailto()" title="Mailto">
            <i class="fas fa-at me-1"></i> Mail
        </button>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelAction('listemails')" title="Télécharger liste emails">
            <i class="fas fa-download me-1"></i> Télécharger
        </button>
    </x-slot:actions>

    {{-- Pagination --}}
    <x-slot:pagination>
        {{ $items->links() }}
    </x-slot:pagination>

    {{-- Hidden fields used by bulk form submission --}}
    <x-slot:hidden>
        <input type="hidden" name="SelectionMail" id="SelectionMail">
    </x-slot:hidden>

</x-ob-commandbar>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Personnel-specific bulk action handlers ──────────────────────────────

    window.personnelAction = function (action) {
        const ids = Array.from(
            document.querySelectorAll('.personnelTable-row-check:checked')
        ).map(cb => cb.value);

        if (!ids.length) { alert('Veuillez sélectionner au moins une personne.'); return; }

        document.getElementById('SelectionMail').value = ids.join(',');

        const form = document.getElementById('personnelTable_form');
        form.action = {
            {{-- TODO: Migrate code --}}
            badge:      '/legacy/pdf.php?pdf=badge',
            listemails: '/legacy/listemails.php',
        {{-- TODO: Migrate code --}}
        }[action] || '/legacy/mail_create.php';
        form.submit();
    };

    window.personnelMailto = function () {
        const emails = Array.from(
            document.querySelectorAll('.personnelTable-row-check:checked')
        ).map(cb => cb.dataset.email).filter(Boolean);

        if (!emails.length) { alert('Veuillez sélectionner au moins un destinataire avec un email.'); return; }
        window.location.href = 'mailto:' + emails.join(',');
    };

}());
</script>
@endpush
