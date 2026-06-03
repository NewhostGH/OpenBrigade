@extends('layout.app')

@section('title', 'Cotisations — ' . config('app.name'))

@section('content')

@php
    $today       = now()->format('Y-m-d');
    $paidCount   = $items->whereNotNull('PC_DATE')->count();
    $unpaidCount = $items->whereNull('PC_DATE')->count();
    $totalPaid   = $items->whereNotNull('PC_DATE')->sum('MONTANT');

    $sortUrl = fn(string $field) => route('cotisations.index', array_merge(request()->query(), ['order' => $field]));
    $sortIcon = fn(string $field) => '<i class="fas fa-sort' . ($order === $field ? '-down active' : '') . ' sort-icon ms-1"></i>';
@endphp

<x-ob-breadcrumb :items="[
    ['label' => 'Cotisations'],
]"/>

{{-- ── Toolbar ─────────────────────────────────────────────────────────────── --}}
<x-ob-toolbar
    title="Cotisations"
    :total="$items->count()"
    total-label="membre"
    filter-action="{{ route('cotisations.index') }}"
    filter-cols="2fr 1.2fr 1.2fr 1fr 1fr auto">

    {{-- Actions: print + export --}}
    <button class="btn btn-sm btn-light" onclick="window.print()" title="Imprimer">
        <i class="fas fa-print"></i>
    </button>
    <a class="btn btn-sm btn-light"
       href="{{ route('cotisations.export', request()->query()) }}"
       title="Exporter Excel">
        <i class="far fa-file-excel" style="color:#1d6f42;"></i>
    </a>

    {{-- Filters --}}
    <x-slot:filters>
        <div>
            <select name="section" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="0" {{ $sectionId === 0 ? 'selected' : '' }}>Toutes sections</option>
                @foreach ($sectionOptions as $opt)
                    @php
                        $depth = $opt['depth'];
                        $bgs   = ['#FFCC33','#FFFF99','#B7D8FB','#D4F1C0','#F0E6FF'];
                        $bg    = $bgs[min($depth, count($bgs) - 1)];
                        $pad   = round(1.2 + $depth * 0.5, 1);
                        $lbl   = $opt['S_CODE'];
                        if ($opt['S_DESCRIPTION']) {
                            $lbl .= ' — ' . \Illuminate\Support\Str::limit($opt['S_DESCRIPTION'], 22);
                        }
                    @endphp
                    <option value="{{ $opt['S_ID'] }}"
                            style="padding-left:{{ $pad }}rem; background:{{ $bg }};"
                            {{ $sectionId === $opt['S_ID'] ? 'selected' : '' }}>
                        {{ $lbl }}
                    </option>
                @endforeach
            </select>
        </div>

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

        <div>
            <select name="type_paiement" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="ALL" {{ $tpId === 'ALL' ? 'selected' : '' }}>Tous modes</option>
                @foreach ($typesPaiement as $tp)
                    <option value="{{ $tp->TP_ID }}" {{ (string)$tpId === (string)$tp->TP_ID ? 'selected' : '' }}>
                        {{ $tp->TP_DESCRIPTION }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="paid" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="2" {{ $paid === '2' ? 'selected' : '' }}>Tout afficher</option>
                <option value="0" {{ $paid === '0' ? 'selected' : '' }}>Pas encore payé</option>
                <option value="1" {{ $paid === '1' ? 'selected' : '' }}>Paiement enregistré</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="fas fa-filter me-1"></i> Filtrer
            </button>
        </div>

        <input type="hidden" name="subsections" value="{{ $subsections ? 1 : 0 }}">
        <input type="hidden" name="include_old" value="{{ $includeOld ? 1 : 0 }}">
        <input type="hidden" name="order"       value="{{ $order }}">
    </x-slot:filters>

    {{-- Secondary: toggles + stats --}}
    <x-slot:secondary>
        @if ($sectionId > 0)
            <div class="ob-toggle-switch">
                <label for="subsToggle">Sous-sections</label>
                <label class="ob-switch">
                    <input type="checkbox" id="subsToggle" {{ $subsections ? 'checked' : '' }}
                           onchange="updateParam('subsections', this.checked ? 1 : 0)">
                    <span class="slider"></span>
                </label>
            </div>
        @endif

        <div class="ob-toggle-switch">
            <label for="oldToggle">Archivés</label>
            <label class="ob-switch">
                <input type="checkbox" id="oldToggle" {{ $includeOld ? 'checked' : '' }}
                       onchange="updateParam('include_old', this.checked ? 1 : 0)">
                <span class="slider"></span>
            </label>
        </div>

        @if ($items->count() > 0)
            <span style="color:var(--component-border)">|</span>
            <span style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                <span class="badge bg-success">{{ $paidCount }} payé(s)</span>
                <span class="badge bg-secondary ms-1">{{ $unpaidCount }} en attente</span>
                @if ($totalPaid > 0)
                    <span class="ms-2">Total encaissé : <strong>{{ number_format($totalPaid, 2, ',', ' ') }} €</strong></span>
                @endif
            </span>
        @endif
    </x-slot:secondary>

</x-ob-toolbar>

{{-- ── Table card ──────────────────────────────────────────────────────────── --}}
<x-ob-commandbar
    table-id="cotisationsTable"
    :total="$items->count()"
    total-label="membre"
    action="{{ route('cotisations.save') }}"
    :show-sel-count="false">

    {{-- Hidden filter params so the save redirect goes back to the same view --}}
    <input type="hidden" name="year"          value="{{ $year }}">
    <input type="hidden" name="periode"       value="{{ $periodeCode }}">
    <input type="hidden" name="section"       value="{{ $sectionId }}">
    <input type="hidden" name="subsections"   value="{{ $subsections ? 1 : 0 }}">
    <input type="hidden" name="type_paiement" value="{{ $tpId }}">
    <input type="hidden" name="paid"          value="{{ $paid }}">
    <input type="hidden" name="include_old"   value="{{ $includeOld ? 1 : 0 }}">

    @foreach ($items as $row)
        <input type="hidden" name="people[]" value="{{ $row->P_ID }}">
    @endforeach

    @if ($items->isEmpty())
        <div class="ob-table-empty p-4">Aucun membre trouvé pour ces critères.</div>
    @else
    <div class="table-responsive">
        <table class="ob-table" id="cotisationsTable">
            <thead>
                <tr>
                    <th>
                        <a href="{{ $sortUrl('P_NOM') }}" class="text-reset text-decoration-none">
                            Nom Prénom {!! $sortIcon('P_NOM') !!}
                        </a>
                    </th>
                    <th class="d-none d-md-table-cell">
                        <a href="{{ $sortUrl('P_STATUT') }}" class="text-reset text-decoration-none">
                            Statut {!! $sortIcon('P_STATUT') !!}
                        </a>
                    </th>
                    <th class="d-none d-lg-table-cell">
                        <a href="{{ $sortUrl('P_SECTION') }}" class="text-reset text-decoration-none">
                            Section {!! $sortIcon('P_SECTION') !!}
                        </a>
                    </th>
                    <th class="d-none d-lg-table-cell">
                        <a href="{{ $sortUrl('P_DATE_ENGAGEMENT') }}" class="text-reset text-decoration-none">
                            Entrée {!! $sortIcon('P_DATE_ENGAGEMENT') !!}
                        </a>
                    </th>
                    <th class="d-none d-lg-table-cell">
                        <a href="{{ $sortUrl('P_FIN') }}" class="text-reset text-decoration-none">
                            Sortie {!! $sortIcon('P_FIN') !!}
                        </a>
                    </th>
                    <th style="text-align:center;">
                        <a href="{{ $sortUrl('PC_ID') }}" class="text-reset text-decoration-none">
                            Payé {!! $sortIcon('PC_ID') !!}
                        </a>
                    </th>
                    <th style="text-align:center;width:90px;">Montant</th>
                    <th style="text-align:center;">
                        <a href="{{ $sortUrl('PC_DATE') }}" class="text-reset text-decoration-none">
                            Date payé {!! $sortIcon('PC_DATE') !!}
                        </a>
                    </th>
                    <th class="d-none d-xl-table-cell">Commentaire</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $row)
                    @php
                        $isPaid   = ! is_null($row->PC_DATE);
                        $amtColor = $isPaid ? 'color:var(--bs-success)' : 'color:var(--bs-secondary)';
                    @endphp
                    <tr id="row-{{ $row->P_ID }}" class="{{ $isPaid ? '' : 'table-light' }}">

                        {{-- Name --}}
                        <td>
                            <a href="{{ route('personnel.show', $row->P_ID) }}"
                               style="font-size:var(--font-size-sm);">
                                {{ strtoupper($row->P_NOM) }} {{ ucfirst(strtolower($row->P_PRENOM)) }}
                            </a>
                        </td>

                        {{-- Statut badge --}}
                        <td class="d-none d-md-table-cell">
                            <span class="ob-badge @if($row->P_STATUT === 'BEN') ob-badge-ben @elseif($row->P_STATUT === 'PRES') ob-badge-pres @elseif($row->P_STATUT === 'EXT') ob-badge-ext @else ob-badge-int @endif">
                                {{ $row->P_STATUT }}
                            </span>
                        </td>

                        {{-- Section --}}
                        <td class="d-none d-lg-table-cell" style="font-size:var(--font-size-xs);">
                            {{ $row->S_CODE }}
                        </td>

                        {{-- Entrée --}}
                        <td class="d-none d-lg-table-cell" style="font-size:var(--font-size-xs);white-space:nowrap;">
                            {{ $row->P_DATE_ENGAGEMENT ? \Carbon\Carbon::parse($row->P_DATE_ENGAGEMENT)->format('d/m/Y') : '—' }}
                        </td>

                        {{-- Sortie --}}
                        <td class="d-none d-lg-table-cell" style="font-size:var(--font-size-xs);white-space:nowrap;">
                            {{ $row->P_FIN ? \Carbon\Carbon::parse($row->P_FIN)->format('d/m/Y') : '—' }}
                        </td>

                        {{-- Paid checkbox --}}
                        <td style="text-align:center;" onclick="event.stopPropagation()">
                            <div class="form-check d-flex justify-content-center">
                                <input type="checkbox"
                                       class="form-check-input paid-check"
                                       name="payments[{{ $row->P_ID }}]"
                                       id="paid-{{ $row->P_ID }}"
                                       value="1"
                                       data-pid="{{ $row->P_ID }}"
                                       data-today="{{ $today }}"
                                       {{ $isPaid ? 'checked' : '' }}
                                       onchange="onPaidToggle(this)">
                            </div>
                        </td>

                        {{-- Amount --}}
                        <td style="text-align:center;" onclick="event.stopPropagation()">
                            <input type="number" step="0.01" min="0"
                                   class="form-control form-control-sm text-end"
                                   style="{{ $amtColor }};width:80px;"
                                   name="montant[{{ $row->P_ID }}]"
                                   id="montant-{{ $row->P_ID }}"
                                   value="{{ $row->MONTANT ?? '' }}"
                                   placeholder="0.00"
                                   onchange="onAmountChange(this, {{ $row->P_ID }})">
                        </td>

                        {{-- Date paid --}}
                        <td style="text-align:center;" onclick="event.stopPropagation()">
                            <input type="date"
                                   class="form-control form-control-sm"
                                   style="width:140px;"
                                   name="date_paid[{{ $row->P_ID }}]"
                                   id="date-{{ $row->P_ID }}"
                                   value="{{ $row->PC_DATE ? \Carbon\Carbon::parse($row->PC_DATE)->format('Y-m-d') : '' }}"
                                   onchange="onDateChange(this, {{ $row->P_ID }})">
                        </td>

                        {{-- Comment --}}
                        <td class="d-none d-xl-table-cell" onclick="event.stopPropagation()">
                            <input type="text" maxlength="100"
                                   class="form-control form-control-sm"
                                   name="commentaire[{{ $row->P_ID }}]"
                                   value="{{ $row->COMMENTAIRE ?? '' }}"
                                   placeholder="Commentaire…">
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Footer actions --}}
    <x-slot:actions>
        <div class="form-check d-flex align-items-center gap-2 noprint">
            <input type="checkbox" id="checkAllBox" class="form-check-input"
                   onchange="toggleCheckAll(this)">
            <label for="checkAllBox" class="form-check-label" style="font-size:var(--font-size-sm);">
                Tout cocher
            </label>
        </div>
        <span id="paidCounter" class="badge bg-success ms-1">{{ $paidCount }} payé(s)</span>
        @if (!$items->isEmpty())
        <button type="submit" class="btn btn-sm btn-success">
            <i class="fas fa-save me-1"></i> Enregistrer
        </button>
        @endif
    </x-slot:actions>

