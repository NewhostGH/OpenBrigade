import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix default marker icon paths broken by Vite's asset hashing
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon   from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl:       markerIcon,
    shadowUrl:     markerShadow,
});

// ── Map initialisation ──────────────────────────────────────────────────────
// Markers are injected by the Blade template into window.GEO_MARKERS before
// this deferred module executes.
document.addEventListener('DOMContentLoaded', function () {
    var mapEl = document.getElementById('geoMap');
    if (!mapEl) return; // not on this page

    var markers = window.GEO_MARKERS || [];

    var defaultCenter = [46.5, 2.5]; // centre of France
    var defaultZoom   = 6;

    if (markers.length > 0) {
        defaultCenter = [markers[0].lat, markers[0].lng];
        defaultZoom   = 8;
    }

    var map = L.map('geoMap').setView(defaultCenter, defaultZoom);

    // OpenStreetMap tile layer — free, no API key required
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    // Custom pin icon using Font Awesome (FA is loaded globally via app.js)
    var pinIcon = L.divIcon({
        className: '',
        html: '<div style="background:var(--accent,#FA7070);color:#fff;border-radius:50% 50% 50% 0;'
            + 'width:28px;height:28px;transform:rotate(-45deg);border:2px solid #fff;'
            + 'box-shadow:0 1px 4px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;">'
            + '<i class="fas fa-user" style="transform:rotate(45deg);font-size:11px;"></i></div>',
        iconSize:    [28, 28],
        iconAnchor:  [14, 28],
        popupAnchor: [0, -30],
    });

    markers.forEach(function (m) {
        var popup = '<div class="ob-geo-popup">'
            + '<img src="' + m.photo_url + '" alt="" onerror="this.style.display=\'none\'">'
            + '<strong>' + m.name + '</strong>'
            + '<small>' + (m.grade || '') + (m.section ? ' &nbsp;·&nbsp; ' + m.section : '') + '</small>'
            + (m.phone   ? '<small>' + m.phone   + '</small>' : '')
            + (m.address ? '<small class="text-muted">' + m.address + '</small>' : '')
            + '<div class="clearfix"></div>'
            + '<a href="' + m.profile_url + '" target="_blank">'
            + '<i class="fas fa-external-link-alt me-1"></i>Voir la fiche</a>'
            + '</div>';

        L.marker([m.lat, m.lng], { icon: pinIcon })
            .addTo(map)
            .bindPopup(popup);
    });

    // Fit map to all markers
    if (markers.length > 1) {
        var bounds = L.latLngBounds(markers.map(function (m) { return [m.lat, m.lng]; }));
        map.fitBounds(bounds, { padding: [30, 30] });
    }
});
