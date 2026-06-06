<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-info-circle"></i> À propos
        </div>
        <a class="ob-widget-card-link" href="{{ route('about') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        <a class="ob-dash-about-row" href="{{ config('brigade.wiki_url', 'https://docs.ebrigade.app') }}" target="_blank" rel="noopener">
            <div class="ob-dash-about-icon ob-dash-stat-icon-blue">
                <i class="fas fa-book"></i>
            </div>
            <span class="ob-dash-about-text">Documentation en ligne</span>
            <i class="fas fa-arrow-right ob-dash-about-arrow"></i>
        </a>

        <a class="ob-dash-about-row" href="{{ config('brigade.community_url', 'https://community.ebrigade.app') }}" target="_blank" rel="noopener">
            <div class="ob-dash-about-icon" style="background:var(--color-purple-bg);color:var(--color-purple);">
                <i class="fas fa-hands-helping"></i>
            </div>
            <span class="ob-dash-about-text">Communauté eBrigade</span>
            <i class="fas fa-arrow-right ob-dash-about-arrow"></i>
        </a>

        @if (!empty($about['supportEmail']))
        <a class="ob-dash-about-row" href="mailto:{{ $about['supportEmail'] }}">
            <div class="ob-dash-about-icon ob-dash-stat-icon-green">
                <i class="fas fa-envelope"></i>
            </div>
            <span class="ob-dash-about-text">Support – {{ $about['supportEmail'] }}</span>
            <i class="fas fa-arrow-right ob-dash-about-arrow"></i>
        </a>
        @endif

        {{-- TODO: Migrate code --}}
        <a class="ob-dash-about-row" href="{{ $about['canAdmin'] ? url('/legacy/configuration.php?tab=conf7') : route('about') }}">
            <div class="ob-dash-about-icon ob-dash-stat-icon-orange">
                <i class="fas fa-info"></i>
            </div>
            <span class="ob-dash-about-text">
                {{ config('app.name') }} &mdash; version <strong>{{ $about['version'] }}</strong>
            </span>
            <i class="fas fa-arrow-right ob-dash-about-arrow"></i>
        </a>
    </div>
</div>
