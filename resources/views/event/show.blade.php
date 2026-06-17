@extends('layout.app')

@section('title', ($event->E_LIBELLE ?? $event->E_CODE) . ' — Activités — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Activités', 'url' => route('event.index')],
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
                <a href="{{ route('event.trombinoscope', $event->E_CODE) }}"
                   class="btn btn-sm btn-outline-secondary" title="Trombinoscope des participants">
                    <i class="fas fa-id-badge me-1"></i> Trombinoscope
                </a>
                <a href="{{ route('event.export.participants', $event->E_CODE) }}"
                   class="btn btn-sm btn-outline-secondary" title="Exporter la liste des participants">
                    <i class="fas fa-file-excel me-1"></i> XLS
                </a>
                <a href="{{ route('event.ical', $event->E_CODE) }}"
                   class="btn btn-sm btn-outline-secondary" title="Télécharger en iCal">
                    <i class="fas fa-calendar-plus me-1"></i> iCal
                </a>
                @if(auth()->user()->hasPermission(15))
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#duplicateModal"
                            title="Dupliquer cette activité">
                        <i class="fas fa-copy me-1"></i> Dupliquer
                    </button>
                    <a href="{{ route('event.edit', $event->E_CODE) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                @endif
                <a href="{{ route('event.index') }}" class="btn btn-sm btn-outline-secondary">
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

                        @feature('multi_site')
                        <dt class="text-muted fw-normal">Section</dt>
                        <dd class="mb-0">{{ $event->section?->S_DESCRIPTION ?? '—' }}</dd>
                        @endfeature

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

    {{-- ── Content sections ───────────────────────────────────────────────── --}}
    <div class="d-flex gap-3 align-items-start">

        {{-- Left sidebar nav — links are generated by ob-event-show.js from the
             [data-evt-section] blocks (their data-nav-* attributes), so the menu
             always matches the sections actually rendered and their order. --}}
        <div class="ob-pers-sidenav-wrap noprint">
            <div class="ob-widget-card">
                <div class="ob-widget-card-body p-0">
                    <nav id="evtSideNav"></nav>
                </div>
            </div>
        </div>

        {{-- Sections column --}}
        <div style="flex:1; min-width:0;">

    {{-- ── Section: Participants ───────────────────────────────────────────── --}}
    <div id="section-participants" data-evt-section data-nav-icon="fas fa-users" data-nav-label="Participants" data-nav-badge="{{ count($participants) ?: '' }}" class="ob-widget-card mb-3">
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
                            @if($eventOptions->isNotEmpty())
                                <th style="width:40px"></th>
                            @endif
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
                                @if(auth()->user()->hasPermission(10) && $equipes->count() > 0 && !$event->E_CLOSED && !$event->E_CANCELED)
                                    <td style="font-size:var(--font-size-xs);">
                                        <form method="POST"
                                              action="{{ route('event.participant.team', [$event->E_CODE, $p->P_ID]) }}"
                                              class="d-inline">
                                            @csrf @method('PATCH')
                                            <select name="EE_ID" onchange="this.form.submit()"
                                                    class="form-select form-select-sm"
                                                    style="font-size:var(--font-size-xs);padding:1px 20px 1px 4px;min-width:90px;">
                                                <option value="">— aucune —</option>
                                                @foreach($equipes as $eq)
                                                    <option value="{{ $eq->EE_ID }}"
                                                            {{ (int)$p->EE_ID === (int)$eq->EE_ID ? 'selected' : '' }}>
                                                        {{ $eq->EE_NAME }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                @else
                                    <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                        {{ $p->EE_NAME ?? '—' }}
                                    </td>
                                @endif
                                @php $pIsSelf = auth()->id() === (int)$p->P_ID; @endphp
                                @if($eventOptions->isNotEmpty() && (auth()->user()->hasPermission(15) || $pIsSelf))
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-xs btn-light py-0 px-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#choicesModal-{{ $p->P_ID }}"
                                                title="Options d'inscription">
                                            <i class="fas fa-sliders-h"></i>
                                        </button>
                                    </td>
                                @elseif($eventOptions->isNotEmpty())
                                    <td></td>
                                @endif
                                @if(auth()->user()->hasPermission(10))
                                    <td class="text-end pe-2">
                                        <button type="button"
                                                class="btn btn-xs btn-light py-0 px-1 me-1"
                                                onclick="openEditParticipant({{ json_encode([
                                                    'p_id'       => $p->P_ID,
                                                    'tp_id'      => $p->TP_ID ?? 0,
                                                    'ee_id'      => $p->EE_ID,
                                                    'ep_comment' => $p->EP_COMMENT ?? '',
                                                ]) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST"
                                              action="{{ route('event.participant.destroy', [$event->E_CODE, $p->P_ID]) }}"
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
    <div id="section-equipes" data-evt-section data-nav-icon="fas fa-layer-group" data-nav-label="Équipes" data-nav-badge="{{ count($equipes) ?: '' }}" class="ob-widget-card mb-3">
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
                @php
                    $participantsByTeam = $participants->groupBy('EE_ID');
                    $materielsByTeam    = $materiels->groupBy('EE_ID');
                    $unassignedP = $participantsByTeam->get(null, collect())->merge($participantsByTeam->get(0, collect()));
                    $canManage   = auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED;
                    $canEnroll   = auth()->user()->hasPermission(10)  && !$event->E_CLOSED && !$event->E_CANCELED;
                @endphp
                <div class="p-3 d-flex flex-wrap gap-3 align-items-start">
                @foreach($equipes as $eq)
                    @php
                        $teamMembers   = $participantsByTeam->get($eq->EE_ID, collect());
                        $teamMateriels = $materielsByTeam->get($eq->EE_ID, collect());
                    @endphp
                    <div style="min-width:220px; flex:1; background:var(--sidebar-bg); border-radius:var(--radius-md);">
                        {{-- Team header --}}
                        <div class="d-flex align-items-center gap-2 px-2 py-1"
                             style="background:var(--card-subheader-bg); border-bottom:1px solid var(--card-subheader-border); border-radius:var(--radius-md) var(--radius-md) 0 0;">
                            <span class="fw-semibold" style="font-size:var(--font-size-sm); flex:1;">
                                {{ $eq->EE_NAME }}
                                @if($eq->EE_ID_RADIO)
                                    <span class="text-muted fw-normal ms-1" style="font-size:var(--font-size-xs)">
                                        <i class="fas fa-broadcast-tower"></i> {{ $eq->EE_ID_RADIO }}
                                    </span>
                                @endif
                            </span>
                            @if(auth()->user()->hasPermission(15))
                                <button type="button"
                                        class="btn btn-xs btn-light py-0 px-1"
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
                                      action="{{ route('event.team.destroy', [$event->E_CODE, $eq->EE_ID]) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Supprimer l\'équipe « {{ addslashes($eq->EE_NAME) }} » ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                        @if($eq->EE_DESCRIPTION)
                            <div class="px-2 pt-1" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                                {{ $eq->EE_DESCRIPTION }}
                            </div>
                        @endif

                        {{-- Personnel --}}
                        <div class="px-2 pt-2 pb-1">
                            <div class="mb-1" style="font-size:var(--font-size-xs);font-weight:600;color:var(--text-muted-soft);text-transform:uppercase;letter-spacing:.04em;">
                                <i class="fas fa-users me-1"></i>Personnel ({{ $teamMembers->count() }})
                            </div>
                            @foreach($teamMembers as $tm)
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <img src="{{ route('personnel.photo', $tm->P_ID) }}"
                                         width="20" height="20"
                                         style="border-radius:4px;object-fit:cover;flex-shrink:0;"
                                         onerror="this.src='{{ asset('images/autre.png') }}'">
                                    <span style="font-size:var(--font-size-xs);flex:1;min-width:0;">
                                        <a href="{{ route('personnel.show', $tm->P_ID) }}" class="text-decoration-none">
                                            {{ $tm->P_PRENOM }} {{ strtoupper($tm->P_NOM) }}
                                        </a>
                                        @if($tm->TP_LIBELLE)
                                            <span class="text-muted">— {{ $tm->TP_LIBELLE }}</span>
                                        @endif
                                    </span>
                                    @if($canEnroll)
                                        <form method="POST"
                                              action="{{ route('event.participant.team', [$event->E_CODE, $tm->P_ID]) }}"
                                              class="d-inline flex-shrink-0">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="EE_ID" value="">
                                            <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-muted"
                                                    title="Retirer de l'équipe">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                            @if($canEnroll && $unassignedP->count() > 0)
                                <form method="POST"
                                      action="{{ route('event.team.participant.add', [$event->E_CODE, $eq->EE_ID]) }}"
                                      class="d-flex gap-1 mt-1">
                                    @csrf
                                    <select name="P_ID" class="form-select form-select-sm"
                                            style="font-size:var(--font-size-xs);flex:1;">
                                        <option value="">+ Ajouter…</option>
                                        @foreach($unassignedP as $up)
                                            <option value="{{ $up->P_ID }}">
                                                {{ strtoupper($up->P_NOM) }} {{ $up->P_PRENOM }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-xs btn-success flex-shrink-0">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Matériel --}}
                        <div class="px-2 pt-1 pb-2">
                            <div class="mb-1" style="font-size:var(--font-size-xs);font-weight:600;color:var(--text-muted-soft);text-transform:uppercase;letter-spacing:.04em;">
                                <i class="fas fa-box me-1"></i>Matériel ({{ $teamMateriels->count() }})
                            </div>
                            @foreach($teamMateriels as $tm)
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <span style="font-size:var(--font-size-xs);flex:1;">
                                        {{ $tm->EM_NB }}× {{ $tm->MA_MODELE }}
                                    </span>
                                    @if($canManage)
                                        <form method="POST"
                                              action="{{ route('event.equipment.detach', [$event->E_CODE, $tm->MA_ID]) }}"
                                              class="d-inline flex-shrink-0"
                                              onsubmit="return confirm('Retirer {{ addslashes($tm->MA_MODELE) }} ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-muted"
                                                    title="Retirer">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                            @if($canManage && $allMateriels->count() > 0)
                                <form method="POST"
                                      action="{{ route('event.team.equipment.add', [$event->E_CODE, $eq->EE_ID]) }}"
                                      class="d-flex gap-1 mt-1">
                                    @csrf
                                    <select name="MA_ID" class="form-select form-select-sm"
                                            style="font-size:var(--font-size-xs);flex:1;">
                                        <option value="">+ Ajouter…</option>
                                        @foreach($allMateriels as $am)
                                            <option value="{{ $am->MA_ID }}">
                                                {{ $am->MA_MODELE }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="EM_NB" value="1" min="1" max="9999"
                                           class="form-control form-control-sm flex-shrink-0"
                                           style="width:50px;font-size:var(--font-size-xs);">
                                    <button type="submit" class="btn btn-xs btn-success flex-shrink-0">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
                </div>
                @if($unassignedP->count() > 0)
                    <div class="px-3 pb-3">
                        <span style="font-size:var(--font-size-xs);color:var(--text-muted-soft);font-style:italic;">
                            {{ $unassignedP->count() }} participant(s) sans équipe
                        </span>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ── Section: Véhicules ─────────────────────────────────────────────── --}}
    <div id="section-vehicules" data-evt-section data-nav-icon="fas fa-truck" data-nav-label="Véhicules" data-nav-badge="{{ count($vehicules) ?: '' }}" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-truck"></i> Véhicules</div>
            <div class="d-flex gap-2">
                @if(count($vehicules) > 0)
                    <a href="{{ route('event.export.vehicles', $event->E_CODE) }}"
                       class="btn btn-sm btn-outline-secondary" title="Exporter la liste des véhicules">
                        <i class="fas fa-file-excel me-1"></i> XLS
                    </a>
                @endif
                @if(auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED)
                    <button type="button" class="btn btn-sm btn-success"
                            data-bs-toggle="modal" data-bs-target="#assignVehiculeModal">
                        <i class="fas fa-plus me-1"></i> Assigner
                    </button>
                @endif
            </div>
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
                                    <a href="{{ route('vehicle.show', $v->V_ID) }}" class="text-decoration-none">
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
                                              action="{{ route('event.vehicle.detach', [$event->E_CODE, $v->V_ID]) }}"
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

    {{-- ── Section: Matériel ─────────────────────────────────────────────── --}}
    <div id="section-materiels" data-evt-section data-nav-icon="fas fa-box" data-nav-label="Matériel" data-nav-badge="{{ count($materiels) ?: '' }}" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-box"></i> Matériel
                @if(count($materiels) > 0)
                    <span class="ob-badge ob-badge-archive ms-1">{{ count($materiels) }}</span>
                @endif
            </div>
            @if(auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED)
                <button type="button" class="btn btn-sm btn-success"
                        data-bs-toggle="modal" data-bs-target="#assignMaterielModal">
                    <i class="fas fa-plus me-1"></i> Assigner
                </button>
            @endif
        </div>
        <div class="ob-widget-card-body p-0">
            @if(count($materiels) === 0)
                <p class="ob-widget-empty p-3">Aucun matériel assigné à cette activité.</p>
            @else
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>Désignation</th>
                            <th>Référence</th>
                            <th class="text-center" style="width:80px">Qté</th>
                            @if(auth()->user()->hasPermission(15))
                                <th style="width:50px"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materiels as $m)
                            <tr>
                                <td class="fw-semibold" style="font-size:var(--font-size-sm)">
                                    {{ $m->MA_MODELE }}
                                </td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $m->MA_NUMERO_SERIE ?: '—' }}
                                </td>
                                <td class="text-center" style="font-size:var(--font-size-sm)">
                                    @if(auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED)
                                        <form method="POST"
                                              action="{{ route('event.equipment.qty', [$event->E_CODE, $m->MA_ID]) }}"
                                              class="d-inline">
                                            @csrf @method('PATCH')
                                            <input type="number" name="EM_NB" value="{{ $m->EM_NB }}"
                                                   min="1" max="9999"
                                                   class="form-control form-control-sm text-center d-inline-block"
                                                   style="width:60px;font-size:var(--font-size-xs);"
                                                   onchange="this.form.submit()">
                                        </form>
                                    @else
                                        {{ $m->EM_NB }}
                                    @endif
                                </td>
                                @if(auth()->user()->hasPermission(15))
                                    <td class="text-end pe-2">
                                        <form method="POST"
                                              action="{{ route('event.equipment.detach', [$event->E_CODE, $m->MA_ID]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Retirer {{ addslashes($m->MA_MODELE) }} ?')">
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
    <div id="section-renforts" data-evt-section data-nav-icon="fas fa-plus-circle" data-nav-label="Renforts" data-nav-badge="{{ count($renforts) ?: '' }}" class="ob-widget-card mb-3">
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
                                    <a href="{{ route('event.show', $r->E_CODE) }}"
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
                                              action="{{ route('event.reinforcement.detach', [$event->E_CODE, $r->E_CODE]) }}"
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

    {{-- ── Section: Demande de renfort ──────────────────────────────────── --}}
    @php
        $hasRenfort = ($renfortRequest && ($renfortRequest->NB_VEHICULES > 0 || $renfortRequest->POINT_REGROUPEMENT || $renfortRequest->DEMANDE_SPECIFIQUE))
                      || $renfortVehicleTypes->isNotEmpty()
                      || $renfortMaterials->isNotEmpty();
    @endphp
    @if($hasRenfort || auth()->user()->hasPermission(15))
    <div id="section-renfort-request" data-evt-section data-nav-icon="fas fa-ambulance" data-nav-label="Demande de renfort" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-ambulance"></i> Demande de renfort
            </div>
            @if(auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED)
                <a href="{{ route('event.renfort-request', $event->E_CODE) }}" class="btn btn-sm btn-outline-primary noprint">
                    <i class="fas fa-edit me-1"></i> Gérer
                </a>
            @endif
        </div>
        <div class="ob-widget-card-body">
            @if(!$hasRenfort)
                <p class="ob-widget-empty mb-0">Aucune demande de renfort enregistrée.</p>
            @else
                @if($renfortRequest && $renfortRequest->NB_VEHICULES > 0)
                    <div class="mb-1" style="font-size:var(--font-size-sm)">
                        <strong>Véhicules :</strong> {{ $renfortRequest->NB_VEHICULES }} au total
                        @foreach($renfortVehicleTypes as $vt)
                            · {{ $vt->TV_CODE }} ({{ $vt->NB_VEHICULES }})
                        @endforeach
                    </div>
                @endif
                @if($renfortMaterials->isNotEmpty())
                    <div class="mb-1" style="font-size:var(--font-size-sm)">
                        <strong>Matériel :</strong>
                        @foreach($renfortMaterials as $m)
                            <span class="badge bg-light text-dark border me-1">{{ $m->TM_CODE ?? $m->CAT_DESCRIPTION ?? $m->TYPE_MATERIEL }}</span>
                        @endforeach
                    </div>
                @endif
                @if($renfortRequest && $renfortRequest->POINT_REGROUPEMENT)
                    <div class="mb-1" style="font-size:var(--font-size-sm)">
                        <strong>Point de regroupement :</strong> {{ $renfortRequest->POINT_REGROUPEMENT }}
                    </div>
                @endif
                @if($renfortRequest && $renfortRequest->DEMANDE_SPECIFIQUE)
                    <div style="font-size:var(--font-size-sm)">
                        <strong>Demande spécifique :</strong> {{ $renfortRequest->DEMANDE_SPECIFIQUE }}
                    </div>
                @endif
            @endif
        </div>
    </div>
    @endif

    {{-- ── Section: Postes requis ────────────────────────────────────────── --}}
    @if($requiredPositions->isNotEmpty() || auth()->user()->hasPermission(15))
    <div id="section-postes" data-evt-section data-nav-icon="fas fa-tasks" data-nav-label="Postes requis" data-nav-badge="{{ $requiredPositions->count() ?: '' }}" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-tasks"></i> Postes requis
                @if($requiredPositions->isNotEmpty())
                    <span class="ob-badge ob-badge-archive ms-1">{{ $requiredPositions->count() }}</span>
                @endif
            </div>
            @if(auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED)
                <button type="button" class="btn btn-sm btn-success"
                        data-bs-toggle="modal" data-bs-target="#addPosteModal">
                    <i class="fas fa-plus me-1"></i> Ajouter
                </button>
            @endif
        </div>
        <div class="ob-widget-card-body p-0">
            @if($requiredPositions->isEmpty())
                <p class="ob-widget-empty p-3">Aucun poste requis défini.</p>
            @else
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>Poste / Qualification</th>
                            <th class="text-center" style="width:80px">Inscrits</th>
                            <th class="text-center" style="width:80px">Requis</th>
                            <th class="text-center" style="width:50px">Statut</th>
                            @if(auth()->user()->hasPermission(15))<th style="width:50px"></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requiredPositions as $rp)
                        @php
                            $enough = $rp->NB == 0 || $rp->actual >= $rp->NB;
                            $excess = $rp->NB > 0 && $rp->actual > $rp->NB;
                        @endphp
                        <tr>
                            <td style="font-size:var(--font-size-sm)">
                                {{ $rp->label }}
                            </td>
                            <td class="text-center fw-semibold">{{ $rp->actual }}</td>
                            <td class="text-center">
                                @if(auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED)
                                    <form method="POST" action="{{ route('event.required-position.update', [$event->E_CODE, $rp->PS_ID]) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="number" name="nb" value="{{ $rp->NB }}" min="0" max="9999"
                                               style="width:60px;text-align:center" class="form-control form-control-sm d-inline"
                                               onchange="this.form.submit()" title="0 = supprimer">
                                    </form>
                                @else
                                    {{ $rp->NB ?: '∞' }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($rp->NB == 0)
                                    <i class="fas fa-check-circle text-success" title="Pas de limite"></i>
                                @elseif($excess)
                                    <i class="fas fa-check-circle text-primary" title="Plus que nécessaire"></i>
                                @elseif($enough)
                                    <i class="fas fa-check-circle text-success" title="Suffisant"></i>
                                @else
                                    <i class="fas fa-exclamation-circle text-danger" title="Insuffisant"></i>
                                @endif
                            </td>
                            @if(auth()->user()->hasPermission(15))
                            <td class="text-center">
                                @if(!$event->E_CLOSED && !$event->E_CANCELED)
                                <form method="POST" action="{{ route('event.required-position.destroy', [$event->E_CODE, $rp->PS_ID]) }}"
                                      onsubmit="return confirm('Supprimer ce poste requis ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-light py-0 px-1 text-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Section: Options d'inscription ──────────────────────────────── --}}
    @if($eventOptions->isNotEmpty() || auth()->user()->hasPermission(15))
    <div id="section-options" data-evt-section data-nav-icon="fas fa-sliders-h" data-nav-label="Options" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-sliders-h"></i> Options d'inscription
                @if($eventOptions->isNotEmpty())
                    <span class="ob-badge ob-badge-archive ms-1">{{ $eventOptions->count() }}</span>
                @endif
            </div>
            @if(auth()->user()->hasPermission(15) && !$event->E_CLOSED && !$event->E_CANCELED)
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#addOptionGroupModal">
                        <i class="fas fa-layer-group me-1"></i> Groupe
                    </button>
                    <button type="button" class="btn btn-sm btn-success"
                            data-bs-toggle="modal" data-bs-target="#addOptionModal">
                        <i class="fas fa-plus me-1"></i> Option
                    </button>
                </div>
            @endif
        </div>
        <div class="ob-widget-card-body p-0">
            @if($eventOptions->isEmpty())
                <p class="ob-widget-empty p-3">Aucune option d'inscription définie.</p>
            @else
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>Nom</th>
                            <th>Groupe</th>
                            <th>Type</th>
                            <th class="text-center" style="width:70px">Réponses</th>
                            @if(auth()->user()->hasPermission(15))<th style="width:80px"></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eventOptions as $opt)
                        <tr>
                            <td style="font-size:var(--font-size-sm)">
                                <strong>{{ $opt->EO_TITLE }}</strong>
                                @if($opt->EO_COMMENT)
                                    <br><span class="text-muted" style="font-size:0.8em">{{ $opt->EO_COMMENT }}</span>
                                @endif
                                @if($opt->EO_TYPE === 'dropdown' && $opt->dropdown_choices->isNotEmpty())
                                    <br>
                                    @foreach($opt->dropdown_choices->skip(1) as $dc)
                                        <span class="ob-badge ob-badge-info me-1" style="font-size:0.75em">{{ $dc->EOD_TEXTE }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td style="font-size:var(--font-size-sm)">{{ $opt->EOG_TITLE ?? '—' }}</td>
                            <td style="font-size:var(--font-size-sm)">
                                @php $typeLabels = ['checkbox'=>'Case à cocher','text'=>'Texte','textnum'=>'Numérique','dropdown'=>'Liste','date'=>'Date','hour'=>'Heure']; @endphp
                                <span class="ob-badge ob-badge-secondary">{{ $typeLabels[$opt->EO_TYPE] ?? $opt->EO_TYPE }}</span>
                            </td>
                            <td class="text-center">
                                <span class="ob-badge ob-badge-archive">{{ $opt->response_count }}</span>
                            </td>
                            @if(auth()->user()->hasPermission(15))
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-xs btn-light"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editOptionModal-{{ $opt->EO_ID }}"
                                            title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if(!$event->E_CLOSED && !$event->E_CANCELED)
                                    <form method="POST"
                                          action="{{ route('event.option.destroy', [$event->E_CODE, $opt->EO_ID]) }}"
                                          onsubmit="return confirm('Supprimer cette option et tous les choix saisis ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-light text-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Section: Groupes d'options ─────────────────────────────────── --}}
    @if($optionGroups->isNotEmpty() && auth()->user()->hasPermission(15))
    <div id="section-option-groups" data-evt-section data-nav-icon="fas fa-layer-group" data-nav-label="Groupes d'options" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-layer-group"></i> Groupes d'options
                <span class="ob-badge ob-badge-archive ms-1">{{ $optionGroups->count() }}</span>
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                    <tr>
                        <th>Nom du groupe</th>
                        <th class="text-center" style="width:70px">Ordre</th>
                        <th style="width:100px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($optionGroups as $grp)
                    <tr>
                        <td style="font-size:var(--font-size-sm)">{{ $grp->EOG_TITLE }}</td>
                        <td class="text-center" style="font-size:var(--font-size-sm)">{{ $grp->EOG_ORDER }}</td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-xs btn-light"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editGroupModal-{{ $grp->EOG_ID }}"
                                        title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if(!$event->E_CLOSED && !$event->E_CANCELED)
                                <form method="POST"
                                      action="{{ route('event.option-group.destroy', [$event->E_CODE, $grp->EOG_ID]) }}"
                                      onsubmit="return confirm('Supprimer ce groupe ? Les options seront dégroupées.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-light text-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Section: Main courante ──────────────────────────────────────────── --}}
    @if($eventLog->isNotEmpty() || auth()->user()->hasPermission(15))
    <div id="section-log" data-evt-section data-nav-icon="fas fa-clipboard-list" data-nav-label="Main courante" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-clipboard-list"></i> Main courante
                @if($eventLog->isNotEmpty())
                    <span class="badge bg-secondary ms-1">{{ $eventLog->count() }}</span>
                @endif
            </div>
            @if(auth()->user()->hasPermission(15))
            <button type="button" class="btn btn-sm btn-outline-secondary"
                    data-bs-toggle="modal" data-bs-target="#addLogModal">
                <i class="fas fa-plus me-1"></i> Ajouter
            </button>
            @endif
        </div>
        <div class="ob-widget-card-body p-0">
            @if($eventLog->isEmpty())
                <p class="text-muted px-3 py-2 mb-0" style="font-size:var(--font-size-sm)">Aucune entrée.</p>
            @else
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:120px">Début</th>
                        <th style="width:130px">Type</th>
                        <th>Titre / Commentaire</th>
                        <th style="width:130px">Auteur</th>
                        @if(auth()->user()->hasPermission(15))
                        <th style="width:60px"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($eventLog as $log)
                    <tr @if($log->EL_IMPORTANT) class="table-warning" @endif>
                        <td style="font-size:var(--font-size-sm);white-space:nowrap">
                            {{ $log->EL_DEBUT ? \Carbon\Carbon::parse($log->EL_DEBUT)->format('d/m H:i') : '—' }}
                            @if($log->EL_IMPORTANT)
                                <i class="fas fa-exclamation-circle text-danger ms-1" title="Important"></i>
                            @endif
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            {{ $log->TEL_DESCRIPTION ?? $log->TEL_CODE ?? '—' }}
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            @if($log->EL_TITLE)
                                <span class="fw-semibold">{{ $log->EL_TITLE }}</span>
                            @endif
                            @if($log->EL_COMMENTAIRE)
                                @if($log->EL_TITLE)<br>@endif
                                <span class="text-muted">{{ $log->EL_COMMENTAIRE }}</span>
                            @endif
                        </td>
                        <td style="font-size:var(--font-size-sm)">
                            {{ $log->P_PRENOM ? $log->P_PRENOM.' '.$log->P_NOM : '—' }}
                        </td>
                        @if(auth()->user()->hasPermission(15))
                        <td>
                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                    onclick="openEditLogModal({{ json_encode($log) }})"
                                    title="Modifier"><i class="fas fa-edit"></i></button>
                            <form method="POST"
                                  action="{{ route('event.log.destroy', [$event->E_CODE, $log->EL_ID]) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer cette entrée ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Supprimer">
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
    @endif

        </div>  {{-- close sections column --}}
    </div>  {{-- close content sections (flex) --}}

</div>  {{-- close mx-3 mt-3 --}}

{{-- ══════════════════════════════════════════════════════════════════════════
     MODALS
════════════════════════════════════════════════════════════════════════════ --}}

{{-- Main courante modals --}}
@if(auth()->user()->hasPermission(15))

{{-- Add log entry --}}
<div class="modal fade" id="addLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Ajouter une entrée</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('event.log.store', $event->E_CODE) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Type <span class="text-danger">*</span></label>
                        <select name="TEL_CODE" class="form-select form-select-sm" required>
                            <option value="">— choisir —</option>
                            @foreach($logTypes as $lt)
                                <option value="{{ $lt->TEL_CODE }}">{{ $lt->TEL_DESCRIPTION }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Début <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="EL_DEBUT" class="form-control form-control-sm" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <label class="form-label" style="font-size:var(--font-size-sm)">Fin</label>
                            <input type="datetime-local" name="EL_FIN" class="form-control form-control-sm">
                        </div>
                        <div class="col">
                            <label class="form-label" style="font-size:var(--font-size-sm)">SLL</label>
                            <input type="datetime-local" name="EL_SLL" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Titre</label>
                        <input type="text" name="EL_TITLE" class="form-control form-control-sm" maxlength="255">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Commentaire</label>
                        <textarea name="EL_COMMENTAIRE" class="form-control form-control-sm" rows="3" maxlength="2000"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="EL_IMPORTANT" value="1" id="addLogImportant">
                        <label class="form-check-label" for="addLogImportant" style="font-size:var(--font-size-sm)">Important</label>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit log entry --}}
