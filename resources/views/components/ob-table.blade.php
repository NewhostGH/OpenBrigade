{{--
    ob-table  —  Universal data table (no outer card, no form, no controls bar).

    The outer card + form is provided by <x-ob-commandbar>.
    Column-toggle, export, and card-toggle controls live in <x-ob-toolbar>
    and are wired via data-for-table="tableId" by ob-table.js.

    Props
    ─────
    columns          array    Column-definition arrays (see schema below)
    items            mixed    Paginated or plain collection
    storageKey       string   localStorage key for column-visibility state
    currentOrder     string   Active sort field (for sort arrows)
    rowUrlPattern    string   Pattern for row click, e.g. '/personnel/{P_ID}'
    rowActions       array    Per-row action-button definitions
    showSelect       bool     Render checkbox column
    selectIdField    string   Model field for checkbox value   (default: P_ID)
    selectEmailField string   Model field for data-email on checkbox
    tableId          string   HTML id for <table>              (default: obTable)
    emptyText        string   Empty-state message

    Column definition keys
    ──────────────────────
    key           string    data-col attribute + localStorage visibility key
    label         string    Column header text
    type          string    text | image | avatar | badge | date | bool | html
    value         callable  fn($item) → rendered value
    exportable    bool      Include in XLS/CSV export            (default: true)
    exportValue   callable  fn($item) → override used only in exports
    alwaysVisible bool      Exclude from col-toggle dropdown     (default: false)
    default       bool      Default visibility in localStorage   (default: true)
    mobile        bool      false → d-none d-md-table-cell       (default: true)
    cardShow      bool      Show in card mode                    (default: false)
    sortField     string    DB field for ORDER BY — makes header clickable
    thWidth       string    Inline width on <th>, e.g. '40px'
    badgeMap      array     For badge type: ['RAW' => ['Label', 'css-class']]
    imageAlt      callable  fn($item) → alt text
    imageClass    string    CSS class(es) on <img>
    imageError    string    Inline onerror value
    imageLazy     bool      Add loading="lazy"                   (default: true)

    Row-action definition keys
    ──────────────────────────
    url    string   URL pattern, e.g. '/personnel/{P_ID}/edit'
    icon   string   FontAwesome class
    title  string   Button tooltip
    class  string   Bootstrap btn variant  (default: btn-outline-secondary)
    target string   Optional link target
--}}

@props([
    'columns'          => [],
    'items',
    'storageKey'       => 'obTableCols',
    'currentOrder'     => null,
    'rowUrlPattern'    => null,
    'rowActions'       => [],
    'showSelect'       => false,
    'selectIdField'    => 'P_ID',
    'selectEmailField' => null,
    'tableId'          => 'obTable',
    'emptyText'        => 'Aucun résultat',
])

@php
    $toggleCols  = array_values(array_filter($columns, fn($c) => !($c['alwaysVisible'] ?? false)));
    $colCount    = count($columns)
                 + ($showSelect ? 1 : 0)
                 + (count($rowActions) > 0 ? 1 : 0);
    $colDefaults = count($toggleCols)
        ? array_combine(
            array_column($toggleCols, 'key'),
            array_map(fn($c) => (bool)($c['default'] ?? true), $toggleCols)
          )
        : [];
@endphp

