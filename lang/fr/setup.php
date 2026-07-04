<?php

return [
    // First-run wizard
    'wizard_title' => 'Configuration initiale',
    'wizard_heading' => 'Bienvenue',
    'wizard_intro' => 'Quelques informations pour configurer votre installation. Vous pourrez modifier ces réglages plus tard.',

    'org_type' => "Type d'organisation",
    'org_type_help' => "Détermine le jeu de rôles préconfigurés proposé. Modifiable ultérieurement.",
    'org_type_choose' => 'Choisissez…',

    'cisname' => 'Nom court de votre organisation',
    'cisname_help' => 'Maximum 25 caractères. Modifiable ultérieurement.',
    'organisation_name' => 'Nom long de votre organisation',
    'organisation_name_help' => 'Maximum 60 caractères. Modifiable ultérieurement.',
    'cisurl' => 'Adresse Web',
    'cisurl_help' => 'Commençant par http:// ou https://.',
    'admin_email' => 'Adresse email de l’administrateur',
    'admin_email_help' => 'Adresse valide de contact / administration.',
    'application_title' => 'Nom personnalisé de l’application',
    'application_title_help' => 'Le nom affiché de l’application.',
    'description' => 'Description (facultatif)',
    'description_help' => 'Courte description de votre organisation. Modifiable ultérieurement.',
    'logo' => 'Logo (facultatif)',
    'logo_help' => 'Image PNG, JPG, GIF, WebP ou ICO, 4 Mo maximum.',

    'submit' => 'Valider et démarrer',
    'completed' => 'Configuration enregistrée.',

    // Admin: change organisation type
    'admin_title' => "Type d'organisation",
    'admin_breadcrumb' => "Type d'organisation",
    'current_type' => 'Type actuel',
    'change_type' => 'Changer de type',
    'change_type_intro' => "Le type d'organisation détermine le jeu de rôles préconfigurés. Le changer n'affecte pas immédiatement vos rôles existants.",

    'consequences_heading' => 'Ce que le changement implique',
    'consequence_roles_kept' => 'Vos rôles existants (et leurs attributions aux membres) sont conservés — rien n’est supprimé.',
    'consequence_active_set' => 'Les listes de rôles affichent désormais le jeu préconfiguré du nouveau type, plus vos rôles personnalisés.',
    'consequence_reset_optional' => 'Vous pouvez, en option, réinitialiser les rôles préconfigurés du type choisi à leurs valeurs par défaut (action destructive ci-dessous).',

    'save_type' => 'Enregistrer le type',
    'type_saved' => "Type d'organisation mis à jour.",

    'reset_roles_heading' => 'Réinitialiser les rôles préconfigurés',
    'reset_roles_warning' => 'Action destructive : réécrit les rôles préconfigurés du type sélectionné et leurs permissions aux valeurs par défaut. Les personnalisations de ces rôles seront perdues. Les rôles personnalisés et les attributions aux membres ne sont pas affectés.',
    'reset_roles_confirm' => 'Réinitialiser les rôles de ce type',
    'reset_roles_done' => 'Rôles préconfigurés réinitialisés pour : :type.',

    // Delete custom roles + remap
    'delete_roles_heading' => 'Supprimer des rôles personnalisés',
    'delete_roles_intro' => 'Sélectionnez les rôles personnalisés à supprimer. Pour chacun, choisissez le rôle préconfiguré vers lequel réaffecter ses membres — ou « Retirer » pour supprimer les affectations.',
    'delete_roles_none_custom' => 'Aucun rôle personnalisé à supprimer.',
    'delete_roles_col_role' => 'Rôle personnalisé',
    'delete_roles_col_members' => 'Membres',
    'delete_roles_col_remap' => 'Réaffecter les membres vers',
    'delete_roles_remap_drop' => 'Retirer les affectations (aucune)',
    'delete_roles_confirm' => 'Supprimer les rôles sélectionnés',
    'delete_roles_confirm_prompt' => 'Supprimer définitivement les rôles sélectionnés et réaffecter leurs membres ? Cette action est irréversible.',
    'delete_roles_none' => 'Aucun rôle sélectionné.',
    'delete_roles_done' => 'Rôles personnalisés supprimés et membres réaffectés.',
];
