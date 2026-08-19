@extends('layout.app')

@section('title', ($event->E_LIBELLE ?? $event->E_CODE) . ' — ' . __('event.report_heading') . ' — ' . config('app.name'))

@section('content')

@php
    $type = $event->type;
    $showVehicules = ($type?->TE_VEHICULES ?? true) || $vehicules->isNotEmpty();
    $showMateriels = ($type?->TE_MATERIEL ?? true) || $materiels->isNotEmpty();
@endphp

<x-ob-breadcrumb :items="[
    ['label' => __('event.title'), 'url' => route('event.index')],
    ['label' => $event->E_LIBELLE ?? $event->E_CODE, 'url' => route('event.show', $event->E_CODE)],
    ['label' => __('event.report_heading')],
]"/>

<div class="mx-3 mt-3">

    {{-- ── Toolbar (screen only) ──────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3 d-print-none">
        <div class="ob-widget-card-body d-flex flex-wrap align-items-center gap-3">
            <span class="fw-semibold" style="font-size:var(--font-size-sm)">{{ __('event.report_toggle_label') }}</span>
            <label class="form-check-label d-flex align-items-center gap-1" style="font-size:var(--font-size-sm)">
                <input type="checkbox" class="form-check-input mt-0" checked onclick="document.getElementById('report-responsable').classList.toggle('d-none', !this.checked)">
                {{ __('event.report_section_responsable') }}
            </label>
            <label class="form-check-label d-flex align-items-center gap-1" style="font-size:var(--font-size-sm)">
                <input type="checkbox" class="form-check-input mt-0" checked onclick="document.getElementById('report-figures').classList.toggle('d-none', !this.checked)">
                {{ __('event.report_section_figures') }}
            </label>
            @if($showVehicules)
            <label class="form-check-label d-flex align-items-center gap-1" style="font-size:var(--font-size-sm)">
                <input type="checkbox" class="form-check-input mt-0" checked onclick="document.getElementById('report-vehicules').classList.toggle('d-none', !this.checked)">
                {{ __('event.report_section_vehicules') }}
            </label>
            @endif
            @if($showMateriels)
            <label class="form-check-label d-flex align-items-center gap-1" style="font-size:var(--font-size-sm)">
                <input type="checkbox" class="form-check-input mt-0" checked onclick="document.getElementById('report-materiels').classList.toggle('d-none', !this.checked)">
                {{ __('event.report_section_materiels') }}
            </label>
            @endif
            <label class="form-check-label d-flex align-items-center gap-1" style="font-size:var(--font-size-sm)">
                <input type="checkbox" class="form-check-input mt-0" checked onclick="document.getElementById('report-log').classList.toggle('d-none', !this.checked)">
                {{ __('event.report_section_log') }}
            </label>

            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> {{ __('event.report_btn_print') }}
                </button>
                <a href="{{ route('event.show', $event->E_CODE) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('event.btn_back') }}
                </a>
            </div>
        </div>
    </div>

    {{-- ── Report header ──────────────────────────────────────────────────── --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-body">
            <h2 style="font-size:var(--font-size-lg); margin-bottom:2px;">{{ $event->E_LIBELLE ?? $event->E_CODE }}</h2>
            <div class="text-muted" style="font-size:var(--font-size-sm)">
                {{ $type?->TE_LIBELLE ?? $event->TE_CODE }}
                @if($event->E_LIEU) · {{ $event->E_LIEU }} @endif
            </div>
            @if($event->horaires->isNotEmpty())
                <div class="mt-1" style="font-size:var(--font-size-sm)">
                    @foreach($event->horaires->sortBy('EH_DATE_DEBUT') as $h)
                        <span class="me-2">
                            {{ \Carbon\Carbon::parse($h->EH_DATE_DEBUT)->locale('fr')->isoFormat('ddd D MMM YYYY') }}
                            @if($h->EH_DATE_FIN && $h->EH_DATE_FIN->toDateString() !== $h->EH_DATE_DEBUT->toDateString())
                                → {{ \Carbon\Carbon::parse($h->EH_DATE_FIN)->locale('fr')->isoFormat('D MMM') }}
                            @endif
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Responsable ────────────────────────────────────────────────────── --}}
    <div id="report-responsable" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-user-shield me-1"></i> {{ __('event.report_section_responsable') }}</div>
        </div>
        <div class="ob-widget-card-body" style="font-size:var(--font-size-sm)">
            @if($event->chef)
                <strong>{{ $event->chef->P_PRENOM }} {{ strtoupper($event->chef->P_NOM) }}</strong>
                @if($event->E_TEL)<span class="text-muted ms-2">{{ $event->E_TEL }}</span>@endif
            @else
                <span class="text-muted fst-italic">{{ __('event.report_no_responsable') }}</span>
            @endif
        </div>
    </div>

    {{-- ── Principaux chiffres ────────────────────────────────────────────── --}}
    <div id="report-figures" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-chart-bar me-1"></i> {{ __('event.report_section_figures') }}</div>
        </div>
        <div class="ob-widget-card-body">
            <div class="d-flex flex-wrap gap-4">
                @foreach([
                    'participants' => __('event.report_fig_participants'),
                    'vehicules'    => __('event.report_fig_vehicules'),
                    'materiels'    => __('event.report_fig_materiels'),
                    'messages'     => __('event.report_fig_messages'),
                    'interventions'=> __('event.report_fig_interventions'),
                ] as $key => $label)
                    <div class="text-center">
                        <div class="fw-bold" style="font-size:var(--font-size-lg)">{{ $figures[$key] }}</div>
                        <div class="text-muted" style="font-size:var(--font-size-xs)">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Véhicules engagés ──────────────────────────────────────────────── --}}
    @if($showVehicules)
    <div id="report-vehicules" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-truck me-1"></i> {{ __('event.report_section_vehicules') }}
                <span class="ob-badge ob-badge-archive ms-1">{{ $vehicules->count() }}</span>
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($vehicules->isEmpty())
                <p class="ob-widget-empty p-3">{{ __('event.vehicules_empty') }}</p>
            @else
                <table class="table table-sm mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>{{ __('event.th_indicatif') }}</th>
                            <th>{{ __('event.th_immat') }}</th>
                            <th class="text-end">{{ __('event.th_km') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicules as $v)
                        <tr>
                            <td style="font-size:var(--font-size-sm)">{{ $v->V_INDICATIF ?: '—' }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $v->V_IMMATRICULATION ?: '—' }}</td>
                            <td class="text-end" style="font-size:var(--font-size-sm)">{{ $v->EV_KM ? $v->EV_KM.' km' : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Matériel engagé ────────────────────────────────────────────────── --}}
    @if($showMateriels)
    <div id="report-materiels" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-box me-1"></i> {{ __('event.report_section_materiels') }}
                <span class="ob-badge ob-badge-archive ms-1">{{ $materiels->count() }}</span>
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($materiels->isEmpty())
                <p class="ob-widget-empty p-3">{{ __('event.materiels_empty') }}</p>
            @else
                <table class="table table-sm mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th>{{ __('event.th_designation') }}</th>
                            <th>{{ __('event.th_reference') }}</th>
                            <th class="text-center">{{ __('event.th_qty') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materiels as $m)
                        <tr>
                            <td style="font-size:var(--font-size-sm)">{{ $m->MA_MODELE }}</td>
                            <td style="font-size:var(--font-size-sm)">{{ $m->MA_NUMERO_SERIE ?: '—' }}</td>
                            <td class="text-center" style="font-size:var(--font-size-sm)">{{ $m->EM_NB }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Main courante ──────────────────────────────────────────────────── --}}
    <div id="report-log" class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-clipboard-list me-1"></i> {{ __('event.report_section_log') }}
                <span class="ob-badge ob-badge-archive ms-1">{{ $eventLog->count() }}</span>
            </div>
        </div>
        <div class="ob-widget-card-body p-0">
            @if($eventLog->isEmpty())
                <p class="ob-widget-empty p-3">{{ __('event.report_log_empty') }}</p>
            @else
                <table class="table table-sm mb-0">
                    <thead style="background:var(--table-header-bg);color:var(--table-header-text)">
                        <tr>
                            <th style="width:150px">{{ __('event.report_log_date') }}</th>
                            <th>{{ __('event.report_log_title') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eventLog as $l)
                        <tr @if($l->EL_IMPORTANT) style="color:var(--bs-danger)" @endif>
                            <td style="font-size:var(--font-size-xs)">
                                @if($l->TEL_CODE === 'I')
                                    <i class="fas fa-medkit me-1" title="{{ __('event.report_log_intervention') }}"></i>
                                @else
                                    <i class="far fa-file-alt me-1" title="{{ __('event.report_log_message') }}"></i>
                                @endif
                                {{ $l->EL_DEBUT ? \Carbon\Carbon::parse($l->EL_DEBUT)->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td style="font-size:var(--font-size-sm)">
                                {{ $l->EL_TITLE ?: ($l->TEL_DESCRIPTION ?? '') }}
                                @if($l->EL_COMMENTAIRE)
                                    <div class="text-muted" style="font-size:var(--font-size-xs);white-space:pre-line">{{ $l->EL_COMMENTAIRE }}</div>
                                @endif
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
