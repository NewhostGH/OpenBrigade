@extends('layout.app')

@section('title', 'Prélèvements — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Cotisations', 'url' => route('dues.index')],
    ['label' => 'Prélèvements'],
]"/>

@include('dues._tabs')

{{-- ── Toolbar ─────────────────────────────────────────────────────────────── --}}
<x-ob-toolbar
    title="Prélèvements"
    :total="$pending->count() + $paid->count()"
    total-label="membre"
    filter-action="{{ route('dues.direct-debits') }}"
    filter-cols="2fr 1.2fr 1.2fr auto">

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
            <select name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach ($periodes as $p)
                    <option value="{{ $p->P_CODE }}" {{ $periodeCode === $p->P_CODE ? 'selected' : '' }}>
                        {{ ucfirst($p->P_DESCRIPTION) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                @for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                    <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
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

        <span class="text-muted small">
            Mode de paiement&nbsp;: <strong>Prélèvement (TP_ID = 1)</strong>
            &mdash; Actifs uniquement
        </span>
    </x-slot:secondary>
</x-ob-toolbar>

{{-- ── Flash message ────────────────────────────────────────────────────────── --}}
@if (session('success'))
    <div class="alert alert-success mx-3 mt-2 py-2">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    </div>
@endif

{{-- ── Pending summary card ─────────────────────────────────────────────────── --}}
<div class="ob-commandbar-wrap mx-3 mt-2">

    <div class="p-3">

        @if ($pending->isEmpty() && $paid->isEmpty())
            <p class="ob-table-empty mb-0">
                Aucun membre avec prélèvement automatique pour la période sélectionnée.
            </p>
        @else

            <div class="row g-3 mb-3">
                {{-- Stat: pending --}}
                <div class="col-sm-4">
                    <div class="border rounded p-3 text-center bg-light">
                        <div class="fs-2 fw-bold text-warning">{{ $pending->count() }}</div>
                        <div class="text-muted small">à enregistrer</div>
                    </div>
                </div>
                {{-- Stat: paid --}}
                <div class="col-sm-4">
                    <div class="border rounded p-3 text-center bg-light">
                        <div class="fs-2 fw-bold text-success">{{ $paid->count() }}</div>
                        <div class="text-muted small">déjà enregistrés</div>
                    </div>
                </div>
                {{-- Stat: total pending amount --}}
                <div class="col-sm-4">
                    <div class="border rounded p-3 text-center bg-light">
                        <div class="fs-2 fw-bold">{{ number_format($totalPending, 2, ',', ' ') }} €</div>
                        <div class="text-muted small">montant estimé (réguls)</div>
                    </div>
                </div>
            </div>

            @if ($pending->count() > 0)
                {{-- Batch save form --}}
                <form method="POST" action="{{ route('dues.direct-debits.save') }}"
                      onsubmit="return confirm('Enregistrer {{ $pending->count() }} prélèvement(s) ?');">
                    @csrf
                    <input type="hidden" name="year"    value="{{ $year }}">
                    <input type="hidden" name="periode" value="{{ $periodeCode }}">
                    @if ($sectionId > 0)
                        <input type="hidden" name="section"     value="{{ $sectionId }}">
                        <input type="hidden" name="subsections" value="{{ $subsections ? 1 : 0 }}">
                    @endif

                    {{-- Hidden pids + per-person montant --}}
                    @foreach ($pending as $row)
                        <input type="hidden" name="pids[]" value="{{ $row->P_ID }}">
                        <input type="hidden" name="montant[{{ $row->P_ID }}]"
                               value="{{ (float) ($row->MONTANT_REGUL ?? 0) }}">
                    @endforeach

                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <label class="fw-semibold mb-0" for="date_prelev">
                            Date du prélèvement&nbsp;:
                        </label>
                        <input type="date" id="date_prelev" name="date_prelev"
                               class="form-control form-control-sm"
                               style="width:160px;"
                               value="{{ now()->format('Y-m-d') }}"
                               required>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-save me-1"></i>
                            Enregistrer les {{ $pending->count() }} prélèvements
                        </button>
                    </div>
                </form>
            @else
                <p class="text-success mb-0">
                    <i class="fas fa-check-circle me-1"></i>
                    Tous les prélèvements ont déjà été enregistrés pour cette période.
                </p>
            @endif

        @endif
    </div>

</div>

{{-- ── Pending members list ────────────────────────────────────────────────── --}}
@if ($pending->count() > 0)
<div class="ob-commandbar-wrap mx-3 mt-2">
    <div class="px-3 py-2 border-bottom" style="background:var(--card-subheader-bg);">
        <span class="fw-semibold text-muted" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:.04em;">
            À enregistrer ({{ $pending->count() }})
        </span>
    </div>
    <table class="ob-table">
        <thead>
            <tr>
                <th>Nom Prénom</th>
                @feature('multi_site')<th>Section</th>@endfeature
                <th>Montant régul</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pending as $row)
                <tr>
                    <td>
                        <a href="{{ route('personnel.show', $row->P_ID) }}" class="text-decoration-none fw-semibold">
                            {{ strtoupper($row->P_NOM) }} {{ ucfirst(mb_strtolower($row->P_PRENOM)) }}
                        </a>
                    </td>
                    @feature('multi_site')<td>{{ $row->S_CODE }}</td>@endfeature
                    <td>{{ $row->MONTANT_REGUL ? number_format((float)$row->MONTANT_REGUL, 2, ',', ' ') . ' €' : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Already-paid list ───────────────────────────────────────────────────── --}}
@if ($paid->count() > 0)
<div class="ob-commandbar-wrap mx-3 mt-2 mb-3">
    <div class="px-3 py-2 border-bottom" style="background:var(--card-subheader-bg);">
        <span class="fw-semibold text-muted" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:.04em;">
            Déjà enregistrés ({{ $paid->count() }}) — Total : {{ number_format($totalPaid, 2, ',', ' ') }} €
        </span>
    </div>
    <table class="ob-table">
        <thead>
            <tr>
                <th>Nom Prénom</th>
                @feature('multi_site')<th>Section</th>@endfeature
                <th>Montant</th>
                <th>Date prélevé</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($paid as $row)
                <tr>
                    <td>
                        <a href="{{ route('personnel.show', $row->P_ID) }}" class="text-decoration-none fw-semibold">
                            {{ strtoupper($row->P_NOM) }} {{ ucfirst(mb_strtolower($row->P_PRENOM)) }}
                        </a>
                    </td>
                    @feature('multi_site')<td>{{ $row->S_CODE }}</td>@endfeature
                    <td>{{ $row->MONTANT ? number_format((float)$row->MONTANT, 2, ',', ' ') . ' €' : '—' }}</td>
                    <td>{{ $row->PC_DATE ? \Carbon\Carbon::parse($row->PC_DATE)->format('d/m/Y') : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection
