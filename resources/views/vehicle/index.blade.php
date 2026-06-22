@extends('layout.app')

@section('title', __('vehicle.index_title', ['app' => config('app.name')]))

@section('content')

    <x-ob-breadcrumb :items="[
            ['label' => __('vehicle.title')],
        ]" />

    <x-ob-toolbar title="{{ __('vehicle.title') }}" :total="$items->total()" filter-action="{{ route('vehicle.index') }}"
        filter-id="filterForm" filter-cols="2fr 1fr 1fr" :columns="$columns" table-id="vehiculeTable"
        :export-xls-url="route('vehicle.export.xls', request()->query())"
        :export-csv-url="route('vehicle.export.csv', request()->query())">

        @if(auth()->user()->hasPermission(17))
            <a href="{{ route('vehicle.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> {{ __('vehicle.new_vehicle') }}
            </a>
        @endif

        <x-slot:filters>
            <input type="hidden" name="order" value="{{ request('order') }}">
            <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm"
                placeholder="{{ __('vehicle.search_placeholder') }}" data-ob-search="filterForm">
            <select name="status" class="form-select form-select-sm">
                <option value="all" @selected($status === 'all')>{{ __('vehicle.status_all') }}</option>
                <option value="op" @selected($status === 'op')>{{ __('vehicle.status_op') }}</option>
                <option value="nop" @selected($status === 'nop')>{{ __('vehicle.status_nop') }}</option>
            </select>
            <x-ob-section-select :selected="$filtSect" all-label="{{ __('vehicle.all_sections') }}" />
        </x-slot:filters>
    </x-ob-toolbar>

    <x-ob-commandbar table-id="vehiculeTable" :total="$items->total()" :total-label="__('vehicle.item_label')">
        <x-ob-table :columns="$columns" :items="$items" storage-key="vehiculeColsV2" :current-order="request('order')"
            row-url-pattern="/vehicles/{V_ID}" :row-actions="[
                    ['url' => '/vehicles/{V_ID}', 'icon' => 'fas fa-eye', 'title' => __('vehicle.row_action_view')],
                ]" :show-select="false" table-id="vehiculeTable" />
        <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
    </x-ob-commandbar>

@endsection