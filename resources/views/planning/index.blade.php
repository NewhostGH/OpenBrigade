@extends('layout.app')

@section('title', 'Mon planning — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Mon planning'],
]"/>

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>{{ __('planning.title') }}</h1>
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
                {{ __('planning.this_month') }}
            </a>
        @endif
    </div>
</div>

<div class="mx-3 mt-3">
    <table class="ob-cal-grid">
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
                        <td class="ob-cal-cell {{ !$cell['inMonth'] ? 'ob-cal-outside' : '' }} {{ $cell['isToday'] ? 'ob-cal-today' : '' }}">
                            <div class="ob-cal-day-num {{ $cell['isToday'] ? 'ob-cal-today-num' : '' }}">
                                {{ $cell['date']->format('j') }}
                            </div>

                            @foreach($cell['events'] as $ev)
                                <a href="{{ route('event.show', $ev->E_CODE) }}"
                                   class="ob-cal-event ob-cal-event-ev d-block text-decoration-none"
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
                                <div class="ob-cal-event {{ $pending ? 'ob-cal-event-abs-pend' : 'ob-cal-event-abs' }}"
                                     title="{{ $abs->TI_LIBELLE ?? 'Absence' }}{{ $abs->I_COMMENT ? ' — '.$abs->I_COMMENT : '' }}">
                                    <i class="fas fa-user-times fa-xs me-1"></i>
                                    {{ $abs->TI_LIBELLE ?? 'Absence' }}
                                    @if($pending) <i class="fas fa-clock fa-xs ms-1" title="{{ __('planning.pending') }}"></i> @endif
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
        <span class="ob-cal-event ob-cal-event-ev px-2">{{ __('planning.legend_event') }}</span>
        <span class="ob-cal-event ob-cal-event-abs px-2">{{ __('planning.legend_abs_ok') }}</span>
        <span class="ob-cal-event ob-cal-event-abs-pend px-2">{{ __('planning.legend_abs_pending') }}</span>
    </div>
</div>

@endsection
