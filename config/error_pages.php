<?php

/*
|--------------------------------------------------------------------------
| Error / status page metadata
|--------------------------------------------------------------------------
|
| Drives the custom error views (resources/views/errors/*). Each entry maps
| an HTTP status code to its French title, message, and the node of the
| client → réseau → serveur diagram that is "at fault" (client | network |
| server). `relogin` marks codes after which the user must authenticate
| again, so the page is shown standalone (outside the app shell) even when
| the rest of the 4xx family keeps the normal layout.
|
*/

return [

    // Fallback when a rendered code is not listed below.
    'default' => [
        'title' => 'Une erreur est survenue',
        'message' => "Une erreur inattendue s'est produite. Réessayez ou contactez votre administrateur.",
        'node' => 'server',
    ],

    'codes' => [

        // ── 4xx — client side ───────────────────────────────────────────
        400 => ['title' => 'Requête incorrecte', 'node' => 'client',
            'message' => "Le serveur n'a pas pu interpréter la requête en raison d'une syntaxe invalide."],
        401 => ['title' => 'Authentification requise', 'node' => 'client', 'relogin' => true,
            'message' => 'Vous devez vous connecter pour accéder à cette page.'],
        403 => ['title' => 'Accès interdit', 'node' => 'client',
            'message' => "Vous n'avez pas les droits nécessaires pour accéder à cette page. Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur."],
        404 => ['title' => 'Page introuvable', 'node' => 'client',
            'message' => "La page demandée n'existe pas ou a été déplacée."],
        405 => ['title' => 'Méthode non autorisée', 'node' => 'client',
            'message' => "La méthode HTTP utilisée n'est pas autorisée pour cette ressource."],
        407 => ['title' => 'Authentification proxy requise', 'node' => 'network',
            'message' => 'Le proxy réseau exige une authentification avant de poursuivre.'],
        408 => ['title' => "Délai d'attente dépassé", 'node' => 'client',
            'message' => 'Le serveur a mis trop de temps à recevoir la requête. Veuillez réessayer.'],
        409 => ['title' => 'Conflit', 'node' => 'client',
            'message' => "La requête entre en conflit avec l'état actuel de la ressource."],
        410 => ['title' => 'Ressource supprimée', 'node' => 'client',
            'message' => "Cette ressource n'est plus disponible et a été définitivement supprimée."],
        411 => ['title' => 'Longueur requise', 'node' => 'client',
            'message' => "Le serveur exige l'en-tête Content-Length pour traiter la requête."],
        412 => ['title' => 'Précondition échouée', 'node' => 'client',
            'message' => "Une condition préalable indiquée dans la requête n'est pas remplie."],
        413 => ['title' => 'Contenu trop volumineux', 'node' => 'client',
            'message' => 'Le contenu envoyé dépasse la taille maximale autorisée.'],
        416 => ['title' => 'Plage non satisfaisable', 'node' => 'client',
            'message' => 'La plage demandée ne peut pas être fournie pour cette ressource.'],
        418 => ['title' => 'Je suis une théière', 'node' => 'client',
            'message' => "Ce serveur refuse de préparer du café : il s'agit, par nature, d'une théière."],
        419 => ['title' => 'Session expirée', 'node' => 'client', 'relogin' => true,
            'message' => 'Votre session a expiré pour des raisons de sécurité. Veuillez vous reconnecter.'],
        429 => ['title' => 'Trop de requêtes', 'node' => 'client',
            'message' => 'Vous avez effectué trop de requêtes en peu de temps. Patientez quelques instants avant de réessayer.'],

        // ── 5xx — server / network side ─────────────────────────────────
        500 => ['title' => 'Erreur interne', 'node' => 'server',
            'message' => "Une erreur inattendue est survenue de notre côté. L'équipe technique a été informée. Réessayez dans quelques instants."],
        502 => ['title' => 'Passerelle incorrecte', 'node' => 'network',
            'message' => 'Le serveur a reçu une réponse invalide depuis un service en amont.'],
        503 => ['title' => 'Service indisponible', 'node' => 'server',
            'message' => "L'application est momentanément indisponible (maintenance ou surcharge). Merci de votre patience, le service sera rétabli sous peu."],
        504 => ['title' => 'Délai de passerelle dépassé', 'node' => 'network',
            'message' => "Un service en amont n'a pas répondu dans le délai imparti."],
        505 => ['title' => 'Version HTTP non supportée', 'node' => 'server',
            'message' => "La version du protocole HTTP utilisée n'est pas prise en charge par le serveur."],
    ],
];
