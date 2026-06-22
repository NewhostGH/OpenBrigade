{{--
    Connection diagram: Client → Réseau → Serveur, with the failing node
    highlighted. $node ∈ {client, network, server} marks where the request
    broke down (4xx → client, gateway/proxy → network, 5xx → server).
--}}
@php
    $cls = fn (string $key) => 'ob-node '.($key === $node ? 'is-fail' : 'is-ok');
    // A connector is broken when it touches the failing node.
    $conn1Fail = in_array($node, ['client', 'network'], true);  // client ↔ réseau
    $conn2Fail = in_array($node, ['network', 'server'], true);  // réseau ↔ serveur
@endphp
<svg class="ob-error-diagram-svg" viewBox="0 0 380 124" xmlns="http://www.w3.org/2000/svg"
     role="img" aria-label="{{ __('errors.diagram_aria') }}">

    {{-- ── Connectors ─────────────────────────────────────────────── --}}
    <line class="ob-conn {{ $conn1Fail ? 'is-fail' : '' }}" x1="82" y1="50" x2="156" y2="50"/>
    <line class="ob-conn {{ $conn2Fail ? 'is-fail' : '' }}" x1="224" y1="50" x2="298" y2="50"/>

    {{-- ── Client (poste) ─────────────────────────────────────────── --}}
    <g class="{{ $cls('client') }}">
        <rect class="ob-glyph" x="33" y="34" width="44" height="30" rx="3"/>
        <line class="ob-glyph" x1="55" y1="64" x2="55" y2="70"/>
        <line class="ob-glyph" x1="44" y1="70" x2="66" y2="70"/>
    </g>

    {{-- ── Réseau (cloud) ─────────────────────────────────────────── --}}
    <g class="{{ $cls('network') }}" transform="translate(166,22) scale(1.85)">
        <path class="ob-glyph" d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>
    </g>

    {{-- ── Serveur ────────────────────────────────────────────────── --}}
    <g class="{{ $cls('server') }}" transform="translate(303,26) scale(1.6)">
        <rect class="ob-glyph" x="2" y="2" width="20" height="8" rx="2"/>
        <rect class="ob-glyph" x="2" y="13" width="20" height="8" rx="2"/>
        <line class="ob-glyph" x1="6" y1="6" x2="6.01" y2="6"/>
        <line class="ob-glyph" x1="6" y1="17" x2="6.01" y2="17"/>
    </g>

    {{-- ── Status badges (✓ ok / ✕ fail) ──────────────────────────── --}}
    @foreach (['client' => 73, 'network' => 207, 'server' => 343] as $key => $bx)
        @php $fail = $key === $node; @endphp
        <g class="ob-badge {{ $fail ? 'is-fail' : 'is-ok' }}">
            <circle cx="{{ $bx }}" cy="30" r="9"/>
            @if ($fail)
                <path class="ob-badge-mark" d="M{{ $bx - 3 }} 27 l6 6 M{{ $bx + 3 }} 27 l-6 6"/>
            @else
                <path class="ob-badge-mark" d="M{{ $bx - 4 }} 30 l3 3 l5 -6"/>
            @endif
        </g>
    @endforeach

    {{-- ── Labels ─────────────────────────────────────────────────── --}}
    <text class="ob-node-label {{ $node === 'client' ? 'is-fail' : '' }}"  x="55"  y="92">{{ __('errors.diagram_client') }}</text>
    <text class="ob-node-label {{ $node === 'network' ? 'is-fail' : '' }}" x="190" y="92">{{ __('errors.diagram_network') }}</text>
    <text class="ob-node-label {{ $node === 'server' ? 'is-fail' : '' }}"  x="325" y="92">{{ __('errors.diagram_server') }}</text>
</svg>
