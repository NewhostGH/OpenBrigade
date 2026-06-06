{{--
    ob-toolbar  —  Filter / action bar above list pages.

    Props
    ─────
    title           string   Page heading
    total           int|null Row count shown next to title
    filterCols      string   CSS grid-template-columns for the filter row
                             e.g. '2fr 1.4fr 1fr 2fr auto'
    filterAction    string   If set, wraps the filters slot in a <form> (GET)
    filterId        string   id for that form  (default: filterForm)

    Table controls — rendered automatically in secondary row (right-aligned)
    ─────────────────────────────────────────────────────────────────────────
    tableId         string   Associated table id  (required for controls)
    columns         array    ob-table column defs — used to build col-toggle
    exportXlsUrl    string   XLSX export base URL
    exportCsvUrl    string   CSV  export base URL
    showCardToggle  bool     Render card/table view toggle button

    Slots
    ─────
    (default)    Action buttons in the header (right of title)
    filters      Filter inputs — one child per grid cell
    secondary    Left-side secondary controls (per-page, toggles, clear …)
                 Table controls are appended automatically on the right.
--}}

@props([
    'title',
    'total'          => null,
    'filterCols'     => null,
    'filterAction'   => null,
    'filterId'       => 'filterForm',
    'tableId'        => null,
    'columns'        => [],
    'exportXlsUrl'   => null,
    'exportCsvUrl'   => null,
    'showCardToggle' => false,
])

@php
    $toggleCols  = array_values(array_filter($columns, fn($c) => !($c['alwaysVisible'] ?? false)));
    $hasControls = $tableId && (
        count($toggleCols) > 0 || $showCardToggle || $exportXlsUrl || $exportCsvUrl
    );
    $hasSecondary = $hasControls || (isset($secondary) && !$secondary->isEmpty());
@endphp

<div class="ob-toolbar">

    {{-- ── Header: title left, actions right ─────────────────────────────── --}}
    <div class="ob-toolbar-header">
        <div class="ob-toolbar-title">
            <h1 class="ob-toolbar-heading">{{ $title }}</h1>
            @if ($total !== null)
                <span class="ob-toolbar-count">{{ number_format($total) }}</span>
            @endif
        </div>

        @if (!$slot->isEmpty())
        <div class="ob-toolbar-actions noprint">
            {{ $slot }}
        </div>
        @endif
    </div>

    {{-- ── Filter form ────────────────────────────────────────────────────── --}}
    @if (isset($filters) && !$filters->isEmpty())
        @if ($filterAction)
        <form id="{{ $filterId }}" method="GET" action="{{ $filterAction }}"
              class="ob-toolbar-filters"
              @if ($filterCols) style="grid-template-columns: {{ $filterCols }}" @endif>
            {{ $filters }}
        </form>
        @else
        <div class="ob-toolbar-filters"
             @if ($filterCols) style="grid-template-columns: {{ $filterCols }}" @endif>
            {{ $filters }}
        </div>
        @endif
    @endif

    {{-- ── Secondary row ─────────────────────────────────────────────────── --}}
    @if ($hasSecondary)
    <div class="ob-toolbar-secondary noprint">

        {{-- User-supplied controls (left) --}}
        @if (isset($secondary) && !$secondary->isEmpty())
            {{ $secondary }}
        @endif

        {{-- Table controls (auto, right-aligned) --}}
        @if ($hasControls)
        <div class="ob-toolbar-table-controls">

            {{-- Column visibility dropdown --}}
            @if (count($toggleCols) > 0)
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false"
                        title="Colonnes visibles">
                    <i class="fa fa-columns fa-sm"></i>
                    <span class="d-none d-sm-inline ms-1">Colonnes</span>
                </button>
                <ul class="dropdown-menu p-2" style="min-width:190px;"
                    onclick="event.stopPropagation()">
                    <li>
                        <label class="dropdown-item rounded px-2 py-1 d-flex gap-2 align-items-center">
                            <input type="checkbox"
                                   data-col-toggle-all
                                   data-for-table="{{ $tableId }}">
                            <span class="fw-semibold">Tout basculer</span>
                        </label>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    @foreach ($toggleCols as $col)
                    <li>
                        <label class="dropdown-item rounded px-2 py-1 d-flex gap-2 align-items-center">
                            <input type="checkbox"
                                   data-col-toggle="{{ $col['key'] }}"
                                   data-for-table="{{ $tableId }}"
                                   {{ ($col['default'] ?? true) ? 'checked' : '' }}>
                            <span>{{ $col['label'] }}</span>
                        </label>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Card / table toggle --}}
            @if ($showCardToggle)
            <button type="button"
                    data-card-toggle
                    data-for-table="{{ $tableId }}"
                    class="btn btn-sm btn-light"
                    title="Vue carte / tableau">
                <i data-card-toggle-icon
                   data-for-table="{{ $tableId }}"
                   class="fa fa-toggle-off"></i>
                <span class="d-none d-sm-inline ms-1">Vue carte</span>
            </button>
            @endif

            {{-- XLSX export --}}
            @if ($exportXlsUrl)
            <a data-export-btn
               data-for-table="{{ $tableId }}"
               data-base-href="{{ $exportXlsUrl }}"
               href="{{ $exportXlsUrl }}"
               class="btn btn-sm btn-light"
               title="Exporter Excel (.xlsx) — colonnes visibles">
                <i class="far fa-file-excel" style="color:var(--color-excel);"></i>
            </a>
            @endif

            {{-- CSV export --}}
            @if ($exportCsvUrl)
            <a data-export-btn
               data-for-table="{{ $tableId }}"
               data-base-href="{{ $exportCsvUrl }}"
               href="{{ $exportCsvUrl }}"
               class="btn btn-sm btn-light"
               title="Exporter CSV — colonnes visibles">
                <i class="fas fa-file-csv" style="color:var(--text-muted-soft);"></i>
            </a>
            @endif

        </div>
        @endif

    </div>
    @endif

</div>
