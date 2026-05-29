<?php

/*
|--------------------------------------------------------------------------
| Application Navigation
|--------------------------------------------------------------------------
|
| Defines the sidebar navigation tree. Each group and item can carry an
| optional 'permission' key — the integer feature ID checked via
| User::hasPermission(). Items without a permission are visible to any
| authenticated user.
|
| Item URLs use the /legacy/ prefix so they route through the
| LegacyBridgeController. External URLs should start with http/https.
|
| null entries inside 'items' render as visual dividers.
|
*/

return [
    'top' => [

        // ── Personnel ──────────────────────────────────────────────────────
        [
            'code'  => 'personnel',
            'label' => 'Personnel',
            'icon'  => 'users',
            'items' => [
                ['key' => 'personnel.list',        'label' => 'Liste',           'url' => '/personnel',                                                       'icon' => 'list'],
                ['key' => 'personnel.search',      'label' => 'Recherche',       'url' => '/legacy/search_personnel.php',                                    'icon' => 'magnifying-glass', 'permission' => 56],
                ['key' => 'personnel.competences', 'label' => 'Compétences',     'url' => '/legacy/qualifications.php?page=1&pompier=0&action_comp=default', 'icon' => 'certificate',      'permission' => 56],
                ['key' => 'personnel.cotisations', 'label' => 'Cotisations',     'url' => '/legacy/cotisations.php',                                         'icon' => 'receipt',          'permission' => 53],
                ['key' => 'personnel.geoloc',      'label' => 'Géolocalisation', 'url' => '/legacy/gps.php',                                                 'icon' => 'map-marker-alt',   'permission' => 76],
            ],
        ],

        // ── Activités ──────────────────────────────────────────────────────
        [
            'code'  => 'activities',
            'label' => 'Activités',
            'icon'  => 'calendar-alt',
            'items' => [
                ['key' => 'activities.list',     'label' => 'Liste',            'url' => '/evenements',                                        'icon' => 'list-ul',    'permission' => 41],
                ['key' => 'activities.mc',       'label' => 'Main courante',    'url' => '/legacy/evenement_choice.php?ec_mode=MC&page=1',      'icon' => 'ambulance',  'permission' => 52],
                ['key' => 'activities.news',     'label' => 'Actualités',       'url' => '/messages?category=amicale',                         'icon' => 'newspaper',  'permission' => 44],
                ['key' => 'activities.geomap',   'label' => 'Géolocalisation',  'url' => '/legacy/gmaps_evenement.php',                        'icon' => 'map',        'permission' => 76],
            ],
        ],

        // ── Garde ──────────────────────────────────────────────────────────
        [
            'code'  => 'garde',
            'label' => 'Garde',
            'icon'  => 'clipboard-list',
            'items' => [
                ['key' => 'garde.tableau', 'label' => 'Tableau de garde', 'url' => '/garde',                                           'icon' => 'shield-alt',   'permission' => 61],
                ['key' => 'garde.jour',    'label' => 'Garde du jour',    'url' => '/garde',                                           'icon' => 'calendar-day', 'permission' => 61],
            ],
        ],

        // ── Planning ───────────────────────────────────────────────────────
        [
            'code'  => 'planning',
            'label' => 'Planning',
            'icon'  => 'calendar-check',
            'items' => [
                ['key' => 'planning.calendar',    'label' => 'Calendrier',      'url' => '/planning',                                   'icon' => 'calendar'],
                ['key' => 'planning.dispos',      'label' => 'Disponibilités',  'url' => '/legacy/dispo.php',                           'icon' => 'check-square',   'permission' => 38],
                ['key' => 'planning.absences',    'label' => 'Absences',        'url' => '/legacy/indispo_choice.php?tab=2&page=1',     'icon' => 'user-times',     'permission' => 11],
                ['key' => 'planning.repos',       'label' => 'Repos',           'url' => '/legacy/repos_saisie.php',                    'icon' => 'bed',            'permission' => 11],
                ['key' => 'planning.remplace',    'label' => 'Remplacements',   'url' => '/legacy/remplacements.php',                   'icon' => 'exchange-alt',   'permission' => 41],
                ['key' => 'planning.astreintes',  'label' => 'Astreintes',      'url' => '/legacy/astreintes.php',                      'icon' => 'bell',           'permission' => 52],
            ],
        ],

        // ── Clients ────────────────────────────────────────────────────────
        [
            'code'       => 'clients',
            'label'      => 'Clients',
            'icon'       => 'user-circle',
            'permission' => 29,
            'items'      => [
                ['key' => 'clients.list', 'label' => 'Liste', 'url' => '/clients', 'icon' => 'list', 'permission' => 29],
            ],
        ],

        // ── Logistique ─────────────────────────────────────────────────────
        [
            'code'  => 'logistics',
            'label' => 'Logistique',
            'icon'  => 'truck',
            'items' => [
                ['key' => 'logistics.vehicules',    'label' => 'Véhicules',    'url' => '/vehicules',                    'icon' => 'truck',    'permission' => 42],
                ['key' => 'logistics.materiels',    'label' => 'Matériels',    'url' => '/materiels',    'icon' => 'toolbox',  'permission' => 42],
                ['key' => 'logistics.consommables', 'label' => 'Consommables', 'url' => '/consommables', 'icon' => 'boxes',    'permission' => 42],
            ],
        ],

        // ── Communication ──────────────────────────────────────────────────
        [
            'code'  => 'comm',
            'label' => 'Communication',
            'icon'  => 'envelope',
            'items' => [
                ['key' => 'comm.chat',    'label' => 'Chat',    'url' => '/legacy/chat.php',           'icon' => 'comments',  'permission' => 51],
                ['key' => 'comm.alerte',  'label' => 'Alerte',  'url' => '/legacy/alerte_create.php',  'icon' => 'bell',      'permission' => 43],
                ['key' => 'comm.message', 'label' => 'Message', 'url' => '/legacy/mail_create.php',    'icon' => 'paper-plane', 'permission' => 43],
            ],
        ],

        // ── Documents ──────────────────────────────────────────────────────
        [
            'code'       => 'docs',
            'label'      => 'Documents',
            'icon'       => 'folder-open',
            'permission' => 44,
            'items'      => [
                ['key' => 'docs.library', 'label' => 'Bibliothèque', 'url' => '/documents', 'icon' => 'book', 'permission' => 44],
                ['key' => 'docs.photos',  'label' => 'Album photos',  'url' => '/legacy/spgm/index.php',                                   'icon' => 'images', 'permission' => 44],
            ],
        ],

        // ── Organisation ───────────────────────────────────────────────────
        [
            'code'       => 'orga',
            'label'      => 'Organisation',
            'icon'       => 'sitemap',
            'permission' => 52,
            'items'      => [
                ['key' => 'orga.sections',    'label' => 'Sections',      'url' => '/legacy/departement.php',   'icon' => 'layer-group',  'permission' => 52],
                ['key' => 'orga.organi',      'label' => 'Organigramme',  'url' => '/organisation',             'icon' => 'project-diagram', 'permission' => 52],
                ['key' => 'orga.map',         'label' => 'Cartographie',  'url' => '/legacy/jvectormap.php',    'icon' => 'map',          'permission' => 27],
            ],
        ],

        // ── Statistiques ───────────────────────────────────────────────────
        [
            'code'       => 'stats',
            'label'      => 'Statistiques',
            'icon'       => 'chart-bar',
            'permission' => 27,
            'items'      => [
                ['key' => 'stats.graphiques', 'label' => 'Graphiques',            'url' => '/statistiques',                    'icon' => 'chart-line',  'permission' => 27],
                ['key' => 'stats.reporting',  'label' => 'Reporting',             'url' => '/legacy/export.php',               'icon' => 'file-export', 'permission' => 27],
                ['key' => 'stats.cotis',      'label' => 'Cotisations', 'url' => '/legacy/report_cotisations.php',   'icon' => 'coins',       'permission' => 53],
                ['key' => 'stats.bilans',     'label' => 'Bilans annuels',        'url' => '/legacy/bilans.php',               'icon' => 'chart-pie',   'permission' => 27],
            ],
        ],

        // ── Administration ─────────────────────────────────────────────────
        [
            'code'       => 'admin',
            'label'      => 'Administration',
            'icon'       => 'cog',
            'permission' => 5,
            'items'      => [
                ['key' => 'admin.configuration', 'label' => 'Général',       'url' => '/legacy/configuration.php',    'icon' => 'sliders-h',  'permission' => 14],
                ['key' => 'admin.parametrage',   'label' => 'Paramétrage',   'url' => '/legacy/parametrage.php',      'icon' => 'wrench',     'permission' => 5],
                null,
                ['key' => 'admin.habilitations', 'label' => 'Habilitations', 'url' => '/legacy/habilitations.php',    'icon' => 'id-badge',   'permission' => 9],
                ['key' => 'admin.monitoring',    'label' => 'Monitoring',    'url' => '/legacy/history.php?lccode=U', 'icon' => 'history',    'permission' => 49],
                ['key' => 'admin.sauvegarde',    'label' => 'Sauvegarde',    'url' => '/legacy/restore.php',          'icon' => 'database',   'permission' => 14],
            ],
        ],

        // ── Modules ────────────────────────────────────────────────────────
        [
            'code'       => 'modules',
            'label'      => 'Modules',
            'icon'       => 'puzzle-piece',
            'permission' => 78,
            'items'      => [
                ['key' => 'modules.list', 'label' => 'Liste', 'url' => '/legacy/addons.php', 'icon' => 'th', 'permission' => 78],
            ],
        ],

    ],
];