<div class="modal fade" id="editLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Modifier une entrée</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editLogForm">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Type <span class="text-danger">*</span></label>
                        <select name="TEL_CODE" id="editLogTelCode" class="form-select form-select-sm" required>
                            <option value="">— choisir —</option>
                            @foreach($logTypes as $lt)
                                <option value="{{ $lt->TEL_CODE }}">{{ $lt->TEL_DESCRIPTION }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Début <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="EL_DEBUT" id="editLogDebut" class="form-control form-control-sm" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <label class="form-label" style="font-size:var(--font-size-sm)">Fin</label>
                            <input type="datetime-local" name="EL_FIN" id="editLogFin" class="form-control form-control-sm">
                        </div>
                        <div class="col">
                            <label class="form-label" style="font-size:var(--font-size-sm)">SLL</label>
                            <input type="datetime-local" name="EL_SLL" id="editLogSll" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Titre</label>
                        <input type="text" name="EL_TITLE" id="editLogTitle" class="form-control form-control-sm" maxlength="255">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Commentaire</label>
                        <textarea name="EL_COMMENTAIRE" id="editLogComment" class="form-control form-control-sm" rows="3" maxlength="2000"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="EL_IMPORTANT" value="1" id="editLogImportant">
                        <label class="form-check-label" for="editLogImportant" style="font-size:var(--font-size-sm)">Important</label>
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

@endif {{-- permission 15 for log modals --}}

{{-- Assign matériel --}}
@if(auth()->user()->hasPermission(15))
<div class="modal fade" id="assignMaterielModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Assigner du matériel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('event.equipment.attach', $event->E_CODE) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:var(--font-size-sm)">
                            Matériel <span class="text-danger">*</span>
                        </label>
                        <select name="MA_ID" class="form-select form-select-sm" required>
                            <option value="">— choisir —</option>
                            @foreach($allMateriels as $m)
                                <option value="{{ $m->MA_ID }}">
                                    {{ $m->MA_MODELE }}{{ $m->MA_NUMERO_SERIE ? ' (' . $m->MA_NUMERO_SERIE . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Quantité</label>
                        <input name="EM_NB" type="number" min="1" max="9999" value="1"
                               class="form-control form-control-sm" style="width:80px;">
                    </div>
                    @if($equipes->count() > 0)
                        <div>
                            <label class="form-label" style="font-size:var(--font-size-sm)">Équipe</label>
                            <select name="EE_ID" class="form-select form-select-sm">
                                <option value="">— toutes —</option>
                                @foreach($equipes as $eq)
                                    <option value="{{ $eq->EE_ID }}">{{ $eq->EE_NAME }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
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

{{-- Assign vehicle --}}
@if(auth()->user()->hasPermission(15))
<div class="modal fade" id="assignVehiculeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Assigner un véhicule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('event.vehicle.attach', $event->E_CODE) }}">
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
            <form method="POST" action="{{ route('event.participant.store', $event->E_CODE) }}">
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
            <form method="POST" action="{{ route('event.team.store', $event->E_CODE) }}">
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
            <form method="POST" action="{{ route('event.reinforcement.attach', $event->E_CODE) }}">
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

{{-- ── Duplicate modal ─────────────────────────────────────────────────────── --}}
@if(auth()->user()->hasPermission(15))
<div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('event.duplicate', $event->E_CODE) }}">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="duplicateModalLabel">
                        <i class="fas fa-copy me-1"></i> Dupliquer l'activité
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size:var(--font-size-sm)">
                        Une copie de <strong>{{ $event->E_LIBELLE ?? $event->E_CODE }}</strong> sera créée
                        à la date indiquée. Le statut sera remis à « Ouvert ».
                    </p>
                    <div class="mb-3">
                        <label for="dup_new_date" class="form-label">
                            Date de début <span class="text-danger">*</span>
                        </label>
                        <input id="dup_new_date" type="date" name="new_date"
                               class="form-control form-control-sm"
                               value="{{ now()->addWeek()->toDateString() }}"
                               required>
                        <div class="form-text">Les autres horaires seront décalés du même nombre de jours.</div>
                    </div>
                    <div class="mb-2 form-check">
                        <input class="form-check-input" type="checkbox" name="copy_participants"
                               id="dup_participants" value="1">
                        <label class="form-check-label" for="dup_participants"
                               style="font-size:var(--font-size-sm)">
                            Copier les participants et les équipes
                        </label>
                    </div>
                    <div class="mb-0 form-check">
                        <input class="form-check-input" type="checkbox" name="copy_vehicles"
                               id="dup_vehicles" value="1">
                        <label class="form-check-label" for="dup_vehicles"
                               style="font-size:var(--font-size-sm)">
                            Copier les véhicules et le matériel
                        </label>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-copy me-1"></i> Dupliquer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Add required position modal --}}
@if(auth()->user()->hasPermission(15))
<div class="modal fade" id="addPosteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Ajouter un poste requis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('event.required-position.store', $event->E_CODE) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:var(--font-size-sm)">
                            Poste / Qualification <span class="text-danger">*</span>
                        </label>
                        <select name="ps_id" class="form-select form-select-sm" required>
                            <option value="">— choisir —</option>
                            <option value="0" @if($requiredPositions->where('PS_ID', 0)->isNotEmpty()) disabled @endif>
                                Total participants (global)
                            </option>
                            @foreach($availablePositions as $pos)
                                <option value="{{ $pos->PS_ID }}">{{ $pos->TYPE }} – {{ $pos->DESCRIPTION }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" style="font-size:var(--font-size-sm)">
                            Nombre requis <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="nb" class="form-control form-control-sm"
                               min="1" max="9999" value="1" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-1"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ══ Event options modals ══════════════════════════════════════════════════ --}}
@if(auth()->user()->hasPermission(15))

{{-- Add option group --}}
<div class="modal fade" id="addOptionGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Nouveau groupe d'options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('event.option-group.store', $event->E_CODE) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Nom <span class="text-danger">*</span></label>
                        <input name="EOG_TITLE" type="text" class="form-control form-control-sm" maxlength="80" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:var(--font-size-sm)">Ordre</label>
                        <input name="EOG_ORDER" type="number" class="form-control form-control-sm" min="1" max="99" value="1">
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

{{-- Edit option group (one modal per group) --}}
@foreach($optionGroups as $grp)
<div class="modal fade" id="editGroupModal-{{ $grp->EOG_ID }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Modifier le groupe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('event.option-group.update', [$event->E_CODE, $grp->EOG_ID]) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Nom <span class="text-danger">*</span></label>
                        <input name="EOG_TITLE" type="text" class="form-control form-control-sm" maxlength="80" value="{{ $grp->EOG_TITLE }}" required>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:var(--font-size-sm)">Ordre</label>
                        <input name="EOG_ORDER" type="number" class="form-control form-control-sm" min="1" max="99" value="{{ $grp->EOG_ORDER }}">
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
@endforeach

{{-- Add option --}}
<div class="modal fade" id="addOptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Nouvelle option d'inscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('event.option.store', $event->E_CODE) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Nom <span class="text-danger">*</span></label>
                        <input name="EO_TITLE" type="text" class="form-control form-control-sm" maxlength="80" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Type <span class="text-danger">*</span></label>
                        <select name="EO_TYPE" class="form-select form-select-sm" required>
                            <option value="checkbox">Case à cocher</option>
                            <option value="text">Texte libre</option>
                            <option value="textnum">Valeur numérique</option>
                            <option value="dropdown">Liste déroulante</option>
                            <option value="date">Date (JJ-MM-AAAA)</option>
                            <option value="hour">Heure (HH:mm)</option>
                        </select>
                    </div>
                    @if($optionGroups->isNotEmpty())
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Groupe</label>
                        <select name="EOG_ID" class="form-select form-select-sm">
                            <option value="">— aucun groupe —</option>
                            @foreach($optionGroups as $grp)
                                <option value="{{ $grp->EOG_ID }}">{{ $grp->EOG_TITLE }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Ordre dans le groupe</label>
                        <input name="EO_ORDER" type="number" class="form-control form-control-sm" min="1" max="99" value="1">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:var(--font-size-sm)">Description / aide</label>
                        <textarea name="EO_COMMENT" class="form-control form-control-sm" rows="2" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-plus me-1"></i> Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit option (one modal per option) --}}
@foreach($eventOptions as $opt)
<div class="modal fade" id="editOptionModal-{{ $opt->EO_ID }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">Modifier l'option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('event.option.update', [$event->E_CODE, $opt->EO_ID]) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Nom <span class="text-danger">*</span></label>
                        <input name="EO_TITLE" type="text" class="form-control form-control-sm" maxlength="80" value="{{ $opt->EO_TITLE }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Type <span class="text-danger">*</span></label>
                        <select name="EO_TYPE" class="form-select form-select-sm" required>
                            @foreach(['checkbox'=>'Case à cocher','text'=>'Texte libre','textnum'=>'Valeur numérique','dropdown'=>'Liste déroulante','date'=>'Date (JJ-MM-AAAA)','hour'=>'Heure (HH:mm)'] as $v => $lbl)
                                <option value="{{ $v }}" @selected($opt->EO_TYPE === $v)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($optionGroups->isNotEmpty())
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Groupe</label>
                        <select name="EOG_ID" class="form-select form-select-sm">
                            <option value="">— aucun groupe —</option>
                            @foreach($optionGroups as $grp)
                                <option value="{{ $grp->EOG_ID }}" @selected($opt->EOG_ID == $grp->EOG_ID)>{{ $grp->EOG_TITLE }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="mb-2">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Ordre</label>
                        <input name="EO_ORDER" type="number" class="form-control form-control-sm" min="1" max="99" value="{{ $opt->EO_ORDER }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:var(--font-size-sm)">Description / aide</label>
                        <textarea name="EO_COMMENT" class="form-control form-control-sm" rows="2" maxlength="255">{{ $opt->EO_COMMENT }}</textarea>
                    </div>
                    {{-- Dropdown choices management --}}
                    @if($opt->EO_TYPE === 'dropdown')
                    <hr>
                    <p class="mb-2" style="font-size:var(--font-size-sm)"><strong>Choix de la liste déroulante</strong></p>
                    @foreach($opt->dropdown_choices->skip(1) as $dc)
                    <div class="d-flex gap-1 align-items-center mb-1">
                        <span class="flex-grow-1" style="font-size:var(--font-size-sm)">{{ $dc->EOD_TEXTE }}</span>
                        <form method="POST"
                              action="{{ route('event.option.choice.destroy', [$event->E_CODE, $opt->EO_ID, $dc->EOD_ID]) }}"
                              onsubmit="return confirm('Supprimer ce choix ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-light text-danger" title="Supprimer">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                    <form method="POST"
                          action="{{ route('event.option.choice.store', [$event->E_CODE, $opt->EO_ID]) }}"
                          class="d-flex gap-1 mt-2">
                        @csrf
                        <input name="EOD_TEXTE" type="text" class="form-control form-control-sm" maxlength="80" placeholder="Nouveau choix…" required>
                        <input name="EOD_ORDER" type="number" class="form-control form-control-sm" min="1" max="99" value="1" style="width:60px" title="Ordre">
                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-plus"></i></button>
                    </form>
                    @endif
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endif {{-- permission 15 --}}

{{-- Participant choices modals (perm 15 or self — shown for enrolled participants with options) --}}
@if($eventOptions->isNotEmpty())
@foreach($participants as $p)
@php $isSelf = auth()->id() === (int)$p->P_ID; @endphp
@if(auth()->user()->hasPermission(15) || $isSelf)
<div class="modal fade" id="choicesModal-{{ $p->P_ID }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:var(--font-size-base)">
                    Options — {{ $p->P_PRENOM }} {{ $p->P_NOM }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('event.participant-choices.save', [$event->E_CODE, $p->P_ID]) }}">
                @csrf
                <div class="modal-body">
                    @php
                        $savedChoices = \Illuminate\Support\Facades\DB::table('evenement_option_choix')
                            ->where('E_CODE', $event->E_CODE)->where('P_ID', $p->P_ID)
                            ->pluck('EOC_VALUE', 'EO_ID');
                    @endphp
                    @foreach($eventOptions as $opt)
                    <div class="mb-3">
                        <label class="form-label" style="font-size:var(--font-size-sm)">
                            <strong>{{ $opt->EO_TITLE }}</strong>
                            @if($opt->EOG_TITLE) <span class="text-muted">({{ $opt->EOG_TITLE }})</span> @endif
                        </label>
                        @if($opt->EO_COMMENT)
                            <div class="form-text mb-1">{{ $opt->EO_COMMENT }}</div>
                        @endif
                        @php $saved = $savedChoices[$opt->EO_ID] ?? null; @endphp
                        @if($opt->EO_TYPE === 'checkbox')
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="O{{ $opt->EO_ID }}" value="1" id="opt-{{ $p->P_ID }}-{{ $opt->EO_ID }}" @checked($saved == '1')>
                                <label class="form-check-label" for="opt-{{ $p->P_ID }}-{{ $opt->EO_ID }}">Oui</label>
                            </div>
                        @elseif($opt->EO_TYPE === 'text')
                            <input type="text" name="O{{ $opt->EO_ID }}" class="form-control form-control-sm" maxlength="255" value="{{ $saved }}">
                        @elseif($opt->EO_TYPE === 'textnum')
                            <input type="number" name="O{{ $opt->EO_ID }}" class="form-control form-control-sm" style="width:100px" value="{{ $saved }}">
                        @elseif($opt->EO_TYPE === 'date')
                            <input type="text" name="O{{ $opt->EO_ID }}" class="form-control form-control-sm" style="width:140px" placeholder="JJ-MM-AAAA" value="{{ $saved }}">
                        @elseif($opt->EO_TYPE === 'hour')
                            <input type="text" name="O{{ $opt->EO_ID }}" class="form-control form-control-sm" style="width:90px" placeholder="HH:mm" value="{{ $saved }}">
                        @elseif($opt->EO_TYPE === 'dropdown')
                            <select name="O{{ $opt->EO_ID }}" class="form-select form-select-sm" style="max-width:250px">
                                @foreach($opt->dropdown_choices as $dc)
                                    <option value="{{ $dc->EOD_ID }}" @selected((string)$saved === (string)$dc->EOD_ID)>{{ $dc->EOD_TEXTE }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-success">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endif

@endsection

@push('scripts')
<script>window.EVT_SHOW_CONFIG = { participantsUrl: '{{ url('/evenements/' . $event->E_CODE . '/participants') }}', equipesUrl: '{{ url('/evenements/' . $event->E_CODE . '/equipes') }}' };</script>
@vite('resources/js/ob-event-show.js')
@if(auth()->user()->hasPermission(15))
<script>
function openEditLogModal(log) {
    const fmt = (dt) => dt ? dt.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('editLogTelCode').value  = log.TEL_CODE   ?? '';
    document.getElementById('editLogDebut').value    = fmt(log.EL_DEBUT);
    document.getElementById('editLogFin').value      = fmt(log.EL_FIN);
    document.getElementById('editLogSll').value      = fmt(log.EL_SLL);
    document.getElementById('editLogTitle').value    = log.EL_TITLE   ?? '';
    document.getElementById('editLogComment').value  = log.EL_COMMENTAIRE ?? '';
    document.getElementById('editLogImportant').checked = !!parseInt(log.EL_IMPORTANT);
    document.getElementById('editLogForm').action =
        '{{ url('/events') }}/' + {{ json_encode($event->E_CODE) }} + '/log/' + log.EL_ID;
    new bootstrap.Modal(document.getElementById('editLogModal')).show();
}
</script>
@endif
@endpush
