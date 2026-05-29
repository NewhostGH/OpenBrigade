@extends('layout.app')

@section('title', ($event->E_LIBELLE ?? $event->E_CODE) . ' — ' . config('app.name'))

@section('content')

<div class="mx-3 mt-3">

    {{-- ── Header card ───────────────────────────────────────────────────── --}}
    <div class="widget-card mb-3">
        <div class="widget-card-header">
            <div class="widget-card-title">
                <i class="fas fa-calendar-alt"></i>
                {{ $event->E_LIBELLE ?? $event->E_CODE }}
                @if($event->E_CANCELED)
                    <span class="badge bg-danger ms-2">Annulé</span>
                @elseif($event->E_CLOSED)
                    <span class="badge bg-secondary ms-2">Clôturé</span>
                @else
                    <span class="badge bg-success ms-2">Ouvert</span>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission(15))
                    <a href="{{ url('/legacy/evenement_edit.php?action=update&evenement=' . $event->E_CODE) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                @endif
                <a href="{{ route('evenement.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
        <div class="widget-card-body">
            <div class="row g-3">
                <div class="col-sm-6">
                    <dl class="row mb-0" style="font-size:var(--font-size-sm)">
                        <dt class="col-5 text-muted fw-normal">Type</dt>
                        <dd class="col-7">{{ $event->TE_CODE ?? '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Lieu</dt>
                        <dd class="col-7">{{ $event->E_LIEU ?: '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Section</dt>
                        <dd class="col-7">{{ $event->section?->S_DESCRIPTION ?? '—' }}</dd>

                        @if($event->chef)
                            <dt class="col-5 text-muted fw-normal">Responsable</dt>
                            <dd class="col-7">
                                {{ $event->chef->P_PRENOM }} {{ strtoupper($event->chef->P_NOM) }}
                            </dd>
                        @endif
                    </dl>
                </div>
                <div class="col-sm-6">
                    <div style="font-size:var(--font-size-sm);font-weight:600;color:var(--text-muted-soft);margin-bottom:4px">
                        Créneaux
                    </div>
                    @forelse($event->horaires as $h)
                        <div style="font-size:var(--font-size-sm)">
                            {{ \Carbon\Carbon::parse($h->EH_DATE_DEBUT)->locale('fr')->isoFormat('ddd D MMM YYYY') }}
                            @if($h->EH_DEBUT)
                                <span class="text-muted">
                                    {{ substr($h->EH_DEBUT, 0, 5) }}–{{ substr($h->EH_FIN, 0, 5) }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <span class="text-muted fst-italic">Aucun créneau</span>
                    @endforelse
                </div>
            </div>

            @if($event->E_DESCRIPTION ?? '')
                <hr style="border-color:var(--component-border)">
                <p style="font-size:var(--font-size-sm)">{!! nl2br(e($event->E_DESCRIPTION)) !!}</p>
            @endif
        </div>
    </div>

    {{-- ── Tabs ───────────────────────────────────────────────────────────── --}}
    <nav class="ob-nav navbar mb-3 px-3 rounded">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'personnel' ? 'active' : '' }}"
                   href="{{ route('evenement.show', [$event->E_CODE, 'tab' => 'personnel']) }}">
                    <i class="fas fa-users me-1"></i> Participants
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'vehicule' ? 'active' : '' }}"
                   href="{{ route('evenement.show', [$event->E_CODE, 'tab' => 'vehicule']) }}">
                    <i class="fas fa-truck me-1"></i> Véhicules
                </a>
            </li>
        </ul>
    </nav>

    {{-- ── Tab: Participants ──────────────────────────────────────────────── --}}
    @if($tab === 'personnel')
        <div class="widget-card">
            <div class="widget-card-header">
                <div class="widget-card-title"><i class="fas fa-users"></i> Participants</div>
                <span style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                    {{ count($participants) }} inscrits
                </span>
            </div>
            <div class="widget-card-body p-0">
                @if(count($participants) === 0)
                    <p class="widget-empty p-3">Aucun participant.</p>
                @else
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                            <tr>
                                <th style="width:36px"></th>
                                <th>Nom</th>
                                <th>Grade</th>
                                <th>Créneau</th>
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
                                        @if($p->EH_DATE_DEBUT)
                                            {{ \Carbon\Carbon::parse($p->EH_DATE_DEBUT)->format('d/m/Y') }}
                                            @if($p->EH_DEBUT)
                                                {{ substr($p->EH_DEBUT, 0, 5) }}
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Tab: Véhicules ────────────────────────────────────────────────── --}}
    @if($tab === 'vehicule')
        <div class="widget-card">
            <div class="widget-card-header">
                <div class="widget-card-title"><i class="fas fa-truck"></i> Véhicules</div>
            </div>
            <div class="widget-card-body p-0">
                @if(count($vehicules) === 0)
                    <p class="widget-empty p-3">Aucun véhicule assigné.</p>
                @else
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                            <tr>
                                <th>Immatriculation</th>
                                <th>Libellé</th>
                                <th>Km</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicules as $v)
                                <tr>
                                    <td style="font-size:var(--font-size-sm);font-weight:600">
                                        {{ $v->V_IMMAT }}
                                    </td>
                                    <td style="font-size:var(--font-size-sm)">{{ $v->V_LIBELLE }}</td>
                                    <td style="font-size:var(--font-size-sm);color:var(--text-muted-soft)">
                                        {{ $v->EV_KM ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endif
</div>

@endsection
