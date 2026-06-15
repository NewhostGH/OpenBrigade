<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Obsolete features
    |--------------------------------------------------------------------------
    |
    | Legacy feature IDs (fonctionnalite.F_ID) whose functionality will NOT be
    | ported to OpenBrigade. They are still listed in the habilitation matrices
    | and "Mes droits", flagged with an "obsolète" badge so administrators know
    | they have no effect in the native app.
    |
    | Add the F_IDs of retired features here, e.g. [76].
    |
    */

    'obsolete_features' => [
        // Cartes Google Maps (76) // port to Leaflet
    ],

    /*
    |--------------------------------------------------------------------------
    | Super-admin
    |--------------------------------------------------------------------------
    |
    | Super-admin is an ACCOUNT-LEVEL flag (pompier.P_SUPERADMIN), not a group.
    | A super-admin bypasses every permission check — uncappable by section
    | ceilings — and at least one is guaranteed to exist at all times (the
    | controllers refuse to clear/remove the last one). See PermissionResolver.
    |
    | The dedicated seeded super-admin account uses this login code.
    |
    */

    'superadmin_code' => 'superadmin',

    /*
    |--------------------------------------------------------------------------
    | Base groups (reserved id block)
    |--------------------------------------------------------------------------
    |
    | The four canonical global groups. Their ids are reserved and the groups
    | are protected from rename/delete in the admin UI. Their grants are SEEDED
    | with sensible defaults (see "classification" below) and then freely
    | editable per-permission in Admin ▸ Permissions.
    |
    | `default` selects the seeded grant set computed by BaseHabilitations.
    |
    */

    'base_groups' => [
        2 => ['name' => 'Admin',   'usage' => 'internes', 'ordering' => 10, 'default' => 'admin'],
        3 => ['name' => 'Auditor', 'usage' => 'internes', 'ordering' => 20, 'default' => 'auditor'],
        6 => ['name' => 'User',    'usage' => 'internes', 'ordering' => 30, 'default' => 'user'],
        7 => ['name' => 'Guest',   'usage' => 'all',      'ordering' => 40, 'default' => 'guest'],
    ],

    // The "accès interdit" block sentinel — kept as-is, never a base group.
    'block_group_id' => -1,

    /*
    | Legacy base-group id => new base-group id, used by the rebuild migration
    | to remap existing memberships (ob_personnel_group, pompier.GP_ID/GP_ID2)
    | off the obsolete legacy groups before dropping them. Custom admin-created
    | groups (is_system = false, id outside the reserved block) are untouched.
    */
    'legacy_group_map' => [
        4 => 2,   // admin   → Admin
        0 => 7,   // public  → Guest
        5 => 7,   // Externe → Guest (usage externes)
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission classification (drives SEEDED defaults only)
    |--------------------------------------------------------------------------
    |
    | Each legacy permission (fonctionnalite.F_ID) is classified on two axes so
    | the base-group default grants can be derived instead of hand-listed. This
    | is NOT a runtime enforcement path — runtime access stays the granular ACL
    | (per-permission allow/deny + section ceilings + per-user overrides).
    |
    |  - domain  = config when TF_ID ∈ config_categories; else data.
    |  - critical = (F_FLAG == 1)  → super-admin territory, never in a base group.
    |  - read    = F_ID ∈ read_features (look, don't touch).
    |
    */

    // type_fonctionnalite ids that are configuration/administration of the app.
    'config_categories' => [1, 2, 3, 11], // configuration, sécurité, paramétrage, module

    // Read-oriented permissions (viewing, reporting, audit, notifications).
    'read_features' => [
        0, 20, 27, 40, 41, 42, 44, 45, 49, 52, 56, 59, 61, 76,
        21, 32, 33, 34, 35, 50, 57, 58, 60, 72, // TF 10 notifications
    ],

    /*
    |--------------------------------------------------------------------------
    | Section roles per organisation type
    |--------------------------------------------------------------------------
    |
    | Roles are section-scoped (ob_user_assignment) and tagged with the
    | organisation type they belong to (ob_group.org_type, keyed by
    | config('brigade.organisation_types')). The seeder creates the role set for
    | every type; a future setup wizard will let an admin activate one type's set.
    |
    | Grants are expressed as named archetypes (data-domain subsets) so the
    | per-type variance is mostly naming, not duplicated F_ID lists.
    |
    */

    'role_archetypes' => [
        // Full operational management of a section.
        'chef' => [
            1, 2, 4, 10, 12, 15, 16, 17, 70, 71, 26, 28, 29, 30, 37, 47, 48, 53, 73,
            40, 41, 42, 44, 52, 56, 59, 61,
        ],
        // Deputy: manage day-to-day, no finance/sensitive admin.
        'adjoint' => [
            2, 10, 12, 15, 16, 17, 70, 71, 28, 47, 48,
            40, 41, 42, 44, 52, 56, 61,
        ],
        // Training / competences.
        'formation' => [
            4, 48, 41, 42, 44, 52, 56, 61,
        ],
        // Vehicles + material + consumables.
        'logistique' => [
            17, 70, 71, 42, 44, 52, 61,
        ],
        // Secretariat / treasury.
        'secretariat' => [
            16, 29, 53, 73, 47, 40, 41, 44, 52, 56, 59,
        ],
        // Plain member: self-service + read.
        'membre' => [
            11, 38, 39, 43, 51, 77, 41, 42, 44, 52, 56, 61,
        ],
    ],

    // org_type (config/brigade.php key) => [ [name, archetype], ... ].
    // org_type 0 (Sans préconfiguration) is the generic fallback set.
    'roles_by_org_type' => [
        0 => [
            ['name' => 'Chef de section', 'archetype' => 'chef'],
            ['name' => 'Adjoint',         'archetype' => 'adjoint'],
            ['name' => 'Membre',          'archetype' => 'membre'],
        ],
        1 => [ // Association de secourisme
            ['name' => 'Chef de section', 'archetype' => 'chef'],
            ['name' => 'Secouriste',      'archetype' => 'membre'],
            ['name' => 'Formateur',       'archetype' => 'formation'],
            ['name' => 'Logistique',      'archetype' => 'logistique'],
            ['name' => 'Secrétaire',      'archetype' => 'secretariat'],
        ],
        2 => [ // Service d'incendie et Secours
            ['name' => 'Chef de centre', 'archetype' => 'chef'],
            ['name' => "Chef d'agrès",   'archetype' => 'adjoint'],
            ['name' => 'Équipier',       'archetype' => 'membre'],
            ['name' => 'Formation',      'archetype' => 'formation'],
            ['name' => 'Logistique',     'archetype' => 'logistique'],
        ],
        3 => [ // SDIS
            ['name' => 'Chef de centre', 'archetype' => 'chef'],
            ['name' => "Chef d'agrès",   'archetype' => 'adjoint'],
            ['name' => 'Sapeur-pompier', 'archetype' => 'membre'],
            ['name' => 'Formation',      'archetype' => 'formation'],
            ['name' => 'Logistique',     'archetype' => 'logistique'],
        ],
        4 => [ // Armée
            ['name' => 'Chef de section',     'archetype' => 'chef'],
            ['name' => 'Sous-officier',       'archetype' => 'adjoint'],
            ['name' => 'Militaire du rang',   'archetype' => 'membre'],
            ['name' => 'Logistique',          'archetype' => 'logistique'],
        ],
        5 => [ // SSLIA
            ['name' => 'Chef de section',       'archetype' => 'chef'],
            ['name' => "Pompier d'aérodrome",   'archetype' => 'membre'],
            ['name' => 'Logistique',            'archetype' => 'logistique'],
        ],
        6 => [ // Hôpital
            ['name' => 'Cadre',      'archetype' => 'chef'],
            ['name' => 'Soignant',   'archetype' => 'membre'],
            ['name' => 'Formation',  'archetype' => 'formation'],
            ['name' => 'Logistique', 'archetype' => 'logistique'],
        ],
    ],

];
