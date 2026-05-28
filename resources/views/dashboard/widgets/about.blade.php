<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-info-circle"></i> À propos
        </div>
        <a class="widget-card-link" href="{{ route('about') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        <a class="about-row" href="{{ config('brigade.wiki_url', 'https://docs.ebrigade.app') }}" target="_blank" rel="noopener">
            <div class="about-icon stat-icon-blue">
                <i class="fas fa-book"></i>
            </div>
            <span class="about-text">Documentation en ligne</span>
            <i class="fas fa-arrow-right about-arrow"></i>
        </a>

        <a class="about-row" href="{{ config('brigade.community_url', 'https://community.ebrigade.app') }}" target="_blank" rel="noopener">
            <div class="about-icon" style="background:rgba(142,68,173,0.12);color:#76448a;">
                <i class="fas fa-hands-helping"></i>
            </div>
            <span class="about-text">Communauté eBrigade</span>
            <i class="fas fa-arrow-right about-arrow"></i>
        </a>

        @if (!empty($about['supportEmail']))
        <a class="about-row" href="mailto:{{ $about['supportEmail'] }}">
            <div class="about-icon stat-icon-green">
                <i class="fas fa-envelope"></i>
            </div>
            <span class="about-text">Support – {{ $about['supportEmail'] }}</span>
            <i class="fas fa-arrow-right about-arrow"></i>
        </a>
        @endif

        <a class="about-row" href="{{ $about['canAdmin'] ? url('/legacy/configuration.php?tab=conf7') : route('about') }}">
            <div class="about-icon stat-icon-orange">
                <i class="fas fa-info"></i>
            </div>
            <span class="about-text">
                {{ config('app.name') }} &mdash; version <strong>{{ $about['version'] }}</strong>
            </span>
            <i class="fas fa-arrow-right about-arrow"></i>
        </a>
    </div>
</div>
