{{--
    Shared tab navigation for all cotisations sub-pages.
    Usage: @include('dues._tabs')
--}}
<nav class="ob-subnav noprint" aria-label="Onglets cotisations">
    <a class="ob-subnav-tab {{ request()->routeIs('dues.index') ? 'active' : '' }}"
       href="{{ route('dues.index', request()->only(['year','periode','section','subsections'])) }}">
        <i class="fas fa-euro-sign me-1"></i> Cotisations
    </a>
    <a class="ob-subnav-tab {{ request()->routeIs('dues.direct-debits*') ? 'active' : '' }}"
       href="{{ route('dues.direct-debits', request()->only(['year','periode','section','subsections'])) }}">
        <i class="fas fa-receipt me-1"></i> Prélèvements
    </a>
    <a class="ob-subnav-tab {{ request()->routeIs('dues.transfers') ? 'active' : '' }}"
       href="{{ route('dues.transfers', request()->only(['section','subsections'])) }}">
        <i class="fas fa-money-check me-1"></i> Virements
    </a>
</nav>
