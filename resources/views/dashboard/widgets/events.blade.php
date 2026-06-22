<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-calendar-check"></i> {{ __('dashboard.events.title') }}
        </div>
        <a class="ob-widget-card-link" href="{{ route('event.index') }}">
            {{ $events['sectionName'] }} <i class="fas fa-external-link-alt ms-1"></i>
        </a>
    </div>
    <div class="ob-widget-card-body"> {{-- i18n-ignore --}}
        @forelse ($events['events'] as $e)
            @php
                $openIcon = $e->E_CLOSED // i18n-ignore
                    ? '<i class="fas fa-lock ob-dash-event-closed" title="' . e(__('dashboard.events.closed_title')) . '"></i>' // i18n-ignore
                    : '<i class="fas fa-unlock ob-dash-event-open" title="' . e(__('dashboard.events.open_title')) . '"></i>'; // i18n-ignore
                $sess = $e->EH_ID > 1 ? ' ' . __('dashboard.events.session_prefix') . $e->EH_ID : ''; // i18n-ignore
            @endphp
            <div class="ob-dash-event-row">
                <div class="ob-dash-event-status">{!! $openIcon !!}</div>
                <div class="ob-dash-event-info">
                    <a class="ob-dash-event-title" href="{{ route('event.show', $e->E_CODE) }}">
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
            <p class="ob-widget-empty">{{ __('dashboard.events.empty') }}</p>
        @endforelse
    </div>
</div>