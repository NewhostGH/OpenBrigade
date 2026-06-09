@extends('layout.app')

@section('title', 'Cartographie — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Organisation'],
    ['label' => 'Cartographie'],
]"/>

<div class="ob-geo-toolbar noprint">
    <a href="{{ route('organisation.sections') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-layer-group me-1"></i>Sections
    </a>
    <span class="ob-geo-stat">
        <i class="fas fa-map-marker-alt me-1"></i>
        {{ $count }} section{{ $count > 1 ? 's' : '' }} géolocalisée{{ $count > 1 ? 's' : '' }}
    </span>
</div>

@if ($count === 0)
    <div class="alert alert-info mx-3 mt-3" style="font-size:var(--font-size-sm);">
        Aucune section géolocalisée. Les sections sont placées au barycentre des positions GPS de leurs membres
        (renseignées depuis la fiche de chaque membre).
    </div>
@endif

{{-- Map container (re-uses the ob-geolocalisation Leaflet module) --}}
<div id="geoMap" class="ob-geo-map"></div>

@endsection

@push('scripts')
<script>window.GEO_MARKERS = @json($markers); window.GEO_CONFIG = @json(config('brigade.geo'));</script>
@vite('resources/js/ob-geolocalisation.js')
@endpush
