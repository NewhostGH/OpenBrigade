@extends('layout.app')

@section('title', 'Bilan — Formations — ' . config('app.name'))

@section('content')

    <x-ob-breadcrumb :items="[
            ['label' => __('statistics.title')],
            ['label' => __('statistics.annual_report_title'), 'url' => route('statistics.annual-report')],
            ['label' => __('statistics.breadcrumb_formations')],
        ]" />

    @include('statistics.annual-report._tabs', ['activeTab' => 'formations'])

    <div class="mx-3 mt-3 ob-bilan-content">

        <p class="text-muted mb-4">
            {{ __('statistics.training_intro', ['year' => $year]) }}
        </p>

        {{-- ── KPI cards ──────────────────────────────────────────────────────── --}}
        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--primary">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_formations_label') }}</span>
                <span class="ob-kpi-card__value">{{ $totalFormations }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_formations_sub', ['year' => $year]) }}</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--success">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_trainees_label') }}</span>
                <span class="ob-kpi-card__value">{{ $totalTrained }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_trainees_sub') }}</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--accent">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_hours_label') }}</span>
                <span class="ob-kpi-card__value">{{ number_format($totalHours, 0, ',', ' ') }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_hours_training_sub') }}</span>
            </div>
        </div>

        {{-- ── Charts ─────────────────────────────────────────────────────────── --}}
        @if($totalFormations > 0)
            <div class="ob-bilan-header-title">
                <i class="fas fa-chart-bar me-2"></i>{{ __('statistics.section_repartition') }}
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-calendar-check me-1"></i> {{ __('statistics.chart_form_month') }}
                            </div>
                        </div>
                        <div class="ob-widget-card-body">
                            <div id="bilan-chart-form-events" data-values="{{ json_encode(array_values($eventsData)) }}"></div>
                        </div>
                    </div>
                </div>

                @if(!empty($eventsByType))
                    <div class="col-lg-6">
                        <div class="ob-widget-card">
                            <div class="ob-widget-card-header">
                                <div class="ob-widget-card-title">
                                    <i class="fas fa-chart-pie me-1"></i> {{ __('statistics.chart_form_type') }}
                                </div>
                            </div>
                            <div class="ob-widget-card-body">
                                <div id="bilan-chart-form-type" data-values="{{ json_encode($eventsByType) }}"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ── Formations list ─────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-list me-2"></i>{{ __('statistics.section_detail') }}
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-body p-0">
                        @if($formationsList->isEmpty())
                            <p class="ob-widget-empty p-3">{{ __('statistics.training_empty', ['year' => $year]) }}</p>
                        @else
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('statistics.th_date') }}</th>
                                        <th>{{ __('statistics.th_intitule') }}</th>
                                        <th>{{ __('statistics.th_type') }}</th>
                                        <th>{{ __('statistics.th_lieu') }}</th>
                                        <th class="text-end">{{ __('statistics.th_duree') }}</th>
                                        <th class="text-end">{{ __('statistics.th_stagiaires') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($formationsList as $f)
                                        <tr>
                                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($f->date)->format('d/m/Y') }}</td>
                                            <td>{{ $f->label ?: '—' }}</td>
                                            <td class="text-muted" style="font-size:var(--font-size-xs)">{{ $f->type ?: '—' }}</td>
                                            <td class="text-muted" style="font-size:var(--font-size-xs)">{{ $f->lieu ?: '—' }}</td>
                                            <td class="text-end">{{ $f->duree_h ?: '—' }}</td>
                                            <td class="text-end">{{ $f->nb_participants }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light fw-semibold">
                                        <td colspan="4">{{ __('statistics.tfoot_total') }}</td>
                                        <td class="text-end">{{ number_format($totalHours, 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ $totalTrained }}</td>
                                    </tr>
                                </tfoot>
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
        window.__BILAN_FORM_EVENTS_DATA__ = @json(array_values($eventsData));
        window.__BILAN_FORM_TYPE_DATA__ = @json($eventsByType);
        window.__BILAN_PDF_DATA__ = {
            tab: 'formations',
            year: {{ $year }},
            section: @json(['code' => $section?->S_CODE, 'name' => $section?->S_DESCRIPTION]),
            letterhead: @json($letterhead ?? null),
            totalFormations: {{ $totalFormations }},
            totalTrained: {{ $totalTrained }},
            totalHours: {{ $totalHours }},
            eventsData: @json(array_values($eventsData)),
            eventsByType: @json($eventsByType),
            $formationsList-> map(function ($f) {
                return [
                    'label' => $f -> label ?: null,
                    'type' => $f -> type ?: null,
                    'lieu' => $f -> lieu ?: null,
                    'date' => $f -> date,
                    'duree_h' => $f -> duree_h ?: null,
                    'nb' => $f -> nb_participants,
                ];
            }) -> values()
            };
    </script>
    @vite(['resources/js/ob-statistics-report.js', 'resources/js/ob-pdf-report.js'])
@endpush