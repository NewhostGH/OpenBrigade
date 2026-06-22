<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Véhicules
    |--------------------------------------------------------------------------
    */

    // Titres / breadcrumb
    'title' => 'Véhicules',
    'new_vehicle' => 'Nouveau véhicule',
    'edit_title' => 'Modifier — :name',
    'breadcrumb_edit' => 'Modifier',
    'breadcrumb_new' => 'Nouveau véhicule',

    // Index filters
    'search_placeholder' => 'Immatriculation ou libellé…',
    'status_all' => 'Tous',
    'status_op' => 'Opérationnels',
    'status_nop' => 'Non opérationnels',
    'all_sections' => 'Toutes les sections',
    'row_action_view' => 'Voir le détail',
    'item_label' => 'véhicule',

    // Form section labels
    'section_identification' => 'Identification',
    'section_characteristics' => 'Caractéristiques',
    'section_expiry_dates' => 'Dates d\'expiration',
    'section_equipment' => 'Équipement',
    'section_comment' => 'Commentaire',

    // Form field labels
    'label_immatriculation' => 'Immatriculation',
    'label_indicatif' => 'Indicatif',
    'label_inventaire' => 'N° inventaire',
    'label_type' => 'Type',
    'label_modele' => 'Modèle',
    'label_annee' => 'Année',
    'label_section' => 'Section',
    'label_position' => 'Statut / Position',
    'label_km' => 'Kilométrage actuel',
    'label_km_revision' => 'Km prochaine révision',
    'choose' => '— Choisir —',

    // Expiry date field labels (used in form $dateFields array and show.blade)
    'exp_insurance' => 'Assurance',
    'exp_ct' => 'Contrôle tech.',
    'exp_revision' => 'Révision',
    'exp_titre' => 'Titre d\'accès',

    // Expiry hints
    'hint_expired' => 'Expiré il y a :days j.',
    'hint_in' => 'Dans :days j.',

    // Equipment flags
    'flag_snow' => 'Neige',
    'flag_clim' => 'Climatisation',
    'flag_pa' => 'Public Address',
    'flag_attelage' => 'Attelage',
    'flag_externe' => 'Véhicule externe',
    'comment_placeholder' => 'Notes libres…',

    // Form action buttons
    'btn_save_edit' => 'Enregistrer les modifications',
    'btn_create' => 'Créer le véhicule',
    'btn_view_sheet' => 'Voir la fiche',

    // Danger zone
    'danger_zone_title' => 'Zone dangereuse',
    'danger_zone_desc' => 'Supprime définitivement ce véhicule et toutes ses affectations à des activités.',
    'confirm_delete' => 'Supprimer définitivement ce véhicule ?',

    // Show page — side-nav sections
    'nav_info' => 'Informations',
    'nav_activities' => 'Activités',
    'nav_equipment' => 'Matériel',
    'nav_documents' => 'Documents',

    // Show page — identity labels
    'dt_type' => 'Type',
    'dt_model' => 'Modèle',
    'dt_indicatif' => 'Indicatif',
    'dt_section' => 'Section',
    'dt_status' => 'Statut',
    'status_operational' => 'Opérationnel',
    'status_limited' => 'Limité',
    'status_unavailable' => 'Indisponible',
    'dt_km' => 'Kilométrage',
    'dt_km_revision' => 'révision à :km km',
    'dt_inventaire' => 'N° inventaire',

    // Show page — activities
    'section_activities' => 'Activités',
    'col_activity' => 'Activité',
    'col_function' => 'Fonction',
    'col_date' => 'Date',
    'col_km' => 'Km',
    'stat_total_engagements' => 'Total engagements :',
    'stat_km_total' => 'Km cumulés :',
    'empty_activities' => 'Aucune activité en :year.',

    // Show page — equipment
    'section_equipment_title' => 'Matériel embarqué',
    'load_equipment_placeholder' => '— Embarquer du matériel —',
    'col_eq_type' => 'Type',
    'col_eq_model' => 'Modèle',
    'col_eq_serial' => 'N° série',
    'col_eq_inventory' => 'Inventaire',
    'col_eq_qty' => 'Qté',
    'confirm_unload' => 'Débarquer ce matériel ?',
    'btn_unload' => 'Débarquer',
    'empty_equipment' => 'Aucun matériel assigné à ce véhicule.',

    // Show page — documents
    'section_documents_title' => 'Documents',
    'col_doc_name' => 'Nom',
    'col_doc_category' => 'Catégorie',
    'col_doc_date' => 'Date',
    'empty_documents' => 'Aucun document associé à ce véhicule.',

    // Index page
    'index_title' => 'Véhicules — :app',

    // Form page
    'form_edit_title' => 'Modifier — :app',
    'form_new_title' => 'Nouveau véhicule — :app',
    'placeholder_inventaire' => 'N° inventaire',
    'unit_km' => 'km',
    'option_other' => 'Autre',
    'flag_externe_short' => 'Externe',
    'attach_btn_title' => 'Embarquer',

    // Show page
    'show_title' => ':vehicle — :app',
    'nav_label_info' => 'Informations',
    'btn_unload_title' => 'Débarquer',

];
