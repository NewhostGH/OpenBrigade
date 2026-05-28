@if (!empty($consumables['items']))
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-boxes"></i> Consommables
        </div>
        <a class="widget-card-link" href="{{ url('/legacy/consommable.php?page=1') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @foreach ($consumables['items'] as $item)
            <div class="alert-item-row">
                <div class="alert-item-info">
                    <div class="alert-item-label">{{ $item['label'] }}</div>
                    @if ($item['sub'])
                        <div class="alert-item-sub">{{ $item['sub'] }}</div>
                    @endif
                </div>
                <span class="alert-badge badge-{{ $item['level'] }}">{{ $item['count'] }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif
