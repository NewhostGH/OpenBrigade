{{--
    Shared tab navigation for all cotisations sub-pages.
    Usage: @include('cotisations._tabs')
--}}
<nav class="ob-subnav noprint" aria-label="Onglets cotisations">
    <a class="ob-subnav-tab {{ request()->routeIs('cotisations.index') ? 'active' : '' }}"
       href="{{ route('cotisations.index', request()->only(['year','periode','section','subsections'])) }}">
        <i class="fas fa-euro-sign me-1"></i> Cotisations
    </a>
    <a class="ob-subnav-tab {{ request()->routeIs('cotisations.prelevements*') ? 'active' : '' }}"
       href="{{ route('cotisations.prelevements', request()->only(['year','periode','section','subsections'])) }}">
        <i class="fas fa-receipt me-1"></i> Prélèvements
    </a>
    <a class="ob-subnav-tab {{ request()->routeIs('cotisations.virements') ? 'active' : '' }}"
       href="{{ route('cotisations.virements', request()->only(['section','subsections'])) }}">
        <i class="fas fa-money-check me-1"></i> Virements
    </a>
</nav>
