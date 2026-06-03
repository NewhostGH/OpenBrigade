@extends('layout.app')

@section('title', 'Tableau de garde — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Garde'],
]"/>

<div class="ob-toolbar mx-3 mt-3">
    <div class="ob-toolbar-title">
        <h1>Tableau de garde</h1>
        @if(auth()->user()->hasPermission(26))
            <a href="{{ url('/legacy/astreinte_edit.php') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nouvelle astreinte
            </a>
        @endif
    </div>

    {{-- Week navigation --}}
    <div class="d-flex align-items-center gap-3 mt-2">
        <a href="{{ route('garde.index', ['week' => $prevWeek]) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-chevron-left"></i> Semaine précédente
        </a>

        <span class="fw-semibold" style="font-size:var(--font-size-sm)">
            Semaine du {{ $monday->locale('fr')->isoFormat('D MMMM') }}
            au {{ $sunday->locale('fr')->isoFormat('D MMMM YYYY') }}
        </span>

        <a href="{{ route('garde.index', ['week' => $nextWeek]) }}"
           class="btn btn-sm btn-outline-secondary">
            Semaine suivante <i class="fas fa-chevron-right"></i>
        </a>

        @if($weekOffset !== 0)
            <a href="{{ route('garde.index') }}" class="btn btn-sm btn-outline-primary">
                Semaine courante
            </a>
        @endif
    </div>
</div>

<div class="mx-3 mt-3">
    @php $hasAny = collect($days)->sum(fn($d) => count($d['slots'])) > 0; @endphp

    @if(!$hasAny)
        <div class="text-muted fst-italic p-3">Aucune astreinte pour cette semaine.</div>
    @else
        <div class="row g-2">
            @foreach($days as $day)
                <div class="col-xl col-lg-4 col-md-6 col-12">
                    <div class="widget-card {{ $day['isToday'] ? 'border-primary' : '' }}">
                        <div class="widget-card-header"
                             style="{{ $day['isToday'] ? 'background:color-mix(in srgb, var(--brand-bg) 8%, #fff)' : '' }}">
                            <div class="widget-card-title">
                                {{ $day['label'] }}
                            </div>
                            @if($day['isToday'])
                                <span class="badge bg-primary" style="font-size:var(--font-size-xs)">Aujourd'hui</span>
                            @endif
                        </div>
                        <div class="widget-card-body p-0">
                            @if($day['slots']->isEmpty())
                                <p class="widget-empty p-2">Aucune astreinte</p>
                            @else
                                @foreach($day['slots'] as $slot)
                                    <div class="duty-row px-2">
                                        <img src="{{ route('personnel.photo', $slot->P_ID) }}"
                                             width="32" height="32"
                                             class="duty-avatar"
                                             onerror="this.src='{{ asset('images/autre.png') }}'">
                                        <div class="duty-info">
                                            <div class="duty-name">
                                                <a href="{{ route('personnel.show', $slot->P_ID) }}"
                                                   class="text-decoration-none"
                                                   style="color:inherit">
                                                    {{ $slot->P_PRENOM }} {{ strtoupper($slot->P_NOM) }}
                                                </a>
                                            </div>
                                            <div class="duty-role">
                                                {{ $slot->GP_DESCRIPTION }}
                                                &mdash;
                                                {{ substr($slot->AS_DEBUT, 11, 5) }}–{{ substr($slot->AS_FIN, 11, 5) }}
                                            </div>
                                            @if($slot->P_PHONE)
                                                <a href="tel:{{ $slot->P_PHONE }}" class="duty-phone">
                                                    <i class="fas fa-phone fa-xs me-1"></i>{{ $slot->P_PHONE }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
