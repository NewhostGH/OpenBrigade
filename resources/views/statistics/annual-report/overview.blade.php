@extends('layout.app')

@section('title', 'Bilan — Généralités — ' . config('app.name'))

@section('content')

    <x-ob-breadcrumb :items="[
            ['label' => __('statistics.title')],
            ['label' => __('statistics.annual_report_title'), 'url' => route('statistics.annual-report')],
            ['label' => __('statistics.breadcrumb_generalites')],
        ]" />

    @include('statistics.annual-report._tabs', ['activeTab' => 'generalites'])

    <div class="mx-3 mt-3 ob-bilan-content">

        <p class="text-muted mb-4">
            {{ __('statistics.overview_intro') }}
        </p>

        {{-- ── Personnel ─────────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-users me-2"></i>{{ __('statistics.personnel_heading') }}
        </div>

        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--primary">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_active_members') }}</span>
                <span class="ob-kpi-card__value">{{ $totalMembers }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_active_members_sub', ['date' => now()->format('d/m/Y')]) }}</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--info">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_new_members_label', ['year' => now()->year - 1]) }}</span>
                <span class="ob-kpi-card__value">{{ $newMembersByYear[now()->year - 1] ?? 0 }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_new_members_sub') }}</span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @if(!empty($membersByGroup))
                <div class="col-lg-6">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-layer-group me-1"></i> {{ __('statistics.chart_members_group') }}
                            </div>
                        </div>
                        <div class="ob-widget-card-body">
                            <div id="bilan-chart-members-group" data-labels="{{ json_encode(array_keys($membersByGroup)) }}"
                                data-values="{{ json_encode(array_values($membersByGroup)) }}"></div>
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty($newMembersByYear))
                <div class="col-lg-6">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-user-plus me-1"></i> {{ __('statistics.chart_new_members_5y') }}
                            </div>
                        </div>
                        <div class="ob-widget-card-body">
                            <div id="bilan-chart-new-members" data-labels="{{ json_encode(array_keys($newMembersByYear)) }}"
                                data-values="{{ json_encode(array_values($newMembersByYear)) }}"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Véhicules ──────────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-truck me-2"></i>{{ __('statistics.vehicles_heading') }}
        </div>

        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--accent">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_total_vehicles') }}</span>
                <span class="ob-kpi-card__value">{{ $totalVehicles }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_total_vehicles_sub') }}</span>
            </div>
        </div>

        @if($vehiclesByType->isNotEmpty())
            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-chart-bar me-1"></i> {{ __('statistics.chart_vehicles_type') }}
                            </div>
                        </div>
                        <div class="ob-widget-card-body">
                            <div id="bilan-chart-vehicles" data-labels="{{ json_encode($vehiclesByType->pluck('label')) }}"
                                data-values="{{ json_encode($vehiclesByType->pluck('nb')) }}"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">{{ __('statistics.vehicles_detail_title') }}</div>
                        </div>
                        <div class="ob-widget-card-body p-0">
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('statistics.th_type') }}</th>
                                        <th>{{ __('statistics.th_qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vehiclesByType as $row)
                                        <tr>
                                            <td>{{ $row->label }}</td>
                                            <td>{{ $row->nb }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <p class="text-muted mb-4">{{ __('statistics.no_vehicles') }}</p>
        @endif

        {{-- ── Matériel ───────────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-toolbox me-2"></i>{{ __('statistics.materiel_heading') }}
        </div>

        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--success">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_total_materiel') }}</span>
                <span class="ob-kpi-card__value">{{ $totalEquipments }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_total_materiel_sub') }}</span>
            </div>
        </div>

        @if($materielsByType->isNotEmpty())
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">{{ __('statistics.materiel_by_cat_title') }}</div>
                        </div>
                        <div class="ob-widget-card-body p-0">
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('statistics.th_category') }}</th>
                                        <th>{{ __('statistics.th_qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($materielsByType as $row)
                                        <tr>
                                            <td>{{ $row->label }}</td>
                                            <td>{{ $row->nb }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <p class="text-muted mb-4">{{ __('statistics.no_materiel') }}</p>
        @endif

        {{-- ── Consommables ────────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-box-open me-2"></i>{{ __('statistics.consommables_heading') }}
        </div>

        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--info">
                <span class="ob-kpi-card__label">{{ __('statistics.kpi_total_consommables') }}</span>
                <span class="ob-kpi-card__value">{{ $totalConsumables }}</span>
                <span class="ob-kpi-card__sub">{{ __('statistics.kpi_total_consommables_sub') }}</span>
            </div>
        </div>

        @if($consommablesByType->isNotEmpty())
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">{{ __('statistics.consommables_by_cat_title') }}</div>
                        </div>
                        <div class="ob-widget-card-body p-0">
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('statistics.th_category') }}</th>
                                        <th>{{ __('statistics.th_qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($consommablesByType as $row)
                                        <tr>
                                            <td>{{ $row->label }}</td>
                                            <td>{{ $row->nb }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <p class="text-muted mb-4">{{ __('statistics.no_consommables') }}</p>
        @endif

    </div>

@endsection

@push('scripts')
    <script>
        window.__BILAN_MEMBERS_GROUP__ = @json(array_values($membersByGroup ?? []));
        window.__BILAN_MEMBERS_LABELS__ = @json(array_keys($membersByGroup ?? []));
        window.__BILAN_MEMBERS_YEARS__ = @json(array_keys($newMembersByYear ?? []));
        window.__BILAN_MEMBERS_NEW__ = @json(array_values($newMembersByYear ?? []));
        window.__BILAN_VEHICLES_LABELS__ = @json(($vehiclesByType ?? collect())->pluck('label')->toArray());
        window.__BILAN_VEHICLES_VALUES__ = @json(($vehiclesByType ?? collect())->pluck('nb')->toArray());
        window.__BILAN_PDF_DATA__ = {
            tab: 'generalites',
            year: {{ $year }},
            section: @json(['code' => $section?->S_CODE, 'name' => $section?->S_DESCRIPTION]),
            letterhead: @json($letterhead ?? null),
            totalMembers: {{ $totalMembers }},
            membersByGroup: @json($membersByGroup ?? []),
            newMembersByYear: @json($newMembersByYear ?? []),
            totalVehicles: {{ $totalVehicles }},
            vehiclesByType: @json(($vehiclesByType ?? collect())->map(fn($r) => ['label' => $r->label, 'nb' => $r->nb])->values()),
            totalEquipments: {{ $totalEquipments }},
            materielsByType: @json(($materielsByType ?? collect())->map(fn($r) => ['label' => $r->label, 'nb' => $r->nb])->values()),
            totalConsumables: {{ $totalConsumables }},
            consommablesByType: @json(($consommablesByType ?? collect())->map(fn($r) => ['label' => $r->label, 'nb' => $r->nb])->values()),
        };
    </script>
    @vite(['resources/js/ob-statistics-report.js', 'resources/js/ob-pdf-report.js'])
@endpush