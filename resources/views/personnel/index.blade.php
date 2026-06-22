@extends('layout.app')

@section('title', 'Personnel — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('personnel.title')],
]"/>

{{-- ── Toolbar ─────────────────────────────────────────────────────────────── --}}
<x-ob-toolbar
    title="{{ __('personnel.title') }}"
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
    <button class="btn btn-sm btn-light" onclick="window.print()" title="{{ __('common.print') }}">
        <i class="fas fa-print"></i>
    </button>
    <a class="btn btn-sm btn-success"
       href="{{ route('personnel.create') }}"
       title="{{ __('personnel.add_title') }}">
        <i class="fa fa-user-plus"></i>
        <span class="d-none d-sm-inline ms-1">{{ __('common.add') }}</span>
    </a>

    {{-- Filters (rendered inside the form by ob-toolbar) --}}
    <x-slot:filters>
        <input type="hidden" name="order"       value="{{ $order }}">
        <input type="hidden" name="perPage"     value="{{ $perPage }}">
        <input type="hidden" name="subsections" value="{{ $subsections ? 1 : 0 }}">

        <x-ob-section-select :selected="$sectionId" all-label="{{ __('personnel.all_sections_option') }}" :auto-submit="true" />

        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="ALL"  {{ $category === 'ALL'  ? 'selected' : '' }}>{{ __('personnel.cat_all') }}</option>
            <option value="INT"  {{ $category === 'INT'  ? 'selected' : '' }}>{{ __('personnel.cat_int') }}</option>
            <option value="BEN"  {{ $category === 'BEN'  ? 'selected' : '' }}>{{ __('personnel.cat_ben') }}</option>
            <option value="EXT"  {{ $category === 'EXT'  ? 'selected' : '' }}>{{ __('personnel.cat_ext') }}</option>
            <option value="PRES" {{ $category === 'PRES' ? 'selected' : '' }}>{{ __('personnel.cat_pres') }}</option>
        </select>

        <select name="position" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all"     {{ $position === 'all'     ? 'selected' : '' }}>{{ __('personnel.pos_all') }}</option>
            <option value="actif"   {{ $position === 'actif'   ? 'selected' : '' }}>{{ __('personnel.pos_actif') }}</option>
            <option value="archive" {{ $position === 'archive' ? 'selected' : '' }}>{{ __('personnel.pos_archive') }}</option>
            <option value="bloqued" {{ $position === 'bloqued' ? 'selected' : '' }}>{{ __('personnel.pos_bloqued') }}</option>
        </select>

        <input type="search" name="q"
               class="form-control form-control-sm"
               placeholder="{{ __('common.search_placeholder') }}"
               value="{{ $search }}"
               data-ob-search>

        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="fas fa-filter me-1"></i> {{ __('personnel.filter_btn') }}
        </button>
    </x-slot:filters>

    {{-- Secondary controls (left side of secondary row) --}}
    <x-slot:secondary>
        @feature('multi_site')
        @if ($sectionId > 0)
            <div class="ob-toggle-switch">
                <label for="subsToggle">{{ __('personnel.subsections_label') }}</label>
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
                    {{ $ps }} {{ __('personnel.per_page_suffix') }}
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
            ['url' => '/personnel/{P_ID}/edit', 'icon' => 'fas fa-edit', 'title' => __('personnel.row_edit_title')],
        ]"
        :show-select="true"
        select-id-field="P_ID"
        select-email-field="P_EMAIL"
        table-id="personnelTable"
    />

    {{-- Bulk action buttons --}}
    <x-slot:actions>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelAction('mail')" title="{{ __('personnel.bulk_send_title') }}">
            <i class="fas fa-envelope me-1"></i> {{ __('personnel.bulk_send_label') }}
        </button>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelAction('badge')" title="{{ __('personnel.bulk_badge_title') }}">
            <i class="fas fa-id-badge me-1"></i> {{ __('personnel.bulk_badge_label') }}
        </button>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelMailto()" title="{{ __('personnel.bulk_mailto_title') }}">
            <i class="fas fa-at me-1"></i> {{ __('personnel.bulk_mailto_label') }}
        </button>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelAction('emails')" title="{{ __('personnel.bulk_emails_title') }}">
            <i class="fas fa-envelope-open-text me-1"></i> {{ __('personnel.bulk_emails_label') }}
        </button>
        <button type="button" class="btn btn-sm btn-light"
                onclick="personnelAction('contacts')" title="{{ __('personnel.bulk_contacts_title') }}">
            <i class="fas fa-address-book me-1"></i> {{ __('personnel.bulk_contacts_label') }}
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
