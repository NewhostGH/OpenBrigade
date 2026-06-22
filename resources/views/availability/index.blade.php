@extends('layout.app')

@section('title', __('availability.title') . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('availability.breadcrumb')],
]"/>

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>{{ __('availability.page_title') }}</h1>
    </div>
</div>

<div class="mx-3 mt-3 row g-3">

    {{-- ── 4-week availability grid ─────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-calendar-check"></i> {{ __('availability.grid_title') }}
                </div>
                <div style="font-size:var(--font-size-xs)">
                    @foreach($periods as $period)
                        <span class="me-2">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:var(--brand-bg);opacity:{{ 0.3 + ($loop->index * 0.2) }}"></span>
                            {{ $period->DP_NAME }}
                        </span>
                    @endforeach
                </div>
            </div>
            <div class="ob-widget-card-body p-0">
                <table class="table table-sm mb-0" style="table-layout:fixed">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            @foreach([__('availability.day_mon'),__('availability.day_tue'),__('availability.day_wed'),__('availability.day_thu'),__('availability.day_fri'),__('availability.day_sat'),__('availability.day_sun')] as $l)
                                <th class="text-center" style="font-size:var(--font-size-xs)">{{ $l }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeks as $week)
                            <tr>
                                @foreach($week as $cell)
                                    <td class="text-center p-1 {{ $cell['isToday'] ? 'table-primary' : '' }}"
                                        style="font-size:var(--font-size-xs)">
                                        <div style="font-weight:{{ $cell['isToday'] ? '700' : '400' }}">
                                            {{ $cell['date']->format('j') }}
                                        </div>
                                        @if($cell['periodId'])
                                            @php $pi = $periods->firstWhere('DP_ID', $cell['periodId']); @endphp
                                            <div style="font-size:9px;color:var(--color-success-icon);font-weight:600">
                                                {{ $pi?->DP_NAME ?? '✓' }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Absences / indisponibilités ─────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="ob-widget-card">
            <div class="ob-widget-card-header">
                <div class="ob-widget-card-title">
                    <i class="fas fa-user-times"></i> {{ __('availability.absences_title') }}
                </div>
                <a href="{{ route('unavailability.index', ['tab' => 'mine']) }}"
                   class="ob-widget-card-link">{{ __('availability.absences_all') }}</a>
            </div>
            <div class="ob-widget-card-body p-0">
                @if($absences->isEmpty())
                    <p class="ob-widget-empty p-3">{{ __('availability.absences_empty') }}</p>
                @else
                    @foreach($absences as $abs)
                        <div class="ob-duty-row px-3">
                            <div class="ob-duty-info">
                                <div class="ob-duty-name">{{ $abs->TI_LIBELLE ?? __('availability.absence_default') }}</div>
                                <div class="ob-duty-role">
                                    {{ $abs->I_DEBUT ? \Carbon\Carbon::parse($abs->I_DEBUT)->format('d/m/Y') : '?' }}
                                    —
                                    {{ $abs->I_FIN ? \Carbon\Carbon::parse($abs->I_FIN)->format('d/m/Y') : '?' }}
                                </div>
                            </div>
                            @if($abs->I_ACCEPT == 1)
                                <span class="badge bg-success">{{ __('availability.status_accepted') }}</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ __('availability.status_pending') }}</span>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="mt-2">
            {{-- TODO: Migrate code — indispo_choice.php has no native route yet --}}
            <a href="{{ url('/legacy/indispo_choice.php') }}" class="btn btn-sm btn-outline-secondary w-100">
                <i class="fas fa-plus me-1"></i> {{ __('availability.declare_absence') }}
            </a>
        </div>
    </div>

</div>

@endsection
