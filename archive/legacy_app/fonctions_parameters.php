<?php
  # project: eBrigade
  # homepage: https://ebrigade.app
  # version: 5.3

  # Copyright (C) 2004, 2021 Nicolas MARCHE (eBrigade Technologies)
  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  #
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

//=====================================================================
// check session parameters
//=====================================================================

function get_session_parameters(){
 
    global $nbsections, $nbmaxlevels, $mysection, $defaultsectionorder, $gardes, $syndicate;
    global $order, $category, $msgfilter, $filter, $filter2, $company, $subsections, $typecompany, 
        $old, $mad, $type_materiel, $position, $typequalif, $type_evenement, $sectionorder, $canceled,
        $dtdb, $dtfn, $td, $vehicule, $matos, $catmessage, $catmateriel, $catGrade, $lccode, $lcid, $ltcode,
        $statut, $type_indispo, $person, $validation, $year, $yearreport, $yeardoc, $month, $type_astreinte, $search, $searchdep, $exp,
        $type_paiement, $periode, $paid, $include_old, $check_all, $niv, $week, $view,
        $dossier, $modevictime, $compte_a_debiter, $renforts, $type_conso, $catconso, $type_element, $competence, $stockonly,
        $tableau_garde_display_mode, $ec_mode, $evenement_facture, $evenement_date, $evenement_periode, $evenement_show_competences, $evenement_show_absents, $evenement_victime,
        $type_victime, $in_cav, $a_reguler, $sectioninscription, $sectionresponsable, $horaires_tableau_garde, $type_event, $competence, $status, $autorefresh,
        $show_section, $birthdate, $birthplace, $firstname, $show_indispos, $show_spp, $nationality, $horaire_list_mode, $display_order,
        $action_comp,$expand, $day_planning, $old_included, $custom_subsections, $equipe;
    global $chxCal;
    global $dbc, $grades;

    if (isset($_SESSION['SES_SECTION'])) $mysection=$_SESSION['SES_SECTION'];
    else $mysection=0;
    
    if (isset($_SESSION['SES_FAVORITE'])) $favoritesection=$_SESSION['SES_FAVORITE'];
    else $favoritesection=$mysection;
    
    // day_planning page planning
    if (isset($_GET["day_planning"])) {
        $day_planning=intval($_GET["day_planning"]);
        $_SESSION['day_planning'] = $day_planning;
    }
    else if ( isset($_SESSION['day_planning']) ) {
        $day_planning=$_SESSION['day_planning'];
    }
    else {
        $day_planning=0;
    }
    
    // expand
    if (isset($_GET["expand"])) {
        $expand=$_GET["expand"];
        $_SESSION['expand'] = $expand;
    }
    else if ( isset($_SESSION['expand']) ) {
        $expand=$_SESSION['expand'];
    }
    else {
        $expand='false';
    }
    
    // gardes montrer indisponibles
    if (isset($_GET["show_indispos"])) {
        $show_indispos=intval($_GET["show_indispos"]);
        $_SESSION['show_indispos'] = $show_indispos;
    }
    else if ( isset($_SESSION['show_indispos']) ) {
        $show_indispos=$_SESSION['show_indispos'];
    }
    else {
        $show_indispos=0;
    }
    
    // gardes choix ordre affichage sur la page evenement_garde
    if (isset($_GET["display_order"])) {
        $display_order=$_GET["display_order"];
        $_SESSION['display_order'] = $display_order;
    }
    else if ( isset($_SESSION['display_order']) ) {
        $display_order=$_SESSION['display_order'];
    }
    else {
        if ( $grades == 1 ) $display_order='grade';
        else $display_order='name';
    }
    
    // competences
    if (isset($_GET["action_comp"])) {
        $action_comp=$_GET["action_comp"];
        $_SESSION['action_comp'] = $action_comp;
    }
    else if ( isset($_SESSION['action_comp']) ) {
        $action_comp=$_SESSION['action_comp'];
    }
    else {
        $action_comp='default';
    }
    
    // gardes montrer SPP
    if (isset($_GET["show_spp"])) {
        $show_spp=intval($_GET["show_spp"]);
        $_SESSION['show_spp'] = $show_spp;
    }
    else if ( isset($_SESSION['show_spp']) ) {
        $show_spp=$_SESSION['show_spp'];
    }
    else {
        $show_spp=1;
    }
    
    // horaires list : affichage par semaine (W) ou par jour (D)
    if (isset($_GET["horaire_list_mode"])) {
        $horaire_list_mode=$_GET["horaire_list_mode"];
        $_SESSION['horaire_list_mode'] = $horaire_list_mode;
    }
    else if ( isset($_SESSION['horaire_list_mode']) ) {
        $horaire_list_mode=$_SESSION['horaire_list_mode'];
    }
    else {
        $horaire_list_mode='W';
    }
    if ( $horaire_list_mode <> 'W' and $horaire_list_mode <> 'D' )
        $horaire_list_mode='W';
    
    // trombinoscope choix options
    if (isset($_GET["show_section"])) {
        $show_section=intval($_GET["show_section"]);
        $_SESSION['show_section'] = $show_section;
    }
    else if ( isset($_SESSION['show_section']) ) {
        $show_section=$_SESSION['show_section'];
    }
    else {
        $show_section=0;
    }
    if (isset($_GET["birthdate"])) {
        $birthdate=intval($_GET["birthdate"]);
        $_SESSION['birthdate'] = $birthdate;
    }
    else if ( isset($_SESSION['birthdate']) ) {
        $birthdate=$_SESSION['birthdate'];
    }
    else {
        $birthdate=0;
    }
    if (isset($_GET["birthplace"])) {
        $birthplace=intval($_GET["birthplace"]);
        $_SESSION['birthplace'] = $birthplace;
    }
    else if ( isset($_SESSION['birthplace']) ) {
        $birthplace=$_SESSION['birthplace'];
    }
    else {
        $birthplace=0;
    }
    if (isset($_GET["firstname"])) {
        $firstname=intval($_GET["firstname"]);
        $_SESSION['firstname'] = $firstname;
    }
    else if ( isset($_SESSION['firstname']) ) {
        $firstname=$_SESSION['firstname'];
    }
    else {
        $firstname=0;
    }
    if (isset($_GET["nationality"])) {
        $nationality=intval($_GET["nationality"]);
        $_SESSION['nationality'] = $nationality;
    }
    else if ( isset($_SESSION['nationality']) ) {
        $nationality=$_SESSION['nationality'];
    }
    else {
        $nationality=0;
    }
    
    //tableau_garde_display_mode utilise pour tableau de garde 
    // 2 valeurs possibles: month, week
    if (isset ($_GET["tableau_garde_display_mode"])) {
        $tableau_garde_display_mode=secure_input($dbc,$_GET["tableau_garde_display_mode"]);
        $_SESSION['tableau_garde_display_mode'] = $tableau_garde_display_mode;
    }
    else if ( isset($_SESSION['tableau_garde_display_mode']) ) {
        $tableau_garde_display_mode=$_SESSION['tableau_garde_display_mode'];
    }
    else {
        $tableau_garde_display_mode='month';
    }
    // afficher ou pas les horaires sur le tableau de garde
    if (isset ($_GET["horaires_tableau_garde"])) {
        $horaires_tableau_garde=intval($_GET["horaires_tableau_garde"]);
        $_SESSION['horaires_tableau_garde'] = $horaires_tableau_garde;
    }
    else if ( isset($_SESSION['horaires_tableau_garde']) ) {
        $horaires_tableau_garde=$_SESSION['horaires_tableau_garde'];
    }
    else {
        $horaires_tableau_garde=0;
    }
    
    //liste événements 2 modes, default et MC - seulement les mains courantes
    if (isset ($_GET["ec_mode"])) {
        $ec_mode=secure_input($dbc,$_GET["ec_mode"]);
        $_SESSION['ec_mode'] = $ec_mode;
    }
    else if ( isset($_SESSION['ec_mode']) ) {
        $ec_mode=$_SESSION['ec_mode'];
    }
    else {
        $ec_mode='default';
    }
    
    //status pour remplacement gardes
    if (isset ($_GET["status"])) {
        $status=secure_input($dbc,$_GET["status"]);
        $_SESSION['status'] = $status;
    }
    else if ( isset($_SESSION['status']) ) {
        $status=$_SESSION['status'];
    }
    else {
        $status='ALL';
    }
    
    //autorefresh pour main courante DPS
    if (isset ($_GET["autorefresh"])) {
        $autorefresh=intval($_GET["autorefresh"]);
        $_SESSION['autorefresh'] = $autorefresh;
    }
    else if ( isset($_SESSION['autorefresh']) ) {
        $autorefresh=$_SESSION['autorefresh'];
    }
    else {
        $autorefresh=0;
    }
    
    //sectioninscription pour evenement_detail
     if (isset ($_GET["sectioninscription"])) {
        $sectioninscription=intval($_GET["sectioninscription"]);
        $_SESSION['sectioninscription'] = $sectioninscription;
    }
    else if ( isset($_SESSION['sectioninscription']) ) {
        $sectioninscription=$_SESSION['sectioninscription'];
    }
    else {
        if ( $mysection > 0 ) $sectioninscription = $mysection;
        else $sectioninscription='-1';
    }
    
    //sectionresponsable pour upd_responsable
     if (isset ($_GET["sectionresponsable"])) {
        $sectionresponsable=intval($_GET["sectionresponsable"]);
        $_SESSION['sectionresponsable'] = $sectionresponsable;
    }
    else if ( isset($_SESSION['sectionresponsable']) ) {
        $sectionresponsable=$_SESSION['sectionresponsable'];
    }
    else {
        if ( $mysection > 0 ) $sectionresponsable = $mysection;
        else $sectionresponsable='-1';
    }

    // evenement_victime utilise pour liste_victimes 
    if (isset ($_GET["evenement_victime"])) {
        $evenement_victime=intval($_GET["evenement_victime"]);
        $_SESSION['evenement_victime'] = $evenement_victime;
    }
    else if ( isset($_SESSION['evenement_victime']) ) {
        $evenement_victime=$_SESSION['evenement_victime'];
    }
    else {
        $evenement_victime='0';
    }
    
    // type_victime: ALL, intervention ou cav (centre accueil victime)
    if (isset ($_GET["type_victime"])) {
        $type_victime=secure_input($dbc, $_GET["type_victime"]);
        $_SESSION['type_victime'] = $type_victime;
    }
    else if ( isset($_SESSION['type_victime']) ) {
        $type_victime=$_SESSION['type_victime'];
    }
    else {
        $type_victime='ALL';
    }

    // in_cav 0 ou 1 pour les victimes en cours de traitement dans un CAV
    if (isset ($_GET["in_cav"])) {
        $in_cav=intval($_GET["in_cav"]);
        $_SESSION['in_cav'] = $in_cav;
    }
    else if ( isset($_SESSION['in_cav']) ) {
        $in_cav=$_SESSION['in_cav'];
    }
    else {
        $in_cav='0';
    }
    
    // a_reguler 0 ou 1 pour les victimes a voir par medecin dans un CAV
    if (isset ($_GET["a_reguler"])) {
        $a_reguler=intval($_GET["a_reguler"]);
        $_SESSION['a_reguler'] = $a_reguler;
    }
    else if ( isset($_SESSION['a_reguler']) ) {
        $a_reguler=$_SESSION['a_reguler'];
    }
    else {
        $a_reguler='0';
    }
    
    // evenement_facture utilise pour configuration evenements facturable 
    if (isset ($_GET["evenement_facture"])) {
        $evenement_facture=intval($_GET["evenement_facture"]);
        $_SESSION['evenement_facture'] = $evenement_facture;
    }
    else if ( isset($_SESSION['evenement_facture']) ) {
        $evenement_facture=$_SESSION['evenement_facture'];
    }
    else {
        $evenement_facture='0';
    }
    
    // evenement_date utilise pour afficher le personnel d'un long événement pour un jour précis
    if (isset ($_GET["evenement_date"])) {
        $evenement_date=secure_input($dbc, $_GET["evenement_date"]);
        $_SESSION['evenement_date'] = $evenement_date;
    }
    else if ( isset($_SESSION['evenement_date']) ) {
        $evenement_date=$_SESSION['evenement_date'];
    }
    else {
        $evenement_date='';
    }
    // evenement_periode utilise pour afficher le personnel d'une garde sur une période 1 ou 2
    if (isset ($_GET["evenement_periode"])) {
        $evenement_periode=intval($_GET["evenement_periode"]);
        $_SESSION['evenement_periode'] = $evenement_periode;
    }
    else if ( isset($_SESSION['evenement_periode']) ) {
        $evenement_periode=$_SESSION['evenement_periode'];
    }
    else {
        $evenement_periode='0';
    }
    
    // evenement_competences utilise pour afficher ou masquer les compétences sur événement
    if (isset ($_GET["evenement_show_competences"])) {
        $evenement_show_competences=intval($_GET["evenement_show_competences"]);
        $_SESSION['evenement_show_competences'] = $evenement_show_competences;
    }
    else if ( isset($_SESSION['evenement_show_competences']) ) {
        $evenement_show_competences=intval($_SESSION['evenement_show_competences']);
    }
    else {
        $evenement_show_competences=0;
    }
    
    // evenement_show_absents utilise pour afficher ou masquer les absents sur événement
    if (isset ($_GET["evenement_show_absents"])) {
        $evenement_show_absents=intval($_GET["evenement_show_absents"]);
        $_SESSION['evenement_show_absents'] = $evenement_show_absents;
    }
    else if ( isset($_SESSION['evenement_show_absents']) ) {
        $evenement_show_absents=intval($_SESSION['evenement_show_absents']);
    }
    else {
        $evenement_show_absents=0;
    }

    //stockonly utilise pour consommables
    if (isset ($_GET["stockonly"])) {
        $stockonly=intval($_GET["stockonly"]);
        $_SESSION['stockonly'] = $stockonly;
    }
    else if ( isset($_SESSION['stockonly']) ) {
        $stockonly=$_SESSION['stockonly'];
    }
    else {
        $stockonly='1';
    }
    
    //competence, utilise sur carte GPS et liste des événements formations
    if (isset ($_GET["competence"])) {
        $competence=intval($_GET["competence"]);
        $_SESSION['competence'] = $competence;
    }
    else if ( isset($_SESSION['competence']) ) {
        $competence=$_SESSION['competence'];
    }
    else {
        $competence='0';
    }    
    
    // mode affichage fiche bilan victime
    if (isset ($_POST["modevictime"])) {
        $modevictime=(secure_input($dbc,$_POST["modevictime"]));
        $_SESSION['modevictime'] = $modevictime;
    }
    if (isset ($_GET["modevictime"])) {
        $modevictime=(secure_input($dbc,$_GET["modevictime"]));
        $_SESSION['modevictime'] = $modevictime;
    }
    else if ( isset($_SESSION['modevictime']) ) {
        $modevictime=$_SESSION['modevictime'];
    }
    else {
        $modevictime='simple';
    }
    
    // compte_a_debiter (pour les virements)
    if (isset ($_GET["compte_a_debiter"])) {
        $compte_a_debiter=intval($_GET["compte_a_debiter"]);
        $_SESSION['compte_a_debiter'] = $compte_a_debiter;
    }
    else if ( isset($_SESSION['compte_a_debiter']) ) {
        $compte_a_debiter=intval($_SESSION['compte_a_debiter']);
    }
    else {
        $compte_a_debiter='0';
    }
    
    // dossier pour documents
    if (isset ($_GET["dossier"])) {
        $dossier=intval($_GET["dossier"]);
        $_SESSION['dossier'] = $dossier;
    }
    else if ( isset($_SESSION['dossier']) ) {
        $dossier=$_SESSION['dossier'];
    }
    else {
        $dossier=0;
    }
    
    // Calendrier perso
    if (isset ($_GET["chxCal"])) {
        $chxCal=$_GET["chxCal"];
        $_SESSION['chxCal'] = $chxCal;
    }
    else if ( isset($_SESSION['chxCal']) ) {
        $chxCal=$_SESSION['chxCal'];
    }
    else {
        $chxCal=array();
    }
    
     // statut personnel (absences)
    if (isset ($_GET["statut"])) {
        $statut=secure_input($dbc,$_GET["statut"]);
        $_SESSION['statut'] = $statut;
    }
    else if ( isset($_SESSION['statut']) ) {
        $statut=$_SESSION['statut'];
    }
    else {
        $statut='ALL';
    }
     // type indisponibilite (absences)
    if (isset ($_GET["type_indispo"])) {
        $type_indispo=secure_input($dbc,$_GET["type_indispo"]);
        $_SESSION['type_indispo'] = $type_indispo;
    }
    else if ( isset($_SESSION['type_indispo']) ) {
        $type_indispo=$_SESSION['type_indispo'];
    }
    else {
        $type_indispo='ALL';
    }
    
    //view (horaires)
    if (isset ($_GET["view"])) {
        $view=secure_input($dbc,$_GET["view"]);
        $_SESSION['view'] = $view;
    }
    else if ( isset($_SESSION['view']) ) {
        $view=$_SESSION['view'];
    }
    else {
        $view='week';
    }
    
     //paid (cotisations)
    if (isset ($_GET["paid"])) {
        $paid=intval($_GET["paid"]);
        $_SESSION['paid'] = $paid;
    }
    else if ( isset($_SESSION['paid']) ) {
        $paid=$_SESSION['paid'];
    }
    else {
        $paid='2';
    }
    
     //niv (niveau organigramme)
    if (isset ($_GET["niv"])) {
        $niv=intval($_GET["niv"]);
        $_SESSION['niv'] = $niv;
    }
    else if ( isset($_SESSION['niv']) ) {
        $niv=$_SESSION['niv'];
    }
    else {
        if ( $syndicate == 1 ) $niv=3;
        else $niv=0;
    }
    
    //check_all (cotisations)
    if ( $paid > 0 ) $_SESSION['check_all'] = 0;
    else if (isset ($_GET["check_all"])) {
        $check_all=intval($_GET["check_all"]);
        $_SESSION['check_all'] = $check_all;
    }
    else if ( isset($_SESSION['check_all']) ) {
        $check_all=$_SESSION['check_all'];
    }
    else {
        $check_all=0;
    }
    
     //include_old (cotisations)
    if (isset ($_GET["include_old"])) {
        $include_old=intval($_GET["include_old"]);
        $_SESSION['include_old'] = $include_old;
    }
    else if ( isset($_SESSION['include_old']) ) {
        $include_old=$_SESSION['include_old'];
    }
    else { 
         $include_old=1;
    }
    
     // type paiement (cotisations)
    if (isset ($_GET["type_paiement"])) {
        $type_paiement=secure_input($dbc,$_GET["type_paiement"]);
        $_SESSION['type_paiement'] = $type_paiement;
    }
    else if ( isset($_SESSION['type_paiement']) ) {
        $type_paiement=$_SESSION['type_paiement'];
    }
    else {
        $type_paiement='ALL';
    }
    // periode 
    if (isset ($_GET["periode"])) {
        $periode=secure_input($dbc,$_GET["periode"]);
        $_SESSION['periode'] = $periode;
    }
    else if ( isset($_SESSION['periode']) ) {
        $periode=$_SESSION['periode'];
    }
    else {
        $periode='A';
    }    
    
     // personne (absences & astreintes & horaires)
    if (isset ($_GET["person"])) {
        $person=secure_input($dbc,$_GET["person"]);
        $_SESSION['person'] = $person;
    }
    else if ( isset($_SESSION['person']) ) {
        $person=$_SESSION['person'];
    }
    else {
        $person='ALL';
    }
    
     // validation (absences)
    if (isset ($_GET["validation"])) {
        $validation=secure_input($dbc,$_GET["validation"]);
        $_SESSION['validation'] = $validation;
    }
    else if ( isset($_SESSION['validation']) ) {
        $validation=$_SESSION['validation'];
    }
    else {
        $validation='ALL';
    }
    
    // recherche (événements)
    if (isset ($_GET["search"])) {
        $search=secure_input($dbc,$_GET["search"]);
        $_SESSION['search'] = $search;
    }
    else if ( isset($_SESSION['search']) ) {
        $search=$_SESSION['search'];
    }
    else {
        $search='';
    }

    // recherche (département)
    if (isset ($_GET["searchdep"])) {
        $searchdep=secure_input($dbc,$_GET["searchdep"]);
        $_SESSION['searchdep'] = $searchdep;
    }
    else if ( isset($_SESSION['searchdep']) ) {
        $searchdep=$_SESSION['searchdep'];
    }
    else {
        $searchdep='';
    }
    
    // type astreinte (astreintes)
    if (isset ($_GET["type_astreinte"])) {
        $type_astreinte=intval($_GET["type_astreinte"]);
        $_SESSION['type_astreinte'] = $type_astreinte;
    }
    else if ( isset($_SESSION['type_astreinte']) ) {
        $type_astreinte=$_SESSION['type_astreinte'];
    }
    else {
        $type_astreinte='0';
    }
     
    // order
    if (isset ($_GET["order"])) {
          $order=secure_input($dbc,$_GET["order"]);
         $_SESSION['order'] = $order;
    }
    else if ( isset($_SESSION['order']) ) {
        $order=$_SESSION['order'];
    }
    else {
         $_SESSION['order'] = '';
         $order='';
    }

    // section
    if (isset ($_GET["filter"])) {
        $filter=intval($_GET["filter"]);
        $_SESSION['filter'] = $filter;
    }
    else if ( isset($_POST['filter']) ) {
        $filter=$_POST['filter'];
    }
    else if ( isset($_SESSION['filter']) ) {
        $filter=$_SESSION['filter'];
    }
    else {
        $filter=$favoritesection;
    }
    
    // equipe (tableau de garde type de garde)
    if (isset($_GET["equipe"])) {
        $equipe=intval($_GET["equipe"]);
        $_SESSION['equipe'] = $equipe;
    }
    else if ( isset($_SESSION['equipe']) ) {
        $equipe=$_SESSION['equipe'];
    }
    else {
        if ( $gardes == 0 ) $equipe=1;
        else {
            if ( $nbsections > 0 ) {
                $query_default="select count(EQ_ID) from type_garde where EQ_DEFAULT=1";
                $numRows = $dbc->query($query_default)->fetch_row()[0];
    
                if($numRows == 0) $query="select min(EQ_ID) from type_garde";
                else $query="select EQ_ID from type_garde where EQ_DEFAULT=1";
            }
            else {
                $query_default="select count(EQ_ID) from type_garde where EQ_DEFAULT=1 and S_ID=".$filter;
                $numRows = $dbc->query($query_default)->fetch_row()[0];

                if($numRows == 0 ) $query="select min(EQ_ID) from type_garde where S_ID=".$filter;
                else $query="select EQ_ID from type_garde where EQ_DEFAULT=1 and S_ID=".$filter;
            }
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $equipe=intval($row["0"]);
        }
    }
    
    // section message
    if (isset ($_GET["msgfilter"])) {
        $msgfilter=intval($_GET["msgfilter"]);
        $_SESSION['msgfilter'] = $msgfilter;
    }
    else if ( isset($_SESSION['msgfilter']) ) {
        $msgfilter=$_SESSION['msgfilter'];
    }
    else {
        $msgfilter=$mysection;
    }

    // category
    if ( isset($_GET["category"])) {
         $category=secure_input($dbc,$_GET["category"]);
    }
    else if ( isset($_SESSION['category']) ) {
        $category=$_SESSION['category'];
    }
    else $category='ALL';
    $_SESSION['category'] = $category;

    // company
    if ( isset($_GET["company"])) {
         $company=secure_input($dbc,$_GET["company"]);
         if ( $company <> '-1' ) $company=intval($_GET["company"]);
    }
    else if ( isset($_SESSION['company']) ) {
        $company=$_SESSION['company'];
    }
    else $company=-1;
    $_SESSION['company'] = $company;

    // subsections
    if ( isset ($_GET["subsections"])) {
         $_SESSION['subsections'] = intval($_GET["subsections"]);
         $subsections=intval($_GET["subsections"]);
    }
    else if ( isset($_SESSION['subsections']) ) {
        $subsections=$_SESSION["subsections"];
    }
    else {
        if ( isset($custom_subsections)) $subsections = intval($custom_subsections);
        else $subsections=1;
    }
    
    // sectionorder
    if (isset ($_GET["sectionorder"])) {
        $_SESSION['sectionorder'] = secure_input($dbc,$_GET["sectionorder"]);
        $sectionorder=$_GET["sectionorder"];
    }
    else if ( isset($_SESSION['sectionorder']) ) {
        $sectionorder=$_SESSION['sectionorder'];
    }
    else {
        $sectionorder=$defaultsectionorder;
    }
    
    // show old
    if (isset ($_GET["old"])) {
        $old=intval($_GET["old"]);
        $_SESSION['old'] = $old;
    }
    else if ( isset($_SESSION['old']) ) {
        $old=$_SESSION['old'];
    }
    else {
        $old=0;
    }
    
    // show mad ( seulement matériel mis à disposition)
    if (isset ($_GET["mad"])) {
        $mad=intval($_GET["mad"]);
        $_SESSION['mad'] = $mad;
    }
    else if ( isset($_SESSION['mad']) ) {
        $mad=$_SESSION['mad'];
    }
    else {
        $mad=0;
    }
    
    // evenements annules
    if (isset ($_GET["canceled"])) {
        $_SESSION['canceled'] = intval($_GET["canceled"]);
        $canceled=intval($_GET["canceled"]);
    }
    else if ( isset($_SESSION['canceled']) ) {
        $canceled=$_SESSION['canceled'];
    }
    else $canceled='0';
    
    // inclure renforts
    if (isset ($_GET["renforts"])) {
        $_SESSION['renforts'] = intval($_GET["renforts"]);
        $renforts=intval($_GET["renforts"]);
    }
    else if ( isset($_SESSION['renforts']) ) {
        $renforts=$_SESSION['renforts'];
    }
    else $renforts='1';
    
    // filter2
    if (isset ($_GET["filter2"])) {
        $filter2=secure_input($dbc,$_GET["filter2"]);
        $_SESSION['filter2'] = $filter2;
    }
    else if ( isset($_SESSION['filter2']) ) {
        $filter2=$_SESSION['filter2'];
    }
    else {
        $filter2='ALL';
    }
    
    // type evenement
    if (isset ($_GET["type_evenement"])) {
        $type_evenement=secure_input($dbc,$_GET["type_evenement"]);
        $_SESSION['type_evenement'] = $type_evenement;
    }
    else if ( isset($_SESSION['type_evenement']) ) {
        $type_evenement=$_SESSION['type_evenement'];
    }
    else {
        $type_evenement='ALL';
    }
    if ( $type_evenement == 'DISPOSONLY' ) {
        $day_planning = 0;
        $_SESSION['day_planning'] = $day_planning;
    }
        
    
    // vehicule - used in page engagement
    if (isset ($_GET["vehicule"])) {
        $vehicule=intval($_GET["vehicule"]);
        $_SESSION['vehicule'] = $vehicule;
    }
    else if ( isset($_SESSION['vehicule']) ) {
        $vehicule=$_SESSION['vehicule'];
    }
    else {
        $vehicule=0;
    }
    
    // matos - used in page engagement
    if (isset ($_GET["matos"])) {
        $matos=intval($_GET["matos"]);
        $_SESSION['matos'] = $matos;
    }
    else if ( isset($_SESSION['matos']) ) {
        $matos=$_SESSION['matos'];
    }
    else {
        $matos=0;
    }
    
    // type company
    if (isset ($_GET["typecompany"])) {
        $typecompany=secure_input($dbc,$_GET["typecompany"]);
        $_SESSION['typecompany'] = $typecompany;
    }
    else if ( isset($_SESSION['typecompany']) ) {
        $typecompany=$_SESSION['typecompany'];
    }
    else {
        $typecompany='ALL';
    }
    
    // type matériel
    if (isset ($_GET["type_materiel"])) {
        $type_materiel=secure_input($dbc,$_GET["type_materiel"]);
        $_SESSION['type_materiel'] = $type_materiel;
    }
    else if ( isset($_SESSION['type_materiel']) ) {
        $type_materiel=$_SESSION['type_materiel'];
    }
    else {
        $type_materiel='ALL';
    }
    if ( $type_materiel == '' ) $type_materiel='ALL';
    
    // type consommable
    if (isset ($_GET["type_conso"])) {
        $type_conso=secure_input($dbc,$_GET["type_conso"]);
        $_SESSION['type_conso'] = $type_conso;
    }
    else if ( isset($_SESSION['type_conso']) ) {
        $type_conso=$_SESSION['type_conso'];
    }
    else {
        $type_conso='ALL';
    }
    if ( $type_conso == '' ) $type_conso='ALL';
    
    // type consommable
    if (isset ($_GET["type_element"])) {
        $type_element=secure_input($dbc,$_GET["type_element"]);
        $_SESSION['type_element'] = $type_element;
    }
    else if ( isset($_SESSION['type_element']) ) {
        $type_element=$_SESSION['type_element'];
    }
    else {
        $type_element='ALL';
    }
    
    // categorie message , infos ou amicale
    if (isset ($_GET["catmessage"])) {
        $catmessage=secure_input($dbc,$_GET["catmessage"]);
        $_SESSION['catmessage'] = $catmessage;
    }
    else if ( isset($_SESSION['catmessage']) ) {
        $catmessage=$_SESSION['catmessage'];
    }
    else {
        $catmessage='amicale';
    }
    
    // categorie matériel, utilisé dans page paramétrage
    if (isset ($_GET["catmateriel"])) {
        $catmateriel=secure_input($dbc,$_GET["catmateriel"]);
        $_SESSION['catmateriel'] = $catmateriel;
    }
    else if ( isset($_SESSION['catmateriel']) ) {
        $catmateriel=$_SESSION['catmateriel'];
    }
    else {
        $catmateriel='ALL';
    }

    // categorie grade, utilisé dans page paramétrage
    if (isset ($_GET["catGrade"])) {
        $catGrade=secure_input($dbc,$_GET["catGrade"]);
        $_SESSION['catGrade'] = $catGrade;
    }
    else if ( isset($_SESSION['catGrade']) ) {
        $catGrade=$_SESSION['catGrade'];
    }
    else {
        $catGrade='ALL';
    }
    
    // categorie consommable, utilisé dans page paramétrage
    if (isset ($_GET["catconso"])) {
        $catconso=secure_input($dbc,$_GET["catconso"]);
        $_SESSION['catconso'] = $catconso;
    }
    else if ( isset($_SESSION['catconso']) ) {
        $catconso=$_SESSION['catconso'];
    }
    else {
        $catconso='ALL';
    }
    
    // position du personnel
    if ( isset($_GET["position"])) {
         $position=secure_input($dbc,$_GET["position"]);
         $_SESSION['position'] = $position;
    }
    else if ( isset($_SESSION['position']) ) {
        $position=$_SESSION['position'];
    }
    else {
        $position='actif';
    }
    
    // type qualif
    if ( isset ($_GET["typequalif"])) {
        $typequalif=intval($_GET["typequalif"]);
        $_SESSION['typequalif'] = $typequalif;
    }
    else if (isset($_SESSION['typequalif'])) {
        $typequalif=$_SESSION['typequalif'];
    }
    else {
        $sql2="select max(EQ_ID) from equipe";
        $res2 = mysqli_query($dbc,$sql2);
        $rows2 = mysqli_fetch_array($res2);
        $typequalif=intval($rows2[0]);
    }
    
    // type document
    if ( isset ($_GET["td"])) {
        $td=secure_input($dbc,$_GET["td"]);
        $_SESSION['td'] = $td;
    }
    else if (isset($_SESSION['td'])) {
        $td=$_SESSION['td'];
    }
    else $td='ALL';
    
    // categorie historique
    if ( isset ($_GET["lccode"])) {
        $lccode=secure_input($dbc,$_GET["lccode"]);
        $_SESSION['lccode'] = $lccode;
    }
    else if (isset($_SESSION['lccode'])) {
        $lccode=$_SESSION['lccode'];
    }
    else $lccode='P';
    
    // type historique
    if ( isset ($_GET["ltcode"])) {
        $ltcode=secure_input($dbc,$_GET["ltcode"]);
        $_SESSION['ltcode'] = $ltcode;
    }
    else if (isset($_SESSION['ltcode'])) {
        $ltcode=$_SESSION['ltcode'];
    }
    else $ltcode='ALL';
    
    // historique pour quoi
    if ( isset ($_GET["lcid"])) {
        $lcid=intval($_GET["lcid"]);
        $_SESSION['lcid'] = $lcid;
    }
    else if (isset($_SESSION['lcid'])) {
        $lcid=$_SESSION['lcid'];
    }
    else $lcid=0;
    
    // default dates
    if ( isset($_SESSION['dtdb'])) 
        $default_dtdb = $_SESSION['dtdb'];
    else
        $default_dtdb=date("d-m-Y",mktime(0,0,0,date("m"),date("d"),date("Y")));

    if ( isset($_SESSION['dtfn'])) 
        $default_dtfn = $_SESSION['dtfn'];
    else if ( date("m") <= 9 ) {
         $D = array(1,3,5,7,8,10,12);
        if ( in_array( date("m")+3, $D)) $n=31;
        else $n=30;
        $default_dtfn=date("d-m-Y",mktime(0,0,0,date("m")+3,$n,date("Y")));
    }
    else if ( date("m") == 10 )
        $default_dtfn=date("d-m-Y",mktime(0,0,0,1,31,date("Y")+1));
    else if ( date("m") == 11 )
        $default_dtfn=date("d-m-Y",mktime(0,0,0,2,28,date("Y")+1));
    else if ( date("m") == 12 )
        $default_dtfn=date("d-m-Y",mktime(0,0,0,3,31,date("Y")+1));

    // get date parameters, else use default dates
    if (isset($_GET['dtdb'])) {
         $dtdb = secure_input($dbc,$_GET['dtdb']);    
         $_SESSION['dtdb'] = $dtdb;
    }
    else 
        $dtdb = $default_dtdb;

    if (isset($_GET['dtfn'])) {
         $dtfn = secure_input($dbc,$_GET['dtfn']);    
         $_SESSION['dtfn'] = $dtfn;
    }
    else
        $dtfn = $default_dtfn;
    
    // year month
    $defaultmonth=date("n");
    $defaultyear=date("Y");

    if (isset($_GET["month"])) {
        $month=intval($_GET["month"]);
        if ( $month > 12 and $month <> 100) $month=$defaultmonth;
        $_SESSION['month'] = $month;
    }
    else if (isset($_SESSION["month"])) {
        $month=$_SESSION["month"];
    }
    else {
        $month=$defaultmonth;
    }
    
    // week
    $defaultweek=date("W");
    if (isset($_GET["week"])) {
        $week=intval($_GET["week"]);
        $_SESSION['week'] = $week;
    }
    else if (isset($_SESSION["week"])) {
        $week=$_SESSION["week"];
    }
    else {
        $week=$defaultweek;
    }
    
    if (isset($_GET["year"])) {
        $year=intval($_GET["year"]);
        $_SESSION['year'] = $year;
    }
    else if (isset($_SESSION["year"])) {
        $year=$_SESSION["year"];
    }
    else {
        $year=$defaultyear;
    }
    
    // year export
    if (isset($_GET["yearreport"])) {
        $yearreport=intval($_GET["yearreport"]);
        if ( $yearreport == 0 ) $yearreport = date('Y') -1;
        $_SESSION['yearreport'] = $yearreport;
    }
    else if (isset($_SESSION["yearreport"])) {
        $yearreport=$_SESSION["yearreport"];
    }
    else {
        $yearreport=date('Y') -1;
    }
    
    // page section, onglet documents
    if (isset($_GET["yeardoc"])) {
        $yeardoc=intval($_GET["yeardoc"]);
        if ( $yeardoc == 0 ) $yeardoc = 'all';
        $_SESSION['yeardoc'] = $yeardoc;
    }
    else if (isset($_SESSION["yeardoc"])) {
        $yeardoc=$_SESSION["yeardoc"];
    }
    else {
        $yeardoc='all';
    }
    
    // type evenement pour report
    if (isset($_GET["type_event"])) {
        $type_event=secure_input($dbc,$_GET["type_event"]);
        $_SESSION['type_event'] = $type_event;
    }
    else if (isset($_SESSION["type_event"])) {
        $type_event=$_SESSION["type_event"];
    }
    else {
        $type_event='ALL';
    }
    
    // competence pour report
    if (isset($_GET["competence"])) {
        $competence=secure_input($dbc,$_GET["competence"]);
        $_SESSION['competence'] = $competence;
    }
    else if (isset($_SESSION["competence"])) {
        $competence=$_SESSION["competence"];
    }
    else {
        $competence='0';
    }
    
    
    // report à afficher
    if (isset($_GET["exp"])) {
        $exp=secure_input($dbc,$_GET["exp"]);
        $_SESSION['exp'] = $exp;
    }
    else if (isset($_SESSION["exp"])) {
        $exp=$_SESSION["exp"];
    }
    else $exp='';

	// périodes d'interdiction interdire anciennes
    if (isset($_GET["old_included"])) {
        $old_included=intval($_GET["old_included"]);
        $_SESSION['old_included'] = $old_included;
    }
    else if (isset($_SESSION["old_included"])) {
        $old_included=$_SESSION["old_included"];
    }
    else {
        $old_included='0';
    }
}

?>