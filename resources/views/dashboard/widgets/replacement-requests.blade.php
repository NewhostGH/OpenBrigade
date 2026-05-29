@if ($replacementRequests['count'] > 0)
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-user-clock"></i> Demande de remplaçant
        </div>
        <a class="widget-card-link"
           href="{{ url('/legacy/remplacements.php?filter=0&replaced=0&substitute=0') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @foreach ($replacementRequests['rows'] as $row)
            <div class="alert-item-row">
                <div class="alert-item-info">
                    <div class="alert-item-label">
                        <a href="{{ url('/legacy/evenement_display.php?evenement=' . $row->E_CODE) }}"
                           style="color:inherit">{{ $row->E_LIBELLE }}</a>
                    </div>
                    <div class="alert-item-sub">
                        {{ $row->P_PRENOM }} {{ $row->P_NOM }}
                        &mdash; {{ $row->FORMDATE }}
                    </div>
                </div>
                <span class="alert-badge badge-warning">Sans remplaçant</span>
            </div>
        @endforeach

        @if ($replacementRequests['count'] > count($replacementRequests['rows']))
            <div class="text-muted text-center" style="font-size:var(--font-size-xs);padding:4px 0">
                + {{ $replacementRequests['count'] - count($replacementRequests['rows']) }} autre(s)
                <a href="{{ url('/legacy/remplacements.php?filter=0&replaced=0&substitute=0') }}">Voir tout</a>
            </div>
        @endif
    </div>
</div>
@endif
