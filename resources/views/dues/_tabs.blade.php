{{--
    Shared tab navigation for all cotisations sub-pages.
    Usage: @include('dues._tabs')
--}}
<nav class="ob-subnav noprint" aria-label="{{ __('dues.tabs_aria_label') }}">
    <a class="ob-subnav-tab {{ request()->routeIs('dues.index') ? 'active' : '' }}"
       href="{{ route('dues.index', request()->only(['year','periode','section','subsections'])) }}">
        <i class="fas fa-euro-sign me-1"></i> {{ __('dues.tab_dues') }}
    </a>
    <a class="ob-subnav-tab {{ request()->routeIs('dues.direct-debits*') ? 'active' : '' }}"
       href="{{ route('dues.direct-debits', request()->only(['year','periode','section','subsections'])) }}">
        <i class="fas fa-receipt me-1"></i> {{ __('dues.tab_direct_debits') }}
    </a>
    <a class="ob-subnav-tab {{ request()->routeIs('dues.transfers') ? 'active' : '' }}"
       href="{{ route('dues.transfers', request()->only(['section','subsections'])) }}">
        <i class="fas fa-money-check me-1"></i> {{ __('dues.tab_transfers') }}
    </a>
</nav>
