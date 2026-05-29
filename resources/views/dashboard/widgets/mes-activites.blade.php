<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-calendar-user"></i> Mes activités
        </div>
        <a class="widget-card-link"
           href="{{ url('/legacy/upd_personnel.php?self=1&from=default&tab=4&type_evenement=ALL') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @forelse ($myActivities['events'] as $e)
            @php
                $sess = ($e->EH_ID ?? 1) > 1 ? ' – session n°' . $e->EH_ID : '';
            @endphp
            <div class="event-row">
                <div class="event-status">
                    @if (!empty($e->EP_ASTREINTE))
                        <i class="fas fa-star event-open" title="Astreinte" style="color:var(--accent)"></i>
                    @elseif ($e->E_CLOSED)
                        <i class="fas fa-lock event-closed" title="Inscriptions fermées"></i>
                    @else
                        <i class="fas fa-check-circle" style="color:#16a34a" title="Inscrit"></i>
                    @endif
                </div>
                <div class="event-info">
                    <a class="event-title"
                       href="{{ url('/legacy/evenement_display.php?evenement=' . $e->E_CODE . '&from=scroller') }}">
                        {{ $e->E_LIBELLE }}{{ $sess }}
                    </a>
                    <div class="event-meta">
                        {{ $e->TE_LIBELLE }}@if($e->E_LIEU) &mdash; {{ $e->E_LIEU }}@endif
                    </div>
                </div>
                <div class="event-date">
                    {{ $e->FORMDATE }}<br>
                    <span>{{ $e->DEBUTDATE }}–{{ $e->FINDATE }}</span>
                </div>
            </div>
        @empty
            <p class="widget-empty">Aucune participation prévue.</p>
        @endforelse
    </div>
</div>
