@extends('layout.app')

@section('title', 'Activités — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
        ['label' => __('event.title')],
    ]" />

<x-ob-toolbar title="{{ __('event.title') }}" :total="$items->total()" filter-action="{{ route('event.index') }}"
    filter-id="filterForm" filter-cols="2fr 1fr 1fr 1fr" :columns="$columns" table-id="evenementTable"
    :export-xls-url="route('event.export.xls', request()->query())"
    :export-csv-url="route('event.export.csv', request()->query())">

    @if(auth()->user()->hasPermission(15))
        <a href="{{ route('event.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> {{ __('event.btn_new') }}
        </a>
    @endif

    <x-slot:filters>
        <input type="hidden" name="order" value="{{ request('order') }}">
        <input type="hidden" name="perPage" value="{{ request('perPage', 50) }}">
        <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="{{ __('common.search_placeholder') }}"
            data-ob-search="filterForm">
        <select name="period" class="form-select form-select-sm">
            <option value="upcoming" @selected($period === 'upcoming')>{{ __('event.filter_upcoming') }}</option>
            <option value="past" @selected($period === 'past')>{{ __('event.filter_past') }}</option>
            <option value="all" @selected($period === 'all')>{{ __('event.filter_all') }}</option>
        </select>
        <select name="type" class="form-select form-select-sm">
            <option value="ALL" @selected($type === 'ALL')>{{ __('event.filter_all_types') }}</option>
            @foreach($types as $t)
                <option value="{{ $t->TE_CODE }}" @selected($type === $t->TE_CODE)>
                    {{ $t->TE_LIBELLE }}
                </option>
            @endforeach
        </select>
        @feature('multi_site')
        <select name="section" class="form-select form-select-sm">
            <option value="" @selected($filtSect === null)>{{ __('event.filter_my_sections') }}</option>
            @foreach($sections as $s)
                <option value="{{ $s->S_ID }}" @selected($filtSect === $s->S_ID)>
                    {{ $s->S_CODE }} — {{ $s->S_DESCRIPTION }}
                </option>
            @endforeach
        </select>
        @endfeature
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="evenementTable" :total="$items->total()" total-label="activité">
    <x-ob-table :columns="$columns" :items="$items" storage-key="evenementColsV2" :current-order="request('order')"
        row-url-pattern="/events/{E_CODE}" :row-actions="[
            ['url' => '/events/{E_CODE}', 'icon' => 'fas fa-eye', 'title' => __('event.row_action_view')],
        ]" :show-select="false" table-id="evenementTable" />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection