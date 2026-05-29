@extends('layout.app')

@section('title', 'Géolocalisation — ' . config('app.name'))

@push('styles')
    @vite('resources/js/geolocalisation.js')
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
<div class="ob-geo-toolbar noprint">
    <select class="form-select" onchange="updateParam('section', this.value)">
        <option value="0" {{ $sectionId === 0 ? 'selected' : '' }}>Toutes sections</option>
        @foreach ($sections as $sec)
            <option value="{{ $sec->S_ID }}" {{ $sectionId === (int)$sec->S_ID ? 'selected' : '' }}>
                {{ $sec->S_CODE }}{{ $sec->S_DESCRIPTION ? ' — ' . $sec->S_DESCRIPTION : '' }}
            </option>
        @endforeach
    </select>
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
    Pass server-side data to the Vite module (geolocalisation.js).
    This plain <script> executes synchronously; the deferred ES module reads
    window.GEO_MARKERS only after DOMContentLoaded, so the order is guaranteed.
--}}
<script>
    window.GEO_MARKERS = @json($markers);

    function updateParam(key, value) {
        var url = new URL(window.location.href);
        url.searchParams.set(key, value);
        window.location.href = url.toString();
    }
</script>
@endpush
