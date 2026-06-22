@extends('layout.app')

@section('title', __('equipment.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('equipment.breadcrumb')],
]"/>

<x-ob-toolbar
    title="{{ __('equipment.title') }}"
    :total="$items->total()"
    filter-action="{{ route('equipment.index') }}"
    filter-id="filterForm"
    filter-cols="2fr 1fr"
    :columns="$columns"
    table-id="materielTable"
    :export-xls-url="route('equipment.export.xls', request()->query())"
    :export-csv-url="route('equipment.export.csv', request()->query())">

    @if(auth()->user()->hasPermission(70))
        {{-- TODO: Migrate code --}}
        <a href="{{ url('/legacy/ins_equipment.php') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> {{ __('equipment.new_equipment') }}
        </a>
    @endif

    <x-slot:filters>
        <input type="text" name="q" value="{{ $search }}"
               class="form-control form-control-sm"
               placeholder="{{ __('equipment.search_placeholder') }}"
               data-ob-search="filterForm">
        @feature('multi_site')
        <select name="section" class="form-select form-select-sm">
            <option value="" @selected($filtSect === null)>{{ __('equipment.all_sections') }}</option>
            @foreach($sections as $s)
                <option value="{{ $s->S_ID }}" @selected($filtSect === $s->S_ID)>
                    {{ $s->S_CODE }} — {{ $s->S_DESCRIPTION }}
                </option>
            @endforeach
        </select>
        @endfeature
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="materielTable" :total="$items->total()" :total-label="__('equipment.item_label')">
    <x-ob-table
        :columns="$columns"
        :items="$items"
        storage-key="materielColsV2"
        :show-select="false"
        table-id="materielTable"
    />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
