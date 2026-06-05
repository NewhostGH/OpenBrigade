@extends('layout.app')

@section('title', 'Remplacements — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Remplacements de garde'],
]"/>

{{-- Tab navigation --}}
<div class="mx-3 mt-3 d-flex gap-2">
    <a href="{{ route('remplacement.index', ['tab' => 'mine']) }}"
       class="btn btn-sm {{ $tab === 'mine' ? 'btn-primary' : 'btn-outline-secondary' }}">
        <i class="fas fa-user me-1"></i> Mes remplacements
    </a>
    <a href="{{ route('remplacement.index', ['tab' => 'section']) }}"
       class="btn btn-sm {{ $tab === 'section' ? 'btn-primary' : 'btn-outline-secondary' }}">
        <i class="fas fa-users me-1"></i> Ma section
    </a>
</div>

<x-ob-toolbar
    title="Remplacements de garde"
    :total="$items->total()"
    :columns="$columns"
    table-id="remplacementTable">

    {{-- TODO: Migrate code --}}
    <a href="{{ url('/legacy/remplacement_edit.php') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-plus me-1"></i> Demander un remplacement
    </a>
</x-ob-toolbar>

<x-ob-commandbar table-id="remplacementTable" :total="$items->total()" total-label="remplacement">
    <x-ob-table
        :columns="$columns"
        :items="$items"
        storage-key="remplacementColsV2"
        :show-select="false"
        table-id="remplacementTable"
    />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
