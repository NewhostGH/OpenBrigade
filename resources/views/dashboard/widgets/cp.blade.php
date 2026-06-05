@if ($cp['count'] > 0)
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-umbrella-beach"></i> Congés à valider
        </div>
        <a class="ob-widget-card-link" href="{{ url('/legacy/indispo_choice.php?tab=2&page=1') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        @foreach ($cp['items'] as $item)
            <a class="ob-dash-alert-item-row"
               href="{{ url('/legacy/indispo_choice.php?tab=2&validation=ATT&person=ALL') }}">
                <div class="ob-dash-alert-item-info">
                    <div class="ob-dash-alert-item-label">
                        {{ ucfirst(strtolower($item->P_PRENOM)) }} {{ strtoupper($item->P_NOM) }}
                    </div>
                    <div class="ob-dash-alert-item-sub">
                        {{ $item->TI_LIBELLE }} &mdash; {{ $item->I_DEBUT }} au {{ $item->I_FIN }}
                    </div>
                </div>
                <span class="ob-dash-day-label ob-dash-day-label-orange">À valider</span>
            </a>
        @endforeach
    </div>
</div>
@endif
