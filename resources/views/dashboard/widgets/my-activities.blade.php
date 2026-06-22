<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-calendar"></i> {{ __('dashboard.my_activities.title') }}
        </div>
        <a class="ob-widget-card-link"
           href="{{ route('personnel.show', auth()->id()) }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body"> {{-- i18n-ignore --}}
        @forelse ($myActivities['events'] as $e)
            @php
                $sess = ($e->EH_ID ?? 1) > 1 ? ' ' . __('dashboard.my_activities.session_prefix') . $e->EH_ID : ''; // i18n-ignore
            @endphp
            <div class="ob-dash-event-row">
                <div class="ob-dash-event-status">
                    @if (!empty($e->EP_ASTREINTE))
                        <i class="fas fa-star ob-dash-event-open" title="{{ __('dashboard.my_activities.astreinte_title') }}" style="color:var(--accent)"></i>
                    @elseif ($e->E_CLOSED)
                        <i class="fas fa-lock ob-dash-event-closed" title="{{ __('dashboard.my_activities.closed_title') }}"></i>
                    @else
                        <i class="fas fa-check-circle" style="color:var(--color-success-icon)" title="{{ __('dashboard.my_activities.registered_title') }}"></i>
                    @endif
                </div>
                <div class="ob-dash-event-info">
                    <a class="ob-dash-event-title"
                       href="{{ route('event.show', $e->E_CODE) }}">
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
            <p class="ob-widget-empty">{{ __('dashboard.my_activities.empty') }}</p>
        @endforelse
    </div>
</div>
