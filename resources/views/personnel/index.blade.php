@extends('layout.app')

@section('title', 'Personnel - ' . config('app.name'))

@push('styles')
<style>
/* ── Personnel list layout ─────────────────────────────────────── */
.personnel-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 12px;
    border-bottom: 1px solid var(--card-border);
    background: var(--bg-base);
    gap: 8px;
    flex-wrap: wrap;
}
.personnel-topbar .breadcrumb { margin: 0; background: transparent; padding: 0; }

.personnel-toolbar {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    flex-wrap: wrap;
    border-bottom: 1px solid var(--card-border);
    background: var(--bg-subtle);
}

/* ── Toggle switch ─────────────────────────────────────────────── */
.toggle-switch { display: flex; align-items: center; gap: 6px; font-size: var(--font-size-sm); white-space: nowrap; }
.toggle-switch label.mb-0 { cursor: default; }
.switch { position: relative; display: inline-block; width: 34px; height: 18px; margin: 0; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
    position: absolute; cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background: var(--sidebar-border);
    border-radius: 18px; transition: .2s;
}
.slider:before {
    position: absolute; content: "";
    height: 12px; width: 12px; left: 3px; bottom: 3px;
    background: white; border-radius: 50%; transition: .2s;
}
input:checked + .slider { background: var(--accent); }
input:checked + .slider:before { transform: translateX(16px); }

/* ── Toolbar selects ───────────────────────────────────────────── */
.personnel-toolbar select.form-select {
    font-size: var(--font-size-sm);
    padding: 3px 28px 3px 8px;
    height: 30px;
    min-width: 110px;
    max-width: 210px;
}
.personnel-toolbar .btn-sm { height: 30px; padding: 0 8px; font-size: var(--font-size-sm); }

