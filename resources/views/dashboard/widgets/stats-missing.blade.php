@if (!empty($missingStats['rows']))
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-chart-bar"></i> Statistiques manquantes
        </div>
    </div>
    <div class="widget-card-body">
        @foreach ($missingStats['rows'] as $row)
            <div class="alert-item-row">
                <div class="alert-item-info">
                    <div class="alert-item-label">
                        <a href="{{ url('/legacy/evenement_display.php?evenement=' . $row->E_CODE . '&tab=8') }}"
                           style="color:inherit">{{ $row->E_LIBELLE }}</a>
                    </div>
                    <div class="alert-item-sub">
                        {{ $row->TE_LIBELLE }}
                        @if($row->E_LIEU) &mdash; {{ $row->E_LIEU }}@endif
                        &mdash; {{ $row->FORMDATE }}
                    </div>
                </div>
                <span class="alert-badge badge-warning">{{ $row->TERMINE_DEPUIS }} j</span>
            </div>
        @endforeach
    </div>
</div>
@endif
