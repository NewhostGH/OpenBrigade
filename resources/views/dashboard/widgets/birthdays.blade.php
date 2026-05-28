@php $hasBirthdays = collect($birthdays['days'])->contains(fn($d) => !empty($d['rows'])); @endphp
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-birthday-cake"></i> Ma section
        </div>
    </div>
    <div class="widget-card-body">

        {{-- Anniversaires --}}
        @if ($hasBirthdays)
            <p style="font-size:var(--font-size-xs);font-weight:600;color:var(--text-muted-soft);margin-bottom:6px;">
                <i class="fas fa-birthday-cake me-1"></i> Anniversaires à souhaiter
            </p>
            @foreach ($birthdays['days'] as $day)
                @foreach ($day['rows'] as $p)
                    <div class="birthday-row">
                        <img src="{{ $p->avatarSrc }}" class="birthday-avatar"
                             onerror="this.src='{{ asset('images/autre.png') }}'">
                        <div class="birthday-name">
                            {{ ucfirst(strtolower($p->P_PRENOM)) }} {{ strtoupper($p->P_NOM) }}
                        </div>
                        <span class="day-label day-label-{{ $day['color'] }}">
                            {{ $day['label'] }}
                        </span>
                    </div>
                @endforeach
            @endforeach
        @else
            <p class="widget-empty">Aucun anniversaire dans les 3 prochains jours.</p>
        @endif

        {{-- WhatsApp groups --}}
        @if (!empty($sectionLinks['links']))
            <p style="font-size:var(--font-size-xs);font-weight:600;color:var(--text-muted-soft);margin:12px 0 6px;">
                <i class="fab fa-whatsapp me-1" style="color:#25d366;"></i> Mes groupes WhatsApp
            </p>
            @foreach ($sectionLinks['links'] as $link)
                <div class="about-row" style="padding:5px 0;">
                    <div class="about-icon" style="background:rgba(37,211,102,0.12);color:#1da851;">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <span class="about-text">{{ $link['label'] }}</span>
                    <a href="{{ $sectionLinks['whatsappBase'] }}/{{ $link['whatsapp'] }}"
                       target="_blank" rel="noopener" title="Rejoindre le groupe WhatsApp">
                        <i class="fas fa-arrow-right about-arrow"></i>
                    </a>
                </div>
            @endforeach
        @endif

    </div>
</div>
