<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-ambulance"></i> Mains courantes
        </div>
        <a class="widget-card-link" href="{{ url('/legacy/evenement_choice.php?ec_mode=MC&page=1') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @forelse ($mc['events'] as $e)
            <div class="event-row">
                <div class="event-status">
                    <i class="fas fa-circle event-open" style="font-size:8px;margin-top:5px;"></i>
                </div>
                <div class="event-info">
                    <a class="event-title"
                       href="{{ url('/legacy/evenement_display.php?evenement=' . $e->E_CODE . '&from=default&tab=1') }}">
                        {{ $e->E_LIBELLE }}
                    </a>
                    <div class="event-meta">{{ $e->DEBUTDATE }}–{{ $e->FINDATE }}</div>
                </div>
                <div class="event-date">{{ $e->FORMDATE }}</div>
            </div>
        @empty
            <p class="widget-empty">Aucune main courante en cours.</p>
        @endforelse
    </div>
</div>
