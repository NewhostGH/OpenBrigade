@extends('layout.app')

@section('title', ($event->E_LIBELLE ?? $event->E_CODE) . ' — Activités — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Activités', 'url' => route('evenement.index')],
    ['label' => $event->E_LIBELLE ?? $event->E_CODE ?? ''],
]"/>

<div class="mx-3 mt-3">

    {{-- ── Header card ───────────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-calendar-alt"></i>
                {{ $event->E_LIBELLE ?? $event->E_CODE }}
                @if($event->E_CANCELED)
                    <span class="ob-badge ob-badge-bloqued ms-2">Annulé</span>
                @elseif($event->E_CLOSED)
                    <span class="ob-badge ob-badge-archive ms-2">Clôturé</span>
                @else
                    <span class="ob-badge ob-badge-actif ms-2">Ouvert</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission(15))
                    <a href="{{ route('evenement.edit', $event->E_CODE) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                @endif
                <a href="{{ route('evenement.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>

        <div class="ob-widget-card-body">
            <div class="row g-4">

                {{-- ── Identity ─────────────────────────────────────────────── --}}
                <div class="col-md-5">
                    <dl class="mb-0" style="display:grid; grid-template-columns:auto 1fr; gap:5px 16px;
                                            font-size:var(--font-size-sm); align-items:baseline;">

                        <dt class="text-muted fw-normal">Type</dt>
                        <dd class="mb-0">{{ $typeLabel ?? $event->TE_CODE ?? '—' }}</dd>

                        <dt class="text-muted fw-normal">Lieu</dt>
                        <dd class="mb-0">{{ $event->E_LIEU ?: '—' }}</dd>

                        <dt class="text-muted fw-normal">Section</dt>
                        <dd class="mb-0">{{ $event->section?->S_DESCRIPTION ?? '—' }}</dd>

                        @if($event->chef)
                            <dt class="text-muted fw-normal">Responsable</dt>
                            <dd class="mb-0">
                                <a href="{{ route('personnel.show', $event->chef->P_ID) }}"
                                   class="text-decoration-none">
                                    {{ $event->chef->P_PRENOM }} {{ strtoupper($event->chef->P_NOM) }}
                                </a>
                                @if($event->E_TEL)
                                    <span class="text-muted ms-1">— {{ $event->E_TEL }}</span>
                                @endif
                            </dd>
                        @endif

                        @if($event->E_NB)
                            <dt class="text-muted fw-normal">Effectif prévu</dt>
                            <dd class="mb-0">{{ $event->E_NB }}</dd>
                        @endif

                        @if($event->E_ADDRESS)
                            <dt class="text-muted fw-normal">Adresse</dt>
                            <dd class="mb-0">{{ $event->E_ADDRESS }}</dd>
                        @endif

                        @if($event->E_LIEU_RDV || $event->E_HEURE_RDV)
                            <dt class="text-muted fw-normal">Rendez-vous</dt>
                            <dd class="mb-0">
                                {{ $event->E_LIEU_RDV ?: '' }}
                                @if($event->E_HEURE_RDV)
                                    <span class="text-muted">à {{ substr($event->E_HEURE_RDV, 0, 5) }}</span>
                                @endif
                            </dd>
                        @endif

                        @if($event->E_ALLOW_REINFORCEMENT)
                            <dt class="text-muted fw-normal">Renforts</dt>
                            <dd class="mb-0"><span class="ob-badge ob-badge-actif">Activés</span></dd>
                        @endif

                        @if($event->E_CONTACT_LOCAL || $event->E_CONTACT_TEL)
                            <dt class="text-muted fw-normal">Contact sur place</dt>
                            <dd class="mb-0">
                                {{ $event->E_CONTACT_LOCAL ?: '' }}
                                @if($event->E_CONTACT_TEL)
                                    <span class="text-muted">— {{ $event->E_CONTACT_TEL }}</span>
                                @endif
                            </dd>
                        @endif

                        @if($event->E_WEBEX_URL)
                            <dt class="text-muted fw-normal">Conférence</dt>
                            <dd class="mb-0">
                                <a href="{{ $event->E_WEBEX_URL }}" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-video me-1"></i>Rejoindre
                                </a>
                                @if($event->E_WEBEX_PIN)
                                    <span class="text-muted ms-1">Code: {{ $event->E_WEBEX_PIN }}</span>
                                @endif
                                @if($event->E_WEBEX_START)
                                    <span class="text-muted ms-1">à {{ substr($event->E_WEBEX_START, 0, 5) }}</span>
                                @endif
                            </dd>
                        @endif

                    </dl>

                    @if($event->E_CONSIGNES)
                        <div class="mt-2 p-2 rounded" style="background:var(--bs-warning-bg-subtle); font-size:var(--font-size-sm); border-left:3px solid var(--bs-warning);">
                            <strong><i class="fas fa-lock me-1"></i>Consignes :</strong>
                            {{ $event->E_CONSIGNES }}
                        </div>
                    @endif
                </div>

                {{-- ── Créneaux ─────────────────────────────────────────────── --}}
                <div class="col-md-7">
                    <div style="font-size:var(--font-size-xs); font-weight:600;
                                color:var(--text-muted-soft); text-transform:uppercase;
                                letter-spacing:.04em; margin-bottom:6px;">
                        Créneaux
                    </div>
                    @forelse($event->horaires as $h)
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="ob-badge ob-badge-archive">{{ $h->EH_ID }}</span>
                            <span style="font-size:var(--font-size-sm)">
                                {{ \Carbon\Carbon::parse($h->EH_DATE_DEBUT)->locale('fr')->isoFormat('ddd D MMM YYYY') }}
                                @if($h->EH_DATE_FIN && $h->EH_DATE_FIN->toDateString() !== $h->EH_DATE_DEBUT->toDateString())
                                    → {{ \Carbon\Carbon::parse($h->EH_DATE_FIN)->locale('fr')->isoFormat('D MMM') }}
                                @endif
                                @if($h->EH_DEBUT && substr($h->EH_DEBUT, 0, 5) !== '00:00')
                                    <span class="text-muted">
                                        {{ substr($h->EH_DEBUT, 0, 5) }}–{{ substr($h->EH_FIN, 0, 5) }}
                                    </span>
                                @endif
                            </span>
                        </div>
                    @empty
                        <span class="text-muted fst-italic" style="font-size:var(--font-size-sm)">Aucun créneau</span>
                    @endforelse

                    @if($event->E_COMMENT)
                        <p class="mb-0 mt-3 text-muted" style="font-size:var(--font-size-sm); white-space:pre-line;">
                            {{ $event->E_COMMENT }}
                        </p>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- ── Sidebar + content layout ───────────────────────────────────────── --}}
    <div class="d-flex gap-3 align-items-start">

        {{-- ── Left sidebar nav ──────────────────────────────────────────── --}}
        <div class="noprint" style="flex:0 0 auto; width:160px;">
            <div class="ob-widget-card">
                <div class="ob-widget-card-body p-0">
                    <nav>
                        <a href="#section-participants" class="pers-sidenav-link active">
                            <i class="fas fa-users" style="width:14px; text-align:center;"></i>
                            Participants
                            <span class="ob-badge ob-badge-archive" style="margin-left:auto;">{{ count($participants) }}</span>
                        </a>
                        <a href="#section-equipes" class="pers-sidenav-link">
                            <i class="fas fa-layer-group" style="width:14px; text-align:center;"></i>
                            Équipes
                            <span class="ob-badge ob-badge-archive" style="margin-left:auto;">{{ count($equipes) }}</span>
                        </a>
                        <a href="#section-vehicules" class="pers-sidenav-link">
                            <i class="fas fa-truck" style="width:14px; text-align:center;"></i>
                            Véhicules
                        </a>
                        <a href="#section-renforts" class="pers-sidenav-link">
                            <i class="fas fa-plus-circle" style="width:14px; text-align:center;"></i>
                            Renforts
                            <span class="ob-badge ob-badge-archive" style="margin-left:auto;">{{ count($renforts) }}</span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        {{-- ── Main content ──────────────────────────────────────────────── --}}
        <div style="flex:1; min-width:0;">

    {{-- ── Section: Participants ───────────────────────────────────────────── --}}
    <div id="section-participants" data-evt-section class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-users"></i> Participants
                @if(count($participants) > 0)
                    <span class="ob-badge ob-badge-archive ms-1">{{ count($participants) }}</span>
                @endif
            </div>
            @if(auth()->user()->hasPermission(10) && !$event->E_CLOSED && !$event->E_CANCELED)
                <button type="button" class="btn btn-sm btn-success"
                        data-bs-toggle="modal" data-bs-target="#addParticipantModal">
                    <i class="fas fa-user-plus me-1"></i> Inscrire
                </button>
            @endif
        </div>
        <div class="ob-widget-card-body p-0">
            @if(count($participants) === 0)
                <p class="ob-widget-empty p-3">Aucun participant inscrit.</p>
            @else
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th style="width:36px"></th>
                            <th>Nom</th>
                            <th>Grade</th>
                            <th>Fonction</th>
                            <th>Équipe</th>
                            @if(auth()->user()->hasPermission(10))
                                <th style="width:72px"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $p)
                            <tr>
                                <td>
                                    <img src="{{ route('personnel.photo', $p->P_ID) }}"
                                         width="28" height="28"
                                         style="border-radius:6px;object-fit:cover;"
                                         onerror="this.src='{{ asset('images/autre.png') }}'">
                                </td>
                                <td style="font-size:var(--font-size-sm)">
                                    <a href="{{ route('personnel.show', $p->P_ID) }}"
                                       class="text-decoration-none fw-semibold">
                                        {{ $p->P_PRENOM }} {{ strtoupper($p->P_NOM) }}
                                    </a>
                                </td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $p->P_GRADE ?? '—' }}
                                </td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $p->TP_LIBELLE ?? '—' }}
                                </td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $p->EE_NAME ?? '—' }}
                                </td>
                                @if(auth()->user()->hasPermission(10))
                                    <td class="text-end pe-2">
                                        <button type="button"
                                                class="btn btn-xs btn-light py-0 px-1 me-1"
                                                onclick="openEditParticipant({{ json_encode([
                                                    'p_id'       => $p->P_ID,
                                                    'tp_id'      => $p->TP_ID ?? 0,
                                                    'ee_id'      => $p->EE_ID ?? '',
                                                    'ep_comment' => $p->EP_COMMENT ?? '',
                                                ]) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST"
                                              action="{{ route('evenement.participant.destroy', [$event->E_CODE, $p->P_ID]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Désinscrire {{ addslashes($p->P_PRENOM . ' ' . strtoupper($p->P_NOM)) }} ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Section: Équipes ───────────────────────────────────────────────── --}}
    <div id="section-equipes" data-evt-section class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-layer-group"></i> Équipes
                @if(count($equipes) > 0)
                    <span class="ob-badge ob-badge-archive ms-1">{{ count($equipes) }}</span>
                @endif
            </div>
            @if(auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED)
                <button type="button" class="btn btn-sm btn-success"
                        data-bs-toggle="modal" data-bs-target="#addEquipeModal">
                    <i class="fas fa-plus me-1"></i> Ajouter
                </button>
            @endif
        </div>
        <div class="ob-widget-card-body p-0">
            @if(count($equipes) === 0)
                <p class="ob-widget-empty p-3">Aucune équipe définie pour cette activité.</p>
            @else
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>ID Radio</th>
                            <th class="text-center" style="width:50px"><i class="fas fa-user" title="Membres"></i></th>
                            <th class="text-center" style="width:50px"><i class="fas fa-truck" title="Véhicules"></i></th>
                            @if(auth()->user()->hasPermission(15))
                                <th style="width:72px"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipes as $eq)
                            <tr>
                                <td class="fw-semibold" style="font-size:var(--font-size-sm)">{{ $eq->EE_NAME }}</td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">{{ $eq->EE_DESCRIPTION ?: '—' }}</td>
                                <td style="font-size:var(--font-size-xs)">{{ $eq->EE_ID_RADIO ?: '—' }}</td>
                                <td class="text-center" style="font-size:var(--font-size-sm)">{{ $eq->member_count }}</td>
                                <td class="text-center" style="font-size:var(--font-size-sm)">{{ $eq->vehicle_count }}</td>
                                @if(auth()->user()->hasPermission(15))
                                    <td class="text-end pe-2">
                                        <button type="button"
                                                class="btn btn-xs btn-light py-0 px-1 me-1"
                                                onclick="openEditEquipe({{ json_encode([
                                                    'ee_id'    => $eq->EE_ID,
                                                    'ee_name'  => $eq->EE_NAME,
                                                    'ee_order' => $eq->EE_ORDER,
                                                    'ee_desc'  => $eq->EE_DESCRIPTION ?? '',
                                                    'ee_radio' => $eq->EE_ID_RADIO ?? '',
                                                ]) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST"
                                              action="{{ route('evenement.equipe.destroy', [$event->E_CODE, $eq->EE_ID]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Supprimer l\'équipe « {{ addslashes($eq->EE_NAME) }} » ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Section: Véhicules ─────────────────────────────────────────────── --}}
    <div id="section-vehicules" data-evt-section class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-truck"></i> Véhicules</div>
            @if(auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED)
                <button type="button" class="btn btn-sm btn-success"
                        data-bs-toggle="modal" data-bs-target="#assignVehiculeModal">
                    <i class="fas fa-plus me-1"></i> Assigner
                </button>
            @endif
        </div>
        <div class="ob-widget-card-body p-0">
            @if(count($vehicules) === 0)
                <p class="ob-widget-empty p-3">Aucun véhicule assigné à cette activité.</p>
            @else
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>Immatriculation</th>
                            <th>Indicatif</th>
                            <th class="text-end">Km</th>
                            @if(auth()->user()->hasPermission(15))
                                <th style="width:50px"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicules as $v)
                            <tr>
                                <td class="fw-semibold" style="font-size:var(--font-size-sm)">
                                    <a href="{{ route('vehicule.show', $v->V_ID) }}" class="text-decoration-none">
                                        {{ $v->V_IMMATRICULATION ?: '—' }}
                                    </a>
                                </td>
                                <td style="font-size:var(--font-size-sm)">{{ $v->V_INDICATIF ?: '—' }}</td>
                                <td class="text-end" style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $v->EV_KM ? number_format($v->EV_KM, 0, ',', "\u{202f}") . ' km' : '—' }}
                                </td>
                                @if(auth()->user()->hasPermission(15))
                                    <td class="text-end pe-2">
                                        <form method="POST"
                                              action="{{ route('evenement.vehicule.detach', [$event->E_CODE, $v->V_ID]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Désassigner ce véhicule ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger">
                                                <i class="fas fa-unlink"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Section: Renforts ──────────────────────────────────────────────── --}}
    <div id="section-renforts" data-evt-section class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-plus-circle"></i> Renforts
                @if(count($renforts) > 0)
                    <span class="ob-badge ob-badge-archive ms-1">{{ count($renforts) }}</span>
                @endif
            </div>
            @if(auth()->user()->hasPermission(15))
                <button type="button" class="btn btn-sm btn-success"
                        data-bs-toggle="modal" data-bs-target="#addRenfortModal">
                    <i class="fas fa-link me-1"></i> Rattacher
                </button>
            @endif
        </div>
        <div class="ob-widget-card-body p-0">
            @if(count($renforts) === 0)
                <p class="ob-widget-empty p-3">Aucun renfort rattaché à cette activité.</p>
            @else
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th style="width:60px">N°</th>
                            <th>Activité</th>
                            <th>Lieu</th>
                            <th class="text-center" style="width:80px">Inscrits</th>
                            @if(auth()->user()->hasPermission(15))
                                <th style="width:50px"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($renforts as $r)
                            <tr>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $r->E_CODE }}
                                </td>
                                <td style="font-size:var(--font-size-sm)">
                                    <a href="{{ route('evenement.show', $r->E_CODE) }}"
                                       class="text-decoration-none fw-semibold">
                                        {{ $r->E_LIBELLE ?? $r->E_CODE }}
                                    </a>
                                    @if($r->E_CANCELED)
                                        <span class="ob-badge ob-badge-bloqued ms-1">Annulé</span>
                                    @endif
                                </td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $r->E_LIEU ?: '—' }}
                                </td>
                                <td class="text-center" style="font-size:var(--font-size-sm)">
                                    {{ $r->participant_count ?? 0 }}
                                </td>
                                @if(auth()->user()->hasPermission(15))
                                    <td class="text-end pe-2">
                                        <form method="POST"
                                              action="{{ route('evenement.renfort.detach', [$event->E_CODE, $r->E_CODE]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Détacher ce renfort ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger"
                                                    title="Détacher">
                                                <i class="fas fa-unlink"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

        </div>  {{-- close main content --}}
    </div>  {{-- close sidebar + content layout --}}

