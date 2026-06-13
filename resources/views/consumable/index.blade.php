@extends('layout.app')

@section('title', 'Consommables — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Consommables'],
]"/>

<x-ob-toolbar
    title="Consommables"
    :total="$items->total()"
    filter-action="{{ route('consumable.index') }}"
    filter-id="filterForm"
    filter-cols="2fr 1fr auto"
    :columns="$columns"
    table-id="consommableTable">

    @if(auth()->user()->hasPermission(71))
        {{-- TODO: Migrate code — upd_consumable.php has no native create route yet --}}
        <a href="{{ url('/legacy/upd_consumable.php?action=insert') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nouveau consommable
        </a>
    @endif

    <x-slot:filters>
        <input type="text" name="q" value="{{ $search }}"
               class="form-control form-control-sm"
               placeholder="Description, type…"
               data-ob-search="filterForm">
        @feature('multi_site')
        <select name="section" class="form-select form-select-sm">
            <option value="0" @selected($filtSect === 0)>Ma section</option>
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
                Alertes seulement
            </label>
        </div>
    </x-slot:filters>
</x-ob-toolbar>

<x-ob-commandbar table-id="consommableTable" :total="$items->total()" total-label="consommable">
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
