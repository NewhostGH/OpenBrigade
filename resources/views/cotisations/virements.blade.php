@extends('layout.app')

@section('title', 'Virements — ' . config('app.name'))

@section('content')

@php
    $sortUrl  = fn(string $f) => route('cotisations.virements', array_merge(request()->query(), ['order' => $f]));
    $sortIcon = fn(string $f) => '<i class="fas fa-sort' . ($order === $f ? '-down active' : '') . ' sort-icon ms-1"></i>';
@endphp

<x-ob-breadcrumb :items="[
    ['label' => 'Cotisations', 'url' => route('cotisations.index')],
    ['label' => 'Virements'],
]"/>

@include('cotisations._tabs')

{{-- ── Toolbar ─────────────────────────────────────────────────────────────── --}}
<x-ob-toolbar
    title="Virements"
    :total="$items->total()"
    total-label="virement"
    filter-action="{{ route('cotisations.virements') }}"
    filter-cols="2fr 1fr 1fr 1fr auto">

    <button class="btn btn-sm btn-light" onclick="window.print()" title="Imprimer">
        <i class="fas fa-print"></i>
    </button>

    <x-slot:filters>
        @feature('multi_site')
        <div>
            <x-ob-section-select :selected="$sectionId" all-label="Toutes sections" :auto-submit="true" />
        </div>
        @endfeature

        <div>
            <input type="date" name="date_from"
                   class="form-control form-control-sm"
                   placeholder="Du…"
                   value="{{ $dateFrom }}"
                   title="Date de début">
        </div>

        <div>
            <input type="date" name="date_to"
                   class="form-control form-control-sm"
                   placeholder="Au…"
                   value="{{ $dateTo }}"
                   title="Date de fin">
        </div>

        <button type="submit" class="btn btn-sm btn-secondary">
            <i class="fas fa-filter me-1"></i> Filtrer
        </button>
    </x-slot:filters>

    <x-slot:secondary>
        @feature('multi_site')
        @if ($sectionId > 0)
            <div class="ob-toggle-switch">
                <label for="subsToggle">Sous-sections</label>
                <label class="ob-switch">
                    <input type="checkbox" id="subsToggle" {{ $subsections ? 'checked' : '' }}
                           onchange="updateParam('subsections', this.checked ? 1 : 0)">
                    <span class="ob-switch-slider"></span>
                </label>
            </div>
            <span class="text-muted">|</span>
        @endif
        @endfeature

        <div class="ob-toggle-switch">
            <label for="oldToggle">Archivés</label>
            <label class="ob-switch">
                <input type="checkbox" id="oldToggle" {{ $includeOld ? 'checked' : '' }}
                       onchange="updateParam('include_old', this.checked ? 1 : 0)">
                <span class="ob-switch-slider"></span>
            </label>
        </div>

        @if ($dateFrom || $dateTo)
            <a href="{{ route('cotisations.virements', array_filter(request()->query(), fn($k) => !in_array($k, ['date_from','date_to']), ARRAY_FILTER_USE_KEY)) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Effacer dates
            </a>
        @endif
    </x-slot:secondary>
</x-ob-toolbar>

{{-- ── Table ────────────────────────────────────────────────────────────────── --}}
<x-ob-commandbar table-id="virementsTable" :total="$items->total()" total-label="virement" :show-sel-count="false">

    @if ($items->isEmpty())
        <div class="ob-table-empty">
            Aucun virement trouvé pour les critères sélectionnés.
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="ob-table" id="virementsTable">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ $sortUrl('P_NOM') }}" class="text-decoration-none d-flex align-items-center">
                                Bénéficiaire {!! $sortIcon('P_NOM') !!}
                            </a>
                        </th>
                        @feature('multi_site')<th>Section</th>@endfeature
                        <th>
                            <a href="{{ $sortUrl('P_DATE_ENGAGEMENT') }}" class="text-decoration-none d-flex align-items-center">
                                Entrée {!! $sortIcon('P_DATE_ENGAGEMENT') !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('P_FIN') }}" class="text-decoration-none d-flex align-items-center">
                                Sortie {!! $sortIcon('P_FIN') !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('MONTANT') }}" class="text-decoration-none d-flex align-items-center">
                                Montant {!! $sortIcon('MONTANT') !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('PC_DATE') }}" class="text-decoration-none d-flex align-items-center">
                                Date virement {!! $sortIcon('PC_DATE') !!}
                            </a>
                        </th>
                        <th>Commentaire</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $row)
                        <tr data-href="{{ route('personnel.show', $row->P_ID) }}">
                            <td>
                                <a href="{{ route('personnel.show', $row->P_ID) }}" class="text-decoration-none fw-semibold">
                                    {{ strtoupper($row->P_NOM) }} {{ ucfirst(mb_strtolower($row->P_PRENOM)) }}
                                </a>
                            </td>
                            @feature('multi_site')<td>{{ $row->S_CODE ?? '—' }}</td>@endfeature
                            <td>{{ $row->P_DATE_ENGAGEMENT ? \Carbon\Carbon::parse($row->P_DATE_ENGAGEMENT)->format('d/m/Y') : '—' }}</td>
                            <td>{{ $row->P_FIN ? \Carbon\Carbon::parse($row->P_FIN)->format('d/m/Y') : '—' }}</td>
                            <td>{{ $row->MONTANT !== null ? number_format((float)$row->MONTANT, 2, ',', ' ') . ' €' : '—' }}</td>
                            <td>{{ $row->PC_DATE ? \Carbon\Carbon::parse($row->PC_DATE)->format('d/m/Y') : '—' }}</td>
                            <td class="text-muted">{{ $row->COMMENTAIRE ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <x-slot:pagination>
        {{ $items->links() }}
    </x-slot:pagination>

</x-ob-commandbar>

@endsection

