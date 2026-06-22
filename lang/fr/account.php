<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Compte utilisateur — authentification & 2FA
    |--------------------------------------------------------------------------
    */

    // Breadcrumb / page titles
    'breadcrumb_account' => 'Mon compte',
    'breadcrumb_auth' => 'Authentification',
    'breadcrumb_totp' => 'Authentification à deux facteurs',
    'title_auth' => 'Authentification',
    'title_totp' => 'Authentification à deux facteurs',

    // Tabs
    'tab_password' => 'Mot de passe',
    'tab_2fa' => 'Double authentification',
    'badge_active' => 'Actif',
    'badge_required' => 'Requis',
    'badge_inactive' => 'Inactif',
    'badge_pending' => 'En attente de confirmation',

    // Password tab
    'pwd_expired_warning' => 'Vous utilisez un mot de passe expiré ou temporaire — vous devez en choisir un nouveau maintenant.',
    'pwd_first_login_info' => 'Bienvenue ! Veuillez choisir un mot de passe personnel.',
    'label_current_pwd' => 'Mot de passe actuel',
    'label_new_pwd' => 'Nouveau mot de passe',
    'label_confirm_pwd' => 'Confirmation',

    // 2FA — active state
    '2fa_active_title' => '2FA activée',
    '2fa_active_desc' => 'Votre compte est protégé par l\'authentification à deux facteurs. Un code TOTP sera demandé à chaque connexion.',

    // 2FA — recovery codes
    'recovery_title' => 'Codes de récupération',
    'recovery_desc' => 'Conservez ces codes dans un endroit sûr. Chaque code est à usage unique et permet de se connecter si vous perdez l\'accès à votre application TOTP.',
    'recovery_hidden' => 'Les codes de récupération ne sont affichés qu\'une seule fois après leur génération. Régénérez-les ci-dessous si vous les avez perdus.',
    'btn_regenerate' => 'Régénérer les codes',
    'confirm_regenerate' => 'Régénérer les codes ? Les anciens codes seront invalides.',

    // 2FA — disable
    'disable_title' => 'Désactiver la 2FA',
    'disable_desc' => 'Saisissez votre code TOTP actuel pour confirmer la désactivation.',
    'btn_disable' => 'Désactiver',
    'confirm_disable' => 'Désactiver la protection 2FA ?',
    '2fa_required_info' => 'La double authentification est requise par votre groupe. Vous ne pouvez pas la désactiver.',

    // 2FA — setup pending
    'setup_pending_title' => 'Configuration de la 2FA',
    'setup_qr_desc' => 'Scannez ce QR code avec votre application d\'authentification (Google Authenticator, Authy, 2FAS…), puis saisissez le code généré pour confirmer.',
    'manual_key_label' => 'Clé manuelle :',
    'manual_key_label_nbsp' => 'Clé manuelle&nbsp;:',
    'label_confirm_code' => 'Code de confirmation',
    'btn_confirm' => 'Confirmer',

    // 2FA — not set up
    'setup_title' => 'Configurer la 2FA',
    'setup_not_configured' => 'La double authentification n\'est pas encore configurée. Rechargez cette page pour démarrer la configuration.',

    // About 2FA sidebar
    'about_title' => 'À propos de la 2FA',
    'about_desc' => 'La double authentification (2FA / TOTP) ajoute une couche de protection : même si votre mot de passe est compromis, l\'attaquant ne peut pas se connecter sans votre téléphone.',
    'about_desc_totp' => 'L\'authentification à deux facteurs (2FA / TOTP) ajoute une couche de protection : même si votre mot de passe est compromis, l\'attaquant ne peut pas se connecter sans votre téléphone.',
    'about_apps' => 'Applications compatibles :',
    'about_apps_list' => 'Google Authenticator, Microsoft Authenticator, Authy, 2FAS Auth, Bitwarden, ou tout client TOTP (RFC 6238).',
    'about_recovery' => 'Codes de récupération :',
    'about_recovery_desc' => 'conservez-les hors ligne. Ils permettent l\'accès si vous perdez votre appareil.',
    'about_recovery_desc2' => 'conservez-les hors ligne.',

];
