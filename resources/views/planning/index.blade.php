@extends('layout.app')

@section('title', 'Mon planning — ' . config('app.name'))

@push('styles')
<style>
.cal-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
.cal-grid th { background: var(--table-header-bg); color: var(--table-header-text); font-size: var(--font-size-xs); font-weight: 600; padding: 5px 4px; text-align: center; }
.cal-cell { border: 1px solid var(--component-border); vertical-align: top; padding: 4px; min-height: 80px; background: var(--component-bg); }
.cal-cell.outside { background: var(--page-bg); }
.cal-cell.today { background: color-mix(in srgb, var(--brand-bg) 5%, #fff); }
.cal-day-num { font-size: var(--font-size-xs); font-weight: 600; color: var(--text-muted-soft); margin-bottom: 2px; }
.cal-day-num.today-num { color: var(--brand-bg); font-size: var(--font-size-sm); }
.cal-event { font-size: 10px; padding: 1px 4px; border-radius: 3px; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cal-event-ev  { background: rgba(41,128,185,0.15); color: #1a5f87; }
.cal-event-abs { background: rgba(230,126,34,0.15); color: #a0530a; }
.cal-event-abs-pend { background: rgba(220,53,69,0.12); color: #842029; }
</style>
@endpush

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Mon planning'],
]"/>

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Mon planning</h1>
        <div class="d-flex gap-2">
            @if(auth()->user()->hasPermission(11))
                <a href="{{ url('/legacy/indispo_choice.php') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-user-times me-1"></i> Déclarer une absence
                </a>
            @endif
        </div>
    </div>

    {{-- Month navigation --}}
    <div class="d-flex align-items-center gap-3 mt-2">
        <a href="{{ route('planning.index', ['year' => $prevYear, 'month' => $prevMonth]) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-chevron-left"></i>
        </a>

        <span class="fw-semibold" style="font-size:var(--font-size-sm); min-width:140px; text-align:center">
            {{ ucfirst($first->locale('fr')->isoFormat('MMMM YYYY')) }}
        </span>

        <a href="{{ route('planning.index', ['year' => $nextYear, 'month' => $nextMonth]) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-chevron-right"></i>
        </a>

        @if($year !== now()->year || $month !== now()->month)
            <a href="{{ route('planning.index') }}" class="btn btn-sm btn-outline-primary">
                Ce mois-ci
            </a>
        @endif
    </div>
</div>

<div class="mx-3 mt-3">
    <table class="cal-grid">
        <thead>
            <tr>
                @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $dayLabel)
                    <th>{{ $dayLabel }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($weeks as $week)
                <tr>
                    @foreach($week as $cell)
                        <td class="cal-cell {{ !$cell['inMonth'] ? 'outside' : '' }} {{ $cell['isToday'] ? 'today' : '' }}">
                            <div class="cal-day-num {{ $cell['isToday'] ? 'today-num' : '' }}">
                                {{ $cell['date']->format('j') }}
                            </div>

                            @foreach($cell['events'] as $ev)
                                <a href="{{ route('evenement.show', $ev->E_CODE) }}"
                                   class="cal-event cal-event-ev d-block text-decoration-none"
                                   title="{{ $ev->E_LIBELLE ?? $ev->E_CODE }}">
                                    @if($ev->event_time)
                                        <span class="me-1">{{ $ev->event_time }}</span>
                                    @endif
                                    <i class="fas fa-{{ $ev->TE_ICON ?? 'calendar' }} fa-xs me-1"></i>
                                    {{ $ev->E_LIBELLE ?? $ev->E_CODE }}
                                </a>
                            @endforeach

                            @foreach($cell['absences'] as $abs)
                                @php $pending = $abs->I_ACCEPT === null || $abs->I_ACCEPT == 0; @endphp
                                <div class="cal-event {{ $pending ? 'cal-event-abs-pend' : 'cal-event-abs' }}"
                                     title="{{ $abs->TI_LIBELLE ?? 'Absence' }}{{ $abs->I_COMMENT ? ' — '.$abs->I_COMMENT : '' }}">
                                    <i class="fas fa-user-times fa-xs me-1"></i>
                                    {{ $abs->TI_LIBELLE ?? 'Absence' }}
                                    @if($pending) <i class="fas fa-clock fa-xs ms-1" title="En attente"></i> @endif
                                </div>
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Legend --}}
    <div class="d-flex gap-3 mt-2" style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
        <span class="cal-event cal-event-ev px-2">Activité</span>
        <span class="cal-event cal-event-abs px-2">Absence acceptée</span>
        <span class="cal-event cal-event-abs-pend px-2">Absence en attente</span>
    </div>
</div>

@endsection
