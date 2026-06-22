@extends('layout.app')

@section('title', __('consumable.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('consumable.breadcrumb')],
]"/>

<x-ob-toolbar
    title="{{ __('consumable.title') }}"
    :total="$items->total()"
    filter-action="{{ route('consumable.index') }}"
    filter-id="filterForm"
    filter-cols="2fr 1fr auto"
    :columns="$columns"
    table-id="consommableTable"
    :export-xls-url="route('consumable.export.xls', request()->query())"
    :export-csv-url="route('consumable.export.csv', request()->query())">

    @if(auth()->user()->hasPermission(71))
        {{-- TODO: Migrate code — upd_consumable.php has no native create route yet --}}
        <a href="{{ url('/legacy/upd_consumable.php?action=insert') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> {{ __('consumable.new_consumable') }}
        </a>
    @endif

    <x-slot:filters>
        <input type="text" name="q" value="{{ $search }}"
               class="form-control form-control-sm"
               placeholder="{{ __('consumable.search_placeholder') }}"
               data-ob-search="filterForm">
        @feature('multi_site')
        <select name="section" class="form-select form-select-sm">
            <option value="" @selected($filtSect === null)>{{ __('consumable.all_sections') }}</option>
            @foreach($sections as $s)
                <option value="{{ $s->S_ID }}" @selected($filtSect === $s->S_ID)>
                    {{ $s->S_CODE }} — {{ $s->S_DESCRIPTION }}
                </option>
            @endforeach
        </select>
        @endfeature
        <div class="form-check mt-1">
            <input type="checkbox" class="form-check-input" id="alertOnly" name="alert"
                   value="1" @checked($alert)
                   onchange="this.form.submit()">
            <label class="form-check-label" for="alertOnly" style="font-size:var(--font-size-sm)">
                {{ __('consumable.alerts_only') }}
            </label>
        </div>
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="consommableTable" :total="$items->total()" :total-label="__('consumable.item_label')">
    <x-ob-table
        :columns="$columns"
        :items="$items"
        storage-key="consommableColsV2"
        :show-select="false"
        table-id="consommableTable"
    />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
