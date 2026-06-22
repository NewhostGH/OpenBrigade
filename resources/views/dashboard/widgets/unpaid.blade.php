@if (!empty($unpaidActivities['rows']))
<div class="ob-widget-card">
    <div class="ob-widget-card-header">
        <div class="ob-widget-card-title">
            <i class="fas fa-file-invoice-dollar"></i>
            {{-- TODO: Migrate code --}}
            <a href="{{ url('/legacy/export.php?filter=0&subsections=1&exp=1tnonpaye&type_event=ALL&affichage=ecran&show=1') }}"
               style="color:inherit;text-decoration:none;">{{ __('dashboard.unpaid.title') }}</a>
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
                $montant = $row->facture_montant ?: ($row->devis_montant ?: 0); // i18n-ignore
                if ($row->relance_date) { // i18n-ignore
                    $badge = '<span class="ob-dash-alert-badge ob-dash-badge-info">' . e(__('dashboard.unpaid.badge_relance')) . '</span>'; // i18n-ignore
                } elseif ($row->facture_date) { // i18n-ignore
                    $badge = '<span class="ob-dash-alert-badge ob-dash-badge-warning">' . e(__('dashboard.unpaid.badge_facture')) . '</span>'; // i18n-ignore
                } else { // i18n-ignore
                    $badge = '<span class="ob-dash-alert-badge ob-dash-badge-danger">' . e(__('dashboard.unpaid.badge_to_bill')) . '</span>'; // i18n-ignore
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