/* ── Status badges ─────────────────────────────────────────────── */
.badge-personnel {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.70rem;
    font-weight: 600;
    white-space: nowrap;
    line-height: 1.4;
}
.badge-ben  { background: #FFCC33; color: #5a4000; }
.badge-ext  { background: #e2e8f0; color: #475569; }
.badge-pres { background: #E8D5F5; color: #6b21a8; }
.badge-int  { background: #dbeafe; color: #1e40af; }
.badge-actif   { background: #dcfce7; color: #166534; }
.badge-archive { background: #f1f5f9; color: #64748b; }
.badge-bloqued { background: #fee2e2; color: #991b1b; }

/* ── Table ─────────────────────────────────────────────────────── */
.personnel-table { border-collapse: collapse; width: 100%; margin: 0; }
.personnel-table thead th {
    font-size: var(--font-size-sm);
    font-weight: 600;
    padding: 6px 8px;
    border-bottom: 2px solid var(--card-border);
    background: var(--bg-base);
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 2;
}
.personnel-table thead th.sortable { cursor: pointer; user-select: none; }
.personnel-table thead th.sortable:hover { background: var(--sidebar-bg-hover); }
.personnel-table tbody td {
    padding: 4px 8px;
    vertical-align: middle;
    font-size: var(--font-size-sm);
    border-bottom: 1px solid var(--card-border);
}
.personnel-table tbody tr:hover { background: var(--sidebar-bg-hover); }
.personnel-table tbody tr:hover { cursor: pointer; }
.personnel-table tbody tr.selected { background: color-mix(in srgb, var(--accent) 8%, transparent); }

.grade-img { height: 26px; width: auto; border-radius: 3px; vertical-align: middle; }
.personnel-photo {
    height: 36px; width: 36px;
    border-radius: 4px; object-fit: cover; vertical-align: middle;
}

/* ── Card / table view ─────────────────────────────────────────── */
#personnelTable.cards thead { display: none; }
#personnelTable.cards tbody {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding: 12px;
}
#personnelTable.cards tbody tr {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 130px;
    border: 1px solid var(--card-border);
    border-radius: var(--radius);
    padding: 10px 8px 8px;
    cursor: pointer;
    text-align: center;
    background: var(--bg-base);
    transition: box-shadow var(--transition-fast);
}
#personnelTable.cards tbody tr:hover { box-shadow: 0 2px 10px rgba(0,0,0,.12); }
#personnelTable.cards tbody td { display: none !important; padding: 2px 0; }
#personnelTable.cards tbody td.card-show { display: block !important; }
#personnelTable.cards tbody td.card-show img.personnel-photo {
    height: 58px; width: 58px; border-radius: 50%;
}

/* ── Column hidden class ───────────────────────────────────────── */
.col-hidden { display: none !important; }

/* ── Action bar ────────────────────────────────────────────────── */
.personnel-actions {
    display: flex;
    gap: 6px;
    padding: 8px 12px;
    border-top: 1px solid var(--card-border);
    background: var(--bg-subtle);
    flex-wrap: wrap;
    align-items: center;
}
.personnel-actions .btn { font-size: var(--font-size-sm); }

/* ── Mobile ────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .hide-mobile { display: none !important; }
}
</style>
@endpush

@section('content')

{{-- ── Top bar ─────────────────────────────────────────────────── --}}
<div class="personnel-topbar noprint">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" style="color:#666;font-size:0.87rem;font-weight:bold;">Accueil</a>
            </li>
            <li class="breadcrumb-item" style="font-size:0.87rem;font-weight:bold;color:#666;">Personnel</li>
            <li class="breadcrumb-item active" aria-current="page"
                style="color:#2b224f;font-size:0.87rem;font-weight:bold;">Liste</li>
        </ol>
    </nav>
    <div class="d-flex gap-2 align-items-center noprint">
        <button class="btn btn-sm btn-light" onclick="window.print()" title="Imprimer">
            <i class="fas fa-print"></i>
        </button>
        <a class="btn btn-sm btn-light"
           href="{{ route('personnel.index', array_merge(request()->query(), ['export' => 'xls'])) }}"
           title="Exporter Excel">
            <i class="far fa-file-excel" style="color:#1d6f42;"></i>
        </a>
        <a class="btn btn-sm btn-success"
           href="{{ route('dashboard.legacy') }}?redirect={{ urlencode('ins_personnel.php?category=INT&suggestedcompany=-1') }}"
           title="Ajouter du personnel">
            <i class="fa fa-user-plus"></i>
            <span class="d-none d-sm-inline"> Ajouter</span>
        </a>
    </div>
</div>

{{-- ── Filter toolbar ──────────────────────────────────────────── --}}
<div class="personnel-toolbar noprint">

    {{-- Subsection toggle (only when a section is selected) --}}
    @if ($sectionId > 0)
        <div class="toggle-switch">
            <label for="subsToggle" class="mb-0">Sous-sections</label>
            <label class="switch mb-0">
                <input type="checkbox" id="subsToggle" {{ $subsections ? 'checked' : '' }}
                    onchange="updateParam('subsections', this.checked ? 1 : 0)">
                <span class="slider"></span>
            </label>
        </div>
    @endif

    {{-- Hierarchical section select --}}
    <select id="sectionFilter" class="form-select"
        onchange="updateParam('section', this.value)">
        <option value="0" {{ $sectionId === 0 ? 'selected' : '' }}>Toutes sections</option>
        @foreach ($sectionOptions as $opt)
            @php
                $depth = $opt['depth'];
                $bgColors = ['#FFCC33', '#FFFF99', '#B7D8FB', '#D4F1C0', '#F0E6FF'];
                $bg = $bgColors[min($depth, count($bgColors) - 1)];
                $pad = round(1.2 + $depth * 0.5, 1);
                $label = $opt['S_CODE'];
                if ($opt['S_DESCRIPTION']) {
                    $trimmed = \Illuminate\Support\Str::limit($opt['S_DESCRIPTION'], 22);
                    $label .= ' - ' . $trimmed;
                }
            @endphp
            <option value="{{ $opt['S_ID'] }}"
                style="padding-left:{{ $pad }}rem;background:{{ $bg }};"
                {{ $sectionId === $opt['S_ID'] ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>

    {{-- Category filter --}}
    <select id="categoryFilter" class="form-select"
        onchange="updateParam('category', this.value)">
        <option value="ALL"  {{ $category === 'ALL'  ? 'selected' : '' }}>Tous</option>
        <option value="INT"  {{ $category === 'INT'  ? 'selected' : '' }}>Tous sauf externes</option>
        <option value="BEN"  {{ $category === 'BEN'  ? 'selected' : '' }}>Personnel bénévole</option>
        <option value="EXT"  {{ $category === 'EXT'  ? 'selected' : '' }}>Personnel externe</option>
        <option value="PRES" {{ $category === 'PRES' ? 'selected' : '' }}>Prestataire</option>
    </select>

    {{-- Position filter --}}
    <select id="positionFilter" class="form-select"
        onchange="updateParam('position', this.value)">
        <option value="all"    {{ $position === 'all'    ? 'selected' : '' }}>Tous</option>
        <option value="actif"  {{ $position === 'actif'  ? 'selected' : '' }}>Actif</option>
        <option value="archive"{{ $position === 'archive'? 'selected' : '' }}>Archivé</option>
        <option value="bloqued"{{ $position === 'bloqued'? 'selected' : '' }}>Bloqué</option>
    </select>

    {{-- Column visibility dropdown --}}
    <div class="dropdown">
        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
            data-bs-toggle="dropdown" aria-expanded="false" title="Colonnes visibles">
            <i class="fa fa-th-list"></i>
        </button>
        <div class="dropdown-menu p-2" style="min-width:185px;" onclick="event.stopPropagation()">
            <label class="dropdown-item p-1 d-flex gap-2 align-items-center">
                <input type="checkbox" id="colToggleAll"> <span>Tout basculer</span>
            </label>
            <hr class="dropdown-divider my-1">
            @foreach ([
                ['photo',     'Photo'],
                ['grade',     'Grade'],
                ['birthdate', 'Date de naissance'],
                ['phone',     'Téléphone'],
                ['code',      'Matricule'],
                ['section',   'Section'],
                ['entree',    "Date d'entrée"],
                ['statut',    'Statut'],
                ['etat',      'Position'],
            ] as [$col, $lbl])
                <label class="dropdown-item p-1 d-flex gap-2 align-items-center">
                    <input type="checkbox" class="col-toggle-check" data-col="{{ $col }}" checked>
                    <span>{{ $lbl }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Card / Table view toggle --}}
    <button type="button" id="viewToggleBtn" class="btn btn-sm btn-secondary"
        title="Basculer vue carte / tableau">
        <i class="fa fa-toggle-off" id="viewToggleIcon"></i>
    </button>

    {{-- Page size --}}
    <select class="form-select" style="width:auto;" onchange="updateParam('perPage', this.value)">
        @foreach ([12, 24, 48, 100, 500] as $ps)
            <option value="{{ $ps }}" {{ $perPage == $ps ? 'selected' : '' }}>{{ $ps }} / page</option>
        @endforeach
    </select>

    {{-- Search --}}
    <div class="ms-auto d-flex gap-1 align-items-center">
        <form method="GET" action="{{ route('personnel.index') }}" class="d-flex gap-1"
              id="searchForm">
            @foreach (['section' => $sectionId, 'category' => $category, 'position' => $position,
                       'order' => $order, 'perPage' => $perPage,
                       'subsections' => $subsections ? 1 : 0] as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <input type="search" name="q" id="searchInput"
                class="form-control form-control-sm"
                placeholder="Rechercher…"
                value="{{ $search }}"
                style="min-width:160px; height:30px;">
        </form>
        @if ($search)
            <a href="{{ route('personnel.index', array_merge(array_filter(request()->query(), fn($k) => $k !== 'q', ARRAY_FILTER_USE_KEY), ['page' => 1])) }}"
               class="btn btn-sm btn-outline-secondary" title="Effacer la recherche"
               style="height:30px;padding:0 8px;display:flex;align-items:center;">
                <i class="fas fa-times"></i>
            </a>
        @endif
    </div>
</div>

{{-- ── Table ───────────────────────────────────────────────────── --}}
<div class="container-fluid px-0">
    <form id="personnelForm" method="POST">
        @csrf
        <div class="table-responsive" style="max-height:calc(100vh - 220px); overflow-y:auto;">
            <table id="personnelTable" class="table personnel-table mb-0">
                <thead>
                    <tr>
                        <th style="width:28px;" class="no-sort">
                            <input type="checkbox" id="checkAll" title="Tout sélectionner / désélectionner">
                        </th>
                        <th data-col="photo" class="col-th">Photo</th>
                        <th data-col="grade" class="col-th">Grade</th>
                        <th data-col="name" class="col-th sortable" data-sort="P_NOM">
                            Nom Prénom
                            @if ($order === 'P_NOM')<i class="fas fa-sort-down ms-1 text-primary"></i>
                            @elseif ($order === 'P_PRENOM')<i class="fas fa-sort-up ms-1 text-primary"></i>
                            @else<i class="fas fa-sort ms-1 text-muted"></i>@endif
                        </th>
                        <th data-col="birthdate" class="col-th hide-mobile sortable" data-sort="P_BIRTHDATE">
                            Date de naissance
                            @if ($order === 'P_BIRTHDATE')<i class="fas fa-sort-down ms-1 text-primary"></i>
                            @else<i class="fas fa-sort ms-1 text-muted"></i>@endif
                        </th>
                        <th data-col="phone" class="col-th hide-mobile">Téléphone</th>
                        <th data-col="code" class="col-th hide-mobile sortable" data-sort="P_CODE">
                            Matricule
                            @if ($order === 'P_CODE')<i class="fas fa-sort-down ms-1 text-primary"></i>
                            @else<i class="fas fa-sort ms-1 text-muted"></i>@endif
                        </th>
                        <th data-col="section" class="col-th hide-mobile">Section</th>
                        <th data-col="entree" class="col-th hide-mobile sortable" data-sort="P_DATE_ENGAGEMENT">
                            Date d'entrée
                            @if ($order === 'P_DATE_ENGAGEMENT')<i class="fas fa-sort-down ms-1 text-primary"></i>
                            @else<i class="fas fa-sort ms-1 text-muted"></i>@endif
                        </th>
                        <th data-col="statut" class="col-th hide-mobile">Statut</th>
                        <th data-col="etat" class="col-th hide-mobile">Position</th>
                        <th style="width:38px;" class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $etat = (int) $item->GP_ID === -1 ? 'Bloqué'
                                  : ((int) $item->P_OLD_MEMBER > 0 ? 'Archivé' : 'Actif');
                            $etatClass = match($etat) {
                                'Actif'   => 'badge-actif',
                                'Archivé' => 'badge-archive',
                                default   => 'badge-bloqued',
                            };
                            $statutMap = [
                                'BEN'  => ['Personnel bénévole', 'badge-ben'],
                                'EXT'  => ['Personnel externe',  'badge-ext'],
                                'PRES' => ['Prestataire',        'badge-pres'],
                            ];
                            [$statutLabel, $statutClass] = $statutMap[$item->P_STATUT] ?? [$item->P_STATUT, 'badge-int'];
                        @endphp
                        <tr data-id="{{ $item->P_ID }}"
                            data-email="{{ $item->P_EMAIL ?? '' }}"
                            onclick="rowClick(event, {{ $item->P_ID }})">

                            {{-- Checkbox --}}
                            <td onclick="event.stopPropagation()">
                                <input type="checkbox" name="ids[]" class="row-check"
                                    value="{{ $item->P_ID }}"
                                    data-email="{{ $item->P_EMAIL ?? '' }}">
                            </td>

                            {{-- Photo --}}
                            <td data-col="photo" class="col-td card-show">
                                <img src="{{ route('personnel.photo', $item) }}"
                                     alt="Photo"
                                     class="personnel-photo"
                                     loading="lazy">
                            </td>

                            {{-- Grade --}}
                            <td data-col="grade" class="col-td">
                                @if ($item->P_GRADE)
                                    <img src="{{ route('personnel.grade_image', ['grade' => $item->P_GRADE]) }}"
                                         alt="{{ $item->P_GRADE }}" title="{{ $item->P_GRADE }}"
                                         class="grade-img"
                                         onerror="this.outerHTML='<span class=\'text-muted\' style=\'font-size:.75rem\'>' + '{{ e($item->P_GRADE) }}' + '</span>'">
                                @else
                                    <img src="{{ route('personnel.grade_image', ['grade' => 'NR']) }}"
                                         alt="NR" title="Non renseigné" class="grade-img">
                                @endif
                            </td>

                            {{-- Nom Prénom --}}
                            <td data-col="name" class="col-td card-show">
                                <strong>{{ $item->P_NOM }} {{ $item->P_PRENOM }}</strong>
                            </td>

                            {{-- Date de naissance --}}
                            <td data-col="birthdate" class="col-td hide-mobile">
                                {{ $item->P_BIRTHDATE?->format('d/m/Y') ?: '-' }}
                            </td>

                            {{-- Téléphone --}}
                            <td data-col="phone" class="col-td hide-mobile">
                                @if ($item->P_PHONE)
                                    {{ $item->P_PHONE }}
                                    @if ($item->P_PHONE2)<br>{{ $item->P_PHONE2 }}@endif
                                @elseif ($item->P_PHONE2)
                                    {{ $item->P_PHONE2 }}
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Matricule --}}
                            <td data-col="code" class="col-td hide-mobile">{{ $item->P_CODE }}</td>

                            {{-- Section --}}
                            <td data-col="section" class="col-td hide-mobile">
                                {{ $item->section?->S_CODE ?: '-' }}
                            </td>

                            {{-- Date d'entrée --}}
                            <td data-col="entree" class="col-td hide-mobile">
                                {{ $item->P_DATE_ENGAGEMENT?->format('d/m/Y') ?: '-' }}
                            </td>

                            {{-- Statut --}}
                            <td data-col="statut" class="col-td hide-mobile card-show">
                                <span class="badge-personnel {{ $statutClass }}">{{ $statutLabel }}</span>
                            </td>

                            {{-- Position --}}
                            <td data-col="etat" class="col-td hide-mobile">
                                <span class="badge-personnel {{ $etatClass }}">{{ $etat }}</span>
                            </td>

                            {{-- Edit button --}}
                            <td onclick="event.stopPropagation()">
                                <a href="{{ route('personnel.edit', $item) }}"
                                   class="btn btn-sm btn-light py-0 px-1" title="Modifier"
                                   onclick="event.stopPropagation()">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">
                                <i class="fas fa-user-slash fa-2x d-block mb-2 opacity-25"></i>
                                Aucun personnel trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Action bar ─────────────────────────────────────── --}}
        <div class="personnel-actions noprint">
            <span class="text-muted me-1" style="font-size:var(--font-size-xs);">
                <span id="selCount">0</span> sélectionné(s)
            </span>
            <button type="button" class="btn btn-sm btn-light" style="color:#198754;"
                onclick="personnelAction('mail')" title="Envoyer un message via l'application">
                <i class="fas fa-envelope"></i> Envoyer
            </button>
            <button type="button" class="btn btn-sm btn-light" style="color:#0d6efd;"
                onclick="personnelAction('badge')" title="Imprimer des badges PDF">
                <i class="fas fa-id-badge"></i> Badges
            </button>
            <button type="button" class="btn btn-sm btn-light" style="color:#0dcaf0;"
                onclick="personnelMailto()" title="Ouvrir votre client mail avec les adresses sélectionnées">
                <i class="fas fa-at"></i> Mail
            </button>
            <button type="button" class="btn btn-sm btn-light"
                onclick="personnelAction('listemails')" title="Télécharger la liste des emails">
                <i class="fas fa-download"></i> Télécharger
            </button>
            <input type="hidden" name="SelectionMail" id="SelectionMail">
        </div>
    </form>

    {{-- Pagination + count --}}
    <div class="d-flex justify-content-between align-items-center px-3 py-2 flex-wrap gap-2">
        <div class="noprint">{{ $items->links() }}</div>
        <span class="text-muted" style="font-size:var(--font-size-sm);">
            {{ number_format($items->total()) }} personne{{ $items->total() > 1 ? 's' : '' }}
        </span>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── URL param helper ────────────────────────────────────────────
    function updateParam(key, value) {
        var url = new URL(window.location.href);
        url.searchParams.set(key, value);
        url.searchParams.delete('page'); // reset to page 1
        window.location.href = url.toString();
    }
    window.updateParam = updateParam;

    // ── Column visibility ───────────────────────────────────────────
    var STORAGE_KEY = 'personnelColsV2';
    var defaultCols = {
        photo: true, grade: true, birthdate: true,
        phone: true, code: true, section: true,
        entree: true, statut: true, etat: true
    };
    var activeCols;
    try {
        activeCols = Object.assign({}, defaultCols, JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'));
    } catch (e) {
        activeCols = Object.assign({}, defaultCols);
    }

    function applyColVisibility() {
        Object.keys(activeCols).forEach(function (col) {
            var visible = activeCols[col];
            document.querySelectorAll('[data-col="' + col + '"]').forEach(function (el) {
                el.classList.toggle('col-hidden', !visible);
            });
        });
    }

    document.querySelectorAll('.col-toggle-check').forEach(function (cb) {
        var col = cb.dataset.col;
        cb.checked = activeCols[col] !== false;
        cb.addEventListener('change', function () {
            activeCols[col] = this.checked;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(activeCols));
            applyColVisibility();
            syncToggleAll();
        });
    });

    var toggleAllCb = document.getElementById('colToggleAll');
    function syncToggleAll() {
        if (!toggleAllCb) return;
        var cbs = document.querySelectorAll('.col-toggle-check');
        var checkedCount = document.querySelectorAll('.col-toggle-check:checked').length;
        toggleAllCb.indeterminate = checkedCount > 0 && checkedCount < cbs.length;
        toggleAllCb.checked = checkedCount === cbs.length;
    }
    if (toggleAllCb) {
        syncToggleAll();
        toggleAllCb.addEventListener('change', function () {
            document.querySelectorAll('.col-toggle-check').forEach(function (cb) {
                cb.checked = toggleAllCb.checked;
                activeCols[cb.dataset.col] = toggleAllCb.checked;
            });
            localStorage.setItem(STORAGE_KEY, JSON.stringify(activeCols));
            applyColVisibility();
        });
    }
    applyColVisibility();

    // ── Card / Table view toggle ────────────────────────────────────
    var table     = document.getElementById('personnelTable');
    var viewBtn   = document.getElementById('viewToggleBtn');
    var viewIcon  = document.getElementById('viewToggleIcon');
    var isCards   = localStorage.getItem('personnelViewCards') === '1';

    function applyView() {
        if (!table) return;
        table.classList.toggle('cards', isCards);
        if (viewIcon) {
            viewIcon.className = isCards ? 'fa fa-toggle-on' : 'fa fa-toggle-off';
        }
    }
    if (viewBtn) {
        viewBtn.addEventListener('click', function () {
            isCards = !isCards;
            localStorage.setItem('personnelViewCards', isCards ? '1' : '0');
            applyView();
        });
    }
    applyView();

    // ── Bulk select ─────────────────────────────────────────────────
    var checkAllCb = document.getElementById('checkAll');
    var selCountEl = document.getElementById('selCount');

    function updateSelCount() {
        var n = document.querySelectorAll('.row-check:checked').length;
        if (selCountEl) selCountEl.textContent = n;
    }

    if (checkAllCb) {
        checkAllCb.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(function (cb) {
                cb.checked = checkAllCb.checked;
                cb.closest('tr').classList.toggle('selected', checkAllCb.checked);
            });
            updateSelCount();
        });
    }

    document.querySelectorAll('.row-check').forEach(function (cb) {
        cb.addEventListener('change', function () {
            this.closest('tr').classList.toggle('selected', this.checked);
            var total   = document.querySelectorAll('.row-check').length;
            var checked = document.querySelectorAll('.row-check:checked').length;
            if (checkAllCb) {
                checkAllCb.indeterminate = checked > 0 && checked < total;
                checkAllCb.checked      = checked === total;
            }
            updateSelCount();
        });
    });

    // ── Row click → navigate ────────────────────────────────────────
    window.rowClick = function (event, id) {
        if (event.target.closest('td:first-child') ||
            event.target.closest('a') ||
            event.target.closest('button')) return;
        window.location.href = '/personnel/' + id;
    };

    // ── Personnel bulk actions ──────────────────────────────────────
    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.row-check:checked')).map(function (cb) {
            return cb.value;
        });
    }
    function getSelectedEmails() {
        return Array.from(document.querySelectorAll('.row-check:checked'))
            .map(function (cb) { return cb.dataset.email; })
            .filter(Boolean);
    }

    window.personnelAction = function (action) {
        var ids = getSelectedIds();
        if (!ids.length) {
            alert('Veuillez sélectionner au moins une personne.');
            return;
        }
        document.getElementById('SelectionMail').value = ids.join(',');
        var form = document.getElementById('personnelForm');
        var actions = {
            'badge':      '/legacy/pdf.php?pdf=badge',
            'listemails': '/legacy/listemails.php',
            'mail':       '/legacy/mail_create.php',
        };
        form.action = actions[action] || '/legacy/mail_create.php';
        form.method = 'POST';
        form.submit();
    };

    window.personnelMailto = function () {
        var emails = getSelectedEmails();
        if (!emails.length) {
            alert('Veuillez sélectionner au moins un destinataire avec un email.');
            return;
        }
        window.location.href = 'mailto:' + emails.join(',');
    };

    // ── Sort by column header ───────────────────────────────────────
    document.querySelectorAll('th.sortable[data-sort]').forEach(function (th) {
        th.addEventListener('click', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('order', th.dataset.sort);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    });

    // ── Submit search on Enter ──────────────────────────────────────
    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                document.getElementById('searchForm').submit();
            }
        });
        // Also auto-submit after a short debounce
        var debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                document.getElementById('searchForm').submit();
            }, 600);
        });
    }

}());
</script>
@endpush
