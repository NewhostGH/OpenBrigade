@if ($replacementRequests['count'] > 0)
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-user-clock"></i> Demande de remplaçant
        </div>
        <a class="widget-card-link"
           href="{{ url('/legacy/remplacements.php?filter=0&replaced=0&substitute=0') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        <div class="alert-item-row">
            <div class="alert-item-info">
                <div class="alert-item-label">Recherche de remplaçant</div>
                <div class="alert-item-sub">
                    En cours
                    @if ($replacementRequests['debut'] && $replacementRequests['fin'])
                        &mdash; du {{ $replacementRequests['debut'] }} au {{ $replacementRequests['fin'] }}
                    @endif
                </div>
            </div>
            <a href="{{ url('/legacy/remplacements.php?filter=0&replaced=0&substitute=0') }}"
               style="text-decoration:none">
                <span class="alert-badge" style="color:#8950fc">{{ $replacementRequests['count'] }}</span>
            </a>
        </div>
    </div>
</div>
@endif