</x-ob-commandbar>

@push('scripts')
<script>
(function () {
    let paidCount = {{ $paidCount }};

    function updateCounter() {
        const el = document.getElementById('paidCounter');
        if (el) el.textContent = paidCount + ' payé(s)';
    }

    window.onPaidToggle = function (checkbox) {
        const pid         = checkbox.dataset.pid;
        const today       = checkbox.dataset.today;
        const dateField   = document.getElementById('date-'    + pid);
        const amountField = document.getElementById('montant-' + pid);
        const row         = document.getElementById('row-'     + pid);

        if (checkbox.checked) {
            paidCount++;
            if (dateField   && !dateField.value) dateField.value = today;
            if (amountField) amountField.style.color = 'var(--bs-success)';
            if (row) row.classList.remove('table-light');
        } else {
            paidCount = Math.max(0, paidCount - 1);
            if (dateField)   dateField.value = '';
            if (amountField) amountField.style.color = 'var(--bs-secondary)';
            if (row) row.classList.add('table-light');
        }
        updateCounter();
    };

    window.onAmountChange = function (input, pid) {
        const check = document.getElementById('paid-' + pid);
        if (check && !check.checked && parseFloat(input.value) > 0) {
            check.checked = true;
            check.dispatchEvent(new Event('change'));
        }
    };

    window.onDateChange = function (input, pid) {
        const check = document.getElementById('paid-' + pid);
        if (check && !check.checked && input.value) {
            check.checked = true;
            check.dispatchEvent(new Event('change'));
        }
    };

    window.toggleCheckAll = function (masterCheckbox) {
        document.querySelectorAll('.paid-check').forEach(function (c) {
            if (c.checked !== masterCheckbox.checked) {
                c.checked = masterCheckbox.checked;
                c.dispatchEvent(new Event('change'));
            }
        });
    };
})();
</script>
@endpush

@endsection
