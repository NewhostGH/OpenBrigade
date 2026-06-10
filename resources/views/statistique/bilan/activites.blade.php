@extends('layout.app')

@section('title', 'Bilan — Activités — ' . config('app.name'))

@section('content')

    <x-ob-breadcrumb :items="[
            ['label' => 'Statistiques'],
            ['label' => 'Bilan annuel', 'url' => route('statistique.bilan')],
            ['label' => 'Activités opérationnelles'],
        ]" />

    @include('statistique.bilan._tabs', ['activeTab' => 'activites'])

    <div class="mx-3 mt-3 ob-bilan-content">

        <p class="text-muted mb-4">
            Bilan annuel de l'ensemble des activités opérationnelles de votre structure pour {{ $year }}.
        </p>

        {{-- ── KPI cards ──────────────────────────────────────────────────────── --}}
        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--primary">
                <span class="ob-kpi-card__label">Activités</span>
                <span class="ob-kpi-card__value">{{ $totalEvents }}</span>
                <span class="ob-kpi-card__sub">en {{ $year }}</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--success">
                <span class="ob-kpi-card__label">Participations</span>
                <span class="ob-kpi-card__value">{{ $totalParticipants }}</span>
                <span class="ob-kpi-card__sub">cumulées</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--accent">
                <span class="ob-kpi-card__label">Heures</span>
                <span class="ob-kpi-card__value">{{ number_format($totalHours, 0, ',', ' ') }}</span>
                <span class="ob-kpi-card__sub">total bénévoles</span>
            </div>
        </div>

        {{-- ── Charts ─────────────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-chart-bar me-2"></i>Répartition mensuelle
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-calendar-check me-1"></i> Activités par mois
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
                            <i class="fas fa-users me-1"></i> Participants par mois
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
            <i class="fas fa-chart-pie me-2"></i>Répartition par type
        </div>

        <div class="row g-3 mb-4">
            @if(!empty($eventsByType))
                <div class="col-lg-6">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">Types d'activités</div>
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
                        <div class="ob-widget-card-title">Détail par type</div>
                    </div>
                    <div class="ob-widget-card-body p-0">
                        @if(empty($eventsByType))
                            <p class="ob-widget-empty p-3">Aucune activité.</p>
                        @else
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Activités</th>
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
            <i class="fas fa-trophy me-2"></i>Top 10 participants
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-body p-0">
                        @if($topParticipants->isEmpty())
                            <p class="ob-widget-empty p-3">Aucune donnée.</p>
                        @else
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:32px">#</th>
                                        <th>Membre</th>
                                        <th>Activités</th>
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
    @vite(['resources/js/ob-statistique-bilan.js', 'resources/js/ob-pdf-bilan.js'])
@endpush