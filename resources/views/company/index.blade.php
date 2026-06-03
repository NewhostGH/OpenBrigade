@extends('layout.app')

@section('title', 'Clients — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Clients / Sociétés'],
]"/>

<x-ob-toolbar
    title="Clients / Sociétés"
    :total="$items->total()"
    filter-action="{{ route('company.index') }}"
    filter-id="filterForm"
    filter-cols="2fr 1fr"
    :columns="$columns"
    table-id="companyTable">

    @if(auth()->user()->hasPermission(29))
        <a href="{{ url('/legacy/ins_company.php') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nouveau client
        </a>
    @endif

    <x-slot:filters>
        <input type="text" name="q" value="{{ $search }}"
               class="form-control form-control-sm"
               placeholder="Nom, contact, email…"
               data-ob-search="filterForm">
        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="ALL" @selected($type === 'ALL')>Tous les types</option>
            @foreach($types as $t)
                <option value="{{ $t->TC_CODE }}" @selected($type === $t->TC_CODE)>
                    {{ $t->TC_LIBELLE }}
                </option>
            @endforeach
        </select>
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="companyTable" :total="$items->total()" total-label="client">
    <x-ob-table
        :columns="$columns"
        :items="$items"
        storage-key="companyColsV2"
        :current-order="request('order')"
        :row-actions="[
            ['url' => '/legacy/upd_company.php?company={C_ID}', 'icon' => 'fas fa-edit', 'title' => 'Modifier'],
        ]"
        :show-select="false"
        table-id="companyTable"
    />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
