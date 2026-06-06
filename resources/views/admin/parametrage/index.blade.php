@extends('layout.app')

@section('title', 'Paramétrage — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Paramétrage'],
]"/>

<div class="mx-3 mt-3">
    <div class="row g-3">
        @php
        $sections = [
            ['route' => 'admin.parametrage.type-evenement',    'icon' => 'calendar-alt',  'label' => 'Types d\'activité',    'count' => $counts['type_evenement'],    'desc' => 'Opération, Formation, Garde…'],
            ['route' => 'admin.parametrage.type-participation','icon' => 'users',          'label' => 'Fonctions activité',   'count' => $counts['type_participation'],'desc' => 'Chef de groupe, Équipier…'],
            ['route' => 'admin.parametrage.type-materiel',     'icon' => 'toolbox',        'label' => 'Types de matériel',    'count' => $counts['type_materiel'],     'desc' => 'EPI, outillage…'],
            ['route' => 'admin.parametrage.type-consommable',  'icon' => 'boxes',          'label' => 'Types de consommable', 'count' => $counts['type_consommable'],  'desc' => 'Carburant, médicaments…'],
            ['route' => 'admin.parametrage.type-vehicule',     'icon' => 'truck',          'label' => 'Types de véhicule',    'count' => 0,                            'desc' => 'VSAV, FPT, VL…'],
            ['route' => 'admin.parametrage.grade',             'icon' => 'medal',          'label' => 'Icônes de grades',     'count' => $counts['grade'],             'desc' => 'Images associées à chaque grade'],
        ];
        @endphp
        @foreach($sections as $s)
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route($s['route']) }}" class="text-decoration-none">
                <div class="ob-widget-card p-3 h-100 d-flex align-items-center gap-3" style="transition:box-shadow .15s;cursor:pointer;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px;height:44px;background:var(--sidebar-bg);color:var(--sidebar-text);">
                        <i class="fas fa-{{ $s['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size:var(--font-size-sm);">{{ $s['label'] }}</div>
                        <div class="text-muted" style="font-size:var(--font-size-xs);">{{ $s['desc'] }}</div>
                    </div>
                    @if($s['count'] > 0)
                        <span class="ms-auto ob-badge ob-badge-int">{{ $s['count'] }}</span>
                    @endif
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>

@endsection
