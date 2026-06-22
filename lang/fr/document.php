<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bibliothèque de documents
    |--------------------------------------------------------------------------
    */

    // ── index ──────────────────────────────────────────────────────────────
    'title' => 'Bibliothèque de documents',
    'new_folder' => 'Nouveau dossier',
    'add_doc' => 'Ajouter',
    'doc_types_title' => 'Types de documents',
    'filter_all_types' => 'Tous les types',
    'folder_tree_heading' => 'Dossiers',
    'root' => 'Racine',
    'empty_folder' => 'Ce dossier est vide.',

    // modals — create folder
    'modal_create_folder_title' => 'Nouveau dossier',
    'folder_name_label' => 'Nom du dossier',
    'folder_inherit_note' => 'Créé dans le dossier courant ; il héritera de son type de document.',

    // modals — upload
    'modal_upload_title' => 'Ajouter un document',
    'upload_files_label' => 'Fichier(s)',
    'upload_hint' => ':exts — max :max Mo.',
    'upload_type_label' => 'Type',
    'upload_visibility_note' => 'La visibilité se gère ensuite via « Partager ».',
    'btn_send' => 'Envoyer',

    // modals — edit doc
    'modal_edit_doc_title' => 'Modifier le document',
    'doc_name_label' => 'Nom du fichier',
    'doc_type_label' => 'Type',
    'doc_folder_label' => 'Dossier',
    'btn_delete_doc' => 'Supprimer',

    // modals — rename folder
    'modal_rename_folder_title' => 'Renommer le dossier',

    // ACL modal
    'modal_acl_title' => 'Partager',

    // JS confirms
    'confirm_delete_folder' => 'Supprimer ce dossier ? Il doit être vide.',
    'confirm_delete_doc' => 'Supprimer définitivement ce document ?',

    // folder-node tooltips
    'folder_share_title' => 'Partager',
    'folder_rename_title' => 'Renommer',
    'folder_delete_title' => 'Supprimer',
    'folder_toggle_aria' => 'Déplier / replier',

    // ── acl (full page & partial) ──────────────────────────────────────────
    'acl_page_title' => 'Partage',
    'acl_desc_folder' => 'ce dossier',
    'acl_desc_doc' => 'ce document',
    'acl_desc' => 'Autorisations propres à :target. Les dossiers transmettent leurs autorisations à leur contenu ; un refus l\'emporte toujours.',
    'acl_desc_partial' => 'les dossiers transmettent leurs autorisations à leur contenu ; un refus l\'emporte toujours.',
    'acl_card_title' => 'Autorisations',
    'acl_empty' => 'Aucune autorisation propre — la sécurité de section / type s\'applique.',
    'acl_th_beneficiary' => 'Bénéficiaire',
    'acl_th_effect' => 'Effet',
    'acl_th_rights' => 'Droits',
    'acl_effect_allow' => 'Autorise',
    'acl_effect_deny' => 'Refus',
    'acl_btn_remove' => 'Retirer',
    'acl_add_card_title' => 'Ajouter une autorisation',
    'acl_label_beneficiary' => 'Bénéficiaire',
    'acl_opt_user' => 'Utilisateur',
    'acl_opt_group' => 'Groupe',
    'acl_opt_role' => 'Rôle',
    'acl_opt_everyone' => 'Tout le monde',
    'acl_label_person' => 'Personne',
    'acl_label_person_id' => 'Personne #:id',
    'acl_label_group' => 'Groupe',
    'acl_label_role' => 'Rôle',
    'acl_everyone_note' => 'S\'applique à toute personne ayant accès à la bibliothèque.',
    'acl_everyone_note_partial' => 'Toute personne ayant accès à la bibliothèque.',
    'acl_label_effect' => 'Effet',
    'acl_opt_allow' => 'Autorise',
    'acl_opt_deny' => 'Refuse',
    'acl_label_rights' => 'Droits',
    'acl_btn_add' => 'Ajouter',
    'acl_btn_add_auth' => 'Ajouter l\'autorisation',
    'acl_confirm_remove' => 'Retirer cette autorisation ?',

    // ── types ──────────────────────────────────────────────────────────────
    'types_page_title' => 'Types de documents',
    'types_btn_new' => 'Nouveau type',
    'types_empty' => 'Aucun type de document.',
    'modal_create_type_title' => 'Nouveau type de document',
    'type_code_label' => 'Code (5 car. max)',
    'type_libelle_label' => 'Libellé',
    'type_security_label' => 'Visible si la personne a le droit',
    'type_security_public' => 'Public (tout le monde)',
    'type_syndicate_label' => 'Réservé au syndicat',
    'modal_edit_type_title' => 'Modifier le type',
    'confirm_delete_type' => 'Supprimer ce type de document ?',

];
