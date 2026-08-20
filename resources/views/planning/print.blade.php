@extends('layout.app')

@section('title', __('planning.print_heading') . ' — ' . ucfirst($first->locale('fr')->isoFormat('MMMM YYYY')) . ' — ' . config('app.name'))

@section('content')

<div class="mx-3 mt-3">

    {{-- ── Toolbar (screen only) ──────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3 d-print-none">
        <div class="ob-widget-card-body d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> {{ __('planning.print_btn') }}
            </button>
            <a href="{{ route('planning.index', ['year' => $year, 'month' => $month]) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('common.back') }}
            </a>
        </div>
    </div>

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-body">
            <h2 style="font-size:var(--font-size-lg); margin-bottom:0;">
                {{ __('planning.print_for', [
                    'name' => $user->P_PRENOM . ' ' . strtoupper($user->P_NOM),
                    'month' => ucfirst($first->locale('fr')->isoFormat('MMMM YYYY')),
                ]) }}
            </h2>
        </div>
    </div>

    {{-- ── Activités ──────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-calendar-check me-1"></i> {{ __('planning.print_section_events') }}
                <span class="ob-badge ob-badge-archive ms-1">{{ $events->count() }}</span>
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($events->isEmpty())
                <p class="ob-widget-empty p-3">{{ __('planning.print_events_empty') }}</p>
            @else
                <table class="table table-sm mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>{{ __('planning.print_col_date') }}</th>
                            <th>{{ __('planning.print_col_time') }}</th>
                            <th>{{ __('planning.print_col_activity') }}</th>
                            <th>{{ __('planning.print_col_type') }}</th>
                            <th>{{ __('planning.print_col_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $e)
                        <tr>
                            <td style="font-size:var(--font-size-sm)">{{ \Carbon\Carbon::parse($e->event_date)->locale('fr')->isoFormat('ddd D MMM') }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $e->event_time && $e->event_time !== '00:00' ? $e->event_time : '—' }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $e->E_LIBELLE ?: $e->E_CODE }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $e->TE_LIBELLE ?? '—' }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $e->E_CLOSED ? __('planning.status_closed') : __('planning.status_open') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Absences ───────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-user-clock me-1"></i> {{ __('planning.print_section_absences') }}
                <span class="ob-badge ob-badge-archive ms-1">{{ $absences->count() }}</span>
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($absences->isEmpty())
                <p class="ob-widget-empty p-3">{{ __('planning.print_absences_empty') }}</p>
            @else
                <table class="table table-sm mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>{{ __('planning.print_col_period') }}</th>
                            <th>{{ __('planning.print_col_type') }}</th>
                            <th>{{ __('planning.print_col_status') }}</th>
                            <th>{{ __('planning.print_col_comment') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absences as $a)
                        <tr>
                            <td style="font-size:var(--font-size-sm)">
                                {{ \Carbon\Carbon::parse($a->I_DEBUT)->locale('fr')->isoFormat('D MMM') }}
                                @if($a->I_FIN && $a->I_FIN !== $a->I_DEBUT)
                                    → {{ \Carbon\Carbon::parse($a->I_FIN)->locale('fr')->isoFormat('D MMM') }}
                                @endif
                            </td>
                            <td style="font-size:var(--font-size-sm)">{{ $a->TI_LIBELLE ?: __('planning.absence_default') }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $a->I_ACCEPT ? __('planning.status_accepted') : __('planning.pending') }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $a->I_COMMENT ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

</div>

@endsection
