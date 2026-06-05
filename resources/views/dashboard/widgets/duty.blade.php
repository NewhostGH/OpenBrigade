<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-shield-alt"></i> Service / Astreinte
        </div>
    </div>
    <div class="ob-widget-card-body">
        @forelse ($duty['duty'] as $p)
            <div class="ob-duty-row">
                <a href="{{ route('personnel.show', $p->P_ID) }}">
                    <img src="{{ $p->avatarSrc }}" class="ob-duty-avatar"
                         onerror="this.src='{{ asset('images/autre.png') }}'">
                </a>
                <div class="ob-duty-info">
                    <div class="ob-duty-name">
                        <a href="{{ route('personnel.show', $p->P_ID) }}"
                           style="color:inherit;text-decoration:none;">
                            {{ ucfirst(strtolower($p->P_PRENOM)) }} {{ strtoupper($p->P_NOM) }}
                        </a>
                    </div>
                    <div class="ob-duty-role">{{ $p->GP_DESCRIPTION }} &mdash; {{ $p->S_DESCRIPTION }}</div>
                </div>
                @if (!empty($p->P_PHONE))
                    <a class="ob-duty-phone" href="tel:{{ preg_replace('/\s/', '', $p->P_PHONE) }}">
                        {{ $p->P_PHONE }}
                    </a>
                @endif
            </div>
        @empty
            <p class="ob-widget-empty">Aucun personnel de service.</p>
        @endforelse
    </div>
</div>