</div>  {{-- close mx-3 mt-3 --}}

{{-- ══════════════════════════════════════════════════════════════════════════
     MODALS
════════════════════════════════════════════════════════════════════════════ --}}

{{-- Assign vehicle --}}
@if(auth()->user()->hasPermission(15))
<div class="modal fade" id="assignVehiculeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Assigner un véhicule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('evenement.vehicule.attach', $event->E_CODE) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label" style="font-size:var(--font-size-sm)">
                        Véhicule <span class="text-danger">*</span>
                    </label>
                    <select name="V_ID" class="form-select form-select-sm" required>
                        <option value="">— choisir —</option>
                        @php
                            $assignedIds = $event->vehicules()->pluck('vehicule.V_ID')->toArray();
                        @endphp
                        @foreach($allVehicles as $v)
                            @if(!in_array($v->V_ID, $assignedIds))
                                <option value="{{ $v->V_ID }}">
                                    {{ $v->V_IMMATRICULATION ?: $v->V_INDICATIF }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Assigner</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Add participant --}}
@if(auth()->user()->hasPermission(10))
<div class="modal fade" id="addParticipantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Inscrire un participant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('evenement.participant.store', $event->E_CODE) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:var(--font-size-sm)">
                            Membre <span class="text-danger">*</span>
                        </label>
                        <select name="P_ID" class="form-select form-select-sm" required>
                            <option value="">— choisir —</option>
                            @foreach($candidates as $c)
                                <option value="{{ $c->P_ID }}">
                                    {{ strtoupper($c->P_NOM) }} {{ $c->P_PRENOM }}
                                    @if($c->P_GRADE) — {{ $c->P_GRADE }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:var(--font-size-sm)">
                            Créneau <span class="text-danger">*</span>
                        </label>
                        <select name="EH_ID" class="form-select form-select-sm" required>
                            @foreach($event->horaires as $h)
                                <option value="{{ $h->EH_ID }}">
                                    Partie {{ $h->EH_ID }}
                                    — {{ \Carbon\Carbon::parse($h->EH_DATE_DEBUT)->format('d/m/Y') }}
                                    @if($h->EH_DEBUT) {{ substr($h->EH_DEBUT, 0, 5) }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($functions->count() > 0)
                        <div class="mb-3">
                            <label class="form-label" style="font-size:var(--font-size-sm)">Fonction</label>
                            <select name="TP_ID" class="form-select form-select-sm">
                                <option value="">— aucune —</option>
                                @foreach($functions as $f)
                                    <option value="{{ $f->TP_ID }}">{{ $f->TP_LIBELLE }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if($equipes->count() > 0)
                        <div class="mb-3">
                            <label class="form-label" style="font-size:var(--font-size-sm)">Équipe</label>
                            <select name="EE_ID" class="form-select form-select-sm">
                                <option value="">— aucune —</option>
                                @foreach($equipes as $eq)
                                    <option value="{{ $eq->EE_ID }}">{{ $eq->EE_NAME }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="form-label" style="font-size:var(--font-size-sm)">Commentaire</label>
                        <input name="EP_COMMENT" type="text" class="form-control form-control-sm" maxlength="150">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Inscrire</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit participant --}}
<div class="modal fade" id="editParticipantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Modifier la participation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editParticipantForm" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    @if($functions->count() > 0)
                        <div class="mb-3">
                            <label class="form-label" style="font-size:var(--font-size-sm)">Fonction</label>
                            <select id="editTpId" name="TP_ID" class="form-select form-select-sm">
                                <option value="">— aucune —</option>
                                @foreach($functions as $f)
                                    <option value="{{ $f->TP_ID }}">{{ $f->TP_LIBELLE }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if($equipes->count() > 0)
                        <div class="mb-3">
                            <label class="form-label" style="font-size:var(--font-size-sm)">Équipe</label>
                            <select id="editEeId" name="EE_ID" class="form-select form-select-sm">
                                <option value="">— aucune —</option>
                                @foreach($equipes as $eq)
                                    <option value="{{ $eq->EE_ID }}">{{ $eq->EE_NAME }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="form-label" style="font-size:var(--font-size-sm)">Commentaire</label>
                        <input id="editComment" name="EP_COMMENT" type="text"
                               class="form-control form-control-sm" maxlength="150">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Add équipe --}}
@if(auth()->user()->hasPermission(15))
<div class="modal fade" id="addEquipeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Nouvelle équipe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('evenement.equipe.store', $event->E_CODE) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Nom <span class="text-danger">*</span></label>
                        <input name="EE_NAME" type="text" class="form-control form-control-sm" maxlength="30" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Ordre d'affichage</label>
                        <input name="EE_ORDER" type="number" class="form-control form-control-sm" min="1" max="50" value="1">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">ID Radio</label>
                        <input name="EE_ID_RADIO" type="text" class="form-control form-control-sm" maxlength="12">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:var(--font-size-sm)">Description / Mission</label>
                        <textarea name="EE_DESCRIPTION" class="form-control form-control-sm"
                                  rows="2" maxlength="300"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit équipe --}}
