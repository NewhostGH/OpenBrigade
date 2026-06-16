@extends('layout.app')

@section('title', 'Géolocalisation — ' . config('app.name'))

@section('content')

<x-ob-breadcrumb :items="[
    ['label' => 'Géolocalisation'],
]"/>

{{-- Toolbar --}}
<div class="ob-geo-toolbar noprint">
    @feature('multi_site')
    <select class="form-select" onchange="updateParam('section', this.value)">
        <option value="" {{ $sectionId === null ? 'selected' : '' }}>Toutes sections</option>
        @foreach ($sections as $sec)
            <option value="{{ $sec->S_ID }}" {{ $sectionId === (int)$sec->S_ID ? 'selected' : '' }}>
                {{ $sec->S_CODE }}{{ $sec->S_DESCRIPTION ? ' — ' . $sec->S_DESCRIPTION : '' }}
            </option>
        @endforeach
    </select>
    @endfeature
    <span class="ob-geo-stat">
        <i class="fas fa-map-marker-alt me-1"></i>
        {{ $count }} membre{{ $count > 1 ? 's' : '' }} géolocalisé{{ $count > 1 ? 's' : '' }}
    </span>
</div>

{{-- Map container --}}
<div id="geoMap" class="ob-geo-map"></div>

@endsection

@push('scripts')
{{--
    Pass server-side data to the Vite module (ob-geolocation.js).
    This plain <script> executes synchronously; the deferred ES module reads
    window.GEO_MARKERS only after DOMContentLoaded, so the order is guaranteed.
--}}
<script>window.GEO_MARKERS = @json($markers); window.GEO_CONFIG = @json(config('brigade.geo'));</script>
@vite('resources/js/ob-geolocation.js')
@endpush
