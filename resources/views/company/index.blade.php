@extends('layout.app')

@section('title', __('company.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('company.breadcrumb')],
]"/>

<x-ob-toolbar
    title="{{ __('company.page_title') }}"
    :total="$items->total()"
    filter-action="{{ route('company.index') }}"
    filter-id="filterForm"
    filter-cols="2fr 1fr"
    :columns="$columns"
    table-id="companyTable"
    :export-xls-url="route('company.export.xls', request()->query())"
    :export-csv-url="route('company.export.csv', request()->query())">

    @if(auth()->user()->hasPermission(29))
        {{-- TODO: Migrate code --}}
        <a href="{{ url('/legacy/ins_company.php') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> {{ __('company.new_client') }}
        </a>
    @endif

    <x-slot:filters>
        <input type="text" name="q" value="{{ $search }}"
               class="form-control form-control-sm"
               placeholder="{{ __('company.search_placeholder') }}"
               data-ob-search="filterForm">
        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="ALL" @selected($type === 'ALL')>{{ __('company.all_types') }}</option>
            @foreach($types as $t)
                <option value="{{ $t->TC_CODE }}" @selected($type === $t->TC_CODE)>
                    {{ $t->TC_LIBELLE }}
                </option>
            @endforeach
        </select>
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="companyTable" :total="$items->total()" :total-label="__('company.client_label')">
    <x-ob-table
        :columns="$columns"
        :items="$items"
        storage-key="companyColsV2"
        :current-order="request('order')"
        :row-actions="[
            {{-- TODO: Migrate code --}}
            ['url' => '/legacy/upd_company.php?company={C_ID}', 'icon' => 'fas fa-edit', 'title' => __('company.action_edit')],
        ]"
        :show-select="false"
        table-id="companyTable"
    />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