<div class="modal fade" id="editEquipeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Modifier l'équipe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editEquipeForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Nom <span class="text-danger">*</span></label>
                        <input id="editEeName" name="EE_NAME" type="text" class="form-control form-control-sm" maxlength="30" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Ordre d'affichage</label>
                        <input id="editEeOrder" name="EE_ORDER" type="number" class="form-control form-control-sm" min="1" max="50">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">ID Radio</label>
                        <input id="editEeRadio" name="EE_ID_RADIO" type="text" class="form-control form-control-sm" maxlength="12">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:var(--font-size-sm)">Description / Mission</label>
                        <textarea id="editEeDesc" name="EE_DESCRIPTION" class="form-control form-control-sm"
                                  rows="2" maxlength="300"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add renfort --}}
<div class="modal fade" id="addRenfortModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Rattacher un renfort</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('evenement.renfort.attach', $event->E_CODE) }}">
                @csrf
                <div class="modal-body">
                    <label class="form-label" style="font-size:var(--font-size-sm)">
                        N° de l'activité renfort <span class="text-danger">*</span>
                    </label>
                    <input name="renfort" type="number" class="form-control form-control-sm"
                           min="1" placeholder="ex. 12345" required>
                    <div class="form-text mt-1">Numéro de l'événement à rattacher en tant que renfort.</div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Rattacher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
