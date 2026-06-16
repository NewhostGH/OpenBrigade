@extends('layout.app')

@section('title', ($event->E_LIBELLE ?? $event->E_CODE) . ' — Trombinoscope — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Activités', 'url' => route('event.index')],
    ['label' => $event->E_LIBELLE ?? $event->E_CODE, 'url' => route('event.show', $event->E_CODE)],
    ['label' => 'Trombinoscope'],
]"/>

<div class="mx-3 mt-3">
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-id-badge me-1"></i>
                Trombinoscope — {{ $event->E_LIBELLE ?? $event->E_CODE }}
            </div>
            <div class="ob-widget-card-actions">
                <span class="badge bg-secondary me-2">{{ $participants->count() }} participant(s)</span>
                <a href="{{ route('event.show', $event->E_CODE) }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>
        </div>
    </div>

    @if($participants->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fas fa-users fa-3x mb-3 d-block opacity-25"></i>
            Aucun participant inscrit à cette activité.
        </div>
    @else
        @php $byFunction = $participants->groupBy('TP_LIBELLE'); @endphp
        @foreach($byFunction as $function => $group)
            @if($function)
                <h6 class="text-muted mb-2 mt-3" style="font-size:var(--font-size-xs);text-transform:uppercase;letter-spacing:.08em;">
                    {{ $function }} ({{ $group->count() }})
                </h6>
            @endif
            <div class="row g-2 mb-3">
                @foreach($group as $p)
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="ob-widget-card text-center p-2 h-100">
                        <a href="{{ route('personnel.show', $p->P_ID) }}" class="text-decoration-none text-body">
                            <img src="{{ $p->avatarSrc }}"
                                 alt="{{ $p->P_PRENOM }} {{ $p->P_NOM }}"
                                 class="ob-avatar rounded-circle mb-2"
                                 style="width:64px;height:64px;object-fit:cover;"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                            <div class="fw-semibold" style="font-size:var(--font-size-sm);line-height:1.2;">
                                {{ strtoupper($p->P_NOM) }}
                            </div>
                            <div class="text-muted" style="font-size:var(--font-size-xs);">
                                {{ ucfirst(mb_strtolower($p->P_PRENOM)) }}
                            </div>
                            @if($p->P_GRADE)
                                <div class="mt-1">
                                    <img src="{{ route('personnel.grade-image', ['grade' => $p->P_GRADE]) }}"
                                         alt="{{ $p->P_GRADE }}"
                                         class="ob-grade-img"
                                         onerror="this.outerHTML='<small class=\'text-muted\'>' + this.alt + '</small>'">
                                </div>
                            @endif
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endforeach
    @endif
</div>

@endsection
