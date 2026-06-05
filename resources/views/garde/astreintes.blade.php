@extends('layout.app')

@section('title', 'Astreintes — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Garde', 'url' => route('garde.index')],
    ['label' => 'Astreintes'],
]"/>

<div class="mx-3 mt-3 d-flex align-items-center gap-3">
    @if(auth()->user()->hasPermission(26))
        {{-- TODO: Migrate code --}}
        <a href="{{ url('/legacy/astreinte_edit.php') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle astreinte
        </a>
    @endif
    <a href="{{ route('garde.astreintes', ['month' => $prevMonth, 'year' => $prevYear]) }}"
       class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-chevron-left"></i>
    </a>
    <span class="fw-semibold" style="font-size:var(--font-size-sm)">
        {{ ucfirst($first->locale('fr')->isoFormat('MMMM YYYY')) }}
    </span>
    <a href="{{ route('garde.astreintes', ['month' => $nextMonth, 'year' => $nextYear]) }}"
       class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-chevron-right"></i>
    </a>
</div>

<x-ob-commandbar table-id="astreintesTable" :total="$slots->total()" total-label="astreinte">
    <x-ob-table
        :columns="$columns"
        :items="$slots"
        storage-key="astreintesColsV2"
        :row-actions="[
            {{-- TODO: Migrate code --}}
            ['url' => '/legacy/astreinte_edit.php?astreinte={AS_ID}', 'icon' => 'fas fa-edit', 'title' => 'Modifier'],
        ]"
        :show-select="false"
        table-id="astreintesTable"
    />
    <x-slot:pagination>{{ $slots->links() }}</x-slot:pagination>
</x-ob-commandbar>

@endsection
