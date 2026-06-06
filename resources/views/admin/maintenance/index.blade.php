@extends('layout.app')

@section('title', 'Maintenance — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Administration'],
    ['label' => 'Maintenance'],
]"/>

<div class="mx-3 mt-3">

    {{-- System info --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-server me-2"></i>Informations système</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <tbody>
                    <tr>
                        <td style="width:35%;font-size:var(--font-size-sm);" class="fw-semibold">Version OpenBrigade (DB)</td>
                        <td><span class="ob-badge ob-badge-int">{{ $appVersion }}</span></td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">Laravel</td>
                        <td style="font-size:var(--font-size-sm);">{{ $laravelVersion }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">PHP</td>
                        <td style="font-size:var(--font-size-sm);">{{ $phpVersion }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">Base de données</td>
                        <td style="font-size:var(--font-size-sm);">{{ $dbVersion }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">Environnement</td>
                        <td><span class="ob-badge {{ $env === 'production' ? 'ob-badge-bloqued' : 'ob-badge-ext' }}">{{ $env }}</span></td>
                    </tr>
                    <tr>
                        <td style="font-size:var(--font-size-sm);" class="fw-semibold">Mode debug</td>
                        <td>
                            <span class="ob-badge {{ $debugMode === 'Activé' ? 'ob-badge-bloqued' : 'ob-badge-int' }}">
                                {{ $debugMode }}
                            </span>
                            @if($debugMode === 'Activé')
                                <span class="ms-2 text-danger" style="font-size:var(--font-size-xs);">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Désactivez APP_DEBUG en production
                                </span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Migration status --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-database me-2"></i>État des migrations Laravel</div>
            <div class="ob-widget-card-actions" style="font-size:var(--font-size-xs);color:var(--text-muted-soft);">
                Remplace upgrade.php — les migrations se lancent via <code>php artisan migrate</code>
            </div>
        </div>
        @if(empty($status))
            <div class="p-3 text-muted" style="font-size:var(--font-size-sm);">
                Impossible de récupérer le statut des migrations.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px;">État</th>
                            <th>Migration</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($status as $m)
                        <tr>
                            <td class="align-middle">
                                @if($m['ran'])
                                    <span class="ob-badge ob-badge-int"><i class="fas fa-check me-1"></i>Exécutée</span>
                                @else
                                    <span class="ob-badge ob-badge-bloqued"><i class="fas fa-clock me-1"></i>En attente</span>
                                @endif
                            </td>
                            <td class="align-middle font-monospace" style="font-size:var(--font-size-xs);">
                                {{ $m['name'] }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Cache / storage --}}
    <div class="ob-widget-card mb-3">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title"><i class="fas fa-broom me-2"></i>Commandes utiles</div>
        </div>
        <div class="p-3">
            <table class="table table-sm mb-0" style="font-size:var(--font-size-sm);">
                <tbody>
                    <tr>
                        <td class="font-monospace" style="width:50%;">php artisan migrate</td>
                        <td>Appliquer les migrations en attente</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">php artisan cache:clear</td>
                        <td>Vider le cache applicatif</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">php artisan view:clear</td>
                        <td>Vider le cache des vues Blade</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">php artisan config:clear</td>
                        <td>Vider le cache de configuration</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">php artisan route:clear</td>
                        <td>Vider le cache des routes</td>
                    </tr>
                    <tr>
                        <td class="font-monospace">npm run build</td>
                        <td>Recompiler les assets Vite (CSS/JS)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
