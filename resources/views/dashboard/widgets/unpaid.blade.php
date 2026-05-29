@if (!empty($unpaidActivities['rows']))
<div class="widget-card">
    <div class="widget-card-header">
        <div class="widget-card-title">
            <i class="fas fa-file-invoice-dollar"></i>
            <a href="{{ url('/legacy/export.php?filter=0&subsections=1&exp=1tnonpaye&type_event=ALL&affichage=ecran&show=1') }}"
               style="color:inherit;text-decoration:none;">Activité non réglée</a>
        </div>
        <a class="widget-card-link"
           href="{{ url('/legacy/export.php?filter=0&subsections=1&exp=1tnonpaye&type_event=ALL&affichage=ecran&show=1') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    </div>
    <div class="widget-card-body">
        @foreach ($unpaidActivities['rows'] as $row)
            @php
                $montant = $row->facture_montant ?: ($row->devis_montant ?: 0);
                if ($row->relance_date) {
                    $badge = '<span class="alert-badge badge-info">Relancé</span>';
                } elseif ($row->facture_date) {
                    $badge = '<span class="alert-badge badge-warning">Facturé</span>';
                } else {
                    $badge = '<span class="alert-badge badge-danger">À facturer</span>';
                }
            @endphp
            <div class="alert-item-row">
                <div class="alert-item-info">
                    <div class="alert-item-label">
                        <a href="{{ url('/legacy/evenement_facturation.php?evenement=' . $row->E_CODE) }}"
                           style="color:inherit">{{ $row->E_LIBELLE }}</a>
                    </div>
                    <div class="alert-item-sub">{{ $row->FORMDATE }} &mdash; {{ number_format($montant, 2) }} €</div>
                </div>
                {!! $badge !!}
            </div>
        @endforeach
    </div>
</div>
@endif
