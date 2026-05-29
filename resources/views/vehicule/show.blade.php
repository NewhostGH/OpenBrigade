@extends('layout.app')

@section('title', $vehicule->V_IMMAT . ' — ' . config('app.name'))

@section('content')

<div class="mx-3 mt-3">
    <div class="widget-card mb-3">
        <div class="widget-card-header">
            <div class="widget-card-title">
                <i class="fas fa-truck"></i>
                {{ $vehicule->V_IMMAT }}
                @if($position)
                    @if($position->VP_OPERATIONNEL >= 2)
                        <span class="badge bg-success ms-2">Opérationnel</span>
                    @elseif($position->VP_OPERATIONNEL === 1)
                        <span class="badge bg-warning text-dark ms-2">Limité</span>
                    @else
                        <span class="badge bg-danger ms-2">Indisponible</span>
                    @endif
                @endif
            </div>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission(17))
                    <a href="{{ url('/legacy/upd_vehicule.php?vehicule=' . $vehicule->V_ID) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                @endif
                <a href="{{ route('vehicule.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
        <div class="widget-card-body">
            <div class="row g-3">
                <div class="col-sm-6">
                    <dl class="row mb-0" style="font-size:var(--font-size-sm)">
                        <dt class="col-5 text-muted fw-normal">Libellé</dt>
                        <dd class="col-7">{{ $vehicule->V_LIBELLE ?: '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Section</dt>
                        <dd class="col-7">{{ $vehicule->section?->S_DESCRIPTION ?? '—' }}</dd>

                        @if($position)
                            <dt class="col-5 text-muted fw-normal">Position</dt>
                            <dd class="col-7">{{ $position->VP_LIBELLE ?? '—' }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="col-sm-6">
                    <dl class="row mb-0" style="font-size:var(--font-size-sm)">
                        @php
                            $warn30 = now()->addDays(30)->toDateString();
                        @endphp

                        <dt class="col-5 text-muted fw-normal">Assurance</dt>
                        <dd class="col-7{{ $vehicule->V_ASS_DATE && $vehicule->V_ASS_DATE->toDateString() <= $warn30 ? ' text-danger fw-semibold' : '' }}">
                            {{ $vehicule->V_ASS_DATE ? $vehicule->V_ASS_DATE->format('d/m/Y') : '—' }}
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Contrôle tech.</dt>
                        <dd class="col-7{{ $vehicule->V_CT_DATE && $vehicule->V_CT_DATE->toDateString() <= $warn30 ? ' text-danger fw-semibold' : '' }}">
                            {{ $vehicule->V_CT_DATE ? $vehicule->V_CT_DATE->format('d/m/Y') : '—' }}
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Révision</dt>
                        <dd class="col-7{{ $vehicule->V_REV_DATE && $vehicule->V_REV_DATE->toDateString() <= $warn30 ? ' text-danger fw-semibold' : '' }}">
                            {{ $vehicule->V_REV_DATE ? $vehicule->V_REV_DATE->format('d/m/Y') : '—' }}
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Titre</dt>
                        <dd class="col-7{{ $vehicule->V_TITRE_DATE && $vehicule->V_TITRE_DATE->toDateString() <= $warn30 ? ' text-danger fw-semibold' : '' }}">
                            {{ $vehicule->V_TITRE_DATE ? $vehicule->V_TITRE_DATE->format('d/m/Y') : '—' }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent events --}}
    <div class="widget-card">
        <div class="widget-card-header">
            <div class="widget-card-title"><i class="fas fa-history"></i> Dernières activités</div>
        </div>
        <div class="widget-card-body p-0">
            @if($recentEvents->isEmpty())
                <p class="widget-empty p-3">Aucune activité enregistrée.</p>
            @else
                <table class="table table-sm table-hover mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>Activité</th>
                            <th>Date</th>
                            <th>Km</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentEvents as $ev)
                            <tr>
                                <td style="font-size:var(--font-size-sm)">
                                    <a href="{{ route('evenement.show', $ev->E_CODE) }}"
                                       class="text-decoration-none">
                                        {{ $ev->E_LIBELLE ?? $ev->E_CODE }}
                                    </a>
                                </td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $ev->EH_DATE_DEBUT ? \Carbon\Carbon::parse($ev->EH_DATE_DEBUT)->format('d/m/Y') : '—' }}
                                </td>
                                <td style="font-size:var(--font-size-xs);color:var(--text-muted-soft)">
                                    {{ $ev->EV_KM ?? '—' }}
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
