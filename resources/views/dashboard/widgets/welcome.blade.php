<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-user"></i> Mon profil
        </div>
        <a class="ob-widget-card-link" href="{{ route('personnel.show', $welcome['user']->P_ID) }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        <div class="d-flex gap-3 align-items-center">
            <a href="{{ route('personnel.show', $welcome['user']->P_ID) }}">
                <img src="{{ $welcome['avatarSrc'] }}"
                     class="ob-dash-welcome-avatar"
                     onerror="this.src='{{ $welcome['avatarFallback'] }}'">
            </a>
            <div class="ob-dash-welcome-info">
                <p class="ob-dash-welcome-name">
                    <a href="{{ route('personnel.show', $welcome['user']->P_ID) }}" style="color:inherit;text-decoration:none;">
                        {{ ucfirst(strtolower($welcome['user']->P_PRENOM ?? '')) }}
                        {{ strtoupper($welcome['user']->P_NOM ?? '') }}
                    </a>
                </p>
                <p class="ob-dash-welcome-meta">Nº {{ $welcome['user']->P_ID }}</p>
                @if ($welcome['section'])
                    <p class="ob-dash-welcome-meta">{{ $welcome['section']->S_DESCRIPTION }}</p>
                @endif
                <p class="ob-dash-welcome-date">
                    {{ ucfirst(\Carbon\Carbon::now()->locale('fr_FR')->isoFormat('dddd D MMMM YYYY')) }}
                    &mdash; Semaine {{ date('W') }}
                </p>
            </div>
        </div>

        @if (!empty($welcome['missingFields']))
            <div class="ob-dash-missing-fields mt-2">
                <div class="ob-dash-missing-fields-title">
                    <i class="fas fa-exclamation-triangle"></i> Fiche incomplète
                </div>
                @foreach ($welcome['missingFields'] as $field)
                    <span class="ob-dash-missing-field-tag">{{ $field }}</span>
                @endforeach
                <div style="margin-top:6px">
                    <a href="{{ route('personnel.show', $welcome['user']->P_ID) }}" style="font-size:var(--font-size-xs)">
                        Compléter ma fiche &rarr;
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
