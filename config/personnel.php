<?php

// project: OpenBrigade

/*
|--------------------------------------------------------------------------
| Personnel lookup maps (single source of truth)
|--------------------------------------------------------------------------
|
| Labels, badge styling, and civility prefixes for the pompier domain.
| Defined once here and referenced via config('personnel.*') from
| controllers, views, and exports. Never re-declare these arrays inline.
|
| Status labels live ONCE in 'statuts'. Badge classes ('statut_badge_class')
| and the editable subset ('statuts_assignable') reference those codes — they
| never restate the labels. Helpers on the Personnel model zip labels+classes
| into the [label, class] pairs that <x-ob-table> / ob-badge expect.
|
*/

return [

    // P_STATUT code → human label (full set, single source for every label).
    'statuts' => [
        'BEN' => 'Bénévole',
        'EXT' => 'Externe',
        'PRES' => 'Prestataire',
        'SAL' => 'Salarié',
        'ADH' => 'Adhérent',
        'INT' => 'Interne',
    ],

    // P_STATUT codes offered in the edit form (order matters; subset of statuts).
    'statuts_assignable' => ['BEN', 'EXT', 'PRES', 'SAL', 'ADH'],

    // P_STATUT code → badge css class. Unknown codes fall back to the INT style.
    'statut_badge_class' => [
        'BEN' => 'ob-badge-ben',
        'EXT' => 'ob-badge-ext',
        'PRES' => 'ob-badge-pres',
        'INT' => 'ob-badge-int',
        'SAL' => 'ob-badge-sal',
        'ADH' => 'ob-badge-adh',
    ],

    // Derived état (see Personnel::getEtatAttribute) → [label, badge class].
    // État is not user-editable, so label and code coincide.
    'etat_badges' => [
        'Actif' => ['Actif',   'ob-badge-actif'],
        'Archivé' => ['Archivé', 'ob-badge-archive'],
        'Bloqué' => ['Bloqué',  'ob-badge-bloqued'],
    ],

    // P_CIVILITE → display prefix.
    'civilites' => [
        1 => 'M.',
        2 => 'Mme',
        3 => 'Dr.',
        4 => 'Pr.',
    ],

];
