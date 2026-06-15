@extends('layout.app')

@section('title', 'Qualifications — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Personnel', 'url' => route('personnel.index')],
    ['label' => 'Qualifications'],
]"/>

{{-- Filter tabs --}}
<div class="mx-3 mt-3 d-flex gap-2">
    <a href="{{ route('personnel.qualifications', ['filter' => 'all']) }}"
       class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">Toutes</a>
    <a href="{{ route('personnel.qualifications', ['filter' => 'expiring']) }}"
       class="btn btn-sm {{ $filter === 'expiring' ? 'btn-warning' : 'btn-outline-secondary' }}">
        <i class="fas fa-clock me-1"></i> Expirant bientôt
    </a>
    <a href="{{ route('personnel.qualifications', ['filter' => 'expired']) }}"
       class="btn btn-sm {{ $filter === 'expired' ? 'btn-danger' : 'btn-outline-secondary' }}">
        <i class="fas fa-exclamation-circle me-1"></i> Expirées
    </a>
</div>

<x-ob-toolbar
    title="Qualifications de la section"
    :total="$items->total()"
    :columns="$columns"
    table-id="qualificationsTable"
    :export-xls-url="route('personnel.qualifications.export.xls', request()->query())"
    :export-csv-url="route('personnel.qualifications.export.csv', request()->query())">
</x-ob-toolbar>

<x-ob-commandbar table-id="qualificationsTable" :total="$items->total()" total-label="qualification">
    <x-ob-table
        :columns="$columns"
        :items="$items"
        storage-key="qualificationsColsV2"
        :show-select="false"
        table-id="qualificationsTable"
    />
    <x-slot:pagination>{{ $items->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
