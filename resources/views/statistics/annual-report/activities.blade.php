@extends('layout.app')

@section('title', 'Bilan — Activités — ' . config('app.name'))

@section('content')

    <x-ob-breadcrumb :items="[
            ['label' => __('statistics.title')],
            ['label' => __('statistics.annual_report_title'), 'url' => route('statistics.annual-report')],
            ['label' => __('statistics.breadcrumb_activites')],
        ]" />

    @include('statistics.annual-report._tabs', ['activeTab' => 'activites'])

    <div class="mx-3 mt-3 ob-bilan-content">

        <p class="text-muted mb-4">
            {{ __('statistics.activities_intro', ['year' => $year]) }}
        </p>

        {{-- ── KPI cards ──────────────────────────────────────────────────────── --}}
        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--primary">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_events_label_bilan') }}</span>
                <span class="ob-kpi-card__value">{{ $totalEvents }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_events_sub_bilan', ['year' => $year]) }}</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--success">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_participants_label_bilan') }}</span>
                <span class="ob-kpi-card__value">{{ $totalParticipants }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_participants_sub_bilan') }}</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--accent">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_hours_label_bilan') }}</span>
                <span class="ob-kpi-card__value">{{ number_format($totalHours, 0, ',', ' ') }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_hours_sub_bilan') }}</span>
            </div>
        </div>

        {{-- ── Charts ─────────────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-chart-bar me-2"></i>{{ __('statistics.section_monthly') }}
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-calendar-check me-1"></i> {{ __('statistics.chart_events_month_bilan') }}
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        <div id="bilan-chart-events" data-values="{{ json_encode(array_values($eventsData)) }}"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-users me-1"></i> {{ __('statistics.chart_participants_month_bilan') }}
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        <div id="bilan-chart-participants" data-values="{{ json_encode(array_values($participantData)) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ob-bilan-header-title">
            <i class="fas fa-chart-pie me-2"></i>{{ __('statistics.section_by_type') }}
        </div>

        <div class="row g-3 mb-4">
            @if(!empty($eventsByType))
                <div class="col-lg-6">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">{{ __('statistics.activity_types_title') }}</div>
                        </div>
                        <div class="ob-widget-card-body">
                            <div id="bilan-chart-type" data-values="{{ json_encode($eventsByType) }}"></div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-lg-6">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">{{ __('statistics.activity_types_detail') }}</div>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if(empty($eventsByType))
                            <p class="ob-widget-empty p-3">{{ __('statistics.no_activities') }}</p>
                        @else
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('statistics.th_type_bilan') }}</th>
                                        <th>{{ __('statistics.th_activities_bilan') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($eventsByType as $label => $nb)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td>{{ $nb }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Top participants ────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-trophy me-2"></i>{{ __('statistics.section_top10') }}
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-body p-0">
                        @if($topParticipants->isEmpty())
                            <p class="ob-widget-empty p-3">{{ __('statistics.no_data') }}</p>
                        @else
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:32px">{{ __('statistics.th_rank') }}</th>
                                        <th>{{ __('statistics.th_member') }}</th>
                                        <th>{{ __('statistics.th_events') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topParticipants as $rank => $p)
                                        <tr>
                                            <td class="text-muted" style="font-size:var(--font-size-xs)">{{ $rank + 1 }}</td>
                                            <td>{{ $p->P_PRENOM }} {{ strtoupper($p->P_NOM) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div
                                                        style="background:var(--brand-bg);opacity:0.6;height:8px;border-radius:2px;width:{{ min($p->nb_events * 8, 160) }}px">
                                                    </div>
                                                    <span
                                                        style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">{{ $p->nb_events }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        window.__BILAN_EVENTS_DATA__ = @json(array_values($eventsData));
        window.__BILAN_PARTICIPANTS_DATA__ = @json(array_values($participantData));
        window.__BILAN_TYPE_DATA__ = @json($eventsByType);
        window.__BILAN_PDF_DATA__ = {
            tab: 'activites',
            year: {{ $year }},
            section: @json(['code' => $section?->S_CODE, 'name' => $section?->S_DESCRIPTION]),
            letterhead: @json($letterhead ?? null),
            totalEvents: {{ $totalEvents }},
            totalParticipants: {{ $totalParticipants }},
            totalHours: {{ $totalHours }},
            eventsData: @json(array_values($eventsData)),
            participantData: @json(array_values($participantData)),
            eventsByType: @json($eventsByType),
            topParticipants: @json($topParticipants->map(fn($p) => ['nom' => strtoupper($p->P_NOM), 'prenom' => $p->P_PRENOM, 'nb' => $p->nb_events])->values()),
        };
    </script>
    @vite(['resources/js/ob-statistics-report.js', 'resources/js/ob-pdf-report.js'])
@endpush