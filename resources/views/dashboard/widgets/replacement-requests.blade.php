@if ($replacementRequests['count'] > 0)
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-user-clock"></i> {{ __('dashboard.replacement_requests.title') }}
        </div>
        <a class="ob-widget-card-link"
           href="{{ route('replacement.index') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        <div class="ob-dash-alert-item-row">
            <div class="ob-dash-alert-item-info">
                <div class="ob-dash-alert-item-label">{{ __('dashboard.replacement_requests.search_label') }}</div>
                <div class="ob-dash-alert-item-sub">
                    {{ __('dashboard.replacement_requests.in_progress') }}
                    @if ($replacementRequests['debut'] && $replacementRequests['fin'])
                        {{ __('dashboard.replacement_requests.date_range', ['debut' => $replacementRequests['debut'], 'fin' => $replacementRequests['fin']]) }}
                    @endif
                </div>
            </div>
            <a href="{{ route('replacement.index') }}"
               style="text-decoration:none">
                <span class="ob-dash-alert-badge" style="color:var(--color-purple-dark)">{{ $replacementRequests['count'] }}</span>
            </a>
        </div>
    </div>
</div>
@endif
