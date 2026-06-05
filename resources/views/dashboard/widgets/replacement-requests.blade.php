@if ($replacementRequests['count'] > 0)
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-user-clock"></i> Demande de remplaçant
        </div>
        <a class="ob-widget-card-link"
           href="{{ url('/legacy/remplacements.php?filter=0&replaced=0&substitute=0') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        <div class="ob-dash-alert-item-row">
            <div class="ob-dash-alert-item-info">
                <div class="ob-dash-alert-item-label">Recherche de remplaçant</div>
                <div class="ob-dash-alert-item-sub">
                    En cours
                    @if ($replacementRequests['debut'] && $replacementRequests['fin'])
                        &mdash; du {{ $replacementRequests['debut'] }} au {{ $replacementRequests['fin'] }}
                    @endif
                </div>
            </div>
            <a href="{{ url('/legacy/remplacements.php?filter=0&replaced=0&substitute=0') }}"
               style="text-decoration:none">
                <span class="ob-dash-alert-badge" style="color:#8950fc">{{ $replacementRequests['count'] }}</span>
            </a>
        </div>
    </div>
</div>
@endif
