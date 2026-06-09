@extends('layout.app')

@section('title', 'Plugins — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Plugins'],
]"/>

<div class="mx-3 mt-3">
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-puzzle-piece me-2"></i>Plugins</div>
        </div>

        <div class="text-center p-5">
            <i class="fas fa-puzzle-piece mb-3" style="font-size:3rem;color:var(--text-muted-soft);"></i>
            <h4 class="mb-2">
                Plugins communautaires
                <span class="ob-badge ob-badge-ext">WIP</span>
            </h4>
            <p class="text-muted mx-auto" style="max-width:520px;font-size:var(--font-size-sm);">
                Les plugins communautaires permettront aux administrateurs d'étendre OpenBrigade
                avec des fonctionnalités supplémentaires développées par la communauté.
                Cette section est en cours de développement (WIP).
            </p>
        </div>
    </div>
</div>

@endsection
