@extends('layout.app')

@section('title', 'Personnel — ' . config('app.name'))

@section('content')

{{-- ── Toolbar ─────────────────────────────────────────────────── --}}
<div class="ob-toolbar mx-3 mt-3">

    <div class="ob-toolbar-title">
        <h1>
            Personnel
            <span class="text-muted fw-normal" style="font-size:var(--font-size-sm);">
                {{ number_format($items->total()) }}
            </span>
        </h1>
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

    {{-- Main filters --}}
    <form class="ob-filters" id="filterForm"
          style="grid-template-columns: 2fr 1.4fr 1fr 2fr auto">
        {{-- Preserve non-filter params --}}
        <input type="hidden" name="order"       value="{{ $order }}">
        <input type="hidden" name="perPage"     value="{{ $perPage }}">
        <input type="hidden" name="subsections" value="{{ $subsections ? 1 : 0 }}">

        <div>
            <select id="sectionFilter" name="section" class="form-select form-select-sm"
                    onchange="this.form.submit()">
                <option value="0" {{ $sectionId === 0 ? 'selected' : '' }}>Toutes sections</option>
                @foreach ($sectionOptions as $opt)
                    @php
                        $depth  = $opt['depth'];
                        $bgs    = ['#FFCC33','#FFFF99','#B7D8FB','#D4F1C0','#F0E6FF'];
                        $bg     = $bgs[min($depth, count($bgs) - 1)];
                        $pad    = round(1.2 + $depth * 0.5, 1);
                        $label  = $opt['S_CODE'];
                        if ($opt['S_DESCRIPTION']) {
                            $label .= ' — ' . \Illuminate\Support\Str::limit($opt['S_DESCRIPTION'], 22);
                        }
                    @endphp
                    <option value="{{ $opt['S_ID'] }}"
                            style="padding-left:{{ $pad }}rem; background:{{ $bg }};"
                            {{ $sectionId === $opt['S_ID'] ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="ALL"  {{ $category === 'ALL'  ? 'selected' : '' }}>Tous</option>
                <option value="INT"  {{ $category === 'INT'  ? 'selected' : '' }}>Sauf externes</option>
                <option value="BEN"  {{ $category === 'BEN'  ? 'selected' : '' }}>Bénévoles</option>
                <option value="EXT"  {{ $category === 'EXT'  ? 'selected' : '' }}>Externes</option>
                <option value="PRES" {{ $category === 'PRES' ? 'selected' : '' }}>Prestataires</option>
            </select>
        </div>

        <div>
            <select name="position" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all"     {{ $position === 'all'     ? 'selected' : '' }}>Tous</option>
                <option value="actif"   {{ $position === 'actif'   ? 'selected' : '' }}>Actif</option>
                <option value="archive" {{ $position === 'archive' ? 'selected' : '' }}>Archivé</option>
                <option value="bloqued" {{ $position === 'bloqued' ? 'selected' : '' }}>Bloqué</option>
            </select>
        </div>

        <div>
            <input type="search" name="q" id="searchInput"
                   class="form-control form-control-sm"
                   placeholder="Rechercher…"
                   value="{{ $search }}">
        </div>

        <div>
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="fas fa-filter me-1"></i> Filtrer
            </button>
        </div>
    </form>

    {{-- Secondary controls --}}
    <div class="d-flex align-items-center gap-2 mt-2 flex-wrap noprint">

        @if ($sectionId > 0)
            <div class="ob-toggle-switch">
                <label for="subsToggle">Sous-sections</label>
                <label class="ob-switch">
                    <input type="checkbox" id="subsToggle" {{ $subsections ? 'checked' : '' }}
                           onchange="updateParam('subsections', this.checked ? 1 : 0)">
                    <span class="slider"></span>
                </label>
            </div>
            <span style="color:var(--component-border)">|</span>
        @endif

        {{-- Column visibility --}}
        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" title="Colonnes visibles">
                <i class="fa fa-th-list"></i> Colonnes
            </button>
            <div class="dropdown-menu p-2" style="min-width:185px;"
                 onclick="event.stopPropagation()">
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

        {{-- Card / Table view --}}
        <button type="button" id="viewToggleBtn" class="btn btn-sm btn-light"
                title="Vue carte / tableau">
            <i class="fa fa-toggle-off" id="viewToggleIcon"></i> Vue carte
        </button>

        {{-- Page size --}}
        <select class="form-select form-select-sm" style="width:auto;"
                onchange="updateParam('perPage', this.value)">
            @foreach ([12, 24, 48, 100, 500] as $ps)
                <option value="{{ $ps }}" {{ $perPage == $ps ? 'selected' : '' }}>
                    {{ $ps }} / page
                </option>
            @endforeach
        </select>

        @if ($search)
            <a href="{{ route('personnel.index', array_filter(request()->query(), fn($k) => $k !== 'q', ARRAY_FILTER_USE_KEY)) }}"
               class="btn btn-sm btn-outline-secondary" title="Effacer la recherche">
                <i class="fas fa-times me-1"></i> "{{ $search }}"
            </a>
        @endif

    </div>
</div>

{{-- ── Table ────────────────────────────────────────────────────── --}}
<div class="ob-table-wrap mx-3 mt-3">
    <form id="personnelForm" method="POST">
        @csrf
        <div class="table-responsive">
            <table id="personnelTable" class="table table-sm table-hover align-middle ob-table">
                <thead>
                    <tr>
                        <th style="width:28px;">
                            <input type="checkbox" id="checkAll" title="Tout sélectionner">
                        </th>
                        <th data-col="photo">Photo</th>
                        <th data-col="grade">Grade</th>
                        <th data-col="name" class="sortable" data-sort="P_NOM" style="cursor:pointer;">
                            Nom Prénom
                            @if ($order === 'P_NOM')<i class="fas fa-sort-down ms-1"></i>
                            @else<i class="fas fa-sort ms-1 opacity-50"></i>@endif
                        </th>
                        <th data-col="birthdate" class="d-none d-md-table-cell sortable"
                            data-sort="P_BIRTHDATE" style="cursor:pointer;">
                            Naissance
                            @if ($order === 'P_BIRTHDATE')<i class="fas fa-sort-down ms-1"></i>
                            @else<i class="fas fa-sort ms-1 opacity-50"></i>@endif
                        </th>
                        <th data-col="phone" class="d-none d-md-table-cell">Téléphone</th>
                        <th data-col="code"  class="d-none d-md-table-cell sortable"
                            data-sort="P_CODE" style="cursor:pointer;">
                            Matricule
                            @if ($order === 'P_CODE')<i class="fas fa-sort-down ms-1"></i>
                            @else<i class="fas fa-sort ms-1 opacity-50"></i>@endif
                        </th>
                        <th data-col="section" class="d-none d-md-table-cell">Section</th>
                        <th data-col="entree"  class="d-none d-md-table-cell sortable"
                            data-sort="P_DATE_ENGAGEMENT" style="cursor:pointer;">
                            Entrée
                            @if ($order === 'P_DATE_ENGAGEMENT')<i class="fas fa-sort-down ms-1"></i>
                            @else<i class="fas fa-sort ms-1 opacity-50"></i>@endif
                        </th>
                        <th data-col="statut" class="d-none d-md-table-cell">Statut</th>
                        <th data-col="etat"   class="d-none d-md-table-cell">Position</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $etat = (int) $item->GP_ID === -1 ? 'Bloqué'
                                  : ((int) $item->P_OLD_MEMBER > 0 ? 'Archivé' : 'Actif');
                            $etatClass = match($etat) {
                                'Actif'   => 'ob-badge-actif',
                                'Archivé' => 'ob-badge-archive',
                                default   => 'ob-badge-bloqued',
                            };
                            $statutMap = [
                                'BEN'  => ['Personnel bénévole', 'ob-badge-ben'],
                                'EXT'  => ['Personnel externe',  'ob-badge-ext'],
                                'PRES' => ['Prestataire',        'ob-badge-pres'],
                            ];
                            [$statutLabel, $statutClass] = $statutMap[$item->P_STATUT] ?? [$item->P_STATUT, 'ob-badge-int'];
                        @endphp
                        <tr onclick="rowClick(event, {{ $item->P_ID }})">

                            <td onclick="event.stopPropagation()">
                                <input type="checkbox" name="ids[]" class="row-check"
                                       value="{{ $item->P_ID }}"
                                       data-email="{{ $item->P_EMAIL ?? '' }}">
                            </td>

                            <td data-col="photo" class="card-show">
                                <img src="{{ route('personnel.photo', $item) }}"
                                     alt="" class="ob-avatar-sm" loading="lazy">
                            </td>

                            <td data-col="grade">
                                <img src="{{ route('personnel.grade_image', ['grade' => $item->P_GRADE ?: 'NR']) }}"
                                     alt="{{ $item->P_GRADE ?: 'NR' }}"
                                     title="{{ $item->P_GRADE ?: 'Non renseigné' }}"
                                     class="ob-grade-img"
                                     onerror="this.outerHTML='<small class=\'text-muted\'>' + '{{ e($item->P_GRADE) }}' + '</small>'">
                            </td>

                            <td data-col="name" class="card-show">
                                <strong style="font-size:var(--font-size-sm);">
                                    {{ $item->P_NOM }} {{ $item->P_PRENOM }}
                                </strong>
                            </td>

                            <td data-col="birthdate" class="d-none d-md-table-cell"
                                style="font-size:var(--font-size-sm);">
                                {{ $item->P_BIRTHDATE?->format('d/m/Y') ?: '—' }}
                            </td>

                            <td data-col="phone" class="d-none d-md-table-cell"
                                style="font-size:var(--font-size-sm);">
                                {{ $item->P_PHONE ?: '' }}
                                @if ($item->P_PHONE && $item->P_PHONE2)<br>@endif
                                {{ $item->P_PHONE2 ?: '' }}
                                @if (! $item->P_PHONE && ! $item->P_PHONE2)—@endif
                            </td>

                            <td data-col="code" class="d-none d-md-table-cell"
                                style="font-size:var(--font-size-sm);">
                                {{ $item->P_CODE }}
                            </td>

                            <td data-col="section" class="d-none d-md-table-cell"
                                style="font-size:var(--font-size-sm);">
                                {{ $item->section?->S_CODE ?: '—' }}
                            </td>

                            <td data-col="entree" class="d-none d-md-table-cell"
                                style="font-size:var(--font-size-sm);">
                                {{ $item->P_DATE_ENGAGEMENT?->format('d/m/Y') ?: '—' }}
                            </td>

                            <td data-col="statut" class="d-none d-md-table-cell card-show">
                                <span class="ob-badge {{ $statutClass }}">{{ $statutLabel }}</span>
                            </td>

                            <td data-col="etat" class="d-none d-md-table-cell">
                                <span class="ob-badge {{ $etatClass }}">{{ $etat }}</span>
                            </td>

                            <td onclick="event.stopPropagation()">
                                <a href="{{ route('personnel.edit', $item) }}"
                                   class="btn btn-sm btn-outline-secondary py-0 px-1"
                                   title="Modifier" onclick="event.stopPropagation()">
                                    <i class="fas fa-edit fa-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted fst-italic">
                                Aucun personnel trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bulk action bar --}}
        <div class="ob-action-bar noprint">
            <span class="text-muted">
                <span id="selCount">0</span> sélectionné(s)
            </span>
            <button type="button" class="btn btn-sm btn-light"
                    onclick="personnelAction('mail')" title="Envoyer un message">
                <i class="fas fa-envelope me-1"></i> Envoyer
            </button>
            <button type="button" class="btn btn-sm btn-light"
                    onclick="personnelAction('badge')" title="Badges PDF">
                <i class="fas fa-id-badge me-1"></i> Badges
            </button>
            <button type="button" class="btn btn-sm btn-light"
                    onclick="personnelMailto()" title="Mailto client">
                <i class="fas fa-at me-1"></i> Mail
            </button>
            <button type="button" class="btn btn-sm btn-light"
                    onclick="personnelAction('listemails')" title="Télécharger emails">
                <i class="fas fa-download me-1"></i> Télécharger
            </button>
            <input type="hidden" name="SelectionMail" id="SelectionMail">
        </div>

        {{-- Pagination --}}
        <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            {{ $items->links() }}
            <span class="text-muted" style="font-size:var(--font-size-xs);">
                {{ number_format($items->total()) }} personne{{ $items->total() > 1 ? 's' : '' }}
            </span>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    function updateParam(key, value) {
        var url = new URL(window.location.href);
        url.searchParams.set(key, value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }
    window.updateParam = updateParam;

    // ── Column visibility ───────────────────────────────────────────
    var STORAGE_KEY = 'personnelColsV2';
    var defaultCols = {
        photo: true, grade: true, birthdate: true, phone: true,
        code: true, section: true, entree: true, statut: true, etat: true
    };
    var activeCols;
    try {
        activeCols = Object.assign({}, defaultCols,
            JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'));
    } catch (e) { activeCols = Object.assign({}, defaultCols); }

    function applyColVisibility() {
        Object.keys(activeCols).forEach(function (col) {
            document.querySelectorAll('[data-col="' + col + '"]').forEach(function (el) {
                el.classList.toggle('col-hidden', !activeCols[col]);
            });
        });
    }

    var toggleAllCb = document.getElementById('colToggleAll');
    function syncToggleAll() {
        if (!toggleAllCb) return;
        var cbs = document.querySelectorAll('.col-toggle-check');
        var n   = document.querySelectorAll('.col-toggle-check:checked').length;
        toggleAllCb.indeterminate = n > 0 && n < cbs.length;
        toggleAllCb.checked = n === cbs.length;
    }

    document.querySelectorAll('.col-toggle-check').forEach(function (cb) {
        cb.checked = activeCols[cb.dataset.col] !== false;
        cb.addEventListener('change', function () {
            activeCols[cb.dataset.col] = cb.checked;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(activeCols));
            applyColVisibility();
            syncToggleAll();
        });
    });
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

    // ── Card / Table view ───────────────────────────────────────────
    var table    = document.getElementById('personnelTable');
    var viewBtn  = document.getElementById('viewToggleBtn');
    var viewIcon = document.getElementById('viewToggleIcon');
    var isCards  = localStorage.getItem('personnelViewCards') === '1';

    function applyView() {
        if (!table) return;
        table.classList.toggle('cards', isCards);
        if (viewIcon) viewIcon.className = isCards ? 'fa fa-toggle-on' : 'fa fa-toggle-off';
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
    var checkAll  = document.getElementById('checkAll');
    var selCountEl = document.getElementById('selCount');

    function updateSelCount() {
        if (selCountEl) selCountEl.textContent =
            document.querySelectorAll('.row-check:checked').length;
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(function (cb) {
                cb.checked = checkAll.checked;
                cb.closest('tr').classList.toggle('table-active', checkAll.checked);
            });
            updateSelCount();
        });
    }

    document.querySelectorAll('.row-check').forEach(function (cb) {
        cb.addEventListener('change', function () {
            cb.closest('tr').classList.toggle('table-active', cb.checked);
            var total   = document.querySelectorAll('.row-check').length;
            var checked = document.querySelectorAll('.row-check:checked').length;
            if (checkAll) {
                checkAll.indeterminate = checked > 0 && checked < total;
                checkAll.checked = checked === total;
            }
            updateSelCount();
        });
    });

    // ── Row click ───────────────────────────────────────────────────
    window.rowClick = function (event, id) {
        if (event.target.closest('td:first-child') ||
            event.target.closest('a') ||
            event.target.closest('button')) return;
        window.location.href = '/personnel/' + id;
    };

    // ── Bulk actions ────────────────────────────────────────────────
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
        if (!ids.length) { alert('Veuillez sélectionner au moins une personne.'); return; }
        document.getElementById('SelectionMail').value = ids.join(',');
        var form = document.getElementById('personnelForm');
        form.action = { badge: '/legacy/pdf.php?pdf=badge', listemails: '/legacy/listemails.php' }[action]
            || '/legacy/mail_create.php';
        form.method = 'POST';
        form.submit();
    };
    window.personnelMailto = function () {
        var emails = getSelectedEmails();
        if (!emails.length) { alert('Veuillez sélectionner au moins un destinataire avec un email.'); return; }
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

    // ── Search debounce ─────────────────────────────────────────────
    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        var debounce;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounce);
            debounce = setTimeout(function () {
                document.getElementById('filterForm').submit();
            }, 600);
        });
    }

}());
</script>
@endpush
