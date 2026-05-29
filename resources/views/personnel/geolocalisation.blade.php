@extends('layout.app')

@section('title', 'Géolocalisation — ' . config('app.name'))

@push('styles')
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
#geoMap {
    height: calc(100vh - 130px);
    min-height: 400px;
    z-index: 0;
}
.geo-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-bottom: 1px solid var(--card-border);
    background: var(--bg-subtle);
    flex-wrap: wrap;
}
.geo-toolbar select.form-select {
    font-size: var(--font-size-sm);
    height: 30px; padding: 3px 28px 3px 8px; min-width: 120px; max-width: 220px;
}
.geo-stat {
    font-size: var(--font-size-xs);
    color: var(--sidebar-border);
    margin-left: auto;
}

/* Custom Leaflet popup styling */
.geo-popup { font-size: 13px; min-width: 150px; }
.geo-popup img { height: 44px; width: 44px; border-radius: 50%; object-fit: cover; float: left; margin-right: 8px; }
.geo-popup strong { display: block; }
.geo-popup small { color: #666; }
.geo-popup a { display: block; margin-top: 4px; font-size: 11px; }
.geo-popup .clearfix { clear: both; }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<div style="padding:4px 12px;border-bottom:1px solid var(--card-border);background:var(--bg-base);">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" style="color:#666;font-size:0.87rem;font-weight:bold;">Accueil</a>
            </li>
            <li class="breadcrumb-item" style="font-size:0.87rem;font-weight:bold;color:#666;">Personnel</li>
            <li class="breadcrumb-item active" style="color:#2b224f;font-size:0.87rem;font-weight:bold;">
                Géolocalisation
            </li>
        </ol>
    </nav>
</div>

{{-- Toolbar --}}
<div class="geo-toolbar noprint">
    <select class="form-select" onchange="updateParam('section', this.value)">
        <option value="0" {{ $sectionId === 0 ? 'selected' : '' }}>Toutes sections</option>
        @foreach ($sections as $sec)
            <option value="{{ $sec->S_ID }}" {{ $sectionId === (int)$sec->S_ID ? 'selected' : '' }}>
                {{ $sec->S_CODE }}{{ $sec->S_DESCRIPTION ? ' — ' . $sec->S_DESCRIPTION : '' }}
            </option>
        @endforeach
    </select>
    <span class="geo-stat">
        <i class="fas fa-map-marker-alt me-1"></i>
        {{ $count }} membre{{ $count > 1 ? 's' : '' }} géolocalisé{{ $count > 1 ? 's' : '' }}
    </span>
</div>

{{-- Map --}}
<div id="geoMap"></div>

@endsection

@push('scripts')
{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WLs=" crossorigin=""></script>

<script>
(function () {
    'use strict';

    // ── URL helper ──────────────────────────────────────────────────
    function updateParam(key, value) {
        var url = new URL(window.location.href);
        url.searchParams.set(key, value);
        window.location.href = url.toString();
    }
    window.updateParam = updateParam;

    // ── Markers data ────────────────────────────────────────────────
    var markers = @json($markers);

    // ── Map init ────────────────────────────────────────────────────
    var defaultCenter = [46.5, 2.5]; // center of France
    var defaultZoom   = 6;

    if (markers.length > 0) {
        defaultCenter = [markers[0].lat, markers[0].lng];
        defaultZoom   = 8;
    }

    var map = L.map('geoMap').setView(defaultCenter, defaultZoom);

    // OpenStreetMap tile layer (free, no API key)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    // Custom pin icon
    var pinIcon = L.divIcon({
        className: '',
        html: '<div style="background:var(--accent,#4F46E5);color:#fff;border-radius:50% 50% 50% 0;width:28px;height:28px;transform:rotate(-45deg);border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;">' +
              '<i class="fas fa-user" style="transform:rotate(45deg);font-size:11px;"></i></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 28],
        popupAnchor: [0, -30],
    });

    // Add markers
    markers.forEach(function (m) {
        var popup = '<div class="geo-popup">' +
            '<img src="' + m.photo_url + '" alt="" onerror="this.style.display=\'none\'">' +
            '<strong>' + m.name + '</strong>' +
            '<small>' + (m.grade || '') + (m.section ? ' &nbsp;·&nbsp; ' + m.section : '') + '</small>' +
            (m.phone ? '<small>' + m.phone + '</small>' : '') +
            (m.address ? '<small class="text-muted">' + m.address + '</small>' : '') +
            '<div class="clearfix"></div>' +
            '<a href="' + m.profile_url + '" target="_blank"><i class="fas fa-external-link-alt me-1"></i>Voir la fiche</a>' +
            '</div>';

        L.marker([m.lat, m.lng], { icon: pinIcon })
            .addTo(map)
            .bindPopup(popup);
    });

    // Fit map to all markers if there are any
    if (markers.length > 1) {
        var bounds = L.latLngBounds(markers.map(function (m) { return [m.lat, m.lng]; }));
        map.fitBounds(bounds, { padding: [30, 30] });
    }

}());
</script>
@endpush
