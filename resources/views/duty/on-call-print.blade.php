@extends('layout.app')

@section('title', __('duty.print_heading') . ' — ' . ucfirst($first->locale('fr')->isoFormat('MMMM YYYY')) . ' — ' . config('app.name'))

@section('content')

<div class="mx-3 mt-3">

    {{-- ── Toolbar (screen only) ──────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3 d-print-none">
        <div class="ob-widget-card-body d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> {{ __('duty.print_btn') }}
            </button>
            <a href="{{ route('duty.on-call', ['month' => $month, 'year' => $year]) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('common.back') }}
            </a>
        </div>
    </div>

    {{-- ── Report ─────────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-shield-alt me-1"></i>
                {{ __('duty.print_heading') }} — {{ ucfirst($first->locale('fr')->isoFormat('MMMM YYYY')) }}
                <span class="ob-badge ob-badge-archive ms-1">{{ __('duty.print_count', ['count' => $slots->count()]) }}</span>
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($slots->isEmpty())
                <p class="ob-widget-empty p-3">{{ __('duty.print_empty') }}</p>
            @else
                <table class="table table-sm mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>{{ __('duty.print_col_debut') }}</th>
                            <th>{{ __('duty.print_col_fin') }}</th>
                            <th>{{ __('duty.print_col_personnel') }}</th>
                            <th>{{ __('duty.print_col_role') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slots as $s)
                        <tr>
                            <td style="font-size:var(--font-size-sm)">{{ \Carbon\Carbon::parse($s->AS_DEBUT)->locale('fr')->isoFormat('ddd D MMM, HH:mm') }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ \Carbon\Carbon::parse($s->AS_FIN)->locale('fr')->isoFormat('ddd D MMM, HH:mm') }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $s->P_PRENOM }} {{ strtoupper($s->P_NOM) }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $s->GP_DESCRIPTION ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

</div>

@endsection