(function () {
    // ── Sidebar active link on scroll ────────────────────────────────────────
    var links    = document.querySelectorAll('.pers-sidenav-link');
    var sections = document.querySelectorAll('[data-evt-section]');

    function activate(id) {
        links.forEach(function (l) { l.classList.remove('active'); });
        var link = document.querySelector('.pers-sidenav-link[href="#' + id + '"]');
        if (link) link.classList.add('active');
    }

    links.forEach(function (l) {
        l.addEventListener('click', function () {
            activate(this.getAttribute('href').slice(1));
        });
    });

    if ('IntersectionObserver' in window && sections.length) {
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) activate(e.target.id);
            });
        }, { rootMargin: '-40% 0px -55% 0px' });
        sections.forEach(function (s) { obs.observe(s); });
        activate(sections[0].id);
    }

    // ── Edit participant modal ────────────────────────────────────────────────
    window.openEditParticipant = function (data) {
        var form = document.getElementById('editParticipantForm');
        if (!form) return;
        form.action = '{{ url('/evenements/' . $event->E_CODE . '/participants') }}/' + data.p_id;

        var tp = document.getElementById('editTpId');
        if (tp) tp.value = data.tp_id || '';
        var ee = document.getElementById('editEeId');
        if (ee) ee.value = data.ee_id || '';
        var comment = document.getElementById('editComment');
        if (comment) comment.value = data.ep_comment || '';

        bootstrap.Modal.getOrCreateInstance(document.getElementById('editParticipantModal')).show();
    };

    // ── Edit équipe modal ────────────────────────────────────────────────────
    window.openEditEquipe = function (data) {
        var form = document.getElementById('editEquipeForm');
        if (!form) return;
        form.action = '{{ url('/evenements/' . $event->E_CODE . '/equipes') }}/' + data.ee_id;

        document.getElementById('editEeName').value  = data.ee_name  || '';
        document.getElementById('editEeOrder').value = data.ee_order || 1;
        document.getElementById('editEeRadio').value = data.ee_radio || '';
        document.getElementById('editEeDesc').value  = data.ee_desc  || '';

        bootstrap.Modal.getOrCreateInstance(document.getElementById('editEquipeModal')).show();
    };
})();
</script>
@endpush
