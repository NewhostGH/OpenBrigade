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

        <x-ob-section-select :selected="$sectionId" all-label="Toutes sections" :auto-submit="true" />

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
        @feature('multi_site')
        @if ($sectionId > 0)
            <div class="ob-toggle-switch">
                <label for="subsToggle">Sous-sections</label>
                <label class="ob-switch">
                    <input type="checkbox" id="subsToggle" {{ $subsections ? 'checked' : '' }}
                           onchange="updateParam('subsections', this.checked ? 1 : 0)">
                    <span class="ob-switch-slider"></span>
                </label>
            </div>
            <span class="text-muted">|</span>
        @endif
        @endfeature

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
                onclick="personnelAction('emails')" title="Télécharger liste emails (.txt)">
            <i class="fas fa-envelope-open-text me-1"></i> Emails.txt
        </button>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelAction('contacts')" title="Télécharger carnet d'adresses (.csv)">
            <i class="fas fa-address-book me-1"></i> Contacts.csv
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
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('personnelTable_form');
    if (form) {
        form.dataset.exportEmailsUrl   = @json(route('personnel.export.emails'));
        form.dataset.exportContactsUrl = @json(route('personnel.export.contacts'));
    }
});
</script>
@vite('resources/js/ob-personnel-index.js')
@endpush
