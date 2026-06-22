<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chaînes du module Organisation
    |--------------------------------------------------------------------------
    |
    | Vues : index, map, sections, section-show, section-form, _node.
    |
    */

    // ── Page titles (used in @section('title')) ───────────────────────────
    'page_org_chart' => 'Organigramme',
    'page_map' => 'Cartographie',
    'page_sections' => 'Sections',

    // ── Breadcrumbs ───────────────────────────────────────────────────────
    'bc_organisation' => 'Organisation',
    'bc_org_chart' => 'Organigramme',
    'bc_map' => 'Cartographie',
    'bc_sections' => 'Sections',
    'bc_edit' => 'Modifier',
    'bc_new' => 'Nouvelle',

    // ── index.blade.php ───────────────────────────────────────────────────
    'manage_sections' => 'Gérer les sections',
    'new_section' => 'Nouvelle section',

    // ── map.blade.php ─────────────────────────────────────────────────────
    'no_geolocated' => 'Aucune section géolocalisée. Les sections sont placées au barycentre des positions GPS de leurs membres (renseignées depuis la fiche de chaque membre).',
    'geolocated_count' => ':count section géolocalisée|:count sections géolocalisées',

    // ── sections.blade.php ────────────────────────────────────────────────
    'col_code' => 'Code',
    'col_name' => 'Nom',
    'col_parent_section' => 'Section parente',
    'col_city' => 'Ville',
    'col_members' => 'Membres',
    'col_order' => 'Ordre',
    'col_status' => 'État',
    'status_inactive' => 'Inactive',
    'status_active' => 'Active',
    'no_sections' => 'Aucune section.',

    // ── section-show.blade.php ────────────────────────────────────────────
    // Header / meta
    'member_count' => ':count membre|:count membres',
    // Tabs
    'tab_informations' => 'Informations',
    'tab_org_chart' => 'Organigramme',
    'tab_personalisation' => 'Personnalisation',
    'tab_agrements' => 'Agréments & Médailles',
    'tab_cotisation' => 'Cotisation',
    // Info tab — card titles
    'card_mandatory_info' => 'Informations obligatoires',
    'card_contact' => 'Contact',
    'card_optional_info' => 'Informations facultatives',
    // Info tab — field labels (show view)
    'field_code' => 'Code',
    'field_name' => 'Nom',
    'field_order' => 'Ordre garde',
    'field_parent_section' => 'Section parente',
    'field_phone' => 'Téléphone',
    'field_phone_ops' => 'Tél opérationnel',
    'field_phone_training' => 'Tél formations',
    'field_fax' => 'Fax',
    'field_email_ops' => 'Email opérationnel',
    'field_email_secretary' => 'Email secrétariat',
    'field_email_training' => 'Email formation',
    'field_whatsapp' => 'Groupe WhatsApp',
    'field_radio_id' => 'ID Radio',
    'field_address' => 'Adresse',
    'field_address_complement' => 'Complément d\'adresse',
    'field_zip_code' => 'Code postal',
    'field_city' => 'Ville',
    'field_siret' => 'SIRET',
    'field_affiliation' => 'N° Affiliation',
    'field_website' => 'Site web',
    // Org tab
    'card_roles' => 'Rôles dans la section',
    'no_roles' => 'Aucun rôle attribué dans cette section.',
    // Personnalisation tab — card titles
    'card_letterhead' => 'Papier à entête',
    'card_badge' => 'Badge',
    'card_lock' => 'Interdire les modifications sur les activités terminées',
    'card_default_texts' => 'Textes par défaut pour devis et factures',
    'card_president_sig' => 'Image de la signature du président',
    // Personnalisation tab — labels
    'label_pdf_model' => 'Modèle (.PDF)',
    'label_reset_letterhead' => 'Modèle par défaut',
    'label_default_model' => 'Modèle par défaut utilisé (pdf_page.pdf)',
    'label_margin_top' => 'Marge haut (mm)',
    'label_margin_lr' => 'Marge gauche/droite (mm)',
    'label_text_top' => 'Début zone de texte (mm)',
    'label_text_bottom' => 'Fin zone de texte (mm)',
    'label_badge_bg' => 'Image de fond du badge',
    'label_reset_badge' => 'Image par défaut',
    'label_no_badge_bg' => 'Aucune image de fond (dessin par défaut)',
    'label_lock_mode' => 'Modifications interdites',
    'lock_never' => 'Jamais',
    'lock_after_days' => 'Après x jours',
    'lock_days_after' => 'jours après la fin',
    'confirm_reset_lh' => 'Réinitialiser le papier à entête ? Le modèle par défaut sera utilisé.',
    'confirm_reset_badge' => 'Réinitialiser l\'image de fond du badge ?',
    // Default texts — field labels
    'label_pdf_signature' => 'Signature des documents',
    'label_devis_debut' => 'Début du devis',
    'label_devis_fin' => 'Fin du devis',
    'label_facture_debut' => 'Début de facture',
    'label_facture_fin' => 'Fin de facture',
    'label_scanned_sig' => 'Signature scannée',
    // Agréments tab
    'col_agr_code' => 'Code',
    'col_agr_label' => 'Libellé',
    'col_agr_delivered' => 'Délivrée le',
    'col_agr_clasp' => 'Agrafe',
    'col_agr_start' => 'Début',
    'col_agr_end' => 'Fin',
    'agr_clasp_placeholder' => 'Agrafe…',
    'agr_save_title' => 'Enregistrer',
    'agr_clear_title' => 'Effacer',
    // Cotisation tab — card titles
    'card_bank' => 'Coordonnées bancaires',
    'label_iban' => 'IBAN',
    'iban_placeholder' => 'FR76 XXXX XXXX XXXX XXXX XXXX XXX',
    'label_bic' => 'BIC / SWIFT',
    'card_rib' => 'RIB (coordonnées françaises)',
    'rib_used_for' => 'Utilisé pour les prélèvements',
    'card_rib_doc' => 'Document RIB',
    // Cotisation tab — labels
    'label_rib_download' => 'Télécharger le document enregistré',
    'label_rib_replace' => 'Remplacer le document',
    'label_rib_upload' => 'Téléverser un document',
    'rib_file_hint' => 'PDF, JPG ou PNG · max 5 Mo',
    'rib_updated_at' => 'Dernière mise à jour : :date',
    'label_code_banque' => 'Code banque',
    'label_etablissement' => 'Établissement',
    'label_guichet' => 'Guichet',
    'label_compte' => 'Compte',
    'label_cle_rib' => 'Clé',

    // ── section-form.blade.php ────────────────────────────────────────────
    'form_title_new' => 'Nouvelle section',
    'form_title_edit' => 'Modifier section',
    'label_code_required' => 'Code *',
    'label_section_inactive' => 'Section inactive',
    'label_root_option' => '— racine (sous l\'organisation) —',
    'confirm_delete' => 'Supprimer la section :code ? Cette action est irréversible.',

    // ── _node.blade.php ───────────────────────────────────────────────────
    'node_member' => ':count membre|:count membres',

];
