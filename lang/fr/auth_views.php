<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chaînes des vues d'authentification
    |--------------------------------------------------------------------------
    |
    | Libellés propres aux vues auth/ (connexion, mot de passe, charte, TOTP,
    | identifiants, utilisateurs connectés). Ne pas confondre avec auth.php
    | (messages de validation Laravel).
    |
    */

    // ── Login ─────────────────────────────────────────────────────────────────
    'login_page_title' => 'Connexion',
    'login_noscript' => 'JavaScript est désactivé&nbsp;: OpenBrigade nécessite JavaScript pour fonctionner correctement. Veuillez l\'activer puis recharger la page.',
    'login_tagline' => 'Organisez le personnel et les activités avec :org',
    'login_welcome' => 'Bienvenue',
    'login_subtitle' => 'Connectez-vous à :org',
    'login_label_login' => 'Identifiant ou adresse e-mail',
    'login_label_password' => 'Mot de passe',
    'login_forgot_link' => 'Mot de passe oublié ?',
    'login_remember' => 'Se souvenir de moi',
    'login_inline_error' => 'Veuillez remplir l\'identifiant et le mot de passe.',
    'login_btn' => 'Se connecter',

    // ── Forgot panel (inside login page) ────────────────────────────────────
    'forgot_title' => 'Mot de passe oublié ?',
    'forgot_subtitle' => 'Utilisez le formulaire de récupération si votre adresse e-mail est enregistrée, ou contactez votre administrateur.',
    'forgot_btn_recover' => 'Récupérer mon mot de passe',
    'btn_back' => 'Retour',

    // ── Password reset request ────────────────────────────────────────────────
    'reset_req_title' => 'Mot de passe oublié',
    'reset_req_subtitle' => 'Indiquez votre identifiant ou adresse e-mail pour recevoir un lien de réinitialisation.',
    'reset_req_label' => 'Identifiant ou adresse e-mail',
    'reset_req_btn' => 'Envoyer le lien',
    'reset_unavailable_title' => 'Réinitialisation non disponible',
    'reset_unavailable_body' => 'L\'envoi d\'e-mails n\'est pas activé sur ce système. Contactez votre administrateur pour qu\'il réinitialise votre mot de passe.',
    'reset_sent_title' => 'Demande envoyée',
    'reset_sent_body' => 'Si un compte correspondant à votre identifiant ou adresse e-mail existe, vous recevrez un e-mail contenant un lien pour réinitialiser votre mot de passe.',
    'reset_sent_spam' => 'Vérifiez également votre dossier spam.',
    'reset_back_btn' => 'Retour à la connexion',
    'reset_back_link' => 'Retour à la connexion',

    // ── Password reset confirm ────────────────────────────────────────────────
    'reset_confirm_title' => 'Nouveau mot de passe',
    'reset_confirm_sent' => 'Votre mot de passe temporaire vous a été envoyé par e-mail.',
    'reset_confirm_expiry' => 'Ce mot de passe expire immédiatement — vous devrez en choisir un nouveau lors de votre prochaine connexion.',
    'reset_invalid_title' => 'Lien invalide',
    'reset_invalid_body' => 'Ce lien de réinitialisation est invalide ou a expiré (validité : 24 h). Faites une nouvelle demande si nécessaire.',
    'reset_confirm_btn' => 'Se connecter',
    'reset_new_request' => 'Nouvelle demande',

    // ── Change password ───────────────────────────────────────────────────────
    'account_breadcrumb' => 'Mon compte',
    'change_pwd_title' => 'Modifier le mot de passe',
    'change_pwd_renew_title' => 'Renouvellement du mot de passe',
    'change_pwd_expired_warn' => 'Vous utilisez un mot de passe expiré ou temporaire — vous devez en choisir un nouveau maintenant.',
    'change_pwd_first_login' => 'Bienvenue ! Veuillez choisir un mot de passe personnel.',
    'change_pwd_current' => 'Mot de passe actuel',
    'change_pwd_new' => 'Nouveau mot de passe',
    'change_pwd_confirm' => 'Confirmation',
    'change_pwd_min' => 'Minimum :min caractères.',
    'change_pwd_uppercase' => 'Au moins une majuscule.',
    'change_pwd_lowercase' => 'Au moins une minuscule.',
    'change_pwd_digits' => 'Au moins un chiffre.',
    'change_pwd_special' => 'Au moins un caractère spécial.',
    'change_pwd_btn' => 'Enregistrer',
    'change_pwd_cancel' => 'Annuler',

    // ── Charter ───────────────────────────────────────────────────────────────
    'charter_page_title' => 'Conditions d\'utilisation',
    'charter_section_title' => 'Conditions d\'utilisation',
    'charter_accepted_on' => 'Vous avez accepté ces conditions le :date.',
    'charter_back_dashboard' => 'Retour au tableau de bord',
    'charter_check_accept' => 'J\'ai lu et j\'accepte les conditions d\'utilisation et je m\'engage à les respecter.',
    'charter_check_rgpd' => 'J\'accepte le Règlement Général sur la Protection des Données (RGPD).',
    'charter_btn_accept' => 'Accepter et continuer',
    'charter_btn_reject' => 'Refuser et se déconnecter',
    'charter_reject_confirm' => 'Refuser les conditions entraînera votre déconnexion. Continuer ?',
    'charter_art1_title' => 'Article 1 : Finalité du document',
    'charter_art1_body' => 'Le présent document définit les principales règles d\'usage du site «&nbsp;:site&nbsp;» mis à disposition du personnel :memberSuffix:orgType.',
    'charter_art2_title' => 'Article 2 : Domaine d\'application',
    'charter_art2_body' => 'Il s\'applique à toutes les personnes explicitement autorisées à utiliser le dit site et qui disposent officiellement des clés personnelles d\'accès.',
    'charter_art3_title' => 'Article 3 : Cadre d\'utilisation',
    'charter_art3_intro' => 'Le site «&nbsp;:site&nbsp;» a pour vocation de permettre à l\'ensemble du personnel:memberSuffix :orgType de&nbsp;:',
    'charter_art3_li_dispo' => 'saisir ses disponibilités ou indisponibilités mensuelles,',
    'charter_art3_li_gardes' => 'consulter le tableau de gardes mensuelles,',
    'charter_art3_li_competences' => 'visualiser ses compétences opérationnelles,',
    'charter_art3_li_infos' => 'prendre connaissance des différentes informations ou consignes,',
    'charter_art3_li_fiche' => 'mettre à jour sa fiche de renseignements personnels,',
    'charter_art3_li_vie' => 's\'informer sur la vie :orgType.',
    'charter_art3_note' => 'Cette liste est non exhaustive ; l\'administrateur du site peut à tout moment la faire évoluer.',
    'charter_art4_title' => 'Article 4 : Règles d\'utilisation',
    'charter_art4_li_nuire' => 'L\'utilisateur s\'engage à ne pas effectuer d\'opérations pouvant nuire au bon fonctionnement du site.',
    'charter_art4_li_session' => 'L\'utilisateur est seul responsable de sa session et s\'engage à se déconnecter après chaque utilisation.',
    'charter_art4_li_navigateur' => 'L\'utilisateur s\'engage à ne pas accepter l\'enregistrement des mots de passe par le navigateur.',
    'charter_art4_li_comportement' => 'L\'utilisateur s\'engage à faire preuve d\'un comportement exemplaire lors de l\'usage de ce site.',
    'charter_art5_title' => 'Article 5 : Compte utilisateur et mot de passe',
    'charter_art5_li_regles' => 'Chaque utilisateur doit définir un mot de passe en respectant les règles de sécurité du site.',
    'charter_art5_li_confidentiel' => 'Un compte utilisateur est strictement personnel et confidentiel. L\'utilisateur ne doit en aucun cas communiquer son mot de passe.',
    'charter_art5_li_recommande' => 'Il est recommandé de ne pas utiliser le même mot de passe que sur d\'autres applications.',
    'charter_art6_title' => 'Article 6 : Confidentialité',
    'charter_art6_li_donnees' => 'Les données du site ne doivent en aucun cas être utilisées en dehors du cadre pour lequel elles sont destinées.',
    'charter_art6_li_divulgation_pre' => 'La divulgation des données du site à des tiers est',
    'charter_art6_li_divulgation_strong' => 'STRICTEMENT INTERDITE',
    'charter_art6_li_secret' => 'L\'article 226-13/14 du code de procédure pénale soumet tout sapeur-pompier au secret professionnel et médical.',
    'charter_art6_li_reseaux' => 'Toute transmission d\'information relative au service via les réseaux sociaux est strictement interdite.',
    'charter_art7_title' => 'Article 7 : Informatique et liberté',
    'charter_art7_li_loi' => 'Conformément à la Loi Informatique et Libertés du 6 janvier 1978, l\'utilisateur dispose d\'un droit d\'accès, de modification et de suppression des données personnelles le concernant.',
    'charter_art7_li_traces' => 'Les connexions des utilisateurs ainsi que les différentes actions effectuées sur le site sont tracées.',

    // ── TOTP challenge ────────────────────────────────────────────────────────
    'totp_section_title' => 'Vérification en deux étapes',
    'totp_intro' => 'Saisissez le code à 6 chiffres affiché par votre application d\'authentification (Google Authenticator, Authy…).',
    'totp_label' => 'Code TOTP',
    'totp_btn' => 'Vérifier',
    'totp_recovery_summary' => 'Utiliser un code de récupération',
    'totp_recovery_btn' => 'Utiliser ce code',
    'totp_back_login' => 'Retour à la connexion',

    // ── Send credentials ──────────────────────────────────────────────────────
    'creds_breadcrumb_personnel' => 'Personnel',
    'creds_page_title' => 'Envoyer identifiants',
    'creds_card_title' => 'Identifiants — :name',
    'creds_intro' => 'Un nouveau mot de passe temporaire va être généré pour <strong>:name</strong>. L\'utilisateur devra le changer à sa prochaine connexion.',
    'creds_field_login' => 'Identifiant',
    'creds_field_email' => 'E-mail enregistré',
    'creds_no_email' => 'Aucun e-mail enregistré',
    'creds_btn_manual' => 'Mode manuel',
    'creds_btn_manual_sub' => 'Afficher le mot de passe ici',
    'creds_btn_auto' => 'Envoi automatique',
    'creds_btn_auto_sub' => 'Envoyer par e-mail',
    'creds_btn_auto_disabled' => 'Aucun e-mail enregistré',
    'creds_manual_success' => 'Le mot de passe a été régénéré avec succès.',
    'creds_manual_intro' => 'Communiquez les informations suivantes à <strong>:name</strong> :',
    'creds_field_tmp_pwd' => 'Mot de passe temporaire',
    'creds_field_telephone' => 'Téléphone',
    'creds_field_email2' => 'E-mail',
    'creds_expiry_note' => 'Ce mot de passe expire immédiatement — l\'utilisateur devra en choisir un nouveau à la prochaine connexion.',
    'creds_auto_sent' => 'Identifiants envoyés par e-mail à :email.',
    'creds_auto_unavailable' => 'Le mot de passe a été régénéré, mais l\'envoi automatique par e-mail n\'est pas encore disponible. Communiquez le mot de passe temporaire manuellement.',
    'creds_auto_tmp_label' => 'Mot de passe temporaire pour <strong>:name</strong> :',
    'creds_btn_back' => 'Retour à la fiche',

    // ── Connected users ────────────────────────────────────────────────────────
    'connected_breadcrumb_admin' => 'Administration',
    'connected_breadcrumb_connexions' => 'Connexions',
    'connected_title' => 'Utilisateurs connectés',
    'connected_subtitle' => ':count utilisateur(s) actif(s) ces 10 dernières minutes',
    'connected_empty' => 'Aucun utilisateur connecté en ce moment.',
    'connected_col_nom' => 'Nom',
    'connected_col_section' => 'Section',
    'connected_col_systeme' => 'Système',
    'connected_col_navigateur' => 'Navigateur',
    'connected_col_connexion' => 'Connexion',
    'connected_col_activite' => 'Dernière activité',
    'connected_col_ip' => 'IP',

];
