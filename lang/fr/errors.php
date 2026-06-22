<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pages d'erreur HTTP
    |--------------------------------------------------------------------------
    |
    | Titres et messages pour chaque code HTTP, plus les libellés des boutons
    | d'action et du schéma de connexion. Les clés sont groupées par code.
    |
    */

    // ── Fallback générique ──────────────────────────────────────────────────
    'default' => [
        'title' => 'Une erreur est survenue',
        'message' => "Une erreur inattendue s'est produite. Réessayez ou contactez votre administrateur.",
    ],

    // ── 4xx — côté client ───────────────────────────────────────────────────
    '400' => [
        'title' => 'Requête incorrecte',
        'message' => "Le serveur n'a pas pu interpréter la requête en raison d'une syntaxe invalide.",
    ],
    '401' => [
        'title' => 'Authentification requise',
        'message' => 'Vous devez vous connecter pour accéder à cette page.',
    ],
    '403' => [
        'title' => 'Accès interdit',
        'message' => "Vous n'avez pas les droits nécessaires pour accéder à cette page. Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur.",
    ],
    '404' => [
        'title' => 'Page introuvable',
        'message' => "La page demandée n'existe pas ou a été déplacée.",
    ],
    '405' => [
        'title' => 'Méthode non autorisée',
        'message' => "La méthode HTTP utilisée n'est pas autorisée pour cette ressource.",
    ],
    '407' => [
        'title' => 'Authentification proxy requise',
        'message' => 'Le proxy réseau exige une authentification avant de poursuivre.',
    ],
    '408' => [
        'title' => "Délai d'attente dépassé",
        'message' => 'Le serveur a mis trop de temps à recevoir la requête. Veuillez réessayer.',
    ],
    '409' => [
        'title' => 'Conflit',
        'message' => "La requête entre en conflit avec l'état actuel de la ressource.",
    ],
    '410' => [
        'title' => 'Ressource supprimée',
        'message' => "Cette ressource n'est plus disponible et a été définitivement supprimée.",
    ],
    '411' => [
        'title' => 'Longueur requise',
        'message' => "Le serveur exige l'en-tête Content-Length pour traiter la requête.",
    ],
    '412' => [
        'title' => 'Précondition échouée',
        'message' => "Une condition préalable indiquée dans la requête n'est pas remplie.",
    ],
    '413' => [
        'title' => 'Contenu trop volumineux',
        'message' => 'Le contenu envoyé dépasse la taille maximale autorisée.',
    ],
    '416' => [
        'title' => 'Plage non satisfaisable',
        'message' => 'La plage demandée ne peut pas être fournie pour cette ressource.',
    ],
    '418' => [
        'title' => 'Je suis une théière',
        'message' => "Ce serveur refuse de préparer du café : il s'agit, par nature, d'une théière.",
    ],
    '419' => [
        'title' => 'Session expirée',
        'message' => 'Votre session a expiré pour des raisons de sécurité. Veuillez vous reconnecter.',
    ],
    '429' => [
        'title' => 'Trop de requêtes',
        'message' => 'Vous avez effectué trop de requêtes en peu de temps. Patientez quelques instants avant de réessayer.',
    ],

    // ── 5xx — côté serveur / réseau ─────────────────────────────────────────
    '500' => [
        'title' => 'Erreur interne',
        'message' => "Une erreur inattendue est survenue de notre côté. L'équipe technique a été informée. Réessayez dans quelques instants.",
    ],
    '502' => [
        'title' => 'Passerelle incorrecte',
        'message' => 'Le serveur a reçu une réponse invalide depuis un service en amont.',
    ],
    '503' => [
        'title' => 'Service indisponible',
        'message' => "L'application est momentanément indisponible (maintenance ou surcharge). Merci de votre patience, le service sera rétabli sous peu.",
    ],
    '504' => [
        'title' => 'Délai de passerelle dépassé',
        'message' => "Un service en amont n'a pas répondu dans le délai imparti.",
    ],
    '505' => [
        'title' => 'Version HTTP non supportée',
        'message' => "La version du protocole HTTP utilisée n'est pas prise en charge par le serveur.",
    ],

    // ── Boutons d'action ────────────────────────────────────────────────────
    'btn_login' => 'Se connecter',
    'btn_home' => 'Accueil',
    'btn_retry' => 'Réessayer',
    'btn_back_home' => "Retour à l'accueil",
    'btn_prev_page' => 'Page précédente',

    // ── Schéma de connexion (diagramme SVG) ─────────────────────────────────
    'diagram_aria' => 'Schéma de connexion client, réseau, serveur — point de défaillance mis en évidence',
    'diagram_client' => 'Client',
    'diagram_network' => 'Réseau',
    'diagram_server' => 'Serveur',

];
