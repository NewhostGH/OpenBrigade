@if (!empty($consumables['items']))
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-boxes"></i> {{ __('dashboard.consumables.title') }}
        </div>
        <a class="ob-widget-card-link" href="{{ route('consumable.index') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        @foreach ($consumables['items'] as $item)
            <div class="ob-dash-alert-item-row">
                <div class="ob-dash-alert-item-info">
                    <div class="ob-dash-alert-item-label">{{ $item['label'] }}</div>
                    @if ($item['sub'])
                        <div class="ob-dash-alert-item-sub">{{ $item['sub'] }}</div>
                    @endif
                </div>
                <span class="ob-dash-alert-badge ob-dash-badge-{{ $item['level'] }}">{{ $item['count'] }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif
