@extends('layout.app')

@section('title', ($vehicule->V_IMMATRICULATION ?: $vehicule->V_INDICATIF) . ' — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Véhicules', 'url' => route('vehicule.index')],
    ['label' => $vehicule->V_IMMATRICULATION ?: $vehicule->V_INDICATIF],
]"/>

@php
    $tvIconMap = [
        'ASSU' => 'fas fa-ambulance',    'VSAV' => 'fas fa-ambulance',
        'VPS'  => 'fas fa-ambulance',    'MPS'  => 'fas fa-ambulance',
        'CTU'  => 'fas fa-truck',        'VTU'  => 'fas fa-truck',
        'VPI'  => 'fas fa-truck',
        'ERS'  => 'fas fa-ship',
        'GER'  => 'fas fa-bolt',
        'MOTO' => 'fas fa-motorcycle',   'QUAD' => 'fas fa-motorcycle',
        'PCM'  => 'fas fa-satellite-dish',
        'REM'  => 'fas fa-trailer',
        'VCYN' => 'fas fa-dog',
        'VELO' => 'fas fa-bicycle',
        'VL'   => 'fas fa-car',          'VLC'  => 'fas fa-car',
        'SSV'  => 'fas fa-car',
        'VLHR' => 'fas fa-truck-monster',
        'VSR'  => 'fas fa-truck-pickup',
        'VTD'  => 'fas fa-hard-hat',
        'VTH'  => 'fas fa-bed',
        'VTI'  => 'fas fa-boxes',
        'VTP'  => 'fas fa-bus',
    ];
    $tvIcon = $tvIconMap[$vehicule->TV_CODE ?? ''] ?? 'fas fa-car-side';

    // Expiry-date card helper — returns style props based on urgency
    $today  = now()->toDateString();
    $soon   = now()->addDays(30)->toDateString();
    $expCard = function ($date, string $label, string $icon) use ($today, $soon) {
        if (! $date) {
            return ['label' => $label, 'icon' => $icon, 'text' => '—',
                    'color' => '#94a3b8', 'bg' => '#f8fafc', 'sub' => ''];
        }
        $ds   = $date->toDateString();
        $diff = now()->diffInDays($date, false);
        $sub  = $diff < 0
            ? 'Expiré il y a ' . abs((int)$diff) . ' j.'
            : 'Dans ' . (int)$diff . ' j.';
        if ($ds < $today) {
            return ['label' => $label, 'icon' => $icon, 'text' => $date->format('d/m/Y'),
                    'color' => '#dc2626', 'bg' => '#fff1f2', 'sub' => $sub];
        }
        if ($ds <= $soon) {
            return ['label' => $label, 'icon' => $icon, 'text' => $date->format('d/m/Y'),
                    'color' => '#d97706', 'bg' => '#fffbeb', 'sub' => $sub];
        }
        return ['label' => $label, 'icon' => $icon, 'text' => $date->format('d/m/Y'),
                'color' => '#16a34a', 'bg' => '#f0fdf4', 'sub' => $sub];
    };

    $expCards = [
        $expCard($vehicule->V_ASS_DATE,   'Assurance',       'fas fa-shield-alt'),
        $expCard($vehicule->V_CT_DATE,    'Contrôle tech.',  'fas fa-clipboard-check'),
        $expCard($vehicule->V_REV_DATE,   'Révision',        'fas fa-wrench'),
        $expCard($vehicule->V_TITRE_DATE, "Titre d'accès",   'fas fa-id-card'),
    ];

    // Status
    if ($position) {
        [$statusLabel, $statusClass] = match(true) {
            $position->VP_OPERATIONNEL >= 3 => ['Opérationnel', 'ob-badge-actif'],
            $position->VP_OPERATIONNEL >= 1 => ['Limité',        'ob-badge-ben'],
            default                         => ['Indisponible',  'ob-badge-bloqued'],
        };
    }
@endphp

<div class="mx-3 mt-3">

    {{-- ── Main info card ─────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="{{ $tvIcon }}" title="{{ $typeVehicule->TV_LIBELLE ?? $vehicule->TV_CODE }}"></i>
                <span>{{ $vehicule->V_IMMATRICULATION ?: '—' }}</span>
                @if($vehicule->V_INDICATIF)
                    <span class="text-muted fw-normal">— {{ $vehicule->V_INDICATIF }}</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission(17))
                    <a href="{{ route('vehicule.edit', $vehicule->V_ID) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                @endif
                <a href="{{ route('vehicule.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>

        <div class="ob-widget-card-body">
            <div class="row g-4">

                {{-- ── Identity column ─────────────────────────────────────── --}}
                <div class="col-md-5">
                    <dl class="mb-0" style="display:grid; grid-template-columns:auto 1fr; gap:6px 16px; font-size:var(--font-size-sm); align-items:baseline;">

                        <dt class="text-muted fw-normal" style="white-space:nowrap;">Type</dt>
                        <dd class="mb-0">
                            <i class="{{ $tvIcon }} me-1" style="color:var(--text-muted-soft); width:14px; text-align:center;"></i>
                            {{ $typeVehicule->TV_LIBELLE ?? ($vehicule->TV_CODE ?: '—') }}
                        </dd>

                        @if($vehicule->V_MODELE || $vehicule->V_ANNEE)
                        <dt class="text-muted fw-normal">Modèle</dt>
                        <dd class="mb-0">
                            {{ trim(($vehicule->V_MODELE ?? '') . ($vehicule->V_ANNEE ? ' (' . $vehicule->V_ANNEE . ')' : '')) ?: '—' }}
                        </dd>
                        @endif

                        <dt class="text-muted fw-normal">Indicatif</dt>
                        <dd class="mb-0 fw-semibold">{{ $vehicule->V_INDICATIF ?: '—' }}</dd>

                        @feature('multi_site')
                        <dt class="text-muted fw-normal">Section</dt>
                        <dd class="mb-0">{{ $vehicule->section?->S_DESCRIPTION ?? '—' }}</dd>
                        @endfeature

                        <dt class="text-muted fw-normal">Statut</dt>
                        <dd class="mb-0">
                            @if($position)
                                <span class="ob-badge {{ $statusClass }}">{{ $position->VP_LIBELLE }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        @if($vehicule->V_KM || $vehicule->V_KM_REVISION)
                        <dt class="text-muted fw-normal">Kilométrage</dt>
                        <dd class="mb-0">
                            @if($vehicule->V_KM)
                                <span>{{ number_format($vehicule->V_KM, 0, ',', '\u{202f}') }} km</span>
                            @else —
                            @endif
                            @if($vehicule->V_KM_REVISION)
                                <span class="text-muted ms-2" style="font-size:var(--font-size-xs);">
                                    révision à {{ number_format($vehicule->V_KM_REVISION, 0, ',', '\u{202f}') }} km
                                </span>
                            @endif
                        </dd>
                        @endif

                        @if($vehicule->V_INVENTAIRE)
                        <dt class="text-muted fw-normal">N° inventaire</dt>
                        <dd class="mb-0">{{ $vehicule->V_INVENTAIRE }}</dd>
                        @endif

                    </dl>

                    {{-- Equipment flags --}}
                    @php $hasFlags = $vehicule->V_FLAG1 || $vehicule->V_FLAG2 || $vehicule->V_FLAG3 || $vehicule->V_FLAG4 || $vehicule->V_EXTERNE; @endphp
                    @if($hasFlags)
                    <div class="d-flex flex-wrap gap-1 mt-3">
                        @if($vehicule->V_FLAG1)
                            <span class="ob-badge ob-badge-int"><i class="fas fa-snowflake me-1"></i>Neige</span>
                        @endif
                        @if($vehicule->V_FLAG2)
                            <span class="ob-badge" style="background:var(--badge-info-bg);color:var(--badge-info-color);"><i class="fas fa-wind me-1"></i>Climatisation</span>
                        @endif
                        @if($vehicule->V_FLAG3)
                            <span class="ob-badge ob-badge-ben"><i class="fas fa-bullhorn me-1"></i>Public Address</span>
                        @endif
                        @if($vehicule->V_FLAG4)
                            <span class="ob-badge ob-badge-archive"><i class="fas fa-link me-1"></i>Attelage</span>
                        @endif
                        @if($vehicule->V_EXTERNE)
                            <span class="ob-badge ob-badge-ext"><i class="fas fa-external-link-alt me-1"></i>Externe</span>
                        @endif
                    </div>
                    @endif

                    @if($vehicule->V_COMMENT)
                    <p class="mt-3 mb-0 text-muted" style="font-size:var(--font-size-sm); white-space:pre-line;">{{ $vehicule->V_COMMENT }}</p>
                    @endif
                </div>

                {{-- ── Expiry dates column ──────────────────────────────── --}}
                <div class="col-md-7">
                    <div class="row g-2">
                        @foreach($expCards as $card)
                        <div class="col-6">
                            <div style="border-left: 3px solid {{ $card['color'] }};
                                        background: {{ $card['bg'] }};
                                        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
                                        padding: 10px 14px;">
                                <div style="font-size:var(--font-size-xs); color:{{ $card['color'] }}; font-weight:600; margin-bottom:3px;">
                                    <i class="{{ $card['icon'] }} me-1"></i>{{ $card['label'] }}
                                </div>
                                <div style="font-size:1rem; font-weight:700; color:{{ $card['text'] === '—' ? '#94a3b8' : $card['color'] }}; letter-spacing:.01em;">
                                    {{ $card['text'] }}
                                </div>
                                @if($card['sub'])
                                <div style="font-size:var(--font-size-xs); color:{{ $card['color'] }}; margin-top:2px; opacity:.8;">
                                    {{ $card['sub'] }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /row --}}
        </div>
    </div>

    {{-- ── Recent events ────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-history"></i> Dernières activités</div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($recentEvents->isEmpty())
                <p class="ob-widget-empty p-3">Aucune activité enregistrée.</p>
            @else
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>Activité</th>
                            <th>Date</th>
                            <th class="text-end">Km</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentEvents as $ev)
                            <tr>
                                <td style="font-size:var(--font-size-sm)">
                                    <a href="{{ route('evenement.show', $ev->E_CODE) }}" class="text-decoration-none">
                                        {{ $ev->E_LIBELLE ?? $ev->E_CODE }}
                                    </a>
                                </td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $ev->EH_DATE_DEBUT ? \Carbon\Carbon::parse($ev->EH_DATE_DEBUT)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-end" style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $ev->EV_KM ? number_format($ev->EV_KM, 0, ',', '\u{202f}') . ' km' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Matériel embarqué ────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-boxes"></i> Matériel embarqué
                @if($materiels->isNotEmpty())
                    <span class="ob-badge ob-badge-archive ms-1">{{ $materiels->count() }}</span>
                @endif
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($materiels->isEmpty())
                <p class="ob-widget-empty p-3">Aucun matériel assigné à ce véhicule.</p>
            @else
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>Type</th>
                            <th>Modèle</th>
                            <th>N° série</th>
                            <th>Inventaire</th>
                            <th class="text-end">Qté</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materiels as $mat)
                            <tr>
                                <td style="font-size:var(--font-size-sm)">{{ $mat->TM_DESCRIPTION ?? $mat->TM_CODE ?? '—' }}</td>
                                <td style="font-size:var(--font-size-sm)">{{ $mat->MA_MODELE ?: '—' }}</td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">{{ $mat->MA_NUMERO_SERIE ?: '—' }}</td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">{{ $mat->MA_INVENTAIRE ?: '—' }}</td>
                                <td class="text-end" style="font-size:var(--font-size-sm)">{{ $mat->MA_NB ?? 1 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Documents ────────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-file-alt"></i> Documents
                @if($documents->isNotEmpty())
                    <span class="ob-badge ob-badge-archive ms-1">{{ $documents->count() }}</span>
                @endif
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($documents->isEmpty())
                <p class="ob-widget-empty p-3">Aucun document associé à ce véhicule.</p>
            @else
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                            <tr>
                                <td style="font-size:var(--font-size-sm)">
                                    <i class="fas fa-file-alt text-muted me-1"></i>{{ $doc->D_NAME }}
                                </td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">{{ $doc->TD_LIBELLE ?? '—' }}</td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $doc->D_CREATED_DATE ? \Carbon\Carbon::parse($doc->D_CREATED_DATE)->format('d/m/Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

</div>

@endsection
