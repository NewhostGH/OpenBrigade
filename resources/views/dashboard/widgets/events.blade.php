<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-calendar-check"></i> Activités à venir
        </div>
        <a class="widget-card-link"
           href="{{ url('/legacy/evenement_choice.php?ec_mode=default&page=1&filter=' . $events['sectionId']) }}">
            {{ $events['sectionName'] }} <i class="fas fa-external-link-alt ms-1"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @forelse ($events['events'] as $e)
            @php
                $openIcon = $e->E_CLOSED
                    ? '<i class="fas fa-lock event-closed" title="Inscriptions fermées"></i>'
                    : '<i class="fas fa-unlock event-open" title="Inscriptions ouvertes"></i>';
                $sess = $e->EH_ID > 1 ? ' – session n°' . $e->EH_ID : '';
            @endphp
            <div class="event-row">
                <div class="event-status">{!! $openIcon !!}</div>
                <div class="event-info">
                    <a class="event-title"
                       href="{{ url('/legacy/evenement_display.php?evenement=' . $e->E_CODE . '&from=scroller') }}">
                        {{ $e->E_LIBELLE }}{{ $sess }}
                    </a>
                    <div class="event-meta">{{ $e->TE_LIBELLE }}@if($e->E_LIEU) &mdash; {{ $e->E_LIEU }}@endif</div>
                </div>
                <div class="event-date">
                    {{ $e->FORMDATE1 }}<br>
                    <span>{{ $e->DEBUTDATE }}–{{ $e->FINDATE }}</span>
                </div>
            </div>
        @empty
            <p class="widget-empty">Aucune activité prévue.</p>
        @endforelse
    </div>
</div>
