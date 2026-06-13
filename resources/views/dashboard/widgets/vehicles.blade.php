@if (!empty($vehicles['items']))
    <div class="ob-widget-card">
        <div class="ob-widget-card-header">
            <div class="ob-widget-card-title">
                <i class="fas fa-truck"></i> Véhicules
            </div>
            <a class="ob-widget-card-link" href="{{ route('vehicle.index') }}">
                <i class="fas fa-external-link-alt"></i>
            </a>
        </div>
        <div class="ob-widget-card-body">
            @foreach ($vehicles['items'] as $item)
                <a class="ob-dash-alert-item-row" href="{{ url($item['url']) }}">
                    <div class="ob-dash-alert-item-info">
                        <div class="ob-dash-alert-item-label">{{ $item['label'] }}</div>
                        @if ($item['sub'])
                            <div class="ob-dash-alert-item-sub">{{ $item['sub'] }}</div>
                        @endif
                    </div>
                    <span class="ob-dash-alert-badge ob-dash-badge-{{ $item['level'] }}">{{ $item['count'] }}</span>
                    <i class="fas fa-chevron-right" style="font-size:10px;color:var(--text-muted-soft)"></i>
                </a>
            @endforeach
        </div>
    </div>
@endif