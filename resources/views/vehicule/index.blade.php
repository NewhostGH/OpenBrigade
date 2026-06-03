@extends('layout.app')

@section('title', 'Véhicules — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Véhicules'],
]"/>

<x-ob-toolbar
    title="Véhicules"
    :total="$items->total()"
    filter-action="{{ route('vehicule.index') }}"
    filter-id="filterForm"
    filter-cols="2fr 1fr 1fr"
    :columns="$columns"
    table-id="vehiculeTable">

    @if(auth()->user()->hasPermission(17))
        <a href="{{ url('/legacy/ins_vehicule.php') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nouveau véhicule
        </a>
    @endif

    <x-slot:filters>
        <input type="hidden" name="order" value="{{ request('order') }}">
        <input type="text" name="q" value="{{ $search }}"
               class="form-control form-control-sm"
               placeholder="Immatriculation ou libellé…"
               data-ob-search="filterForm">
        <select name="status" class="form-select form-select-sm">
            <option value="all" @selected($status === 'all')>Tous</option>
            <option value="op"  @selected($status === 'op')>Opérationnels</option>
            <option value="nop" @selected($status === 'nop')>Non opérationnels</option>
        </select>
        <select name="section" class="form-select form-select-sm">
            <option value="0" @selected($filtSect === 0)>Ma section</option>
            @foreach($sections as $s)
                <option value="{{ $s->S_ID }}" @selected($filtSect === $s->S_ID)>
                    {{ $s->S_CODE }} — {{ $s->S_DESCRIPTION }}
                </option>
            @endforeach
        </select>
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="vehiculeTable" :total="$items->total()" total-label="véhicule">
    <x-ob-table
        :columns="$columns"
        :items="$items"
        storage-key="vehiculeColsV2"
        :current-order="request('order')"
        row-url-pattern="/vehicules/{V_ID}"
        :row-actions="[
            ['url' => '/vehicules/{V_ID}', 'icon' => 'fas fa-eye', 'title' => 'Voir le détail'],
        ]"
        :show-select="false"
        table-id="vehiculeTable"
    />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
