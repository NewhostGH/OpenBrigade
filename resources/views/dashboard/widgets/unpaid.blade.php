@if (!empty($unpaidActivities['rows']))
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-file-invoice-dollar"></i>
            {{-- TODO: Migrate code --}}
            <a href="{{ url('/legacy/export.php?filter=0&subsections=1&exp=1tnonpaye&type_event=ALL&affichage=ecran&show=1') }}"
               style="color:inherit;text-decoration:none;">Activité non réglée</a>
        </div>
        <a class="ob-widget-card-link"
           {{-- TODO: Migrate code --}}
           href="{{ url('/legacy/export.php?filter=0&subsections=1&exp=1tnonpaye&type_event=ALL&affichage=ecran&show=1') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="ob-widget-card-body">
        @foreach ($unpaidActivities['rows'] as $row)
            @php
                $montant = $row->facture_montant ?: ($row->devis_montant ?: 0);
                if ($row->relance_date) {
                    $badge = '<span class="ob-dash-alert-badge ob-dash-badge-info">Relancé</span>';
                } elseif ($row->facture_date) {
                    $badge = '<span class="ob-dash-alert-badge ob-dash-badge-warning">Facturé</span>';
                } else {
                    $badge = '<span class="ob-dash-alert-badge ob-dash-badge-danger">À facturer</span>';
                }
            @endphp
            <div class="ob-dash-alert-item-row">
                <div class="ob-dash-alert-item-info">
                    <div class="ob-dash-alert-item-label">
                        {{-- TODO: Migrate code --}}
                        <a href="{{ url('/legacy/evenement_facturation.php?evenement=' . $row->E_CODE) }}"
                           style="color:inherit">{{ $row->E_LIBELLE }}</a>
                    </div>
                    <div class="ob-dash-alert-item-sub">{{ $row->FORMDATE }} &mdash; {{ number_format($montant, 2) }} €</div>
                </div>
                {!! $badge !!}
            </div>
        @endforeach
    </div>
</div>
@endif
