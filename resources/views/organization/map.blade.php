@extends('layout.app')

@section('title', 'Cartographie — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => __('organization.bc_organisation')],
    ['label' => __('organization.bc_map')],
]"/>

<div class="ob-geo-toolbar noprint">
    <a href="{{ route('organization.sections') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-layer-group me-1"></i>{{ __('organization.page_sections') }}
    </a>
    <span class="ob-geo-stat">
        <i class="fas fa-map-marker-alt me-1"></i>
        {{ trans_choice('organization.geolocated_count', $count, ['count' => $count]) }}
    </span>
</div>

@if ($count === 0)
    <div class="alert alert-info mx-3 mt-3" style="font-size:var(--font-size-sm);">
        {{ __('organization.no_geolocated') }}
    </div>
@endif

{{-- Map container (re-uses the ob-geolocation Leaflet module) --}}
<div id="geoMap" class="ob-geo-map"></div>

@endsection

@push('scripts')
<script>window.GEO_MARKERS = @json($markers); window.GEO_CONFIG = @json(config('brigade.geo'));</script>
@vite('resources/js/ob-geolocation.js')
@endpush
