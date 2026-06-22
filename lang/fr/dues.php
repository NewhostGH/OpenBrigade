<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cotisations
    |--------------------------------------------------------------------------
    */

    // Onglets
    'tab_dues' => 'Cotisations',
    'tab_direct_debits' => 'Prélèvements',
    'tab_transfers' => 'Virements',

    // Titres / breadcrumb
    'title_dues' => 'Cotisations',
    'title_direct_debits' => 'Prélèvements',
    'title_transfers' => 'Virements',

    // Filtres
    'all_modes' => 'Tous modes',
    'show_all' => 'Tout afficher',
    'not_paid' => 'Pas encore payé',
    'paid_recorded' => 'Paiement enregistré',
    'all_sections' => 'Toutes sections',
    'filter' => 'Filtrer',
    'date_from_placeholder' => 'Du…',
    'date_to_placeholder' => 'Au…',
    'date_from_title' => 'Date de début',
    'date_to_title' => 'Date de fin',
    'clear_dates' => 'Effacer dates',

    // Toggles
    'archived' => 'Archivés',
    'subsections' => 'Sous-sections',

    // Stats / badges
    'paid_count' => ':count payé(s)',
    'pending_count' => ':count en attente',
    'total_collected' => 'Total encaissé :',

    // Table headers — dues index
    'col_name' => 'Nom Prénom',
    'col_status' => 'Statut',
    'col_section' => 'Section',
    'col_entry' => 'Entrée',
    'col_exit' => 'Sortie',
    'col_paid' => 'Payé',
    'col_amount' => 'Montant',
    'col_date_paid' => 'Date payé',
    'col_comment' => 'Commentaire',
    'comment_placeholder' => 'Commentaire…',

    // Footer actions
    'check_all' => 'Tout cocher',
    'save' => 'Enregistrer',

    // Empty state
    'empty_dues' => 'Aucun membre trouvé pour ces critères.',

    // Direct debits
    'payment_mode_note' => 'Mode de paiement&nbsp;: <strong>Prélèvement (TP_ID = 1)</strong> &mdash; Actifs uniquement',
    'stat_to_record' => 'à enregistrer',
    'stat_already' => 'déjà enregistrés',
    'stat_estimated' => 'montant estimé (réguls)',
    'debit_date_label' => 'Date du prélèvement&nbsp;:',
    'save_debits' => 'Enregistrer les :count prélèvements',
    'confirm_save_debits' => 'Enregistrer :count prélèvement(s) ?',
    'all_recorded' => 'Tous les prélèvements ont déjà été enregistrés pour cette période.',
    'empty_direct_debits' => 'Aucun membre avec prélèvement automatique pour la période sélectionnée.',
    'section_header_pending' => 'À enregistrer (:count)',
    'section_header_paid' => 'Déjà enregistrés (:count) — Total : :total €',
    'col_amount_regul' => 'Montant régul',
    'col_debit_date' => 'Date prélevé',

    // Transfers
    'col_beneficiary' => 'Bénéficiaire',
    'col_transfer_date' => 'Date virement',
    'empty_transfers' => 'Aucun virement trouvé pour les critères sélectionnés.',

    // Total labels
    'total_label_member' => 'membre',
    'total_label_transfer' => 'virement',

    // Export
    'export_excel' => 'Exporter Excel',

    // Onglets aria
    'tabs_aria_label' => 'Onglets cotisations',
];