{{-- Container carries the JS configuration via data-* attributes --}}
<div data-ob-table
     data-ob-table-id="{{ $tableId }}"
     data-ob-storage-key="{{ $storageKey }}"
     data-ob-col-defaults="{{ e(json_encode((object)$colDefaults)) }}"
     @if ($showSelect) data-ob-select @endif
     @if (true)        data-ob-card-toggle @endif>

    {{-- ── Table ──────────────────────────────────────────────────────────── --}}
    <div class="table-responsive">
    <table id="{{ $tableId }}" class="ob-table">
        <thead>
            <tr>
                @if ($showSelect)
                <th style="width:28px;">
                    <input type="checkbox" data-check-all title="Tout sélectionner">
                </th>
                @endif

                @foreach ($columns as $col)
                @php
                    $alwaysVis = $col['alwaysVisible'] ?? false;
                    $mobile    = $col['mobile'] ?? true;
                    $sortField = $col['sortField'] ?? null;
                    $isActive  = $sortField && $currentOrder === $sortField;
                    $thClass   = trim(implode(' ', array_filter([
                        $mobile    ? '' : 'd-none d-md-table-cell',
                        $sortField ? 'sortable' : '',
                    ])));
                    $thStyle   = ($sortField ? 'cursor:pointer;' : '')
                               . (isset($col['thWidth']) ? 'width:' . $col['thWidth'] . ';' : '');
                @endphp
                <th data-col="{{ $col['key'] }}"
                    @if ($alwaysVis) data-always="1" @endif
                    @if ($thClass)   class="{{ $thClass }}" @endif
                    @if ($thStyle)   style="{{ $thStyle }}" @endif
                    @if ($sortField) data-sort="{{ $sortField }}" @endif>
                    {{ $col['label'] }}
                    @if ($sortField)
                        <i class="fas fa-sort{{ $isActive ? '-down' : '' }} sort-icon{{ $isActive ? ' active' : '' }} ms-1"></i>
                    @endif
                </th>
                @endforeach

                @if (count($rowActions) > 0)
                <th style="width:{{ count($rowActions) * 38 }}px;"></th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
            @php
                $rowUrl = null;
                if ($rowUrlPattern) {
                    $rowUrl = preg_replace_callback(
                        '/\{([^}]+)\}/',
                        fn($m) => $item->{$m[1]} ?? '',
                        $rowUrlPattern
                    );
                }
            @endphp
            <tr @if ($rowUrl) data-href="{{ $rowUrl }}" @endif>

                @if ($showSelect)
                <td onclick="event.stopPropagation()">
                    <input type="checkbox" name="ids[]"
                           class="{{ $tableId }}-row-check"
                           value="{{ $item->{$selectIdField} ?? '' }}"
                           @if ($selectEmailField) data-email="{{ $item->{$selectEmailField} ?? '' }}" @endif>
                </td>
                @endif

                @foreach ($columns as $col)
                @php
                    $mobile   = $col['mobile'] ?? true;
                    $cardShow = $col['cardShow'] ?? false;
                    $tdClass  = trim(implode(' ', array_filter([
                        $mobile   ? '' : 'd-none d-md-table-cell',
                        $cardShow ? 'card-show' : '',
                    ])));
                    $rawVal = is_callable($col['value'])
                        ? ($col['value'])($item)
                        : ($item->{$col['value']} ?? '');
                @endphp
                <td data-col="{{ $col['key'] }}"
                    @if ($tdClass) class="{{ $tdClass }}" @endif>

                    @switch($col['type'] ?? 'text')

                        @case('avatar')
                            <img src="{{ $rawVal }}" alt=""
                                 class="{{ $col['imageClass'] ?? 'ob-avatar-sm' }}"
                                 loading="lazy">
                            @break

                        @case('image')
                            @php
                                $imgAlt = isset($col['imageAlt'])
                                    ? (is_callable($col['imageAlt'])
                                        ? ($col['imageAlt'])($item)
                                        : ($item->{$col['imageAlt']} ?? ''))
                                    : '';
                            @endphp
                            <img src="{{ $rawVal }}"
                                 alt="{{ $imgAlt }}"
                                 class="{{ $col['imageClass'] ?? '' }}"
                                 @if (isset($col['imageError'])) onerror="{{ $col['imageError'] }}" @endif
                                 @if ($col['imageLazy'] ?? true) loading="lazy" @endif>
                            @break

                        @case('badge')
                            @php
                                $mapped   = $col['badgeMap'][$rawVal] ?? null;
                                $badgeLbl = $mapped[0] ?? $rawVal;
                                $badgeCls = $mapped[1] ?? 'ob-badge';
                            @endphp
                            <span class="ob-badge {{ $badgeCls }}">{{ $badgeLbl }}</span>
                            @break

                        @case('date')
                            {{ $rawVal instanceof \Carbon\Carbon
                                ? $rawVal->format('d/m/Y')
                                : ($rawVal ? \Carbon\Carbon::parse($rawVal)->format('d/m/Y') : '—') }}
                            @break

                        @case('bool')
                            {!! $rawVal
                                ? '<i class="fas fa-check text-success"></i>'
                                : '<span class="text-muted">—</span>' !!}
                            @break

                        @case('html')
                            {!! $rawVal !!}
                            @break

                        @default
                            {{ $rawVal !== null && $rawVal !== '' ? $rawVal : '—' }}

                    @endswitch
                </td>
                @endforeach

                @if (count($rowActions) > 0)
                <td onclick="event.stopPropagation()">
                    @foreach ($rowActions as $act)
                    @php
                        $actUrl = preg_replace_callback(
                            '/\{([^}]+)\}/',
                            fn($m) => $item->{$m[1]} ?? '',
                            $act['url']
                        );
                    @endphp
                    <a href="{{ $actUrl }}"
                       class="btn btn-sm {{ $act['class'] ?? 'btn-outline-secondary' }} py-0 px-1"
                       title="{{ $act['title'] ?? '' }}"
                       @if (!empty($act['target'])) target="{{ $act['target'] }}" @endif
                       onclick="event.stopPropagation()">
                        <i class="{{ $act['icon'] ?? '' }} fa-xs"></i>
                    </a>
                    @endforeach
                </td>
                @endif

            </tr>
            @empty
            <tr>
                <td colspan="{{ $colCount }}">
                    <div class="ob-table-empty">{{ $emptyText }}</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

</div>
