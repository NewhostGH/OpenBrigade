<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Statistiques & bilan annuel
    |--------------------------------------------------------------------------
    */

    // ── index ──────────────────────────────────────────────────────────────
    'title' => 'Statistiques',
    'year_label' => 'Année :',
    'btn_annual_report' => 'Bilan annuel',

    // KPI cards — index
    'kpi_events_label' => 'Activités',
    'kpi_events_sub' => 'en :year',
    'kpi_participants_label' => 'Participations',
    'kpi_participants_sub' => 'cumulées sur l\'année',
    'kpi_hours_label' => 'Heures',
    'kpi_hours_sub' => 'total bénévoles',
    'kpi_members_label' => 'Membres actifs',
    'kpi_members_sub' => ':new nouveau(x) en :year',

    // chart titles — index
    'chart_events_month' => 'Activités par mois',
    'chart_participants_month' => 'Participants par mois',
    'chart_events_type' => 'Répartition par type',
    'chart_new_members' => 'Nouveaux membres (5 ans)',

    // top participants — index
    'top_participants_title' => 'Top 10 participants — :year',
    'th_rank' => '#',
    'th_member' => 'Membre',
    'th_events' => 'Activités',
    'no_data' => 'Aucune donnée.',

    // ── annual report shared (_tabs) ──────────────────────────────────────
    'annual_report_title' => 'Bilan annuel',
    'btn_dashboard' => 'Tableau de bord',
    'btn_download_pdf' => 'Télécharger PDF',
    'tab_generalites' => 'Généralités',
    'tab_activites' => 'Activités opérationnelles',
    'tab_formations' => 'Formations',

    // ── annual-report/overview ─────────────────────────────────────────────
    'overview_page_title' => 'Bilan — Généralités',
    'breadcrumb_generalites' => 'Généralités',
    'overview_intro' => 'Retrouvez le bilan annuel complet du personnel et des moyens — véhicules, matériel et consommables.',

    // overview — personnel
    'personnel_heading' => 'Personnel',
    'kpi_active_members' => 'Membres actifs',
    'kpi_active_members_sub' => 'au :date',
    'kpi_new_members_label' => 'Nouveaux :year',
    'kpi_new_members_sub' => 'engagements',
    'chart_members_group' => 'Répartition par groupe',
    'chart_new_members_5y' => 'Évolution des engagements (5 ans)',

    // overview — véhicules
    'vehicles_heading' => 'Véhicules',
    'kpi_total_vehicles' => 'Total véhicules',
    'kpi_total_vehicles_sub' => 'en parc',
    'chart_vehicles_type' => 'Véhicules par type',
    'vehicles_detail_title' => 'Détail',
    'th_type' => 'Type',
    'th_qty' => 'Quantité',
    'no_vehicles' => 'Aucun véhicule enregistré.',

    // overview — matériel
    'materiel_heading' => 'Matériel',
    'kpi_total_materiel' => 'Total matériel',
    'kpi_total_materiel_sub' => 'articles inventoriés',
    'materiel_by_cat_title' => 'Matériel par catégorie',
    'th_category' => 'Catégorie',
    'no_materiel' => 'Aucun matériel enregistré.',

    // overview — consommables
    'consommables_heading' => 'Consommables',
    'kpi_total_consommables' => 'Total consommables',
    'kpi_total_consommables_sub' => 'articles inventoriés',
    'consommables_by_cat_title' => 'Consommables par catégorie',
    'no_consommables' => 'Aucun consommable enregistré.',

    // ── annual-report/activities ───────────────────────────────────────────
    'activities_page_title' => 'Bilan — Activités',
    'breadcrumb_activites' => 'Activités opérationnelles',
    'activities_intro' => 'Bilan annuel de l\'ensemble des activités opérationnelles de votre structure pour :year.',

    // activities KPI
    'kpi_events_label_bilan' => 'Activités',
    'kpi_events_sub_bilan' => 'en :year',
    'kpi_participants_label_bilan' => 'Participations',
    'kpi_participants_sub_bilan' => 'cumulées',
    'kpi_hours_label_bilan' => 'Heures',
    'kpi_hours_sub_bilan' => 'total bénévoles',

    // activities charts
    'section_monthly' => 'Répartition mensuelle',
    'chart_events_month_bilan' => 'Activités par mois',
    'chart_participants_month_bilan' => 'Participants par mois',
    'section_by_type' => 'Répartition par type',
    'activity_types_title' => 'Types d\'activités',
    'activity_types_detail' => 'Détail par type',
    'no_activities' => 'Aucune activité.',
    'th_type_bilan' => 'Type',
    'th_activities_bilan' => 'Activités',

    // activities top participants
    'section_top10' => 'Top 10 participants',

    // ── annual-report/training ─────────────────────────────────────────────
    'training_page_title' => 'Bilan — Formations',
    'breadcrumb_formations' => 'Formations',
    'training_intro' => 'Liste de toutes les formations prodiguées durant l\'année :year.',

    // training KPI
    'kpi_formations_label' => 'Formations',
    'kpi_formations_sub' => 'sessions en :year',
    'kpi_trainees_label' => 'Stagiaires',
    'kpi_trainees_sub' => 'participations cumulées',
    'kpi_hours_training_sub' => 'de formation dispensées',

    // training charts / sections
    'section_repartition' => 'Répartition',
    'chart_form_month' => 'Formations par mois',
    'chart_form_type' => 'Répartition par type',
    'section_detail' => 'Détail des sessions',
    'th_date' => 'Date',
    'th_intitule' => 'Intitulé',
    'th_lieu' => 'Lieu',
    'th_duree' => 'Durée (h)',
    'th_stagiaires' => 'Stagiaires',
    'training_empty' => 'Aucune formation enregistrée pour :year.',
    'tfoot_total' => 'Total',

];
