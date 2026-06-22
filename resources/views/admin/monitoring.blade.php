@extends('layout.app')

@section('title', 'Monitoring — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('admin.administration')],
    ['label' => __('admin.monitoring.title')],
]"/>

<x-ob-toolbar
    title="{{ __('admin.monitoring.title') }}"
    :total="$items->total()"
    filter-action="{{ route('admin.monitoring') }}"
    filter-id="filterForm"
    filter-cols="2fr 1fr"
    :columns="$columns"
    table-id="monitoringTable">

    <x-slot:filters>
        <input type="text" name="q" value="{{ $search }}"
               class="form-control form-control-sm"
               placeholder="{{ __('common.search_placeholder') }}"
               data-ob-search="filterForm">
        <select name="type" class="form-select form-select-sm">
            <option value="ALL" @selected($ltCode === 'ALL')>{{ __('admin.monitoring.all_types') }}</option>
            @foreach($logTypes as $t)
                <option value="{{ $t->LT_CODE }}" @selected($ltCode === $t->LT_CODE)>
                    {{ $t->LT_DESCRIPTION }}
                </option>
            @endforeach
        </select>
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="monitoringTable" :total="$items->total()" total-label="entrée">
    <x-ob-table
        :columns="$columns"
        :items="$items"
        storage-key="monitoringColsV2"
        :show-select="false"
        table-id="monitoringTable"
    />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
