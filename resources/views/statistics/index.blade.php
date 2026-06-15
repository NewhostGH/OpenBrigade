@extends('layout.app')

@section('title', 'Statistiques — ' . config('app.name'))

@section('content')

    <x-ob-breadcrumb :items="[
            ['label' => 'Statistiques'],
            ['label' => 'Tableau de bord', 'url' => route('statistics.dashboard')],
        ]" />

    <div class="ob-toolbar mx-3 mt-3">
        <div class="ob-toolbar-title">
            <h1>Statistiques {{ $year }}</h1>
            <form method="GET" action="{{ route('statistics.dashboard') }}" class="ob-stats-year-form">
                <label class="text-muted" style="font-size:var(--font-size-sm)">Année :</label>
                <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="ob-toolbar-actions">
            <a href="{{ route('statistics.annual-report') }}?year={{ $year }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-pdf me-1"></i>Bilan annuel
            </a>
        </div>
    </div>

    <div class="mx-3 mt-3">

        {{-- ── KPI cards ──────────────────────────────────────────────────────── --}}
        <div class="ob-kpi-grid">
            <div class="ob-kpi-card ob-kpi-card--primary">
                <span class="ob-kpi-card__label">Activités</span>
                <span class="ob-kpi-card__value">{{ $totalEvents }}</span>
                <span class="ob-kpi-card__sub">en {{ $year }}</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--success">
                <span class="ob-kpi-card__label">Participations</span>
                <span class="ob-kpi-card__value">{{ $totalParticipants }}</span>
                <span class="ob-kpi-card__sub">cumulées sur l'année</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--accent">
                <span class="ob-kpi-card__label">Heures</span>
                <span class="ob-kpi-card__value">{{ number_format($totalHours, 0, ',', ' ') }}</span>
                <span class="ob-kpi-card__sub">total bénévoles</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--info">
                <span class="ob-kpi-card__label">Membres actifs</span>
                <span class="ob-kpi-card__value">{{ $totalMembers }}</span>
                <span class="ob-kpi-card__sub">{{ $newMembersThisYear }} nouveau{{ $newMembersThisYear !== 1 ? 'x' : '' }}
                    en {{ $year }}</span>
            </div>
        </div>

        {{-- ── Charts row 1 ───────────────────────────────────────────────────── --}}
        <div class="row g-3">

            <div class="col-lg-6">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-chart-bar me-1"></i> Activités par mois
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        <div id="chart-events-month" data-values="{{ json_encode(array_values($eventsData)) }}"></div>
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
                        <div id="chart-participants-month" data-values="{{ json_encode(array_values($participantData)) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Events by type --}}
            <div class="col-lg-6">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-chart-pie me-1"></i> Répartition par type
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        <div id="chart-events-type" data-values="{{ json_encode($eventsByType) }}"></div>
                    </div>
                </div>
            </div>

            {{-- New members trend --}}
            <div class="col-lg-6">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-user-plus me-1"></i> Nouveaux membres (5 ans)
                        </div>
                    </div>
                    <div class="ob-widget-card-body">
                        @if(empty($newMembersByYear))
                            <span class="ob-widget-empty">Aucune donnée.</span>
                        @else
                            <div id="chart-new-members" data-values="{{ json_encode($newMembersByYear) }}"></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Top participants --}}
            <div class="col-12">
                <div class="ob-widget-card">
                    <div class="ob-widget-card-header">
                        <div class="ob-widget-card-title">
                            <i class="fas fa-trophy me-1"></i> Top 10 participants — {{ $year }}
                        </div>
                    </div>
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
                                            <td>
                                                <a href="{{ route('personnel.show', $p->P_ID) }}" class="text-decoration-none">
                                                    {{ $p->P_PRENOM }} {{ strtoupper($p->P_NOM) }}
                                                </a>
                                            </td>
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
    @vite(['resources/js/ob-statistics-index.js'])
@endpush