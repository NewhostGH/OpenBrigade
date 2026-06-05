@if (!empty($missingStats['rows']))
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-chart-bar"></i> Statistiques manquantes
        </div>
    </div>
    <div class="ob-widget-card-body">
        @foreach ($missingStats['rows'] as $row)
            <div class="ob-dash-alert-item-row">
                <div class="ob-dash-alert-item-info">
                    <div class="ob-dash-alert-item-label">
                        <a href="{{ route('evenement.show', $row->E_CODE) }}"
                           style="color:inherit">{{ $row->E_LIBELLE }}</a>
                    </div>
                    <div class="ob-dash-alert-item-sub">
                        {{ $row->TE_LIBELLE }}
                        @if($row->E_LIEU) &mdash; {{ $row->E_LIEU }}@endif
                        &mdash; {{ $row->FORMDATE }}
                    </div>
                </div>
                <span class="ob-dash-alert-badge ob-dash-badge-warning">{{ $row->TERMINE_DEPUIS }} j</span>
            </div>
        @endforeach
    </div>
</div>
@endif
