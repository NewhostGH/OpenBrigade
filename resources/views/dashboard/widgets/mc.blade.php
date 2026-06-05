<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-ambulance"></i> Mains courantes
        </div>
        {{-- TODO: Migrate code — ec_mode=MC filter has no native equivalent yet --}}
        <a class="ob-widget-card-link" href="{{ url('/legacy/evenement_choice.php?ec_mode=MC&page=1') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        @forelse ($mc['events'] as $e)
            <div class="ob-dash-event-row">
                <div class="ob-dash-event-status">
                    <i class="fas fa-circle ob-dash-event-open" style="font-size:8px;margin-top:5px;"></i>
                </div>
                <div class="ob-dash-event-info">
                    <a class="ob-dash-event-title"
                       href="{{ route('evenement.show', $e->E_CODE) }}">
                        {{ $e->E_LIBELLE }}
                    </a>
                    <div class="ob-dash-event-meta">{{ $e->DEBUTDATE }}–{{ $e->FINDATE }}</div>
                </div>
                <div class="ob-dash-event-date">{{ $e->FORMDATE }}</div>
            </div>
        @empty
            <p class="ob-widget-empty">Aucune main courante en cours.</p>
        @endforelse
    </div>
</div>
