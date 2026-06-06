<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-calendar-user"></i> Mes activités
        </div>
        <a class="ob-widget-card-link"
           href="{{ route('personnel.show', auth()->id()) }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        @forelse ($myActivities['events'] as $e)
            @php
                $sess = ($e->EH_ID ?? 1) > 1 ? ' – session n°' . $e->EH_ID : '';
            @endphp
            <div class="ob-dash-event-row">
                <div class="ob-dash-event-status">
                    @if (!empty($e->EP_ASTREINTE))
                        <i class="fas fa-star ob-dash-event-open" title="Astreinte" style="color:var(--accent)"></i>
                    @elseif ($e->E_CLOSED)
                        <i class="fas fa-lock ob-dash-event-closed" title="Inscriptions fermées"></i>
                    @else
                        <i class="fas fa-check-circle" style="color:var(--color-success-icon)" title="Inscrit"></i>
                    @endif
                </div>
                <div class="ob-dash-event-info">
                    <a class="ob-dash-event-title"
                       href="{{ route('evenement.show', $e->E_CODE) }}">
                        {{ $e->E_LIBELLE }}{{ $sess }}
                    </a>
                    <div class="ob-dash-event-meta">
                        {{ $e->TE_LIBELLE }}@if($e->E_LIEU) &mdash; {{ $e->E_LIEU }}@endif
                    </div>
                </div>
                <div class="ob-dash-event-date">
                    {{ $e->FORMDATE }}<br>
                    <span>{{ $e->DEBUTDATE }}–{{ $e->FINDATE }}</span>
                </div>
            </div>
        @empty
            <p class="ob-widget-empty">Aucune participation prévue.</p>
        @endforelse
    </div>
</div>
