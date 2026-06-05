@if ($remplacements['count'] > 0)
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-exchange-alt"></i> Remplacements
        </div>
        <a class="ob-widget-card-link" href="{{ route('remplacement.index') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        <div class="ob-dash-alert-item-row">
            <div class="ob-dash-alert-item-info">
                <div class="ob-dash-alert-item-label">Remplacements de garde</div>
                <div class="ob-dash-alert-item-sub">{{ $remplacements['type'] }}</div>
            </div>
            <span class="ob-dash-alert-badge ob-dash-badge-warning">{{ $remplacements['count'] }}</span>
        </div>
    </div>
</div>
@endif
