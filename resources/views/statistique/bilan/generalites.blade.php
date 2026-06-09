@extends('layout.app')

@section('title', 'Bilan — Généralités — ' . config('app.name'))

@section('content')

    <x-ob-breadcrumb :items="[
            ['label' => 'Statistiques'],
            ['label' => 'Bilan annuel', 'url' => route('statistique.bilan')],
            ['label' => 'Généralités'],
        ]" />

    @include('statistique.bilan._tabs', ['activeTab' => 'generalites'])

    <div class="mx-3 mt-3 ob-bilan-content">

        <p class="text-muted mb-4">
            Retrouvez le bilan annuel complet du personnel et des moyens — véhicules, matériel et consommables.
        </p>

        {{-- ── Personnel ─────────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-users me-2"></i>Personnel
        </div>

        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--primary">
                <span class="ob-kpi-card__label">Membres actifs</span>
                <span class="ob-kpi-card__value">{{ $totalMembers }}</span>
                <span class="ob-kpi-card__sub">au {{ now()->format('d/m/Y') }}</span>
            </div>
            <div class="ob-kpi-card ob-kpi-card--info">
                <span class="ob-kpi-card__label">Nouveaux {{ now()->year - 1 }}</span>
                <span class="ob-kpi-card__value">{{ $newMembersByYear[now()->year - 1] ?? 0 }}</span>
                <span class="ob-kpi-card__sub">engagements</span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @if(!empty($membersByGroup))
                <div class="col-lg-6">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-layer-group me-1"></i> Répartition par groupe
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
                                <i class="fas fa-user-plus me-1"></i> Évolution des engagements (5 ans)
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
            <i class="fas fa-truck me-2"></i>Véhicules
        </div>

        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--accent">
                <span class="ob-kpi-card__label">Total véhicules</span>
                <span class="ob-kpi-card__value">{{ $totalVehicles }}</span>
                <span class="ob-kpi-card__sub">en parc</span>
            </div>
        </div>

        @if($vehiclesByType->isNotEmpty())
            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">
                                <i class="fas fa-chart-bar me-1"></i> Véhicules par type
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
                            <div class="ob-widget-card-title">Détail</div>
                        </div>
                        <div class="ob-widget-card-body p-0">
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Quantité</th>
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
            <p class="text-muted mb-4">Aucun véhicule enregistré.</p>
        @endif

        {{-- ── Matériel ───────────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-toolbox me-2"></i>Matériel
        </div>

        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--success">
                <span class="ob-kpi-card__label">Total matériel</span>
                <span class="ob-kpi-card__value">{{ $totalMateriels }}</span>
                <span class="ob-kpi-card__sub">articles inventoriés</span>
            </div>
        </div>

        @if($materielsByType->isNotEmpty())
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">Matériel par catégorie</div>
                        </div>
                        <div class="ob-widget-card-body p-0">
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Catégorie</th>
                                        <th>Quantité</th>
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
            <p class="text-muted mb-4">Aucun matériel enregistré.</p>
        @endif

        {{-- ── Consommables ────────────────────────────────────────────────────── --}}
        <div class="ob-bilan-header-title">
            <i class="fas fa-box-open me-2"></i>Consommables
        </div>

        <div class="ob-kpi-grid mb-4">
            <div class="ob-kpi-card ob-kpi-card--info">
                <span class="ob-kpi-card__label">Total consommables</span>
                <span class="ob-kpi-card__value">{{ $totalConsommables }}</span>
                <span class="ob-kpi-card__sub">articles inventoriés</span>
            </div>
        </div>

        @if($consommablesByType->isNotEmpty())
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="ob-widget-card">
                        <div class="ob-widget-card-header">
                            <div class="ob-widget-card-title">Consommables par catégorie</div>
                        </div>
                        <div class="ob-widget-card-body p-0">
                            <table class="table table-sm ob-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Catégorie</th>
                                        <th>Quantité</th>
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
            <p class="text-muted mb-4">Aucun consommable enregistré.</p>
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
            totalMembers: {{ $totalMembers }},
            membersByGroup: @json($membersByGroup ?? []),
            newMembersByYear: @json($newMembersByYear ?? []),
            totalVehicles: {{ $totalVehicles }},
            vehiclesByType: @json(($vehiclesByType ?? collect())->map(fn($r) => ['label' => $r->label, 'nb' => $r->nb])->values()),
            totalMateriels: {{ $totalMateriels }},
            materielsByType: @json(($materielsByType ?? collect())->map(fn($r) => ['label' => $r->label, 'nb' => $r->nb])->values()),
            totalConsommables: {{ $totalConsommables }},
            consommablesByType: @json(($consommablesByType ?? collect())->map(fn($r) => ['label' => $r->label, 'nb' => $r->nb])->values()),
        };
    </script>
    @vite(['resources/js/ob-statistique-bilan.js', 'resources/js/ob-pdf-bilan.js'])
@endpush