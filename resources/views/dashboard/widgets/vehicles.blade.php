@if (!empty($vehicles['items']))
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-truck"></i> Véhicules
        </div>
        <a class="widget-card-link" href="{{ url('/legacy/vehicule.php?page=1') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @foreach ($vehicles['items'] as $item)
            <a class="alert-item-row" href="{{ url($item['url']) }}">
                <div class="alert-item-info">
                    <div class="alert-item-label">{{ $item['label'] }}</div>
                    @if ($item['sub'])
                        <div class="alert-item-sub">{{ $item['sub'] }}</div>
                    @endif
                </div>
                <span class="alert-badge badge-{{ $item['level'] }}">{{ $item['count'] }}</span>
                <i class="fas fa-chevron-right" style="font-size:10px;color:var(--text-muted-soft)"></i>
            </a>
        @endforeach
    </div>
</div>
@endif
