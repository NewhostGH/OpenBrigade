<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-calendar-check"></i> Activités à venir
        </div>
        <a class="ob-widget-card-link"
           href="{{ url('/legacy/evenement_choice.php?ec_mode=default&page=1&filter=' . $events['sectionId']) }}">
            {{ $events['sectionName'] }} <i class="fas fa-external-link-alt ms-1"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        @forelse ($events['events'] as $e)
            @php
                $openIcon = $e->E_CLOSED
                    ? '<i class="fas fa-lock ob-dash-event-closed" title="Inscriptions fermées"></i>'
                    : '<i class="fas fa-unlock ob-dash-event-open" title="Inscriptions ouvertes"></i>';
                $sess = $e->EH_ID > 1 ? ' – session n°' . $e->EH_ID : '';
            @endphp
            <div class="ob-dash-event-row">
                <div class="ob-dash-event-status">{!! $openIcon !!}</div>
                <div class="ob-dash-event-info">
                    <a class="ob-dash-event-title"
                       href="{{ url('/legacy/evenement_display.php?evenement=' . $e->E_CODE . '&from=scroller') }}">
                        {{ $e->E_LIBELLE }}{{ $sess }}
                    </a>
                    <div class="ob-dash-event-meta">{{ $e->TE_LIBELLE }}@if($e->E_LIEU) &mdash; {{ $e->E_LIEU }}@endif</div>
                </div>
                <div class="ob-dash-event-date">
                    {{ $e->FORMDATE1 }}<br>
                    <span>{{ $e->DEBUTDATE }}–{{ $e->FINDATE }}</span>
                </div>
            </div>
        @empty
            <p class="ob-widget-empty">Aucune activité prévue.</p>
        @endforelse
    </div>
</div>
