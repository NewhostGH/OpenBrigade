@extends('layout.app')

@section('title', 'Statistiques — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Statistiques'],
]"/>

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Statistiques</h1>
        <form method="GET" action="{{ route('statistique.index') }}" class="d-flex gap-2 align-items-center">
            <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                @foreach($years as $y)
                    <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="mx-3 mt-3">
    <div class="row g-3">

        {{-- ── Activities per month ─────────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="widget-card">
                <div class="widget-card-header">
                    <div class="widget-card-title">
                        <i class="fas fa-chart-bar"></i> Activités par mois — {{ $year }}
                    </div>
                </div>
                <div class="widget-card-body">
                    @php
                        $monthLabels = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                        $maxEv = max(array_merge($eventsData, [1]));
                    @endphp
                    <div class="d-flex align-items-end gap-1" style="height:120px">
                        @foreach($eventsData as $i => $val)
                            @php $pct = $maxEv > 0 ? round($val / $maxEv * 100) : 0; @endphp
                            <div class="d-flex flex-column align-items-center flex-fill">
                                <div style="font-size:9px;color:var(--text-muted-soft)">{{ $val ?: '' }}</div>
                                <div style="background:var(--brand-bg);opacity:0.7;width:100%;height:{{ max($pct, 4) }}%;border-radius:2px 2px 0 0"></div>
                                <div style="font-size:9px;color:var(--text-muted-soft);margin-top:2px">{{ $monthLabels[$i] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Participants per month ───────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="widget-card">
                <div class="widget-card-header">
                    <div class="widget-card-title">
                        <i class="fas fa-users"></i> Participants par mois — {{ $year }}
                    </div>
                </div>
                <div class="widget-card-body">
                    @php $maxPart = max(array_merge($participantData, [1])); @endphp
                    <div class="d-flex align-items-end gap-1" style="height:120px">
                        @foreach($participantData as $i => $val)
                            @php $pct = $maxPart > 0 ? round($val / $maxPart * 100) : 0; @endphp
                            <div class="d-flex flex-column align-items-center flex-fill">
                                <div style="font-size:9px;color:var(--text-muted-soft)">{{ $val ?: '' }}</div>
                                <div style="background:#27ae60;opacity:0.7;width:100%;height:{{ max($pct, 4) }}%;border-radius:2px 2px 0 0"></div>
                                <div style="font-size:9px;color:var(--text-muted-soft);margin-top:2px">{{ $monthLabels[$i] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── New members per year ─────────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="widget-card">
                <div class="widget-card-header">
                    <div class="widget-card-title"><i class="fas fa-user-plus"></i> Nouveaux membres (5 ans)</div>
                </div>
                <div class="widget-card-body">
                    @if(empty($newMembersByYear))
                        <span class="widget-empty">Aucune donnée.</span>
                    @else
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach($newMembersByYear as $yr => $nb)
                                    <tr>
                                        <td style="font-size:var(--font-size-sm);font-weight:600">{{ $yr }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="background:var(--brand-bg);opacity:0.6;height:12px;width:{{ min($nb * 10, 200) }}px;border-radius:2px"></div>
                                                <span style="font-size:var(--font-size-xs)">{{ $nb }}</span>
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

        {{-- ── Top participants ─────────────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="widget-card">
                <div class="widget-card-header">
                    <div class="widget-card-title"><i class="fas fa-trophy"></i> Top participants — {{ $year }}</div>
                </div>
                <div class="widget-card-body p-0">
                    @if($topParticipants->isEmpty())
                        <p class="widget-empty p-3">Aucune donnée.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach($topParticipants as $rank => $p)
                                    <tr>
                                        <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft);width:24px">
                                            {{ $rank + 1 }}.
                                        </td>
                                        <td style="font-size:var(--font-size-sm)">
                                            <a href="{{ route('personnel.show', $p->P_ID) }}" class="text-decoration-none">
                                                {{ $p->P_PRENOM }} {{ strtoupper($p->P_NOM) }}
                                            </a>
                                        </td>
                                        <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                            {{ $p->nb_events }} activités
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

    {{-- Link to full reporting --}}
    <div class="mt-3 d-flex gap-2">
        <a href="{{ url('/legacy/export.php') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-file-export me-1"></i> Export personnalisé
        </a>
        <a href="{{ url('/legacy/bilans.php') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-chart-pie me-1"></i> Bilans annuels
        </a>
    </div>
</div>

@endsection
