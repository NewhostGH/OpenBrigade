{{--
    ob-commandbar  —  Table card wrapper + footer bar.

    Renders the outer card (ob-commandbar-wrap), a <form> for bulk actions,
    and a footer with selection count, action buttons, and pagination.

    Props
    ─────
    tableId      string   id of the associated <x-ob-table>
    total        int|null Total row count shown next to pagination
    totalLabel   string   Singular label, e.g. 'personne' → 'personnes'

    Slots
    ─────
    (default)    The <x-ob-table> component
    actions      Bulk-action buttons shown in the footer left area
    pagination   {{ $items->links() }} — shown in the footer right area
    hidden       Hidden <input> fields needed for form submission
--}}

@props([
    'tableId',
    'total'        => null,
    'totalLabel'   => 'résultat',
    'action'       => '',
    'showSelCount' => true,
])

<div class="ob-commandbar-wrap">
    <form id="{{ $tableId }}_form" method="POST"
          @if($action) action="{{ $action }}" @endif>
        @csrf

        {{-- The table (default slot) --}}
        {{ $slot }}

        {{-- Footer bar --}}
        <div class="ob-commandbar noprint">

            <div class="ob-commandbar-left">
                @if ($showSelCount)
                <span class="ob-commandbar-count">
                    <span id="{{ $tableId }}_selCount">0</span> {{ __('components.selected_count') }}
                </span>
                @endif
                @if (isset($actions) && !$actions->isEmpty())
                    {{ $actions }}
                @endif
            </div>

            @if ((isset($pagination) && !$pagination->isEmpty()) || $total !== null)
            <div class="ob-commandbar-right">
                @if (isset($pagination) && !$pagination->isEmpty())
                    {{ $pagination }}
                @endif
                @if ($total !== null)
                <span class="text-muted" style="font-size:var(--font-size-xs); white-space:nowrap;">
                    {{ number_format($total) }}&nbsp;{{ $totalLabel }}{{ $total > 1 ? 's' : '' }}
                </span>
                @endif
            </div>
            @endif

        </div>

        {{-- Hidden fields for form submission (SelectionMail etc.) --}}
        @if (isset($hidden) && !$hidden->isEmpty())
            {{ $hidden }}
        @endif

    </form>
</div>
