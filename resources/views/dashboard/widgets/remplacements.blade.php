@if ($remplacements['count'] > 0)
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-exchange-alt"></i> Remplacements
        </div>
        <a class="widget-card-link" href="{{ url('/legacy/remplacements.php') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        <div class="alert-item-row">
            <div class="alert-item-info">
                <div class="alert-item-label">Remplacements de garde</div>
                <div class="alert-item-sub">{{ $remplacements['type'] }}</div>
            </div>
            <span class="alert-badge badge-warning">{{ $remplacements['count'] }}</span>
        </div>
    </div>
</div>
@endif
