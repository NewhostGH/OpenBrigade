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

include_once ("config.php");
include_once ($basedir."/fonctions_documents.php");
@session_start();
check_all(0);
get_session_parameters();
if (isset ($_GET["print"])) $print=1;
else $print=0;
$id=$_SESSION['id'];
if ( $print == 1 ) $nomenu=1;
writehead();
$mysection=$_SESSION['SES_SECTION'];
$myparent=$_SESSION['SES_PARENT'];

if (isset($_GET['tab'])) $tab = secure_input($dbc, $_GET['tab']);
else $tab = 0;
if (isset($_GET['child'])) $child = secure_input($dbc, $_GET['child']);
else $child = 0;

if ( check_rights($_SESSION['id'], 25)) $granted_for_all=true;
else $granted_for_all=false;

// ====================================================
// parameters
// ====================================================

$possibleorders= array('E_PARENT','EE_NAME','TSP_ID','TP_LIBELLE','P_NOM','G_LEVEL');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='E_PARENT';

// if param e provided, keep it 5 minutes as a cookie
if (isset($_GET["evenement"])){
    $evenement=intval($_GET["evenement"]);
    @setcookie("evenement", $evenement, time()+60*5);
}

$query ="select lh.LH_ID,lh.P_ID
        from log_type lt, pompier p, log_history lh
        left join evenement e on ( e.E_CODE = lh.COMPLEMENT_CODE)
        left join pompier p2 on ( p2.P_ID = lh.LH_WHAT)
        where p.P_ID = lh.P_ID
        and lh.LT_CODE=lt.LT_CODE";
$query .= " and lh.COMPLEMENT_CODE='".$evenement."'";
$query .= " and lt.LC_CODE='P'";
$query .= " union select lh.LH_ID,lh.P_ID
        from log_type lt, pompier p, log_history lh, evenement e
        where e.E_CODE = lh.LH_WHAT
        and p.P_ID = lh.P_ID
        and lh.LT_CODE=lt.LT_CODE
        and lh.LH_WHAT='".$evenement."'
        and lt.LC_CODE='E'";
$result=mysqli_query($dbc,$query);
$nHistory=mysqli_num_rows($result);

$possibleorders= array('E_PARENT','EE_NAME','TSP_ID','TP_LIBELLE','P_NOM','G_LEVEL');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='E_PARENT';

// if param e provided, keep it 5 minutes as a cookie
if (isset($_GET["evenement"])){
    $evenement=intval($_GET["evenement"]);
    @setcookie("evenement", $evenement, time()+60*5);
}

if (isset ($_GET["id"])) $evenement=intval($_GET["id"]);

$is_inscrit = false;
$is_present = false;
if (is_inscrit($id,$evenement)) {
    $is_inscrit = true;
    if (is_present($id,$evenement))
        $is_present = true;
}

// test permission visible
if (! $is_inscrit ) {
    if ( get_company_evenement($evenement) == $_SESSION['SES_COMPANY'] ) {
        if (! check_rights($id,41))
            check_all(45);
    }
    else {
        check_all(41);
        if ( ! check_rights($id,40)) {
            $organisateur=get_section_organisatrice ($evenement);
            if (! check_rights($id,41, $organisateur))
                if ( $organisateur <> $myparent and get_section_parent($organisateur) <> $myparent )
                    check_all(40);
        }
    }
}

// from: scroller , inscription , calendar, choice, vehicule, personnel, formation, calendar
if ( isset ( $_SESSION['from_interventions'])) {
    $from ='interventions';
    unset($_SESSION['from_interventions']);
}
else if (isset ($_GET["from"])) $from=secure_input($dbc,$_GET["from"]);
else if (isset ($_SESSION['eventabdoc'])) {
    $from='document';
    unset($_SESSION['eventabdoc']);
}
else if (isset ($_SESSION['from'])) {
    $from=secure_input($dbc,$_SESSION['from']);
    unset($_SESSION['from']);
}
else $from='default';

if (isset ($_GET["section"])) $section=intval(secure_input($dbc,$_GET["section"]));
else $section=$mysection;
if (isset ($_GET["type"])) $type=secure_input($dbc,$_GET["type"]);
else $type="ALL";
if (isset ($_GET["date"])) $date=secure_input($dbc,$_GET["date"]);
else $date="FUTURE";
if (isset ($_GET["day"])) $day=secure_input($dbc,$_GET["day"]);
else $day="";
if (isset ($_GET["pid"])) $pid=secure_input($dbc,$_GET["pid"]);
else $pid="";

// ====================================================
// permissions
// ====================================================

if (! $is_inscrit ) {
    if (! check_rights($id,41))
        if ( get_company_evenement($evenement) == $_SESSION['SES_COMPANY'] )
            check_all(45);
        else
            check_all(41);
}

$query="select s.S_ID, s.S_CODE, s.S_HIDE, s.S_PARENT, e.E_OPEN_TO_EXT , sf.NIV
        from evenement e left join section s on e.S_ID = s.S_ID
        left join section_flat sf on e.S_ID = sf.S_ID
        where e.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
$nb = mysqli_num_rows($result);
$row=@mysqli_fetch_array($result);
$S_ID=intval($row["S_ID"]);
$NIV=intval($row["NIV"]);
$S_CODE=$row["S_CODE"];
if ( $S_CODE == "" ) $S_CODE = get_section_code($S_ID);
$S_HIDE=intval($row["S_HIDE"]);
$S_PARENT=intval($row["S_PARENT"]);
$E_OPEN_TO_EXT=intval($row["E_OPEN_TO_EXT"]);
if ( $nb == 0  ) {
    // remove cookie if set
    @setcookie("evenement", "", time()-3600);
    write_msgbox("ERREUR", $error_pic, "Activité n°$evenement introuvable<br><p align=center>
        <a href='index.php' target='_top'><input type='submit' class='btn btn-secondary' value='Retour'></font></a> ",10,0);
    exit;
}
else if ( $S_HIDE == 1 and $E_OPEN_TO_EXT == 0 ) {
    if (! check_rights($id,41, intval($S_ID)) and ! $is_inscrit) {
        $my_parent_section = get_section_parent($_SESSION['SES_SECTION']);
        if ( $S_PARENT <> $my_parent_section and $S_ID <> $my_parent_section ) {
            // cas personne ayant des permissions sur une antenne du département, OK, sinon msg erreur
            if ( ! has_role_in_dep($id, $S_ID)) {
                write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir <br>l'activité n°".$evenement."<br> organisée par ".$S_CODE." <br><p align=center>
                <a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
                exit;
            }
        }
    }
}

if ( isset($_GET["anomalie"]) ) {
    $query="update evenement set E_ANOMALIE=".intval($_GET["anomalie"])." where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
}

// ====================================================
// get data
// ====================================================

$evts=get_event_and_renforts($evenement);
$chefs=get_chefs_evenement($evenement);

$query="select E.E_CODE, E.S_ID,E.TE_CODE, TE.TE_ICON, TE.TE_LIBELLE, E.E_LIEU, 
        EH.EH_DATE_DEBUT _EH_DATE_DEBUT,EH.EH_DATE_FIN _EH_DATE_FIN, 
        date_format(EH_DATE_FIN,'%d-%m-%Y') LAST_DAY,
        EH.EH_DESCRIPTION _EH_DESCRIPTION,
        TIME_FORMAT(EH.EH_DEBUT, '%k:%i') as _EH_DEBUT, S.S_CODE, YEAR(EH.EH_DATE_DEBUT) as YEAR,
        TIME_FORMAT(EH.EH_FIN, '%k:%i') as _EH_FIN, E.E_MAIL1, E.E_MAIL2, E.E_MAIL3, E.E_OPEN_TO_EXT, E.E_ANOMALIE,
        E.E_NB, E.E_NB_STAGIAIRES, E.E_COMMENT, E.E_COMMENT2, E.E_LIBELLE, S.S_DESCRIPTION, E.E_CLOSED, E.E_CANCELED, E.E_CANCEL_DETAIL,
        E.E_CONVENTION, E.E_PARENT, E.E_CREATED_BY, E_ALLOW_REINFORCEMENT, E.TF_CODE, E.PS_ID, E.E_EQUIPE,
        date_format(E.E_CREATE_DATE,'%d-%m-%Y %H:%i') E_CREATE_DATE, E.C_ID, E.E_CONTACT_LOCAL, ".phone_display_mask('E.E_CONTACT_TEL')." as E_CONTACT_TEL,
        S.DPS_MAX_TYPE, E.TAV_ID, EH.EH_ID _EH_ID, EH.EH_DUREE _EH_DUREE, E.E_FLAG1, E.E_VISIBLE_OUTSIDE, E.E_ADDRESS, E.E_TARIF, E.E_PARTIES,
        date_format(E.E_DATE_ENVOI_CONVENTION,'%d-%m-%Y') E_DATE_ENVOI_CONVENTION, E_EXTERIEUR, S.S_HIDE, S.S_PARENT,
        TE.TE_MAIN_COURANTE, TE.TE_VICTIMES, TE.TE_MULTI_DUPLI, TE.ACCES_RESTREINT, E.E_VISIBLE_INSIDE,
        TE.TE_PERSONNEL, TE.TE_VEHICULES, TE.TE_MATERIEL, TE.TE_CONSOMMABLES, E.E_URL, E.E_COLONNE_RENFORT, TE.REMPLACEMENT, TE.PIQUET, TE.TE_MAP, TE.CLIENT,TE.TE_DPS, TE.TE_DOCUMENT,
        TE.EVAL_PAR_STAGIAIRES, TE.PROCES_VERBAL, TE.FICHE_PRESENCE, TE.ORDRE_MISSION, TE.CONVENTION, TE.EVAL_RISQUE, TE.CONVOCATIONS, TE.FACTURE_INDIV,
        tg.EQ_ICON, tg.EQ_NOM, E.E_CONSIGNES, 
        case when E.E_AUTOCLOSE_BEFORE is null then -1
        else E.E_AUTOCLOSE_BEFORE
        end
        as E_AUTOCLOSE_BEFORE,
        E.E_HEURE_RDV as E_HEURE_RDV, E.E_LIEU_RDV, ".phone_display_mask('E.E_TEL')." as E_TEL, E.E_WHATSAPP, E.E_WEBEX_URL, E.E_WEBEX_PIN, E.E_WEBEX_START
        from evenement E left join type_garde tg on E.E_EQUIPE = tg.EQ_ID,
        evenement_horaire EH, type_evenement TE, section S
        where E.TE_CODE=TE.TE_CODE
        and E.E_CODE=EH.E_CODE
        and S.S_ID=E.S_ID
        and E.E_CODE=".$evenement."
        order by EH.EH_ID";
$result=mysqli_query($dbc,$query);
$queryevt=$query;

$EH_ID= array();
$EH_DEBUT= array();
$EH_DATE_DEBUT= array();
$EH_DATE_FIN= array();
$EH_FIN= array();
$EH_DUREE= array();
$horaire_evt= array();
$description_partie= array();
$date1=array();
$month1=array();
$day1=array();
$year1=array();
$date2=array();
$month2=array();
$day2=array();
$year2=array();
$E_DUREE_TOTALE = 0;
$i=1;
while (custom_fetch_array($result)) {
    if ( $i == 1 ) {
        if ( $TE_CODE == 'MC' ) check_feature("main_courante");
        else check_feature("evenements");
        $PS_ID_FORMATION=$PS_ID;
        $E_EQUIPE=intval($E_EQUIPE);
        $S_DESCRIPTION=stripslashes($S_DESCRIPTION);
        $E_LIBELLE=stripslashes($E_LIBELLE);
        $E_LIEU=stripslashes($E_LIEU);
        $E_NB_STAGIAIRES=intval($E_NB_STAGIAIRES);
        $E_COMMENT=stripslashes($E_COMMENT);
        $E_COMMENT2=stripslashes($E_COMMENT2);
        $E_ADDRESS=stripslashes($E_ADDRESS);
        $E_HEURE_RDV=substr($E_HEURE_RDV,0,5);
        if ( $E_EXTERIEUR == 1 ) $E_LIEU .= " <span style='background-color:yellow;'>hors département</span>";
        if ( $S_HIDE == 1 and $E_OPEN_TO_EXT == 0 ) {
            if (! check_rights($id,41, intval($S_ID))) {
                $my_parent_section = get_section_parent($_SESSION['SES_SECTION']);
                if ( $S_PARENT <> $my_parent_section and $S_ID <> $my_parent_section ) {
                    if ( ! has_role_in_dep($id, $S_ID)) {
                        write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir <br>l'activité n°".$evenement."<br> organisée par ".$S_CODE." <br><p align=center>
                        <a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
                        exit;
                    }
                }
            }
        }
        if ( $ACCES_RESTREINT == 1 ) {
            if (! check_rights($id,26, intval($S_ID)) and ! $is_inscrit and ! in_array($id,$chefs) and $E_CREATED_BY <> $id) {
                write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir <br>l'activité n°".$evenement."<br>
                Le type d'activité <b>$TE_LIBELLE</b> a été configuré en accès restreint par l'administrateur.
                Seuls les inscrits et les personnes ayant la permission n°26 peuvent voir le détail.<p align=center>
                <a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
                exit;
            }
        }
        $REMAINING="";
        if ( $E_AUTOCLOSE_BEFORE >= 24 ) {
            $DAYS = $E_AUTOCLOSE_BEFORE / 24;
            $queryr="select DATE_FORMAT(DATE_ADD(EH_DATE_DEBUT, INTERVAL -".$DAYS." DAY),'%d-%m-%Y') as CLOSE_TIME, 
                    DATEDIFF(EH_DATE_DEBUT, NOW()) as REMAINING 
                    from evenement_horaire where E_CODE=".$evenement." and EH_ID=1";
            $resultr=mysqli_query($dbc,$queryr);
            $rowr=mysqli_fetch_array($resultr);
            $CLOSE_TIME="le ".$rowr["CLOSE_TIME"];
            $REMAINING=$rowr["REMAINING"];

            // auto close now?
            if ( $REMAINING <= $DAYS and $E_CLOSED == 0 ) {
                $queryr="update evenement set E_CLOSED=1 where E_CODE in (".$evts.")";
                $resultr=mysqli_query($dbc,$queryr);
                $E_CLOSED=1;
            }
        }
        else if ( $E_AUTOCLOSE_BEFORE >= 0 ) {
            $MINUTES = intval($E_AUTOCLOSE_BEFORE) * 60;
            $queryr="select date_format(DATE_ADD(EH_DEBUT, INTERVAL -".$MINUTES." MINUTE) ,'%Hh%i') as CLOSE_TIME,
                    DATEDIFF(EH_DATE_DEBUT, NOW()) as REMAINING_DAYS,
                    TIMESTAMPDIFF(MINUTE,'".date('Y-m-d H:i:s')."',concat(EH_DATE_DEBUT,' ',EH_DEBUT)) as REMAINING
                    from evenement_horaire where E_CODE=".$evenement." and EH_ID=1";
            $resultr=mysqli_query($dbc,$queryr);
            $rowr=mysqli_fetch_array($resultr);
            $CLOSE_TIME="à ".$rowr["CLOSE_TIME"];
            $REMAINING_DAYS=intval($rowr["REMAINING_DAYS"]);
            $REMAINING= $REMAINING_DAYS * 1440 + intval($rowr["REMAINING"]);

            // auto close now?
            if ( $REMAINING <= $MINUTES and $E_CLOSED == 0 ) {
                $queryr="update evenement set E_CLOSED=1 where E_CODE in (".$evts.")";
                $resultr=mysqli_query($dbc,$queryr);
                $E_CLOSED=1;
            }
        }
    }

    // tableau des sessions
    $EH_ID[$i]=$_EH_ID;
    $description_partie[$i]=$_EH_DESCRIPTION;
    $EH_DEBUT[$i]=$_EH_DEBUT;
    $EH_DATE_DEBUT[$i]=$_EH_DATE_DEBUT;
    if ( $_EH_DATE_FIN == '' )
        $EH_DATE_FIN[$i]=$_EH_DATE_DEBUT;
    else
        $EH_DATE_FIN[$i]=$_EH_DATE_FIN;
    $EH_FIN[$i]=$_EH_FIN;
    $EH_DUREE[$i]=$_EH_DUREE;
    if ( $EH_DUREE[$i] == "") $EH_DUREE[$i]=0;
    $E_DUREE_TOTALE = $E_DUREE_TOTALE + $EH_DUREE[$i];
    $tmp=explode ( "-",$EH_DATE_DEBUT[$i]); $year1[$i]=$tmp[0]; $month1[$i]=$tmp[1]; $day1[$i]=$tmp[2];
    $date1[$i]=mktime(0,0,0,$month1[$i],$day1[$i],$year1[$i]);
    $tmp=explode ( "-",$EH_DATE_FIN[$i]); $year2[$i]=$tmp[0]; $month2[$i]=$tmp[1]; $day2[$i]=$tmp[2];
    $date2[$i]=mktime(0,0,0,$month2[$i],$day2[$i],$year2[$i]);

    $very_end=$day2[$i]."-".$month2[$i]."-".$year2[$i];
    if ( $EH_DATE_DEBUT[$i] == $EH_DATE_FIN[$i])
        $horaire_evt[$i]=date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$year1[$i]." de ".$EH_DEBUT[$i]." à ".$EH_FIN[$i];
    else
        $horaire_evt[$i]="\ndu ".date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$EH_DEBUT[$i]." au "
            .date_fran($month2[$i], $day2[$i] ,$year2[$i])." ".moislettres($month2[$i])." ".$year2[$i]." ".$EH_FIN[$i];
    $i++;
}

// ==========================================
// which tab to display?
// ==========================================
if ( isset($_GET["tab"]))$tab=secure_input($dbc,$_GET["tab"]);
else if ( $from == 'inscription' || $from == 'gardes' || $from =='calendar') $tab=2;
else if ( $from == 'vehicule' ) $tab=3;
else if ( $from == 'materiel' ) $tab=4;
else if ( $from == 'consommables' ) $tab=9;
else if ( $from == 'formation' ) $tab=5;
else if ( $from == 'tarif' ) $tab=6;
else if ( $from == 'document' ) $tab=7;
else if ( $from == 'interventions' ) $tab=8;
else if ( $from == 'choice' and $TE_CODE == 'MC' ) $tab=8;
else if ( $from == 'piquets') $tab=11;
else if ( $from == 'Logistique') $tab=14;
else $tab="1";

// ==========================================
// evaluate permissions
// ==========================================

if ( $gardes == 1 and $TE_CODE == 'GAR' ) $gardeSP = true;
else $gardeSP = false;

// permission voir tableau de garde
if ( $gardeSP ) {
    check_all(61);
    if ( $nbsections == 0 ) {
        if ( ! check_rights($id, 61,"$S_ID") )
            if ( $myparent <> "$S_ID")
                if ( $myparent <> get_section_parent("$S_ID")) {
                    // check if at least a role on this section
                    if ( ! has_role_in_dep($id, $S_ID))
                        check_all(40);
                }
    }
}

$chef=false;
$chefs_parent=get_chefs_evenement($E_PARENT);
if ( in_array($id,$chefs) or in_array($id,$chefs_parent)) {
    $chef=true;
}

if ( $assoc ) {
    $voircompta = check_rights($id, 29,"$S_ID");
    // le chef de l'activité a toujours accès à ces fonctionnalités
    if ( $chef ) {
        $voircompta = true;
    }
    // le cadre de permanence a toujours accès à ces fonctionnalités
    if ( get_cadre ($S_ID) == $id ) {
        $voircompta = true;
    }
}
else
    $voircompta = false;

if (check_rights($id, 15, $S_ID)) $granted_event=true;
else if ( $chef ) $granted_event=true;
else $granted_event=false;

if (is_operateur_pc($id,$evenement)) $is_operateur_pc=true;
else $is_operateur_pc=false;

$nbsessions=sizeof($EH_ID);
$nummaxpartie=max($EH_ID);
$organisateur= $S_ID;
if (get_level("$organisateur") > $nbmaxlevels - 2 ) $departement=get_family(get_section_parent("$organisateur"));
else $departement=get_family("$organisateur");

$evts_not_canceled=get_event_and_renforts($evenement,true);
$query1="select count(distinct P_ID) as NB from evenement_participation
     where E_CODE in (".$evts_not_canceled.")";
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NP=$row1["NB"];
$query2="select count(distinct P_ID) as NB from evenement_participation
     where E_CODE in (".$evts_not_canceled.") and EP_ABSENT=0";
$result2=mysqli_query($dbc,$query2);
$row2=@mysqli_fetch_array($result2);
$NP2=$row2["NB"];

$ischef=is_chef($id,$S_ID);

$OPEN_TO_ME = 1;
$perm_via_role=false;
if (( $E_OPEN_TO_EXT == 0 ) and ( ! check_rights($id, 39, $S_ID) )) {
    // hors département?
    if ( get_section_parent("$mysection") <> get_section_parent("$S_ID")) {
        $list = preg_split('/,/' , get_family_up("$S_ID"));
        if (! in_array($mysection,$list)) {
            $list = preg_split('/,/' , get_family("$S_ID"));
            if (! in_array($mysection,$list)) {
                // permission sur une antenne via rôle? => permettre inscription sur tout le département
                if ( get_level("$S_ID") ==  $nbmaxlevels - 2 ) $sections_roles_list = get_family("$S_ID");
                else if ( get_level("$S_ID") ==  $nbmaxlevels - 1 ) $sections_roles_list = get_family(get_section_parent("$S_ID"));
                else $sections_roles_list = "$S_ID";

                $query3="select count(*) as NB from
                    habilitation h, section_role sr
                    where sr.GP_ID = h.GP_ID
                    and h.F_ID = 39
                    and sr.S_ID in (".$sections_roles_list.")
                    and sr.P_ID =".$id;
                $result3=mysqli_query($dbc,$query3);
                $row3=@mysqli_fetch_array($result3);
                if ( $row3["NB"] == 0 ) $OPEN_TO_ME = 0;
            }
        }
    }
    else {
        // je peux inscrire sur les antennes voisines mais pas les départements voisins
        // si je suis à un niveau supérieur à antenne -> je ne peux pas m'inscrire
        if ( get_level("$mysection") + 2 <= $nbmaxlevels  )
            $OPEN_TO_ME = 0;
    }
}
// activité national,régional
$list = preg_split('/,/'  , get_family_up("$mysection"));
if ( $nbsections == 0 and $mysection <> $S_ID and in_array($S_ID,$list)) {
    if ( get_level($S_ID) < $nbmaxlevels - 2  and  ! check_rights($id, 26)) {
        if ( $E_OPEN_TO_EXT == 0 )
            $OPEN_TO_ME = -2;
    }
}
// cas particulier un agent lambda ne doit pas s'inscrire lui même sur un activité extérieur
elseif ( $nbsections == 0 and $E_OPEN_TO_EXT == 1  and ! check_rights($id, 39, $S_ID) ) {
    if ( get_section_parent("$mysection") <> $S_ID
        and  get_section_parent("$mysection") <> get_section_parent("$S_ID")) {
        if ( ! check_rights($id, 26))
            $OPEN_TO_ME = -1;
    }
    elseif (get_section_parent("$mysection") == get_section_parent("$S_ID")
        and get_level("$mysection") + 2 <= $nbmaxlevels) {
        if ( ! check_rights($id, 26))
            $OPEN_TO_ME = -1;
    }
}

// definition des permissions
if (check_rights($id, 15, $organisateur) or $chef) $granted_event=true;
else $granted_event=false;
if (check_rights($id, 10, $organisateur) or $chef) $granted_personnel=true;
else $granted_personnel=false;
if (check_rights($id, 17, $organisateur) or $granted_event or $chef) $granted_vehicule=true;
else $granted_vehicule=false;
if (check_rights($id, 19, $organisateur)) $granted_delete=true;
else $granted_delete=false;
if (check_rights($id, 26, $organisateur)) {
    $veille=true;
    $SECTION_CADRE=get_highest_section_where_granted($id,26);
}
else $veille=false;

$granted_inscription=false;
if ( $gardeSP and check_rights($id, 6, $S_ID) and $nbsections == 0)
    $granted_inscription=true;
else if ( $gardeSP and check_rights($id, 6) and $nbsections > 0)
    $granted_inscription=true;
else if (($OPEN_TO_ME == 1 ) and (check_rights($id, 28) or check_rights($id, 10)))
    $granted_inscription=true;

// cas particulier
if (check_rights($id, 17) and (! $granted_vehicule)) {
    if ( $E_OPEN_TO_EXT == 1 ) $granted_vehicule=true;
}

if ( $gardeSP and check_rights($id, 6, "$organisateur")) {
    $granted_personnel=true;
    $granted_vehicule=true;
}

if ((check_rights($id, 47, "$organisateur")) or $chef or $granted_event)
    $documentation=true;
else $documentation=false;

if ( $granted_event or $granted_vehicule ) $granted_consommables=true;
else $granted_consommables=false;


if ( $ACCES_RESTREINT == 1 ) {
    if (! check_rights($id,26, intval($S_ID)) and ! $is_inscrit and ! $chef and $E_CREATED_BY <> $id) {
        write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir <br>l'activité n°".$evenement."<br> 
            Car son accès est restreint aux inscrits et aux personnes ayant la permission n°26.<p align=center>
            <a href=\"javascript:history.back(1)\"><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
        exit;
    }
}

// ==========================================
// counters and number of docs
// ==========================================

$query="select count(distinct V_ID) as NB from evenement_vehicule
     where E_CODE in (".$evts.")";
$result=@mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$NB2=$row["NB"];

$query="select sum(em.EM_NB) as NB from evenement_materiel em, materiel m
     where em.E_CODE in (".$evts.")
    and em.MA_ID = m.MA_ID
    and m.MA_PARENT is null";
$result=@mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB3=intval($row["NB"]);

$NB4=0;
$mypath=$filesdir."/files/".$evenement;
if (is_dir($mypath)) {
    $dir=opendir($mypath);
    while ($file = readdir ($dir)) {
        if ($file != "." && $file != ".." and (file_extension($file) <> "db")) $NB4++;
    }
}
if ( intval($E_PARENT) > 0 ) {
    $mypath=$filesdir."/files/".$E_PARENT;
    if (is_dir($mypath)) {
        $dir=opendir($mypath);
        while ($file = readdir ($dir)) {
            if ($file != "." && $file != ".." and (file_extension($file) <> "db")) $NB4++;
        }
    }
    $query="select E_CLOSED from evenement where E_CODE=".$E_PARENT;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $PARENT_CLOSED=$row["E_CLOSED"];
}
else
    $PARENT_CLOSED=0;

// ajout documents générés
if ( $granted_event ) {
    if ( $EVAL_PAR_STAGIAIRES == 1  and $competences == 1)  $NB4++;
    if ( $FACTURE_INDIV == 1  and $E_TARIF > 0 )  $NB4++;
    if ( $FICHE_PRESENCE == 1  and $E_CLOSED == 1 )  $NB4++;
    if ( $PROCES_VERBAL == 1  and $E_CLOSED == 1 and  $PS_ID <> '' and in_array($TF_CODE,array('I','C','R','M')))  $NB4++;
    if ( $CONVENTION == 1  and $E_PARENT == 0 ) $NB4++;
    if ( $CONVENTION == 1  and $E_PARENT == 0 and signature_president_disponible($S_ID)) $NB4++;
    if ( $EVAL_RISQUE == 1  and dim_ready($evenement))  $NB4 = $NB4 + 2;
    if ( $CONVOCATIONS == 1  and $E_CLOSED == 1 ) $NB4 = $NB4 + 2; // convocations collective et individuelles
    if ( ! $gardeSP and $TE_PERSONNEL == 1 and $TE_CODE <> 'MC' and $TE_CODE <> 'FOR' and $ORDRE_MISSION == 1 and ($assoc or $army))  $NB4++; // demande de renforts
}

if ( $granted_event or $is_present ) {
    if ( $ORDRE_MISSION == 1  and $E_CLOSED == 1 )  $NB4++;
}

// ajout des documents spécifiques formation ou DPS
$query1="select TYPE from poste where PS_ID='".$PS_ID."' union select '".$TE_CODE."'";
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$type_doc=$row1["TYPE"];
// documents SST, PSC1 ou autres  (DPS)
$NB4 = $NB4 + count_specific_documents($type_doc);

// attestations de présence SST
if ( $E_CLOSED == 1 and ( $type_doc == 'SST' or $type_doc == 'PRAP' ) and $granted_event) $NB4 = $NB4 + 1;

// main courante
$query1="select count(1) from evenement_log where E_CODE=".$evenement;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NB5=$row1[0];

// produits consommés
$query="select count(1) as NB from evenement_consommable
     where E_CODE in (".$evts.")";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB6=$row["NB"];

// PDF produits consommés
if ( $NB6 > 0 and $granted_event) $NB4 = $NB4 + 1;

// remplacements
$query1="select count(1) from remplacement where E_CODE=".$evenement;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NB7=$row1[0];



// ====================================================
// header and tabs
// ====================================================

if ( $print == 1 )
    echo "<link rel='stylesheet' href='".$basedir."/css/print.css'>";

if ( $autorefresh == 1 ) echo "<meta http-equiv='Refresh' content='20'>";
echo "
<link rel='stylesheet' type='text/css' href='css/print.css' media='print' />
<STYLE type='text/css'>
.section{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.categorie{color:black; background-color:#fafafafa; font-size:10pt;}
.materiel{color:<?php echo $mydarkcolor; ?>; background-color:#fafafafa; font-size:9pt;}
</STYLE>
<script type=text/javascript>
function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}

$(function(){
    $('.statut_select').change(function() {
        var bgcolor=$('option:selected',this).css('background-color');
        var txtcolor=$('option:selected',this).css('color');
        $(this).css('background-color', bgcolor);
        $(this).css('color', txtcolor);
    });
});

</script>

<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript' src='js/evenement.js?version=".$patch_version."'></script>";

echo "</head>";

if ( $print == 1 )
    echo "<body onload='javascript:window.print();'>";
else
    echo "<body>";

write_debugbox($queryevt);

$cmt=get_info_evenement($evenement,1);
if ( $pompiers == 1 and $nbsections == 0 ) $cmt = $S_CODE." - ".$cmt;
$buttons_container="";
if (! $print ) {
    $buttons_container .= "<div class='buttons-container' style='margin-left:auto'>";
    if ( $E_ADDRESS <> "" and $geolocalize_enabled ) {
        $querym="select LAT, LNG from geolocalisation where TYPE='E' and CODE=".$evenement;
        $resultm=mysqli_query($dbc,$querym);
        $NB=mysqli_num_rows($resultm);
        if ( $NB > 0 ) {
            custom_fetch_array($resultm);
            $url = $waze_url."&ll=".$LAT.",".$LNG."&";
            $buttons_container .= " <a class='btn btn-default' href=".$url." target=_blank><i class='fab fa-waze fa-1x noprint' title='Voir la carte Waze' ></i></a>";
        }
    }

    if (check_rights($id, 41) and $TE_CODE <> 'MC') {
        $buttons_container .= " <a class='btn btn-default hide_mobile' href='#'><i class='far fa-file-excel fa-1x noprint excel-hover' title='Excel' onclick=\"window.open('evenement_xls.php?evenement=$evenement')\"/></i></a>";
        if ( $gardeSP )
            $buttons_container .= " <a class='btn btn-default noprint' href='evenement_display.php?evenement=$evenement&print=1&from=print'><i class='fa fa-print fa-1x' title='Version imprimable' ></i></a>";
        $buttons_container .= " <a class='btn btn-default noprint' href=\"evenement_ical.php?evenement=$evenement&section=$section\" target=_blank><i class='far fa-calendar-alt fa-1x' title=\"Télécharger le fichier ical\"></i></a>";
    }
    //BOUTON INSCRIRE DESINSCRIRE
    $query="select DATEDIFF(NOW(), ep.EP_DATE) as NB_DAYS 
                from evenement_participation ep, evenement e
                where ep.E_CODE = e.E_CODE
                and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")
                and ep.P_ID=".$id;
    $r1=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($r1);


    // savoir si le public peut s'inscrire lui même sur une garde du tableau
    if ( $gardeSP ) {
        if ( ! isset($public_can_enroll)) {
            if ( $assoc == 1 ) $public_can_enroll=1;
            else $public_can_enroll=0;
        }
    }
    else $public_can_enroll=1;

    if ( $E_CLOSED == 0  and (! $print ) and $public_can_enroll and $TE_PERSONNEL == 1) {
        if ( my_date_diff(date('d')."-".date('m')."-".date('Y'),$very_end) >= 0 ) {
            if ( $num == 0 ) {
                $disabled_inscr="";
                // si photo_obligatoire on peut bloquer les inscriptions
                if ( $photo_obligatoire ) {
                    $photo = get_photo($id);
                    if ( $photo == '' ) {
                        $since=get_nb_days_since_creation($id);
                        if ( $since > $limit_days_photo )
                            $disabled_inscr=" disabled title=\"Inscription interdite: Vous n'avez pas enregistré votre photo\" ";
                    }
                }
                // attention si il y a déjà inscription sur principal, bloquer le bouton s'inscrire
                $query2="select count(*) as NB from evenement_participation ep, evenement e
                    where ep.E_CODE = e.E_CODE
                    and e.E_CODE =(select E_PARENT from evenement where E_CODE=".$evenement." )
                    and ep.P_ID=".$id;
                $r2=mysqli_query($dbc,$query2);
                $rowd=@mysqli_fetch_array($r2);
                $num2=$rowd["NB"];
                if ($num2 > 0 and $disabled_inscr == '' )
                    $disabled_inscr=" disabled title=\"Inscription interdite: Vous êtes déjà inscrit sur l'activité principale\" ";

                if ( $OPEN_TO_ME == 1 and check_rights($id, 39))
                    $buttons_container.= " <button type='button' class='btn btn-success noprint' value=\"S'inscrire\" title data-original-title=\"S'inscrire\" $disabled_inscr
                    onclick='bouton_redirect(\"evenement_inscription.php?evenement=".$evenement."&action=inscription\",\"inscription\");'><i class='fas fa-user-plus'></i><span class='hide_mobile'> S'inscrire</span></button> ";
                else if ( $OPEN_TO_ME == -1 )
                    $buttons_container.= " <button type='button' title data-original-title=\"S'inscrire\" class='btn btn-default noprint' value=\"S'inscrire\" $disabled_inscr
                    onclick=\"swalAlert('Votre inscription sur cette activité extérieur ne peut être faite que par votre responsable.');\"><i class='fas fa-user-plus'></i><span class='hide_mobile'> S'inscrire</span></button> ";
                else if (( $OPEN_TO_ME == -2 ) or ( $OPEN_TO_ME == -3 ))
                    $buttons_container.= " <button type='button' title data-original-title=\"S'inscrire\" class='btn btn-default noprint' value=\"S'inscrire\" $disabled_inscr
                    onclick=\"swalAlert('Votre inscription sur cette activité national ou régional ne peut être faite que par votre responsable.');\"><i class='fas fa-user-plus'></i><span class='hide_mobile'> S'inscrire</span></button> ";
            }
            else {
                $row=mysqli_fetch_array($r1);
                $show_btn=false;
                if (isset($desinscription_seulement_jour_inscription)) {
                    if ( $row["NB_DAYS"] < 1 ) 
                        $show_btn=true;
                }
                else $show_btn=true;

                if (check_rights($id, 39) and $show_btn )
                    $buttons_container.= " <button type='button' title data-original-title=\"Se désinscrire\" class='btn btn-warning' value=\"Se désinscrire\" onclick='bouton_redirect(\"evenement_inscription.php?evenement=".$evenement."&action=desinscription\",\"desinscription\");'><i class='fas fa-user-minus'></i><span class='hide_mobile'> Se désinscrire</span></button>";
            }
        }
    }

    $buttons_container .= "</div>";
    writeBreadCrumb($cmt,"Activité","evenement_choice.php", $buttons_container);
}

if ( $E_ANOMALIE and $gardeSP ) {
    echo "<p><font color=red><i class='fa fa-exclamation-circle fa-lg'></i> Anomalie sur le tableau de garde.</font></p>";
}

if ( $E_VISIBLE_INSIDE == 0  ) {
    if ( $gardeSP )
        echo "<p><font color=orange><i class='fa fa-exclamation-triangle fa-lg'></i> Le tableau de garde n'est pas accessible par le personnel.</font></p>";
    else {
        // activité caché
        if ( ! $is_inscrit and ! check_rights($id,9) and ! in_array($id, $chefs)) {
            write_msgbox("ERREUR", $error_pic, "Activité n°$evenement introuvable<br><p align=center>
            <a href='index.php' target='_top'><input type='submit' class='btn btn-secondary' value='Retour'></font></a> ",10,0);
            exit;
        }
        echo "<p><font color=orange><i class='fa fa-exclamation-triangle fa-lg'></i> Activité cachée, seules les personnes inscrites ou disposant de la permission n°9 peuvent le voir.</font></p>";
    }
}

echo "<div style='background:white;' class='table-responsive table-nav table-tabs'>";
echo  "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";

// infos generales
if ( $tab == 1 or $tab == 51 or $tab ==52 or $tab == 53 or $tab == 54 or $tab == 55 or $tab == 58 or $tab == 59 or $tab == 60 or $tab==63) $class='active'; else $class='';
echo "<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=1&evenement=$evenement\" role='tab' aria-controls='tab1' href='#tab1'>
        <i class='fa fa-info-circle'></i>
        <span title=\"Informations générales sur l'activité\">Information</span></a>
    </li>";

// personnel
if ( $TE_PERSONNEL == 1 or $PIQUET==1 or $REMPLACEMENT==1) {
    if ( $syndicate == 1 ) $label = "Inscriptions";
    else $label = "Personnel";
    if ( $tab == 2 or $tab==56 or $tab==61) {
        $class='active';
        $typeclass='active-badge';
    }
    else {
        $class='';
        $typeclass='inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class'  href=\"evenement_display.php?pid=$pid&from=$from&tab=2&evenement=$evenement\" role='tab' aria-controls='tab2' href='#tab2'>
            <i class='fa fa-users'></i>
            <span title=\"Personnel inscrit et présent sur l'activité\">$label <span class='badge $typeclass'> $NP2 </span><i class='ml-1 fas fa-chevron-down fa-xs'></i></span></a>
            </li>";
}
//logistique
if ( ($vehicules == 1 and $TE_VEHICULES ==  1) || ($materiel == 1 and $TE_MATERIEL == 1) || ($consommables == 1 and $TE_CONSOMMABLES == 1) ) {
    $show_logistique=true;
    if ( $tab == 3 or $tab == 50) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=3&child=1&evenement=$evenement\" role='tab' aria-controls='tab3' href='#tab3'>
            <i class='fa fa-clipboard-list'></i>
            <span title=\"Véhicule, matériel et consommable de l'activité\">Logistique <i class='ml-1 fas fa-chevron-down fa-xs'></i></span></a>
            </li>";
}
else $show_logistique=false;

//formation
if ($competences == 1  and  $TE_CODE == 'FOR'  and  $PS_ID <> "" and $TF_CODE <> "" and $TE_PERSONNEL == 1){
    if ( $tab == 5 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=5&evenement=$evenement\" role='tab' aria-controls='tab5' href='#tab5'>
            <i class='fa fa-medal'></i>
            <span title=\"Informations concernant la formation et les diplômes\">Formation </span></a>
            </li>";
}

// factures individuelles
if ( $E_TARIF > 0 and $granted_event) {
    if ( $tab == 6 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=6&evenement=$evenement\" role='tab' aria-controls='tab6' href='#tab6'>
            <i class='fa fa-receipt'></i>
            <span title=\"Tarif et factures individuelles\">Tarif</span></a>
            </li>";
}

// documents 
if ($TE_DOCUMENT == 1) {
    if ( $tab == 7 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=7&evenement=$evenement\" role='tab' aria-controls='tab7' href='#tab7'>
            <i class='fa fa-folder-open'></i>
            <span title=\"Documents générés ou attachés à l'activité\">Document <span class='badge $badgeClass'>$NB4</span></span></a>
        </li>";
}

// Carte
if ($TE_MAP==1 and (check_rights($id,76) or $is_operateur_pc)) {
    if ( $tab == 15 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=15&evenement=$evenement&table=1\" role='tab' aria-controls='tab15' href='#tab15'>
            <i class='fa fa-map-marked'></i>
            <span title=\"Carte avec géolocalisation des équipes\">Carte</span></a>
        </li>";
}

// Rapport compte rendu
if ( $TE_MAIN_COURANTE == 1){
    if ( $tab == 8 or ($tab==21 or $tab==22 or $tab==23 or $tab==62)) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    if ( $TE_VICTIMES == 0) $t="Rapport ";
    else $t="Rapport ";
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=8&evenement=$evenement&autorefresh=$autorefresh\" role='tab' aria-controls='tab8' href='#tab8'>
            <i class='fa fa-tasks'></i>
            <span title=\"Compte rendu et main courante\">".$t."<span class='badge $badgeClass'>$NB5</span></span></a>
            </li>";
}

// Client
if ( $voircompta and $CLIENT==1) {
    if ( $tab == 17 ){
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    $query = "SELECT devis_accepte, facture_date, relance_date, paiement_date FROM evenement_facturation Where E_ID = $evenement";
    $row = $dbc->query($query)->fetch_row();
    $letter='';
    $letters = ['D','F','R','P'];
    for($i = 3; $i >=0; $i--){
        if(@$row[$i] != null){
            $letter=$letters[$i];
            break;
        }
    }
    $badge = $letter != '' ? "<span class='badge $badgeClass'>$letter</span>" : "";
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=17&evenement=$evenement&table=1\" role='tab' aria-controls='tab17' href='#tab17'>
            <i class='fa fa-keyboard'></i>
            <span title=\"Afficher les informations de devis, facture, et paiement pour le client\">Client $badge</span> <i class='ml-1 fas fa-chevron-down fa-xs'></i></a>
            </li>";
}

// DPS
if ( $TE_DPS==1 ) {
    if ( $tab == 16 ) $class='active'; else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=16&evenement=$evenement&table=1\" role='tab' aria-controls='tab16' href='#tab16'>
            <i class='fa fa-th-list'></i>
            <span title=\"Dimentionnement du dispositif prévisionnel de secours\">DPS</span></a>
    </li>";
}

// historique
if (check_rights($id,49)) {
    if ( $tab == 9 ) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else {
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"evenement_display.php?pid=$pid&from=$from&tab=9&evenement=$evenement&lccode=E&lcid=$evenement&order=LH_STAMP&ltcode=ALL&table=1\" role='tab' aria-controls='tab7' href='#tab7'>
            <i class='fa fa-history'></i>
            <span title=\"Historique des modifications pour cette activité\">Historique <span class='badge $badgeClass'>$nHistory</span></span></a>
            </li>";
}
echo "\n"."</ul>"; // fin tabs
echo "</div>";

// bloquer les changements dans le passé
$ended=get_number_days_after_block($evenement);
$changeallowed=true;
if ( $ended > 0 ) {
    if ( ! $granted_delete ) {
        $granted_personnel=false;
        $granted_vehicule=false;
        $granted_inscription=false;
        $documentation=false;
        $changeallowed=false;
        $granted_consommables=false;
    }
    $link="<a href=upd_section.php?S_ID=".$organisateur."&status=parametrage title='Voir la configuration'>".$ended."</a>";

    if (! $print ) echo "<table class='noBorder noprint'><tr><td><i class='fa fa-exclamation-triangle' style='color:$widget_fgorange' title=\"Cette activité n'est plus modifiable, sauf par les personnes ayant la permission n°19\"></i></td>
                    <td><small>Les modifications sur cette activité terminé ne sont plus possibles depuis ".$link." jours.</small></td>
                    </tr></table>";
}

//=====================================================================
// équipes 
//=====================================================================

if ( intval($E_PARENT) > 0 ) $evts_list=$evenement.",".intval($E_PARENT);
else $evts_list=$evenement;

$querye="select E_CODE, EE_ID, EE_NAME, EE_DESCRIPTION
         from evenement_equipe 
         where E_CODE in (".$evts_list.")
         order by EE_ORDER, EE_NAME";
$resulte=mysqli_query($dbc,$querye);
$equipes=array();
while ($rowe=@mysqli_fetch_array($resulte)) {
    array_push($equipes, array($rowe["EE_ID"],$rowe["EE_NAME"]));
}
$nbe=sizeof($equipes);

//=====================================================================
// titre si impression
//=====================================================================
if ( $print) {

    $logo=get_logo();

    $queryz="select S_DESCRIPTION from section where S_ID = 0";
    $resultz=mysqli_query($dbc,$queryz);
    $rowz=mysqli_fetch_array($resultz);
    $S_DESCRIPTION0 =  $rowz["S_DESCRIPTION"];

    if ( $gardeSP ) {
        echo "<table class='noBorder'><tr>
        <td width=90><img src=".$logo." style='max-width:60px';></td>
        <td><font size=5>".$S_DESCRIPTION0."</font><br><font size=4>".get_info_evenement($evenement)."</font></td>
        </tr></table>";
    }
    else {
        echo "<table class='noBorder'><tr><td width=90><img src=".$logo." style='max-width:60px';></td><td><font size=5>".$S_DESCRIPTION0."</font><br><font size=4>Section : ".$S_CODE." - ".$S_DESCRIPTION."</font></td></tr></table>
        <p>Bonjour, veuillez trouver ci-dessous les éléments relatifs à la mise en place de :<br>
        ".$TE_LIBELLE." - ".$E_LIBELLE." (".$E_LIEU.")";

        for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
            if (isset($horaire_evt[$i]))
                echo "<br>".$horaire_evt[$i];
        }

        // info demandeur DPS
        if ( $C_ID <> "" and $C_ID > 0)
            echo "<br>Pour le compte de ".get_company_name($C_ID)."";
    }
}

//=====================================================================
// informations générales
//=====================================================================
if ( $tab == 1 or $print){
    if(isset($_GET['upd_company']) and $_GET['upd_company'] == 1 and isset($_GET['C_ID'])){
        require_once('upd_company.php');
        exit;
    }
    if (!$print) {
        echo "<div align=right class='tab-buttons-container'>";
        $disabled='';$t="";
        
        //Switch ouvert
        if ( $E_AUTOCLOSE_BEFORE >= 0 ) {
            $t=" Attention il y a une clôture automatique activée pour cette activité.";
            if (isset($REMAINING) and $REMAINING <= 0 ) $t .= " Et il est trop tard pour le réouvrir.";
        }

        if ( $E_CANCELED == 0 and $TE_PERSONNEL == 1 and (!$gardeSP or $assoc)) {
            if ( $E_CLOSED == 0  ){
                $checked='checked';
                $action='close';
            }
            else {
                $checked='';
                $action='open';
                
                if ( $granted_event and $changeallowed) {
                    // ne pas permettre d'ouvrir un renfort si le principal est fermé
                    $queryd="select E_CLOSED from evenement where E_CODE =".intval($E_PARENT);
                    $resultd=mysqli_query($dbc,$queryd);
                    $rowd=@mysqli_fetch_array($resultd);
                    $c=@$rowd["E_CLOSED"];
                    if ( $c == 1 and ! check_rights($id, 14) and ! $chef ) {
                        $disabled="disabled";
                        $t .=" On ne peut pas réouvrir un ".$renfort_label." pour lequel l'activité principale est clôturé";
                    }
                    else {
                        if ( $E_AUTOCLOSE_BEFORE >= 0 ) {
                            if ( $REMAINING <= 0 ) {
                                $disabled="disabled";
                            }
                        }
                        else $t .=" Ouvrir les inscriptions pour cette activité et ses ".$renfort_label."s";
                    }
                }
            }
            
            if ( $granted_event ) {
                echo "<div style = 'float:left;margin-top:10px;margin-left:10px;' > Inscription</div>";
                echo "<label class='switch' style='float:left; margin-top:12px;margin-left:3px;margin-right:12px;'>
                     <input type='checkbox' value='1' $checked $disabled
                            onclick='bouton_redirect(\"evenement_inscription.php?evenement=".$evenement."&action=".$action."\",\"".$action."\");' >
                            <span class='slider round' title='Ouvrir ou fermer les inscriptions sur cette activité. ".$t."'></span>
                        </label>";
            }
            
            if ( check_rights($id, 77) and $notes ==1)
                echo "<span style='margin-left:10px;'><a class='btn btn-success' value=\"+ Note de frais\" title=Créer une note de frais pour me faire rembourser de mes dépenses sur cette activité'
                        onclick='bouton_redirect(\"note_frais_edit.php?evenement=".$evenement."&action=insert&person=".$id."\",\"note de frais\");'>
                        <i class='fas fa-plus-circle' style='color:white' data-original-title='' title=''></i> Note de Frais</a></span>";
        }

        //=====================================================================
        // email notifications
        //=====================================================================

        if ( $granted_event and ! $print and $changeallowed  and (! $gardeSP or $assoc ) and $TE_CODE <> 'MC' and $E_VISIBLE_INSIDE == 1) {
            // email ouverture
            if ( $E_CLOSED == 0 and $E_CANCELED == 0 ) {
                if ( $E_MAIL1 == 0 ) {
                    echo "<input type='button' class='btn btn-primary' value='Message ouverture'
                        title=\"envoyer un message à tout le personnel pour les inviter à s'inscrire\"
                        onclick='bouton_redirect(\"evenement_notify.php?evenement=".$evenement."&action=enroll\",\"notify\");'>";
                }
            }

            // email cloture
            if ( $E_CLOSED == 1 and $E_CANCELED == 0 ) {
                if ( $ORDRE_MISSION ) {
                    $link="pdf_document.php?section=".$organisateur."&evenement=".$evenement."&mode=4&signed=0";
                    $attached=", avec l'ordre de mission 
                    <a href=".$link." title='Voir ordre de mission' target='_blank'><i class='far fa-file-pdf fa-lg' style='color:$widget_fgred;' ></i></a>";
                }
                else $attached = "";
                if ( $E_MAIL2 == 0 ) {
                    echo "<input type='button' class='btn btn-primary' value='Message aux inscrits' 
                        title=\"envoyer un message aux inscrits pour confirmer leur participation\"
                        onclick='bouton_redirect(\"evenement_notify.php?evenement=".$evenement."&action=closed\",\"notify\");'>";
                }
            }
            // email annulation
            if ( $E_CANCELED == 1 ) {
                if ( $E_MAIL3 == 0 ) {
                    echo "<input type='button' class='btn btn-primary' value='Message annulation'
                        title=\"envoyer un message aux inscrits pour leur indiquer que l'activité est annulée\"
                        onclick='bouton_redirect(\"evenement_notify.php?evenement=".$evenement."&action=canceled\",\"notify\");'>";
                }
            }
        }
        
        $laterBt='';
        if ( $granted_delete and ! $gardeSP) {
            $csrf = generate_csrf('delete');
            $laterBt.= " <input type='button' class='btn btn-danger' value='Supprimer' 
            onclick='bouton_redirect(\"evenement_save.php?action=delete&evenement=".$evenement."&csrf_token_delete=".$csrf."\",\"delete\");'> ";
        }
        else if ( $granted_delete and $gardeSP and ($E_PARENT <> NULL or $E_EQUIPE == 0) ) {
            $csrf = generate_csrf('delete');
            $laterBt.= " <input type='button' class='btn btn-danger' value='Supprimer' 
            onclick='bouton_redirect(\"evenement_save.php?action=delete&evenement=".$evenement."&csrf_token_delete=".$csrf."\",\"delete\");'> ";
        }

        if ( $granted_event and $changeallowed) {
            $laterBt.= " <a class='btn btn-primary'
          onclick='bouton_redirect(\"evenement_display.php?tab=60&evenement=".$evenement."&action=update\",\"update\");'>Modifier</a> ";
        }

        if ( $granted_event ) {
            echo "<div class='btn-group' style='position:inherit;margin-right:25px;'>
                  <button class='btn btn-primary dropdown-toggle' type='button' id='dropdownMenuButton1' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' >
                    Dupliquer
                  </button>
                  <div class='dropdown-menu' style ='margin-right:23px' aria-labelledby='dropdownMenuButton1'>
                   <a class ='dropdown-item' onclick='bouton_redirect(\"evenement_edit.php?evenement=".$evenement."&action=copy\",\"copy\");'><div>Une fois</div></a>";
            if ($TE_MULTI_DUPLI == 1 ) {
                echo " <a class ='dropdown-item' title='Dupliquer multiple, possible seulement pour les activités à une seule partie'
                onclick='bouton_redirect(\"evenement_duplicate.php?evenement=".$evenement."\",\"update\");'><div>Plusieurs fois</div></a>";
            }
            echo "</div></div>";
        }

        //=====================================================================
        // boutons d'inscription /désinscription
        //=====================================================================

        $query="select DATEDIFF(NOW(), ep.EP_DATE) as NB_DAYS 
                    from evenement_participation ep, evenement e
                    where ep.E_CODE = e.E_CODE
                    and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")
                    and ep.P_ID=".$id;
        $r1=mysqli_query($dbc,$query);
        $num=mysqli_num_rows($r1);

        //Ajout renforts
        if ( $nbsections == 0 and ! $gardeSP
            and ( check_rights($id, 15) or $chef )
            and  $E_ALLOW_REINFORCEMENT == 1
            and  $E_PARENT == '' ) {
            if ( $E_CLOSED == 1 or $E_CANCELED == 1 ) $disabled='disabled';
            else $disabled="";

            // pour une ADPC on peut créer les renforts pour chaque antenne
            $level_orga = get_level("$S_ID");
            if ( $nbsections == 0 and ($level_orga == $nbmaxlevels - 2  or $level_orga <= 2  )) {
                if ( $level_orga <= 2 ) $t=$levels[3];
                else $t=$levels[4];

                echo "<div class='btn-group' style='float:left; position:inherit;margin-left:10px'>
                      <button class='btn btn-primary dropdown-toggle' type='button' id='dropdownMenuButton2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' style='float:left' > Renfort</button>
                          <div class='dropdown-menu' style ='position:relative'aria-labelledby='dropdownMenuButton2'>
                            <a class='dropdown-item' href='evenement_edit.php?evenement=".$evenement."&action=renfort'>Créer ".$renfort_label." simple</a></li>
                            <a class='dropdown-item' href='evenement_display.php?evenement=".$evenement."&tab=59'>Créer ".$renfort_label." pour chaque ".$t."</a>
                          </div>
                    </div>";
            }
            else
                echo "<input type='button' style='margin-left:10px;float:left' class='btn btn-primary' value='Créer ".$renfort_label."' title='créer une activité en ".$renfort_label." de celle-ci' $disabled
                        onclick='bouton_redirect(\"evenement_edit.php?evenement=".$evenement."&action=renfort\",\"renfort\");'> ";
        }
        echo "</div>";
    }

    if ( $E_CREATED_BY <> '' and ! $print)
        $author = "<font size=1><i> - créé par ".my_ucfirst(get_prenom($E_CREATED_BY))." ".strtoupper(get_nom($E_CREATED_BY))."
                   le ". $E_CREATE_DATE."
                    </i></font>";
    else
        $author='';

    echo "<div class='table-responsive'>
            <div class='container-fluid'>
            <div class='row'>";
    if ( $print )
        echo "<div class='col-sm-8'>";
    else
        echo "<div class='col-sm-4'>";
    
    echo "<div class='card hide card-default graycarddefault' align=center >
                <div class='card-header graycard'>
                <div class='card-title'><strong> Activité n° ".$E_CODE ."</strong></div>
                </div>
                    <div class='card-body graycard'>";

    echo "<table class='noBorder'>";

    if ( intval($E_PARENT) > 0 and  $nbsections == 0) {
        echo "<tr><td width=33%>".ucfirst($renfort_label)." pour </td>";
        $queryR="select e.TE_CODE, e.E_LIBELLE, s.S_CODE, s.S_DESCRIPTION
                from evenement e, section s 
                where s.S_ID = e.S_ID
                and e.E_CODE=".$E_PARENT;
        $resultR=mysqli_query($dbc,$queryR);
        $rowR=@mysqli_fetch_array($resultR);
        $ER_LIBELLE=stripslashes($rowR["E_LIBELLE"]);
        $SR_CODE=$rowR["S_CODE"];
        $SR_DESCRIPTION=$rowR["S_DESCRIPTION"];
        echo "<td width=67%><a href=evenement_display.php?evenement=".$E_PARENT.">
        ".$ER_LIBELLE." organisé par ".$SR_CODE." - ".$SR_DESCRIPTION."</a></td></tr>";
    }

    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
        if ( $nbsessions == 1 ) $t="Dates et heures";
        else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
        if ( isset($horaire_evt[$i])) {
            if ( $description_partie[$i] <> "" ) $dp = " - <i>".$description_partie[$i]."</i>";
            else $dp="";
            echo "<tr style='color:#3f4254'><td>".$t." </td>
                <td> ".$horaire_evt[$i].$dp."
             </td></tr>";
        }
    }
    if($E_DUREE_TOTALE <> ''){
        echo "<tr style='color:#3f4254'><td>Durée totale </td>
            <td> ".$E_DUREE_TOTALE." heures</td></tr>";
    }

    if ( $E_ADDRESS <> "" ) {
        $map="";
        if ( $geolocalize_enabled ) {
            $querym="select LAT, LNG from geolocalisation where TYPE='E' and CODE=".$evenement;
            $resultm=mysqli_query($dbc,$querym);
            $NB=mysqli_num_rows($resultm);
            if ( $NB > 0 ) {
                custom_fetch_array($resultm);
                $url = $waze_url."&ll=".$LAT.",".$LNG."&pin=1";
                $map = " <a href=".$url." target=_blank><i class='fab fa-waze fa-lg' title='Voir la carte Waze' class='noprint'></i></a>";
                if ( check_rights($id,76) or $is_operateur_pc)
                    $map .= " <a href=sitac.php?evenement=".$evenement." ><i class='fa fa-map noprint' style='color:$widget_fggreen;' title=\"Voir la carte Google Maps\"></i></a>";
            }
        }
        echo "<tr style='color:#3f4254'><td width=30%>Adresse </td>
            <td width=70%>".$E_ADDRESS." ".$map."</td></tr>";
    }

    echo "<tr style='color:#3f4254'><td width=30%>Lieu </td>
            <td width=70%> ".$E_LIEU."</td></tr>";

    if( $E_HEURE_RDV <> '' or $E_LIEU_RDV <> ''){
        echo "<tr style='color:#3f4254'><td>RDV </td>
            <td> ".$E_HEURE_RDV." ".$E_LIEU_RDV."</td></tr>";
    }

    if ( $gardeSP and $pompiers == 1) {
        $t = 'Section de garde';
        $desc = "<a href=upd_section.php?S_ID=".$S_ID.">".$S_CODE."</a>";
        $SECTION_GARDE=get_section_garde_evenement($evenement, 1);
        if ( $SECTION_GARDE > 0 and $SECTION_GARDE <> $S_ID ) {
            $desc .= " - <i class='fa fa-sun fa-lg' style='color:yellow;' title='section du jour'></i> 
                        <a href=upd_section.php?S_ID=".$SECTION_GARDE."> ".get_section_code($SECTION_GARDE)."</a>";
            $SECTION_GARDE2=get_section_garde_evenement($evenement, 2);
            if ( $SECTION_GARDE <> $SECTION_GARDE2 )
                $desc .= " - <i class='fa fa-moon fa-lg' style='color:black;' title='section nuit'></i> 
                            <a href=upd_section.php?S_ID=".$SECTION_GARDE2.">".get_section_code($SECTION_GARDE2)."</a>";
        }
    }
    else {
        $t = 'Organisateur';
        $desc = "<a href=upd_section.php?S_ID=".$S_ID.">".$S_CODE." - ".get_section_name($organisateur)."</a>";
    }
    echo "<tr style='color:#3f4254'><td>".$t." </td>
            <td>".$desc."</td></tr>";

    if ( $granted_event and ( $E_CONTACT_LOCAL <> '' or $E_CONTACT_TEL <> '')) {
        echo "<tr  style='color:#3f4254'><td width=25% >Contact sur place </td>";
        if ( $E_CONTACT_TEL <> '') {
            if (is_iphone()) $E_CONTACT_TEL=" - <a href='tel:".$E_CONTACT_TEL."'>".$E_CONTACT_TEL."</a> ";
            else $E_CONTACT_TEL="- ".$E_CONTACT_TEL;
        }
        echo "<td width=75%>".$E_CONTACT_LOCAL." ".$E_CONTACT_TEL."</td></tr>";
    }

    if ( intval($E_TEL) > 0 )
        echo "<tr style='color:#3f4254'><td title=\"Donne tous les droits d'accès sur cet évenement\">Téléphone Contact</td>
            <td><a href='tel:".$E_TEL."'>".$E_TEL."</a></td></tr>";


    if ( $E_WHATSAPP <> "" and ($is_inscrit or $granted_personnel)) {
        echo "<tr style='color:#3f4254'><td>Groupe Whatsapp</td>
                <td><a href=\"".$whatsapp_chat_url."/".$E_WHATSAPP."\" target='_blank'
                title=\"Rejoindre ou communiquer avec le groupe Whatsapp de cette activité.\">
                <i class='fab fa-whatsapp-square fa-2x' style='color:#00cc00'></i></a></td></tr>";
    }
    
    if ( $C_ID <> '' and $C_ID > 0 ) {
        echo "<tr><td>Pour le compte de </td>";
        $link = "evenement_display.php?tab=1&evenement=$evenement&upd_company=1&C_ID=$C_ID";
        if (check_rights($id, 37)) $edit="<a class='btn btn-default btn-action' href='$link'><i class='fa fa-edit fa-lg'></i></a>";
        else $edit='';
        echo "<td>".get_company_name($C_ID)." $edit</td></tr>";
    }
    echo "</table></div></div></div><div class='col-sm-5' align=center>
                        <div class='card hide card-default graycarddefault' align=center >
                <div class='card-header graycard'>
                <div class='card-title'><strong> Personnel </strong> ";
    if ( ! $print and $TE_PERSONNEL == 1) {
        if ( $E_CANCELED == 1 ) {
            if ( $E_CANCEL_DETAIL <> '' ) $pr=" - ".$E_CANCEL_DETAIL." ";
            else $pr='';
            echo "<div style='text-align:right'><font size=3 color=red>Evénement annulé ".$pr."</font></div>";
        }
        else if ( ! $gardeSP ) {
            if ( $E_CLOSED == 1 ) echo "<div style='float:right;display:inline-block;$widget_all_orange;padding:5px;border-radius:5px;margin-top:-5px'>Inscriptions fermées</div>";
            else if ( $OPEN_TO_ME == 0 ) echo "<div style='float:right;display:inline-block;$widget_all_orange;padding:5px;border-radius:5px;margin-top:-5px'>Inscriptions interdites pour les personnes des autres ".$levels[3]."s</div>";
            else if ( $OPEN_TO_ME == -1 ) echo "<div style='float:right;display:inline-block;$widget_all_orange;padding:5px;border-radius:5px;margin-top:-5px'>Inscriptions possibles pour les personnes des autres ".$levels[3]."s par leur responsable</div>";
            else echo "<div style='float:right;display:inline-block;$widget_all_green;padding:5px;border-radius:5px;margin-top:-5px'>Inscriptions ouvertes</div>";
        }
    }

    echo"</div>
                </div>
                    <div class='card-body graycard'><table class='noBorder'>";

    if ( $E_URL <> "" ) {
        if ( $cisname == 'Protection Civile' ) $t = "Lien URL vers calendrier ADPC";
        else $t = "Lien URL vers descriptif";
        $E_URL=str_replace("http://","",$E_URL);
        $E_URL=str_replace("https://","",$E_URL);
        echo "<tr  style='color:#3f4254'><td width=30%>".$t."</td>
            <td width=70%><a href=http://".$E_URL." target='_blank'>".$E_URL."</a></td></tr>";
    }

    if ( $syndicate == 1 )  $t = "Gestionnaire de l'activité";
    else $t = "Responsable ".$cisname;

    echo "<tr style='color:#3f4254'><td title=\"Donne tous les droits d'accès sur cet évenement\"> ".$t." </td>
            <td>";
    if ( count($chefs) > 0 ) {
        for ( $c = 0; $c < count($chefs); $c++ ) {
            $queryz="select ".phone_display_mask('P_PHONE')." P_PHONE, P_HIDE, P_NOM, P_PRENOM from pompier where P_ID=".$chefs[$c];
            $resultz=mysqli_query($dbc,$queryz);
            $rowz=mysqli_fetch_array($resultz);
            $phone =  $rowz["P_PHONE"];
            $P_HIDE = $rowz["P_HIDE"];
            if ( $syndicate == 1 or intval($E_TEL) > 0 ) $phone="";
            else if ( $phone <> '' ) {
                if (is_iphone())
                    $phone=" <small><a href='tel:".$phone."'>".$phone."</a></small>";
                else
                    $phone = " - ".$phone;
                if ($P_HIDE == 1 and  $nbsections == 0 ) {
                    if (( ! $ischef )
                        and ( ! in_array($id, $chefs) )
                        and (! check_rights($id, 2))
                        and (! check_rights($id, 12)))
                        $phone=" - **********";
                }
            }
            echo "<a href=upd_personnel.php?pompier=".$chefs[$c]." title=\"A tous les droits d'accès sur cet évenement\"> 
                ".my_ucfirst($rowz["P_PRENOM"])." ".strtoupper($rowz["P_NOM"])."</a> ".$phone;
            if ( $c < (count($chefs) -1) ) echo "<br>";
        }
    }

    if ( $granted_event and (!$print) and $changeallowed) {
        $url="evenement_display.php?evenement=".$evenement."&what=responsable&tab=51";
        echo "<a href='".$url."'><i class='fa fa-user fa-lg' title='choisir les responsables'></i></a>";
    }
    echo "</td></tr>";

    // compétences requises
    $querym="select EH_ID from evenement_horaire where E_CODE=".$evenement." order by EH_ID";
    $resultm=mysqli_query($dbc,$querym);

    if ( $TE_PERSONNEL == 1 and $TE_CODE <> 'MC' ) {
        if ( $nbsessions == 1 ) $showcpt = "<tr style='color:#3f4254'><td width=30%>Personnel requis </td><td>";
        else $showcpt = "<tr><td colspan=2>Personnel requis ";

        // options inscriptions
        if ( !$print and $granted_event and ! $gardeSP ) {
            echo "<tr style='color:#3f4254'><td>Options inscription </td><td>";
            if ( $E_PARENT > 0 ) $e = $E_PARENT;
            else $e = $evenement;
            $nboptions=count_entities("evenement_option", "E_CODE=".$e);
            if ( $nboptions > 0 ) echo  "<span class='badge'>".$nboptions." options</span>";
            echo  " <a class='btn btn-default btn-action' href=evenement_display.php?evenement=".$e."&renfort=".$evenement."&tab=52 title=\"Voir et modifier les options d'inscriptions\">
                <i class='fa fa-edit fa-lg'></i></a></td></tr>";
        }

        while ( $rowm=mysqli_fetch_array($resultm) ) {
            $i=$rowm["EH_ID"];
            if ( $nbsessions > 1 ) $showcpt .= "</td></tr><tr style='color:#3f4254'><td align=right><font size=1>partie ". $i."</font></td><td>";
            $nbt=0;

            // -------------------------
            // PARTIE A OPTIMISER - ne pas executer une fois par partie
            // -------------------------
            $queryt="select ec.nb 'nbt'
                    from evenement_competences ec 
                    where ec.E_CODE=".$evenement." 
                    and ec.PS_ID = 0
                    and ec.EH_ID=".$i;
            $resultt=mysqli_query($dbc,$queryt);
            custom_fetch_array($resultt);

            $queryp="select ec.PS_ID, p.TYPE, p.DESCRIPTION, ec.nb 
                    from evenement_competences ec , poste p
                    where ec.E_CODE=".$evenement." 
                    and ec.PS_ID = p.PS_ID
                    and ec.EH_ID=".$i."
                    order by p.PH_LEVEL desc, p.PS_ORDER, ec.PS_ID";
            $resultp=mysqli_query($dbc,$queryp);
            $nbp=mysqli_num_rows($resultp);
            // -------------------------
            // FIN PARTIE A OPTIMISER 
            // -------------------------

            // total personnel demandé
            $type='TOTAL';
            $inscrits=get_nb_competences($evenement,$i,0);
            if ( $inscrits == $nbt ){
                $fgcolor=$widget_fggreen;
                $bgcolor=$widget_bggreen;
            }
            else if ($inscrits > $nbt ) {
                if ( $gardeSP ){
                    $fgcolor=$widget_fgorange;
                    $bgcolor=$widget_bgorange;
                }
                else{
                    $fgcolor=$widget_fgblue;
                    $bgcolor=$widget_bgblue;
                }
            }
            else{
                $fgcolor=$widget_fgred;
                $bgcolor=$widget_bgred;
            }
            $desc = $inscrits." participants.";
            $showcpt .= " <a title=\"$nbt personnes requises\n".$desc."\"><span class='badge' style='background-color:$bgcolor; color:$fgcolor'> $nbt </span></a>";
            if ( $nbp > 0 ) $showcpt .= " <small>dont </small>";

            // détail par compétence
            while ( custom_fetch_array($resultp) ) {
                $inscrits=get_nb_competences($evenement,$i,$PS_ID);
                if ($inscrits >= $nb ){
                    $fgcolor=$widget_fggreen;
                    $bgcolor=$widget_bggreen;
                }
                else{
                    $fgcolor=$widget_fgred;
                    $bgcolor=$widget_bgred;
                }
                $desc=$nb." ".$DESCRIPTION." requis, ";
                if ( $inscrits < 2 ) $desc .= $inscrits." participant ayant cette compétence valide.";
                else $desc .= "\n".$inscrits." participants ayant cette compétence valide.";
                $showcpt .= " <a title=\"".$desc."\"><span class='badge' style='background-color:$bgcolor; color:$fgcolor'>$nb $TYPE</span></a>";
                $showcpt = rtrim($showcpt,',');
            }
            if ( $granted_event and (!$print) and $changeallowed ) {
                if ( $competences ) $cmt = 'Modifier les compétences demandées';
                else $cmt = 'Modifier le nombre de personnes demandées';
                $showcpt .= " <a class='btn btn-default btn-action' href='#'><i class='fa fa-edit fa-lg' title='".$cmt."' 
                            onclick=\"modifier_competences('".$evenement."',".$i.",58)\"></i></a>";
            }
        }
        $showcpt = rtrim($showcpt,',');
        $showcpt .= "</td></tr>";
        print $showcpt;
    }

    // équipes, groupes (seulement pour activité principale)
    if ($E_PARENT == '' and  $TE_PERSONNEL == 1 and $TE_CODE <> 'MC') {
        $querym="select EE_ID, EE_NAME, EE_DESCRIPTION from evenement_equipe
            where E_CODE=".$evenement."
            order by EE_ORDER,EE_NAME";
        $resultm=mysqli_query($dbc,$querym);
        $nbm=mysqli_num_rows($resultm);

        $showcpt = "<tr style='color:#3f4254'><td width=30%>Equipes</td><td>";

        while ( $rowm=mysqli_fetch_array($resultm) ) {
            $EE_ID=$rowm["EE_ID"];
            $type=$rowm["EE_NAME"];
            $desc=$rowm["EE_DESCRIPTION"];
            $showcpt .= " <a href=evenement_display.php?tab=55&evenement=".$evenement."&equipe=".$EE_ID."&action=update>".$type."</a>,";
        }
        $showcpt = rtrim($showcpt,',');

        if ( !$print and ( $granted_event or $is_operateur_pc))
            $showcpt .= " <a class='btn btn-default btn-action' href=evenement_display.php?tab=55&evenement=".$evenement." title=\"Voir l'organisation des équipes\">
                <i class='fa fa-edit fa-lg'></i></a>  ";
        $showcpt .= "</td></tr>";
        print $showcpt;
    }

    // cas du DPS
    if ( $TE_CODE == 'DPS' ) {
        $warn="";
        if ( $TAV_ID == 1  or  $TAV_ID == '' ) $tdps='Non défini';
        else {
            // type de DPS choisi
            $querydps="select TAV_ID, TA_VALEUR from type_agrement_valeur
                   where TA_CODE = 'D'
                   and TAV_ID=".$TAV_ID;
            $resultdps=mysqli_query($dbc,$querydps);
            $rowdps=mysqli_fetch_array($resultdps);
            $tdps = $rowdps["TA_VALEUR"];

            //comparer avec agrément
            $queryag="select a.S_ID, a.A_DEBUT, a.A_FIN, tav.TAV_ID, tav.TA_VALEUR,
                        DATEDIFF(NOW(), a.A_FIN) as NB_DAYS
                        from agrement a, type_agrement_valeur tav
                        where a.TA_CODE=tav.TA_CODE
                        and a.TAV_ID= tav.TAV_ID
                        and a.TA_CODE='D'
                        and a.S_ID in (".$S_ID.",".get_section_parent("$S_ID").")";
            $resultag=mysqli_query($dbc,$queryag);
            $rowag=mysqli_fetch_array($resultag);
            $debut = @$rowag["A_DEBUT"];
            $tag = @$rowag["TA_VALEUR"];
            $tagid = @$rowag["TAV_ID"];
            $nbd = @$rowag["NB_DAYS"];
            $sectionag = @$rowag["S_ID"];

            if ( $tagid <> "" and ( !$print)) {
                if ( $TAV_ID > $tagid or $debut == '') {
                    $title="ATTENTION Il n'y a pas d'agrément ou l'agrément est insuffisant pour ce type de DPS.";
                    if ( $tagid > 1 and $debut <> '')
                        $title .=" L'agrément permet seulement l'organisation de DPS de type $tag.";
                    $warn_img="<i class='fa fa-exclamation-circle fa-lg' style='color:$widget_fgred;' title=\"$title\" ></i>";
                }
                else if  ( $nbd > 0  )
                    $warn_img="<i class='fa fa-exclamation-circle fa-lg' style='color:$widget_fgred;' title=\"ATTENTION agrément pour les DPS périmé\" ></i>";
                else if ( $DPS_MAX_TYPE <> '' and $DPS_MAX_TYPE < $TAV_ID ) {
                    $warn_img="<i class='fa fa-exclamation-triangle' style='color:$widget_fgorange' title=\"ATTENTION le $levels[3] ne permet pas à cette $levels[4] d'organiser ce type de DPS\" border=0></i>";
                    $warn="<a href=upd_section.php?S_ID=".$S_ID.">".$warn_img."</a>";
                }
                else
                    $warn_img="<i class='fa fa-check fa-lg' style='color:$widget_fggreen'
                        title=\"Agrément valide pour ce type de DPS\"></i>";

                if ( $warn == '')
                    $warn="<a href=upd_section.php?S_ID=".$sectionag."&status=agrements>".$warn_img."</a>";
            }
        }
        if ( $E_FLAG1 == 1 ) $interassociatif='Inter-associatif, ';
        else $interassociatif='';
        echo "<tr style='color:#3f4254'><td width=30%>Type de DPS</td>
            <td width=70%> ".$interassociatif." ".$tdps." ".$warn."</td></tr>";
    }

    if ( $E_CONVENTION <> "" ) {
        echo "<tr style='color:#3f4254'><td width=30%>Numéro de convention</td>
            <td width=70%> ".$E_CONVENTION."</td></tr>";
    }
    if ( $E_DATE_ENVOI_CONVENTION <> "" ) {
        echo "<tr style='color:#3f4254'><td width=30%>Date envoi convention</td>
            <td width=70%> ".$E_DATE_ENVOI_CONVENTION."</td></tr>";
    }
    if ($E_CLOSED == 0  and  $nbsections == 0 and  $TE_PERSONNEL == 1) {
        if ( $E_OPEN_TO_EXT == 1 && $E_ALLOW_REINFORCEMENT == 1 )
            $cmt="Possibles pour les personnes des autres ".$levels[3]."s et pour les ".$renfort_label."s.";
        elseif ( $E_OPEN_TO_EXT == 1 && $E_ALLOW_REINFORCEMENT == 0 )
            $cmt="Possibles pour les personnes extérieures.";
        elseif ( $E_OPEN_TO_EXT == 0 && $E_ALLOW_REINFORCEMENT == 1 )
            $cmt="Impossibles pour les personnes des autres ".add_final_s($levels[3]).", mais possible pour les ".$renfort_label."s.";
        else
            $cmt="Impossibles pour les personnes des autres ".add_final_s($levels[3])." et pour les ".$renfort_label."s.";
    }
    else  {
        if ( $E_OPEN_TO_EXT == 1)
            $cmt="Possibles pour les personnes des autres ".add_final_s($levels[3]).".";
        else
            $cmt="Impossibles pour les personnes des autres ".add_final_s($levels[3]);
    }
    if ( ! $print and ! $gardeSP and $TE_PERSONNEL == 1 and $TE_CODE <> 'MC')
        echo "<tr style='color:#3f4254'><td width=30%>Inscriptions</td> 
                 <td width=70%>".$cmt."</td></tr>";

    if ( $E_COMMENT <> "" ) {
        echo "<tr style='color:#3f4254'><td width=30%>Détails</td>
            <td width=70%> ".$E_COMMENT."</td></tr>";
    }
    if ( $E_AUTOCLOSE_BEFORE > -1 ) {
        if ( $E_AUTOCLOSE_BEFORE >= 24 ) {
            $TIME_BEFORE = $E_AUTOCLOSE_BEFORE / 24;
            $TIME_BEFORE .= " jours avant le début";
        }
        else if ( $E_AUTOCLOSE_BEFORE > 0 ) {
            $TIME_BEFORE = $E_AUTOCLOSE_BEFORE." heures avant le début";
        }
        else $TIME_BEFORE = "au début, quand elle commence";

        echo "<tr style='color:#3f4254'><td width=30%>Clôture automatique</td>
            <td width=70%><i class='fa fa-exclamation-triangle' style='color:$widget_fgorange' title=\"Date limite pour les inscriptions ".$TIME_BEFORE.".\" ></i> 
             L'activité est automatiquement clôturée ".$TIME_BEFORE.", soit ".$CLOSE_TIME.".</td></tr>";
    }
    if ( $E_VISIBLE_OUTSIDE == 1 ) {
        echo "<tr><td width=30%>Visible de l'extérieur </td>
            <td width=70%>Peut être vu dans un site externe sans identification <i class='fa fa-exclamation-triangle noprint' style='color:$widget_fgorange' title=\"Visible de l'extérieur\"></i></td></tr>";
    }
    if ( $E_COMMENT2 <> "" ) {
        echo "<tr><td width=30%>Commentaire extérieur </td>
            <td width=70%> ".$E_COMMENT2."</td></tr>";
    }

    if ( $C_ID <> '' and $C_ID > 0 ) {

        // responsable formation ou opérationnel
        $queryr="select p.P_ID, p.P_NOM, p.P_PRENOM, ".phone_display_mask('p.P_PHONE')." P_PHONE , tcr.TCR_DESCRIPTION
                    from pompier p, company_role cr, type_company_role tcr 
                    where p.P_ID=cr.P_ID
                    and tcr.TCR_CODE = cr.TCR_CODE
                    and cr.C_ID=".$C_ID;
        if ( $TE_CODE == 'FOR' ) $queryr .=" and cr.TCR_CODE='RF'";
        else $queryr .=" and cr.TCR_CODE='RO'";
        $resultr=mysqli_query($dbc,$queryr);
        $rowr=mysqli_fetch_array($resultr);
        $TCR_DESCRIPTIONr =  @$rowr["TCR_DESCRIPTION"];
        $P_IDr         =  @$rowr["P_ID"];
        $P_NOMr     =  @$rowr["P_NOM"];
        $P_PRENOMr     =  @$rowr["P_PRENOM"];
        $P_PHONEr     =  @$rowr["P_PHONE"];
        if     ( $P_IDr <> "" ) {
            if ($P_PHONEr <> '') {
                if (is_iphone())
                    $phone=" - <a href='tel:".$P_PHONEr."'>".$P_PHONEr."</a>";
                else
                    $phone = " - ".$P_PHONEr."";
            }
            else $phone="";
            echo "<tr><td>".$TCR_DESCRIPTIONr."</td>";
            echo "<td>
            <a href=upd_personnel.php?pompier=".$P_IDr.">".my_ucfirst($P_PRENOMr)." ".strtoupper($P_NOMr)."</a>".$phone."</td></tr>";
        }
    }
    if ( $E_CONSIGNES <> "" ) {
        echo "<tr  style='color:#3f4254'><td width=25% >Consignes intervenants </td>";
        echo "<td width=75%>".$E_CONSIGNES."</td></tr>";
    }

    if ($nbsections == 0 ) {
        //------------------------
        // Renforts
        //------------------------
        $queryA="select e.E_CODE as CE_CODE, e.E_CANCELED as CE_CANCELED, e.E_CLOSED as CE_CLOSED,
                    s.S_CODE CS_CODE, s.S_DESCRIPTION CS_DESCRIPTION
                    from evenement e, section s
                    where e.E_PARENT=".$evenement."
                    and e.S_ID = s.S_ID
                    order by s.S_CODE, e.E_CODE";
        $resultA=mysqli_query($dbc,$queryA);
        $nb_renforts=mysqli_num_rows($resultA);

        if ( $nb_renforts > 0  or $E_COLONNE_RENFORT == 1) {
            // ajout possible si colonne de renfort
            if ( $E_COLONNE_RENFORT == 1 and $granted_event and $E_CLOSED == 0) {
                $url="evenement_modal.php?action=colonne&evenement=".$evenement;
                $plus= write_modal( $url, "add_renfort", "<i class='fa fa-plus-circle fa-lg' style='color:#1bc5bd'  title='Ajouter ".$renfort_label."'></i>");
            }
            else
                $plus="";

            echo "<tr><td colspan=2>".ucfirst($renfort_label)."s ".$plus."</td></tr>";

            while ( custom_fetch_array($resultA)) {
                if ( $CE_CANCELED == 1 ) {
                    $color="#f64e60";
                    $info="activité annulée";
                }
                elseif ( $CE_CLOSED == 1 ) {
                    $color="orange";
                    $info="activité clôturée";
                }
                else {
                    $color= "#1bc5bd";
                    $info="activité ouverte";
                }
                if ($granted_event and ! $print and $changeallowed)
                    $cancelbtn = "<a class='btn btn-default btn-action' href=\"javascript:cancel_renfort('".$evenement."','".$CE_CODE."')\">
                        <i class='far fa-trash-alt fa-lg' title='détacher ce renfort' ></i></a>";
                else $cancelbtn ='';

                echo "<tr><td colspan=2> <a href=evenement_display.php?evenement=".$CE_CODE.">
                    <i class='fa fa-plus-square lg' style='color:".$color."' title='$info' ></i></a>
                    <a href=evenement_display.php?evenement=".$CE_CODE.">".ucfirst($renfort_label)." de ".$CS_CODE." - ".$CS_DESCRIPTION."</a> ".$cancelbtn."</a> ";

                $queryR="select eh.EH_ID,
                DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m') as EH_DATE_DEBUT0,
                DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m') as EH_DATE_FIN0,
                eh.EH_DATE_DEBUT as EH_DATE_DEBUT1,
                TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT0,  
                TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN0
                from evenement e, evenement_horaire eh
                where eh.E_CODE = e.E_CODE
                and e.E_CODE=".$CE_CODE."
                order by eh.EH_ID";
                $resultR=mysqli_query($dbc,$queryR);
                $nbpr=mysqli_num_rows($resultR);
                //$nbsessions=sizeof($EH_ID);
                //$nummaxpartie=max($EH_ID);

                $EH_ID0= array();
                $EH_DEBUT0= array();
                $EH_DATE_DEBUT0= array();
                $EH_DATE_DEBUT1= array();
                $EH_DATE_FIN0= array();
                $EH_FIN0= array();
                $j=1;
                // mettre les dates pour ce renfort dans un tableau
                while ( $rowR=@mysqli_fetch_array($resultR)) {
                    $EH_DATE_DEBUT0[$j]=$rowR["EH_DATE_DEBUT0"];
                    $EH_DATE_DEBUT1[$j]=$rowR["EH_DATE_DEBUT1"];
                    $EH_DATE_FIN0[$j]=$rowR["EH_DATE_FIN0"];
                    $EH_DEBUT0[$j]=$rowR["EH_DEBUT0"];
                    $EH_FIN0[$j]=$rowR["EH_FIN0"];
                    $EH_ID0[$j]=$rowR["EH_ID"];
                    if ( $EH_DATE_DEBUT0[$j] <> $EH_DATE_FIN0[$j] ) $dates_renfort=$EH_DATE_DEBUT0[$j] ." au ".$EH_DATE_FIN0[$j];
                    else $dates_renfort=$EH_DATE_DEBUT0[$j];
                    $detail_renfort[$j]=$dates_renfort." - ".$EH_DEBUT0[$j]."-".$EH_FIN0[$j];
                    $j++;
                }

                // boucle sur les dates de l'activité principale
                $j=1;$c="";
                if ( $E_COLONNE_RENFORT == 1 ) {
                    if ( evenements_overlap( $evenement, $CE_CODE )) echo "<i class='fa fa-clock fa-lg' style='color:#1bc5bd'  title=\"".$detail_renfort[$j]."\"></i>";
                    else echo "<i class='fa fa-ban fa-lg' style='color:$widget_fgred'  title=\"Les dates de ".$renfort_label." ne correspondent pas à celles de l'activité principale.\"></i>";
                    echo " <small>".$detail_renfort[$j]."</small>";
                }
                else {
                    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
                        if (isset($EH_ID[$i])) {
                            if (isset($EH_ID0[$j])
                                and $EH_DATE_DEBUT1[$j]==$EH_DATE_DEBUT[$i]
                                and ($EH_DEBUT0[$j]==$EH_DEBUT[$i] or ($nbpr == 1 and $nbsessions == 1))) {
                                if ( $nbpr == 1 ) $c = " <font size=1>".$detail_renfort[$j]."</font>";
                                if ( $CE_CANCELED == 0 ) $clock=$widget_fggreen;
                                else {
                                    $clock="red";
                                    $detail_renfort[$j] = "ANNULE";
                                }
                                echo "<a class='btn btn-default btn-action' href='#'><i class = 'fa fa-clock fa-lg' style='color:". $clock."' title=\"".$detail_renfort[$j]."\"></i></a>";
                                $j++;
                            }
                            else {
                                echo "<a class='btn btn-default btn-action' href='#'><i class = 'fa fa-ban fa-lg' style='color:gray' title=\"".ucfirst($renfort_label)." non activé pour la Partie n°".$EH_ID[$i]."\"></i></a>";
                            }
                        }
                    }
                }
                echo $c." </td></tr>";
            }
        }
    }

    //------------------------
    // type de formation
    //------------------------

    if ( $TE_CODE == 'FOR' ){
        if ( intval($PS_ID_FORMATION) == 0 ) {
            $_TYPE="";
            $_DESCRIPTION="<i>non défini</i>";
        }
        else {
            $query2="select PS_ID, TYPE, DESCRIPTION from poste where PS_ID =".intval($PS_ID_FORMATION);
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            $_TYPE=$row2["TYPE"];
            $_DESCRIPTION=$row2["DESCRIPTION"];
        }
        echo "<tr><td>Formation pour</td><td><span class='badge noprint'>".$_TYPE."</span> ".$_DESCRIPTION."</td></tr>";

        if ( $TF_CODE == '' ) {
            $_TF_LIBELLE="<i>non défini</i>";
        }
        else {
            $query2="select TF_LIBELLE from type_formation where TF_CODE='".$TF_CODE."'";
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            $_TF_LIBELLE=$row2["TF_LIBELLE"];
        }
        echo "<tr><td>Type de formation</td><td>".$_TF_LIBELLE."</td></tr>";
    }

    echo "</table>";
    echo "</tr>";

    //=====================================================================
    // lien webex pour les activités de 1 jour max, à une seule partie
    //=====================================================================

    if ( ($is_inscrit or $granted_event) and $E_WEBEX_URL <> '') {
        if ( $nbsessions == 1 and $EH_DATE_DEBUT[1] == $EH_DATE_FIN[1]) {
            if ( $E_WEBEX_START == '' ) $E_WEBEX_START=$EH_DEBUT[1];
            $E_WEBEX_START=substr($E_WEBEX_START,0,5);
            echo "<tr><td CLASS='newTabHeader' colspan=2>Lien Conférence Web</td></tr>";
            echo "<tr><td CLASS='Menu' bgcolor=$mylightcolor colspan=2>";
            echo " <a href=\"".$E_WEBEX_URL."\" target='_blank'>
                            <label class='btn btn-default btn-file' title='Accèder à la conférence web' style='margin-top:3px;'>
                            <i class='fas fa-video' style='color:$widget_fggreen;'></i>
                        </label></a>    <span> Début de la conférence web à ".$E_WEBEX_START."";
            if ( $E_WEBEX_PIN <> '' )
                echo ". Code à utiliser ".$E_WEBEX_PIN."";
            echo "</span></td></tr>";
        }
    }
    echo "</div></div></div>";
    
    echo "<div class='col-sm-3'>";
    //=====================================================================
    // logistique
    //=====================================================================
    if(!$print and $show_logistique){
        
        echo "<div class='card hide card-default graycarddefault' align=center >
                    <div class='card-header graycard'>
                      <div class='card-title'><strong> Logistique</strong></div>
                    </div>
                    <div class='card-body graycard'><table class='noBorder'>";
        // Véhicules requis
        if ( $vehicules == 1 and $TE_VEHICULES ==  1 and (! $gardeSP or $assoc == 1) ) {
            echo "<tr><td>Véhicules requis</td><td>";
            $url="evenement_display.php?evenement=".$evenement."&tab=54";
            $detail="";
            $querym="select NB_VEHICULES 
                    from demande_renfort_vehicule
                    where TV_CODE = '0'
                    and E_CODE=".$evenement;
            $resultm=mysqli_query($dbc,$querym);
            $rowm=mysqli_fetch_array($resultm);
            $demandes=intval(@$rowm["NB_VEHICULES"]);

            $querym="select count(1) from evenement_vehicule where E_CODE in (".$evts.")";
            $resultm=mysqli_query($dbc,$querym);
            $rowm=mysqli_fetch_array($resultm);
            $inscrits=intval($rowm[0]);
            if ( $demandes > 0 ) {
                if ( $inscrits >= $demandes ) $colors = $widget_all_green;
                else $colors = $widget_all_red;
                $detail .=  "<span class='badge' style='$colors' title='$demandes vehicules demandes\n$inscrits inscrits'>".$demandes."</span>";
            }
            $querym="select t.TV_CODE, t.TV_LIBELLE, t.TV_USAGE , d.NB_VEHICULES 'demandes'
                        from type_vehicule t, demande_renfort_vehicule d
                        where d.TV_CODE = t.TV_CODE
                        and E_CODE=".$evenement."
                        order by t.TV_LIBELLE";
            $resultm=mysqli_query($dbc,$querym);
            $nbm=mysqli_num_rows($resultm);
            if ( $nbm > 0 ) {
                $detail .= " <small>dont </small>";
                while ( custom_fetch_array($resultm) ) {
                    $query2="select count(1) from evenement_vehicule ev, vehicule v where ev.E_CODE in (".$evts.") and ev.V_ID=v.V_ID and v.TV_CODE='".$TV_CODE."'";
                    $result2=mysqli_query($dbc,$query2);
                    $row2=mysqli_fetch_array($result2);
                    $inscrits=intval($row2[0]);
                    if ($inscrits >= $demandes ) $colors = $widget_all_green;
                    else $colors = $widget_all_red;
                    $detail .= " <a title=\"". $demandes." ".$TV_LIBELLE." demandes\n".$inscrits." engages\"><span class='badge' style='$colors'>".$demandes." ".$TV_CODE."</span></a>";
                }
            }
            echo $detail;
            if ( $granted_event ) echo " <a class='btn btn-default btn-action' href='".$url."' title='Modifier les véhicules et matériel demandés'><i class='fa fa-edit fa-lg noprint' ></i></a>";
            echo "</td><tr>";
        }
        // Matériel requis
        if ( $materiel == 1 and $TE_MATERIEL ==  1 and ! $gardeSP ) {
            $detail="";
            echo "<tr><td>Matériel requis</td><td>";
            $querym="select tm.TM_ID, tm.TM_CODE
                        from type_materiel tm, demande_renfort_materiel drm 
                        where tm.TM_ID = drm.TYPE_MATERIEL
                        and tm.TM_USAGE not in ('Habillement','Promo-Com','ALL')
                        and drm.E_CODE = ".$evenement."
                        order by tm.TM_USAGE, tm.TM_CODE";
            $resultm=mysqli_query($dbc,$querym);
            while ( $rowm=mysqli_fetch_array($resultm)) {
                $queryn="select count(1) from evenement_materiel em, materiel m
                            where em.MA_ID = m.MA_ID
                            and m.TM_ID='".$rowm["TM_ID"]."'
                            and em.E_CODE in (".$evts.")";
                $resultn=mysqli_query($dbc,$queryn);
                $rown=mysqli_fetch_array($resultn);
                $inscrits=intval($rown[0]);
                if ( $inscrits > 0 ){
                    $fgcolor=$widget_fggreen;
                    $bgcolor=$widget_bggreen;
                }
                else{
                    $fgcolor=$widget_fgred;
                    $bgcolor=$widget_bgred;
                }
                $detail .=  " <span class='badge' style='color:$fgcolor; background-color:$bgcolor' title=\"besoin de ".$rowm["TM_CODE"]."\n$inscrits engagés\">".$rowm["TM_CODE"]."</span>";
            }

            $querym="select cm.TM_USAGE, cm.CM_DESCRIPTION
                        from categorie_materiel cm, demande_renfort_materiel drm 
                        where cm.TM_USAGE = drm.TYPE_MATERIEL
                        and cm.TM_USAGE not in ('Habillement','Promo-Com','ALL')
                        and drm.E_CODE = ".$evenement."
                        order by cm.TM_USAGE";
            $resultm=mysqli_query($dbc,$querym);
            while ( $rowm=mysqli_fetch_array($resultm)) {
                $queryn="select count(1) from evenement_materiel em, type_materiel tm, materiel m
                            where em.MA_ID = m.MA_ID
                            and m.TM_ID = tm.TM_ID
                            and tm.TM_USAGE='".$rowm["TM_USAGE"]."'
                            and em.E_CODE in (".$evts.")";
                $resultn=mysqli_query($dbc,$queryn);
                $rown=mysqli_fetch_array($resultn);
                $inscrits=intval($rown[0]);
                if ( $inscrits > 0 ) {
                    $fgcolor=$widget_fggreen;
                    $bgcolor=$widget_bggreen;
                }
                else{
                    $fgcolor=$widget_fgred;
                    $bgcolor=$widget_bgred;
                }
                $detail .=  " <span class='badge' style='color:$fgcolor; background-color:$bgcolor' title=\"besoin de ".$rowm["CM_DESCRIPTION"]."\n$inscrits engagés\">".$rowm["TM_USAGE"]."</span>";
            }
            echo $detail;
            if ( $granted_event ) echo " <a class='btn btn-default btn-action' href='".$url."' title='Modifier les véhicules et matériel demandés'><i class='fa fa-edit fa-lg noprint' ></i></a>";
            echo "</td></table>";
        }
        echo "</div></div>";
    }
    //=====================================================================
    // stats
    //=====================================================================
    if ( $TE_MAIN_COURANTE == 1 and intval($E_PARENT) == 0 and !$print){
        if ( $granted_event ) $K="<a href=evenement_display.php?from=interventions&evenement=".$evenement." title=\"Modifier les statistiques dans l'onglet Rapport\">Statistiques</a>";
        else $K="Statistiques";
        echo "<div class='card hide card-default graycarddefault' align=center >
              <div class='card-header graycard'>
                  <div class='card-title'><strong>".$K."</strong></div>
              </div>
              <div class='card-body graycard'><table class='noBorder'>";
        $queryN="select TB_NUM,TB_LIBELLE from type_bilan where TE_CODE='".$TE_CODE."' order by TB_NUM";
        $resultN=mysqli_query($dbc,$queryN);
        if ( mysqli_num_rows($resultN) > 0 ) {

            $S=get_main_stats($evenement);
            $finished=my_date_diff($LAST_DAY,getnow());
            if ( strlen($S) == 0 ) {
                if ( $finished > 0 )
                    $S = "<a href=evenement_display.php?from=interventions&evenement=".$evenement." 
                title=\"Modifier les statistiques dans l'onglet Rapport\">
                <span class='badge' style='color:$widget_fgred; background-color:$widget_bgred'> Aucune statistique enregistrée</span></a>";
            }
            if($S == '')
                $S = 'Rien à afficher';
            echo "<tr>
               <td>".$S."</td>
            </tr>";
        }
        echo "</table></div>";
    }
    echo "</div></div></div>";
    echo @$laterBt;
    
    echo "</div>"; //table-responsive
}

//=====================================================================
// participants
//=====================================================================
if ( $tab == 2  or $tab == 61 or $print ) {

    echo "<div style='background:white;' class='table-responsive table-nav table-tabs sub-tabs'>";
    echo "<ul class = 'nav nav-tabs sub-tabs noprint' id='myTab' role = 'tablist'>";

    $class = '';
    $badgeClass = 'inactive-badge';

    if ($child == 0) $child = 1;
    if ($child == 1) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    if ($TE_PERSONNEL == 1)
        echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'evenement_display.php?pid=$pid&from=$from&tab=$tab&child=1&evenement=$evenement' role = 'tab'>Liste</a>
        </li>";

    if ($child == 2) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }

    if ($remplacements and $TE_CODE <> 'MC' and $REMPLACEMENT==1) {
        echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'evenement_display.php?pid=$pid&from=$from&tab=$tab&child=2&evenement=$evenement' role = 'tab'>Remplacements <span class='badge $badgeClass'>$NB7</span></a>
        </li>";
    }

    if ($child == 3) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }

    if ( $TE_CODE <> 'MC' and $PIQUET==1)
        echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'evenement_display.php?pid=$pid&from=$from&tab=$tab&child=3&evenement=$evenement' role = 'tab'><span>Affectations</span></a>
        </li>";

    echo "</ul>";
    echo "</div>";
    echo "<div class='table-responsive'>";
    if ($child == 1) {
        if(!$print){
            echo "<div align=right class='table-responsive tab-buttons-container'>";
            if ( $remplacements and $REMPLACEMENT==1 and $is_present) {
                echo " <div class='noprint'><label style='float:left' class='btn btn-primary' title='Je veux demander à être remplacé' onclick=\"bouton_redirect('evenement_display.php?tab=61&child=2&replaced=".$id."&evenement=$evenement');\">
                     Me faire remplacer
                    </label></div>";
            }
        }
        //Ajouter du personnel
        if ( $E_CLOSED == 0  and  $E_CANCELED == 0  and ! $print and $changeallowed){
            if ( $granted_personnel  or  $granted_inscription ) {
                // cas simple, caserne de pompiers
                if ( $nbsections > 0 and $gardeSP and check_rights($id,6, $organisateur)) {
                    echo "<a class='btn btn-success noprint' style='float:right;margin-left:10px' onclick=\"inscrire(".$evenement.",'personnel_garde');\">
                        <i class='fas fa-plus-circle' style='color:white'></i> Personnel</a>";
                }
                else if ( $nbsections > 0 and ! $gardeSP and check_rights($id,6, $organisateur)) {
                    echo "<a class='btn btn-success noprint' style='float:right;margin-left:10px' onclick=\"inscrire(".$evenement.",'personnel');\">
                        <i class='fas fa-plus-circle' style='color:white'></i> Personnel</a>";
                }
                // cas général

                else {
                    if ( $syndicate == 1 ) $label = "Inscrire";
                    else $label = "Interne";
                    if ( $gardeSP ) $cat='personnel_garde';
                    else $cat='personnel';
                    if ( ! $gardeSP or check_rights($id,6, $organisateur))
                        echo " <a class='btn btn-success noprint' value='".$label."' onclick=\"inscrire(".$evenement.",'".$cat."')\"><i class='fas fa-plus-circle' style='color:white' data-original-title='''></i> ".$label."</a>";

                    if ( $nbsections == 0 and $gardeSP ) {// deuxième bouton pour pouvoir inscrire du personnel des autres centres
                        if ( $sdis ) $lib = 'centres';
                        else $lib = $levels[4].'s';
                        if($lib == 'antennes') $lib = 'sections';
                        echo " <a class='btn btn-success noprint' onclick=\"inscrire(".$evenement.",'personnel')\"><i class='fas fa-plus-circle' style='color:white' data-original-title='''></i> Autres ".$lib."</a>";
                    }
                    if ( $externes == 1 and ! $gardeSP ) {
                        echo " <a class='btn btn-success noprint' onclick=\"inscrire(".$evenement.",'personnelexterne')\"><i class='fas fa-plus-circle' style='color:white' data-original-title='''></i> Externe</a>";
                        if (check_rights($id, 37)) {
                            echo " <a class='btn btn-success noprint' onclick=\"nouvel_externe(".$evenement.");\"><i class='fas fa-plus-circle' style='color:white' data-original-title='''></i> Créer Externe</a>";
                        }
                    }
                }
            }
        }
        echo "</div>";

        $tableau_visible=true;
        $nboptions=count_entities("evenement_option", "E_CODE in (".$evenement.",".intval($E_PARENT).") and E_CODE > 0");
        if ( $gardeSP and $E_VISIBLE_INSIDE == 0 ) {
            if ( $nbsections == 0 and ! check_rights($id, 6, "$organisateur")) $tableau_visible=false;
            if ( $nbsections > 0 and ! check_rights($id, 6))  $tableau_visible=false;
        }
        if  ( $tableau_visible ) {
            $_date = $EH_DATE_DEBUT[1];
            $last = $EH_DATE_FIN[$nbsessions];
            $found=false;
            $found2=false;
            if ( $nbsessions == 2 and $gardeSP) {
                $date_selector =" Montrer le personnel <select name='evenement_periode' class='selectpicker smalldropdown2' data-container='body' data-style='btn btn-default' id='evenement_periode' onchange=\"change_periode('".$evenement."')\" 
                        style='max-width:178px'>";
                $date_selector .="<option value='0' selected > sur toutes les périodes</option>";
                if ( $evenement_periode == '1' ) {
                    $selected = 'selected';
                    $found2=true;
                }
                else $selected = '';
                $periode1 = $EH_DEBUT[1]."-".$EH_FIN[1];
                $periode2 = $EH_DEBUT[2]."-".$EH_FIN[2];
                $date_selector .="<option value='1' $selected >présent ".$periode1."</option>";
                if ( $evenement_periode == '2' ) {
                    $selected = 'selected';
                    $found2=true;
                }
                else $selected = '';
                $date_selector .="<option value='2' $selected >présent ".$periode2."</option>";
                $date_selector .="</select>";
            }
            else if ( $_date <> $last and $nbsessions > 1 ) {
                $date_selector =", Montrer le personnel <select name='evenement_date' id='evenement_date' onchange=\"change_date('".$evenement."')\" >";
                $date_selector .="<option value='' selected > sur toutes les dates</option>";
                while ( $_date <> $last ) {
                    $tmp = explode ("-",$_date);
                    $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
                    if ( $evenement_date == $_date ) {
                        $selected = 'selected';
                        $found=true;
                    }
                    else $selected = '';
                    $date_selector .="<option value='".$_date."' $selected >présent le ".$day."-".$month."-".$year."</option>";
                    $real_date = date_create($_date);
                    date_modify($real_date, '+1 day');
                    $_date = date_format($real_date, 'Y-m-d');
                }
                $tmp = explode ("-",$_date);
                $year=$tmp[0]; $month=$tmp[1]; $day=$tmp[2];
                if ( $evenement_date == $_date ) {
                    $selected = 'selected';
                    $found=true;
                }
                else $selected = '';
                $date_selector .="<option value='".$_date."' $selected >présent le ".$day."-".$month."-".$year."</option>";
                $date_selector .="</select>";
            }
            else $date_selector="";

            if ( check_rights($id,56,$organisateur) or  check_rights($id,40) or $granted_inscription or $granted_event)
                $personnel_visible=true;
            else if ( check_rights($id,56) and in_array($organisateur, array($_SESSION['SES_PARENT'],$_SESSION['SES_SECTION']) ))
                $personnel_visible=true;
            else
                $personnel_visible=false;

            if ( $E_NB == 0 ) $cmt = "Pas de limite sur le nombre";
            else $cmt = "Requis <span class='badge'>$E_NB</span>";
            if ( $TE_CODE == 'FOR' and  $E_NB_STAGIAIRES > 0 ) $cmt .=", dont places stagiaires $E_NB_STAGIAIRES";

            $queryf="select count(1) from type_participation where TE_CODE='".$TE_CODE."'";
            if ( $gardeSP ) $queryf .= " and EQ_ID in (0,".intval($E_EQUIPE).")";
            $resultf=mysqli_query($dbc,$queryf);
            $nbfn=mysqli_num_rows($resultf);

            // trouver tous les participants
            $query="select distinct e.E_PARENT, ep.EP_DATE, ep.E_CODE as EC, p.P_ID, p.P_NOM, ".phone_display_mask('p.P_PHONE')." P_PHONE, p.P_PRENOM, p.P_GRADE, g.G_DESCRIPTION, g.G_ICON, s.S_ID, 
                    p.P_HIDE, p.P_STATUT, p.P_OLD_MEMBER, s.S_CODE, p.P_EMAIL, p.C_ID, p.P_CIVILITE, p.TS_CODE, tp.TP_LIBELLE, ee.EE_NAME,
                    TIMESTAMPDIFF(YEAR,p.P_BIRTHDATE,'".$EH_DATE_DEBUT[1]."') AS AGE,
                    max(ep.TSP_ID) TSP_ID,tp.PS_ID, tp.PS_ID2,
                    ep.EP_FLAG1, ep.TP_ID, ep.EE_ID, ep.EP_COMMENT, ep.EP_KM, ep.EP_BY, ee.EE_DESCRIPTION, ep.EP_REMINDER, max(ep.EP_ASTREINTE) EP_ASTREINTE,
                    ep.EP_ASA, ep.EP_DAS, tsp.TSP_CODE, tsp.TSP_COLOR, g.G_LEVEL,
                    y.P_NOM P_NOM_BY, y.P_PRENOM P_PRENOM_BY,
                    case
                    when tp.TP_NUM is null then 1000
                    else tp.TP_NUM
                    end
                    as TP_NUM
                    from evenement_participation ep 
                    left join type_participation tp on tp.TP_ID = ep.TP_ID
                    left join pompier y on y.P_ID = ep.EP_BY
                    left join evenement_equipe ee on (ee.E_CODE in (".$evts_list.") and ee.EE_ID=ep.EE_ID)
                    left join type_statut_participation tsp on tsp.TSP_ID = ep.TSP_ID,
                    pompier p left join grade g on g.G_GRADE=p.P_GRADE, section s, evenement e, evenement_horaire eh
                    where ep.E_CODE in (".$evts.")
                    and eh.E_CODE = ep.E_CODE
                    and ep.EH_ID = eh.EH_ID
                    and e.E_CODE = ep.E_CODE
                    and p.P_ID=ep.P_ID
                    and p.P_SECTION=s.S_ID";

            if (! $personnel_visible )
                $query .= "    and ep.P_ID =  ".$id;

            if ( $evenement_show_absents == 0 )
                $query .= "    and ep.EP_ABSENT = 0 ";

            if ( $evenement_date <> '' and $found ) {
                $query .= "
                            and ((  eh.EH_DATE_FIN >= '".$evenement_date."' and  eh.EH_DATE_DEBUT <= '".$evenement_date."' and ep.EP_DATE_DEBUT is null)
                              or ( ep.EP_DATE_FIN >= '".$evenement_date."' and  ep.EP_DATE_DEBUT <= '".$evenement_date."'))";
            }
            else if ( $evenement_periode > 0 and $found2 ) {
                $query .= "    and ep.EH_ID=".$evenement_periode;
            }
            $query .= " group by ep.P_ID";
            if ( $order == 'EE_NAME' ) $query .= " order by EE_NAME desc, p.P_NOM";
            else if ( $order == 'TP_LIBELLE' ) $query .= " order by TP_LIBELLE desc, p.P_NOM";
            else if ( $order == 'TSP_ID' ) $query .= " order by TSP_ID, p.P_NOM";
            else if ( $order == 'E_PARENT' ) $query .= "    order by e.E_PARENT, ep.E_CODE asc, p.P_NOM";
            else if ( $order == 'P_NOM' ) $query .= "    order by p.P_NOM, ep.EP_DATE asc";
            else if ( $order == 'G_LEVEL' ) $query .= "    order by g.G_LEVEL desc, p.P_NOM";
            else if ( $gardeSP and $grades ) $query .= "    order by e.E_PARENT, TP_NUM, ep.E_CODE asc, g.G_LEVEL desc, p.P_NOM";
            else $query .= " order by p.P_NOM, ep.EP_DATE asc";
            write_debugbox( $query);
            $result=mysqli_query($dbc,$query);
            $nbparticipants=mysqli_num_rows($result);
            $listePompiers = "";
            $arrayPompiers=array();
            $mailist = "";
            if ( $nbparticipants > 0 or $NP > 0 ) {
                echo "</table><div class='table-responsive'>";
                if ( $print )  echo "<div class='col-sm-12'>
                
                <div style='float:right'>".$cmt." Inscrits <span class='badge'>$NP</span> Présents <span class='badge'>$NP2</span> ".$date_selector." </td></tr>
                </div><table class ='newTableAll' cellspacing=0 border=0  style='font-weight:100 !important'>";
                else echo "<div class='col-sm-12'>
                
                <div style='float:right'>".$cmt." Inscrits <span class='badge'>$NP</span> Présents <span class='badge'>$NP2</span> ".$date_selector." 
                </div><table class ='newTableAll' cellspacing=0 border=0  style='font-weight:100 !important'>";
                if ( $evenement_show_competences == 1 ) $checked='checked';
                else $checked='';

                if ( $competences == 1 )
                    $competences_checkbox = "<span style='position:relative;top:-5px'> Compétence </span>
                        <label class='switch'>
                            <input type='checkbox' id ='evenement_show_competences' class='ml-3' 
                            value='1' style='height:22px' 
                            onClick=\"show_competences('".$evenement."')\" $checked >
                            <span class='slider round'></span>
                        </label>";

                else
                    $competences_checkbox = "";

                if ( $evenement_show_absents == 1 ) $checked='checked';
                else $checked='';

                $absents_checkbox = "
                           <span style='position:relative;top:-5px'> Absent </span>
                        <label class='switch'>
                     <input type='checkbox' id ='evenement_show_absents' class='ml-3'
                            value='1' style='height:22px' 
                            onClick=\"show_absents('".$evenement."','2')\" $checked >
                            <span class='slider round' title='Montrer aussi les absents'></span>
                        </label>";
                
                echo "<div align=left class='noprint'>";
                echo "<span>";
                echo $competences_checkbox;
                echo "</span>";
                echo "<span>";
                echo $absents_checkbox;
                echo "</span>";
                echo "</div>";

                if ( $grades ) $colspan=10;
                else $colspan=9;
                if ( $print ) $colspan = $colspan -1;
                if ( $date_selector <> '' ) {
                    $date_selector .= " soit ".$nbparticipants." personnes.";
                    if ( $gardeSP and $nbsessions == 2 ) {
                        $get_ev = get_inscrits_garde($evenement,1);
                        if (empty($get_ev))
                            $inscrits1=0;
                        else
                            $inscrits1=count(explode(",",$get_ev));

                        $get_ev = get_inscrits_garde($evenement,2);
                        if (empty($get_ev))
                            $inscrits2=0;
                        else
                            $inscrits2=count(explode(",",$get_ev));

                        $date_selector .= " Dont ".$periode1." <span class='badge' style='background-color:yellow; color:$mydarkcolor' title='inscrits ".$periode1."'>".$inscrits1."</span> ";
                        $date_selector .= " ".$periode2." <span class='badge' style='background-color:#CEE3F6; color:$mydarkcolor' title='inscrits ".$periode2."'>".$inscrits2."</span>";
                    }
                }
                if ( $print ) $date_selector='';
                else
                    echo "<td width=60>Image</td>";
                if ( $grades ) echo "<td width=40><a href=evenement_display.php?evenement=".$evenement."&tab=2&order=G_LEVEL title='trier par Grade décroissant'>Grade</a></td>";
                if ( $print ) echo "<td style='min-width:300px;' >Personnel présent</td>";
                else {
                    echo "<td style='min-width:300px;'>";
                    if ( "$evts" <> "$evenement" ) echo " <a href=evenement_display.php?evenement=".$evenement."&tab=2&order=E_PARENT title='trier par ".$renfort_label."'>".ucfirst($renfort_label)."</a> /";
                    echo " <a href=evenement_display.php?evenement=".$evenement."&tab=2&order=P_NOM title='trier par Nom'>Inscrits</a></td>";
                }
                if ( ! $print) echo "<td style='max-width:160px; min-width:60px' align=left>Téléphone</td>";
                if ( $nbfn > 0 ){
                    if ($granted_event and (!$print) and $changeallowed) echo "<td width=170><a href=evenement_display.php?evenement=".$evenement."&tab=2&order=TP_LIBELLE title='trier par fonction'>Fonction</a></td>";
                    else echo "<td style='min-width:80px;'>Fonction</td>";
                }
                else echo "<td></td>";
                if ( $nbe > 0 ) echo "<td style='min-width:80px;'><a href=evenement_display.php?evenement=".$evenement."&tab=2&order=EE_NAME title='trier par équipe'>Equipe</a></td>";
                if ( $gardes == 0 and $TE_VICTIMES == 1)
                    echo "<td width=22><a href=evenement_display.php?evenement=".$evenement."&tab=2&order=TSP_ID title='trier par statut'>Statut.</a></td>";
                else echo "<td></td>";
                if ( $evenement_show_competences == 1 )
                    echo "<td style='min-width:200px;'></td>";
                else
                    echo "<td></td>";
                echo "<td></td>
                <td colspan=3 style='min-width:180px;'></td>";
                $prevEC='';
                while (custom_fetch_array($result)) {
                    // affiche les infos pour ce renfort
                    if ( $EC <> $prevEC ) {
                        $queryR="select e.E_CANCELED as CE_CANCELED, e.E_CLOSED as CE_CLOSED, eh.EH_ID,
                        s.S_CODE CS_CODE, s.S_DESCRIPTION CS_DESCRIPTION,
                        DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m') as EH_DATE_DEBUT0,
                        DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m') as EH_DATE_FIN0,
                        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT0,  
                        TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN0,
                        eh.EH_DATE_DEBUT as EH_DATE_DEBUT1
                        from evenement e, section s, evenement_horaire eh
                        where e.S_ID = s.S_ID
                        and eh.E_CODE = e.E_CODE
                        and e.E_CODE=".$EC."
                        order by eh.EH_ID";
                        $resultR=mysqli_query($dbc,$queryR);
                        $EH_DATE_DEBUT0 = Array();
                        $EH_DATE_DEBUT0 = Array();
                        $EH_DEBUT0 = Array();
                        $EH_FIN0 = Array();
                        $EH_DATE_DEBUT1 = Array();
                        $EH_ID0=array();
                        $horaire_renfort = Array();

                        $n=1;
                        while ( $rowR=@mysqli_fetch_array($resultR)) {
                            $EH_ID0[$n]=$rowR["EH_ID"];
                            $EH_DATE_DEBUT0[$n]=$rowR["EH_DATE_DEBUT0"];
                            $EH_DATE_DEBUT1[$n]=$rowR["EH_DATE_DEBUT1"];
                            $EH_DATE_FIN0[$n]=$rowR["EH_DATE_FIN0"];
                            $EH_DEBUT0[$n]=$rowR["EH_DEBUT0"];
                            $EH_FIN0[$n]=$rowR["EH_FIN0"];
                            $CE_CANCELED=$rowR["CE_CANCELED"];
                            $CE_CLOSED=$rowR["CE_CLOSED"];
                            $CS_CODE=$rowR["CS_CODE"];
                            $CS_DESCRIPTION=$rowR["CS_DESCRIPTION"];
                            if ( $CE_CANCELED == 1 ) {
                                $color="red";
                                $info="activité annulée";
                            }
                            elseif ( $CE_CLOSED == 1 ) {
                                $color=$widget_fgorange;
                                $info="activité clôturée";
                            }
                            else {
                                $color=$widget_fggreen;
                                $info="activité ouverte";
                            }
                            if ( $EH_DATE_DEBUT0[$n] <> $EH_DATE_FIN0[$n] ) $dates_renfort=$EH_DATE_DEBUT0[$n] ." au ".$EH_DATE_FIN0[$n];
                            else $dates_renfort=$EH_DATE_DEBUT0[$n];
                            $horaire_renfort[$n]=$dates_renfort." - ".$EH_DEBUT0[$n]."-".$EH_FIN0[$n];
                            $n++;
                        }
                        if ( $EC <> $evenement and $order == 'E_PARENT') {
                            if ( mysqli_num_rows($resultR) == 1 ) $dt=$horaire_renfort[1];
                            else $dt="";
                            echo "<tr bgcolor= ><td colspan=2>
                            <i><a href=evenement_display.php?evenement=$EC&from=inscription>
                            <i class='fa fa-plus-square lg' style='color:".$color."' title='$info' ></i>
                            ".ucfirst($renfort_label)."s de ".$CS_CODE."</i></a>
                            </td>
                            <td colspan=8>".$dt."</td></tr>";
                        }
                        $prevEC = $EC;
                    }
                    $HORAIRE = get_horaire($P_ID, $E_CODE);

                    if ( $grades ) $P_GRADE="<img src='".$G_ICON."' title='".$G_DESCRIPTION."' class='img-max-22' style='border-radius: 2px;' >";
                    else $P_GRADE="";
                    $F_PS_ID=$PS_ID;
                    $F_PS_ID2=$PS_ID2;
                    $TP_ID=intval($TP_ID);
                    if ( $TS_CODE == 'SC' ) $SC = True;
                    else $SC = False;
                    if ( $P_EMAIL <> "" ) $mailist .= $P_ID.",";
                    if ( intval($P_PHONE) > 0 ) {
                        if (is_iphone())
                            $P_PHONE=" <small><a href='tel:".$P_PHONE."'>".$P_PHONE."</a></small>";
                        
                        if ( $P_HIDE == 1  and  $nbsections == 0 and $id <> $P_ID ) {
                            if (( ! $ischef )
                                and ( ! in_array($id,$chefs) )
                                and (! check_rights($id, 2))
                                and (! check_rights($id, 12)))
                                $P_PHONE=" ***** ";
                        }
                    }
                    else $P_PHONE="";
                    $listePompiers .= $P_ID.",";
                    if ( in_array($P_ID,$arrayPompiers)) $warn_duplicate_pid=true;
                    else {
                        $arrayPompiers[] = $P_ID;
                        $warn_duplicate_pid=false;
                    }

                    if ( is_children($S_ID,$organisateur)) $prio=true;
                    else $prio=false;

                    if ( check_rights($id, 10,"$S_ID")) $granted_update=true;
                    else $granted_update=false;

                    // récupérer horaires de la personne dans un tableau
                    $clock="";
                    $EP_DATE_DEBUT=array();
                    $EP_DATE_FIN=array();
                    $EP_DATE_DEBUT1=array();
                    $EP_DATE_FIN1=array();
                    $EP_DEBUT=array();
                    $EP_FIN=array();
                    $EP_ABSENT=array();
                    $EP_EXCUSE=array();
                    $EP_FLAG1=array();
                    $EH_ID1=array();
                    $full_absent=true;

                    $query_horaires="select EH_ID,
                       DATE_FORMAT(EP_DATE, '%d-%m %H:%i') as EP_DATE, 
                       DATE_FORMAT(EP_DATE_DEBUT,'%d-%m-%Y') EP_DATE_DEBUT, 
                       DATE_FORMAT(EP_DATE_FIN,'%d-%m-%Y') EP_DATE_FIN,
                       TIME_FORMAT(EP_DEBUT, '%k:%i') EP_DEBUT,  
                       TIME_FORMAT(EP_FIN, '%k:%i') EP_FIN,
                       EP_DATE_DEBUT EP_DATE_DEBUT1,
                       EP_DATE_FIN EP_DATE_FIN1,
                       EP_ABSENT,EP_EXCUSE, EP_ASTREINTE, EP_FLAG1
                       from evenement_participation
                       where E_CODE=".$EC."
                       and P_ID=".$P_ID."
                       order by EH_ID";
                    $resultH=mysqli_query($dbc,$query_horaires);
                    $j=1;
                    while ( $rowH=@mysqli_fetch_array($resultH)) {
                        $EH_ID1[$j]=$rowH["EH_ID"];
                        $EP_DATE_DEBUT[$j]=$rowH["EP_DATE_DEBUT"];    // DD-MM-YYYY
                        $EP_DATE_FIN[$j]=$rowH["EP_DATE_FIN"];
                        $EP_DATE_DEBUT1[$j]=$rowH["EP_DATE_DEBUT1"];  // YYYY-MM-DD
                        $EP_DATE_FIN1[$j]=$rowH["EP_DATE_FIN1"];
                        $EP_DEBUT[$j]=$rowH["EP_DEBUT"];
                        $EP_FIN[$j]=$rowH["EP_FIN"];
                        $EP_ABSENT[$j]=$rowH["EP_ABSENT"];
                        if ( $EP_ABSENT[$j] == 0 ) $full_absent = false;
                        $EP_EXCUSE[$j]=$rowH["EP_EXCUSE"];
                        $EP_ASTREINTE[$j]=$rowH["EP_ASTREINTE"];
                        $EP_FLAG1[$j]=$rowH["EP_FLAG1"];
                        $j++;
                    }
                    if ( $E_COLONNE_RENFORT == 1 ) {
                        $overlap = evenements_overlap( $evenement, $EC );
                    }
                    else $overlap = false;

                    // boucle sur les dates de l'activité principal
                    $j=1;$clock="";$p1=0;$p2=0;
                    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
                        $subclock="";
                        if (isset($EH_ID[$i])) {
                            if (( isset($EH_ID0[$j])
                                    and $EH_DATE_DEBUT1[$j]==$EH_DATE_DEBUT[$i]
                                    and ( $EH_DEBUT0[$j]==$EH_DEBUT[$i]  or ( $nbsessions == 1 and sizeof($EH_ID0) == 1))
                                ) or ( $overlap )) { // renfort actif sur cette partie
                                $num_partie=$EH_ID0[$j];
                                if (in_array($num_partie, $EH_ID1)) { // personne inscrite
                                    $key = array_search($num_partie, $EH_ID1);
                                    if ($EP_FLAG1[$key]==0 and $P_STATUT =="SPP"){
                                        $normalicon="purple";
                                        $normalicon2="#cc00ff";
                                        $titleprefix="Garde en tant que SPV ";
                                    }
                                    else if ( $EP_ASTREINTE[$key] == 1 ) {
                                        $normalicon="$widget_fgblue";
                                        $normalicon2="#3333cc";
                                        $titleprefix="ASTREINTE (garde non rémunérée) ";
                                    }
                                    else {
                                        $normalicon="$widget_fggreen";
                                        $normalicon2="$widget_fgorange";
                                        $titleprefix="";
                                    }
                                    if ($nbsessions == 1 ) $t=" de l'activité";
                                    else $t=" de la partie n°".$num_partie;
                                    if ( $EP_ABSENT[$key] == 1 ) {
                                        if ( $EP_EXCUSE[$key] == 0 ) $n='non excusée';
                                        else $n='excusée';
                                        $subclock ="<i class='fa fa-clock fa-lg' style='color:darkgrey' title=\"Absence ".$n."\ncliquer pour modifier\"></i>";
                                    }
                                    elseif ( $EP_DATE_DEBUT[$key] <> "" ) {
                                        if ( $EP_DATE_DEBUT[$key] == $EP_DATE_FIN[$key] ) $horaire_p[$key]= substr($EP_DATE_DEBUT[$key],0,5).", ".$EP_DEBUT[$key]."-".$EP_FIN[$key];
                                        else $horaire_p[$key]= substr($EP_DATE_DEBUT[$key],0,5)." au ".substr($EP_DATE_FIN[$key],0,5).", ".$EP_DEBUT[$key]."-".$EP_FIN[$key];
                                        $subclock ="<i class='fa fa-clock fa-lg' style='color:".$normalicon2."' title=\"".$titleprefix."horaires différents de ceux $t \n".$horaire_p[$key]."\"></i>";
                                    }
                                    else if ( isset($horaire_renfort[$i])) $subclock ="<i class='fa fa-clock fa-lg' style='color:".$normalicon."'  title=\"".$titleprefix."horaires identiques à ceux $t \n".$horaire_renfort[$i]."\"></i>";
                                    else $subclock ="<i class='fa fa-clock fa-lg' style='color:".$normalicon."' title=\"".$titleprefix."horaires identiques à ceux de la partie n°".$i." \n".$horaire_renfort[$j]."\"></i>";
                                    if ( $num_partie == 1 ) {
                                        $tmp_arr = explode(":",$EH_DEBUT[$i]);
                                        $heure_deb = $tmp_arr[0];
                                        if ( intval($heure_deb) >= 18 ) $p2=1;
                                        else $p1=1;
                                    }
                                    else if ( $num_partie == 2 ) $p2=1;
                                }
                                else if ( $E_COLONNE_RENFORT == 1 ) {
                                    if ( $overlap ) $subclock ="<i class='fa fa-clock fa-lg' style='color:$widget_fggreen'></i>";
                                    else $subclock ="<i class='fa fa-ban fa-lg' style='color:$widget_fgred' title=\"Les dates de ".$renfort_label." ne correspondent pas à celles de l'activité principale.\"></i>";
                                }
                                else $subclock ="<i class='far fa-circle fa-lg' style='color:grey' title=\"Pas inscrit(e) pour la Partie n°".$EH_ID[$i]."\"></i>";
                                $j++;
                            }
                            else {
                                if ( $E_COLONNE_RENFORT == 1 ) $subclock ="<i class='fa fa-ban fa-lg' style='color:$widget_fgred' title=\"Les dates de ".$renfort_label." ne correspondent pas à celles de l'activité principale\"></i>";
                                else $subclock ="<i class='fa fa-ban fa-lg' style='color:$widget_fgred'  title=\"".ucfirst($renfort_label)." inactif pour la Partie n°".$EH_ID[$i]."\"></i>";
                            }
                        }
                        if ( $CE_CANCELED == 1 and $subclock <> "" ) $subclock = "<i class='fa fa-clock fa-lg' style='color:$widget_fgred'  title=\"annulé\"></i>";
                        if($subclock != '')
                            $clock .= "<button class='btn btn-default btn-action noprint'>$subclock</button>";
                    }

                    // Cas garde SP, vérifier la dispo des SPV, sinon Warning
                    $warnclock="";
                    if ( $gardeSP and $P_STATUT == 'SPV') {
                        $query1="select sum( d.PERIOD_ID * d.PERIOD_ID ) as NUM from disponibilite d where d.P_ID=".$P_ID." and d.D_DATE='".$year1[1]."-".$month1[1]."-".$day1[1]."'";
                        $result1=mysqli_query($dbc,$query1);
                        custom_fetch_array($result1);
                        $label=dispo_label($NUM);
                        $array_jour=array(30,5,14,21);
                        $array_nuit=array(30,25,26,29);
                        $array_aprem=array(30,5,13,4,14,20,21,29);
                        $heure_debut_garde = intval(substr($EH_DEBUT0[1],0,2));
                        if ( $NUM == 0                                                                  ||
                            ( $p1 == 1 and $heure_debut_garde < 12 and ! in_array($NUM,$array_jour))    ||
                            ( $p1 == 1 and $heure_debut_garde >= 12 and ! in_array($NUM,$array_aprem))  ||
                            ( $p2 == 1 and ! in_array($NUM,$array_nuit))  )
                            $warnclock = " <i class='fa fa-exclamation-triangle fa-lg' style='color:$widget_fgred' title=\"Attention: ce SPV n'a pas la disponibilité suffisante pour cette garde ".$label."\"></i>";
                    }

                    if ( $EP_FLAG1[1] == 1 and $EP_COMMENT <> '') $txtimg="sticky-note fa-lg' style='color:purple;";
                    else if ( $EP_FLAG1[1] == 1 ) $txtimg="sticky-note fa-lg' style='color:#7D62A4;";
                    else if ( $EP_COMMENT <> '' ) $txtimg="sticky-note fa-lg";
                    else $txtimg="sticky-note fa-lg' style='color:grey;";

                    if ( $TP_ID == 0 ) { $F_PS_ID=0; $F_PS_ID2=0; }
                    if ( $EP_BY <> "" and $EP_BY <> $P_ID) {
                        $inscritPar="par ".my_ucfirst($P_PRENOM_BY)." ".strtoupper($P_NOM_BY);
                    }
                    else $inscritPar="";
                    $popup="Inscrit le: ".$EP_DATE;
                    if ( $gardeSP and $P_STATUT == 'SPP' and $EP_FLAG1[1] == 1 ) $popup .= "\nGarde en qualité de SPP";
                    else if ( $EP_FLAG1[1] == 1 ) {
                        if ( $SC ) $ss = "service civique";
                        else $ss = "salarié(e)";
                        $popup .= "\nParticipation en tant que ".$ss;
                    }
                    if ( $EP_COMMENT <> "" ) $popup .= "\nCommentaire: ".$EP_COMMENT;

                    $myimg="";
                    if ( $gardeSP ) { // vérifier que pas inscrit sur 2 tableaux de gardes
                        $querySP="select count(1) from evenement_participation ep, evenement e, evenement_horaire eh
                            where ep.P_ID=".$P_ID." 
                            and ep.E_CODE = e.E_CODE 
                            and e.E_CODE <> ".$evenement." 
                            and eh.E_CODE = ep.E_CODE
                            and ep.EH_ID = eh.EH_ID
                            and e.TE_CODE='GAR'
                            and e.E_CODE = eh.E_CODE
                            and eh.EH_DATE_DEBUT = '".$year1[1]."-".$month1[1]."-".$day1[1]."'";
                        $resultSP=mysqli_query($dbc,$querySP);
                        $rowSP=@mysqli_fetch_array($resultSP);
                        $autre_garde=$rowSP[0];
                        if ( $autre_garde > 0 ) $myimg="<i class='fa fa-exclamation' style='color:$widget_fgred' title='attention ce personnel est parallèlement inscrit sur une autre garde'></i>";
                    }
                    if ( $nbsessions == 1 and ! $gardeSP and $nbparticipants < 30 and $TE_CODE <> 'MC' ) {
                        $nb = get_nb_inscriptions($P_ID, $year1[1], $month1[1], $day1[1], $year2[$nummaxpartie], $month2[$nummaxpartie], $day2[$nummaxpartie], 0, $EC) ;
                        if ( $nb > 1 )
                            $myimg="<i class='fa fa-exclamation' style='color:$widget_fgred'  title='attention ce personnel est parallèlement inscrit sur $nb autres activités'></i>";
                        else if ( $nb == 1 )
                            $myimg="<i class='fa fa-exclamation' style='color:#ff8000'  title='attention ce personnel est parallèlement inscrit sur 1 autre activité'></i>";
                    }

                    $cmt="";
                    if ( $P_OLD_MEMBER > 0 ) {
                        $altcolor="<font color=black>";
                        $extcmt="ATTENTION: Ancien membre";
                    }
                    else if ( $gardeSP and $P_STATUT == 'SPP' and $EP_FLAG1 == 0 ) {
                        $altcolor="<font color=purple>";
                        $extcmt="SPP de ".$S_CODE." en garde SPPV";
                    }
                    else if ( $P_STATUT=='SPP') {
                        $altcolor="<font color=red>";
                        $extcmt="SPP de ".$S_CODE;
                    }
                    else if ( $P_STATUT=='EXT') {
                        $altcolor="<font color=$widget_fggreen>";
                        $extcmt="Personnel externe ".get_company_name("$C_ID");
                    }
                    else {
                        $altcolor=(($prio)?"":"<font color=purple>");
                        $extcmt=$S_CODE;
                    }
                    if ( $P_CIVILITE > 3 )
                        $cmt="<span class='badge' style='background-color:purple; color:white; font-size:9px; padding:2px;'>chien</span>";
                    else if ( $AGE <> '' )
                        if ($AGE < 18 ) $cmt="<span class='badge' style='background-color:$widget_fgred; color:white; font-size:9px; padding:2px;' title=\"mineur au début de l'activité\">-18</span>";

                    // nouvelle ligne
                    if ( ! $print or ! $full_absent ) {
                        $date = $year1[1]."-".$month1[1]."-".$day1[1];
                        $SP_SPECIFIC_TEXT="";
                        if ( $gardeSP and $P_STATUT == 'SPV' ) {
                            $SP_DISPO_TIME_DAY = dispo_hr_spp ($P_ID, $date); // les heures dispo
                            $SP_HORAIRE_GARDE = get_horaire($P_ID, $E_CODE); // horaires prévus de garde
                            $SP_HORAIRE_GARDE = $SP_HORAIRE_GARDE[1];
                            $free_time = $SP_DISPO_TIME_DAY - $SP_HORAIRE_GARDE;
                            if($free_time > 0)
                                $SP_SPECIFIC_TEXT = "<br><small><span style='color:black; font-style:italic; '>Dispo restante: ".$free_time."h</span></small>";
                        }
                        echo "<tr>";
                        
                        $query = "Select P_PHOTO From pompier Where P_ID = $P_ID";
                        $file = $dbc->query($query)->fetch_row()[0];
                        $path = "$trombidir/$file";
                        if($file != "" and file_exists($path))
                            $src = $path;
                        else{
                            $defaultpic="./images/default.png";
                            $defaultboy="./images/boy.png";
                            $defaultgirl="./images/girl.png";
                            $defaultother="./images/autre.png";
                            $defaultdog='./images/chien.png';
                            if ($P_CIVILITE==1) $defaultpic=$defaultboy;
                            elseif ($P_CIVILITE==2) $defaultpic=$defaultgirl;
                            elseif ($P_CIVILITE==3) $defaultpic=$defaultother;
                            elseif ($P_CIVILITE==4 or $P_CIVILITE==5) $defaultpic=$defaultdog;
                            $src = $defaultpic;
                        }
                        if(!$print)
                            echo "<td><img src='$src' class='img-max-40' style='border-radius:10px'></td>";
                        if ( $grades ) echo "<td align=left >".$altcolor.$P_GRADE."</font></td>";
                        echo "<td style='padding-left:3px;'><a href=upd_personnel.php?pompier=$P_ID title=\"$extcmt\">".$altcolor.strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)." $cmt</a>";
                        if ( $warn_duplicate_pid ) echo "<i class='fa fa-exclamation-triangle' style='color:$widget_fgorange' title='Attention cette personne apparaît plusieurs fois dans la liste'></i>";
                        echo $SP_SPECIFIC_TEXT;
                        echo "</td>";

                        echo "<td>$P_PHONE</td>";

                        // compétences
                        $required_comp = intval($F_PS_ID + $F_PS_ID2);
                        $postes ="";
                        if ( $evenement_show_competences == 1 ) $postes=get_competences($P_ID, $TE_CODE);
                        else if ( $required_comp > 0 ) $null=get_competences($P_ID, $TE_CODE);
                        // affiche fonctions / équipes
                        if (($granted_personnel or $granted_event or $granted_update or ($granted_inscription and $gardeSP))
                            and ! $print and $changeallowed ) {
                            if ( $nbfn > 0 ) {
                                $warnflag="";
                                if ( $required_comp > 0  and ! $found) {
                                    $warnflag="<i class='fa fa-exclamation-triangle fa-lg' style='color:$widget_fgorange' title=\"Attention cette personne n'est pas qualifiée pour assurer cette fonction\"></i>";
                                }
                                if (  ($granted_event or ($granted_inscription and $gardeSP) ) and $changeallowed ) {
                                    // choix fonction
                                    $url="evenement_modal.php?action=fonction&evenement=".$evenement."&pid=".$P_ID;
                                    if ( $TP_ID == "" or $TP_ID == 0 ) $TP_LIBELLE="<div id='divfn".$P_ID."' class='noprint' title='sélectionner une fonction'>Choisir</div>";
                                    else $TP_LIBELLE="<div id='divfn".$P_ID."' title='changer la fonction'>".$TP_LIBELLE." ".$warnflag."</div>";
                                    echo "<td>";
                                    print write_modal( $url, "fonction_".$P_ID, $TP_LIBELLE);
                                    echo "</td>";
                                }
                                else {
                                    if ( $TP_ID == "" or $TP_ID == 0 ) $TP_LIBELLE="";
                                    echo  "<td><span style='font-size:9px; font-style: italic;'>".$TP_LIBELLE."</span></a> ".$warnflag."</td>";
                                }
                            }
                            else echo "<td></td>";

                            // choix équipe
                            if ( $nbe > 0 ) {
                                if (! $granted_event or ! $changeallowed) {
                                    if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="";
                                    echo  "<td><small>".$EE_NAME." </small></a></td>";
                                }
                                else {
                                    // choix équipe
                                    $url="evenement_modal.php?action=equipe&evenement=".$evenement."&pid=".$P_ID;
                                    if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="<div id='divpe".$P_ID."' title='Choisir une équipe' class='noprint'>Choisir équipe</div>";
                                    else $EE_NAME="<div id='divpe".$P_ID."' title='Changer équipe'>".$EE_NAME." <a></a></div>";
                                    echo "<td>";
                                    print write_modal( $url, "equipe_".$P_ID, $EE_NAME);
                                    echo "</td>";
                                }
                            }
                            else echo "<td></td>";
                        }
                        else { // impression ou personne sans habilitations
                            if ( $nbfn > 0 ) {
                                if ( $TP_ID == "" ) $TP_LIBELLE="-";
                                echo "<td><small>".$TP_LIBELLE."</small></td>";
                            }
                            else echo "<td></td>";
                            if ( $nbe > 0 ) {
                                if ( $EE_ID == "" ) $EE_NAME="-";
                                echo "<td><small>".$EE_NAME."</small></td>";
                            }
                            else echo "<td></td>";
                        }

                        // Infos
                        if (! $print and $changeallowed ) {
                            $textbox_disabled='disabled';
                            $can_save=false;
                            if (($granted_personnel or $granted_event or $granted_update or $id == $P_ID)) {
                                $textbox_disabled='';
                                $can_save=true;
                            }

                            if ( $EP_REMINDER == 1 and $cron_allowed == 1 and $P_EMAIL <> "")
                                $bell="<span class='btn btn-default btn-action'><i class='fa fa-bell fa-lg' style='color:$widget_fgred;' title=\"Une notification de rappel sera envoyée la veille par mail\" ></i></span>";
                            else
                                $bell='';

                            if ( $EP_ASTREINTE == 1 ) {
                                $garde_astreinte="<span class='btn btn-default btn-action' >
                                        <i class='fa fa-exclamation-triangle' style='color:$widget_fgorange;' title=\"Astreinte (garde non rémunérée) sur les parties de la garde montrant une horloge bleue ou orange.\" ></i>
                                        </span>";
                            }
                            else {
                                $garde_astreinte='';
                            }

                            if ( $EP_KM <> '' ) $_km="<span class='btn btn-default btn-action' title='$EP_KM km parcourus en véhicule personnel'>".$EP_KM." </span>";
                            else $_km='';
                            if ( $EP_ASA == 1 ) $_asa="<span class='btn btn-default btn-action' title=\"Autorisation spéciale d'absence\">ASA</span>";
                            else $_asa='';
                            if ( $EP_DAS == 1 ) $_das="<span class='btn btn-default btn-action' title=\"Décharge d'activité de service\">DAS</span>";
                            else $_das='';
                            
                            $laterprint='';
                            $url="evenement_info_participant.php?evenement=".$evenement."&pid=".$P_ID;
                            $laterprint.= write_modal( $url, "infos_".$P_ID,"<span class='btn btn-default btn-action noprint' ><i class='fa fa-".$txtimg."' title=\"".$popup."\"></i></span>");
                            $laterprint.= " ";
                            $laterprint.= write_modal( $url, "infos_2_".$P_ID ,$garde_astreinte." ".$bell." ".$_km." ".$_asa." ".$_das." ".$myimg);
                        }

                        // statut participation?
                        // sur les activités opérationnelles associatives on peut choisir un statut
                        if ( $gardes == 0 and $TE_VICTIMES == 1 and ! $print) {
                            $url="evenement_modal.php?action=statut&evenement=".$evenement."&pid=".$P_ID;
                            if ( $granted_event )
                                $tsp= write_modal( $url, "statut_".$P_ID, "<div id='sp".$P_ID."' style='color:".$TSP_COLOR.";' ><i class='fa fa-info-circle' title=\"Statut, cliquer pour modifier: ".$TSP_CODE."\" ></i></div>");
                            else
                                $tsp="<i class='fa fa-info-circle' title=\"Statut: ".$TSP_CODE."\" ></i>";
                        }
                        else
                            $tsp="";
                        echo "<td align=right>".$tsp."</td>";
                        echo "<td><span class='small'>".$postes."</span></td>";
                        echo "<td align=right>";
                        // Options inscriptions
                        if (! $print and $nboptions > 0 ) {
                            if ( $granted_inscription or $id == $P_ID ) {
                                if ( intval($E_PARENT) > 0 ) $e=$E_PARENT;
                                else $e=$evenement;
                                $nbchoix=count_entities("evenement_option_choix","P_ID=".$P_ID." and E_CODE=".$e);
                                if (  $nbchoix > 0 ) {
                                    $color=$widget_fggreen;
                                    $title="Voir et modifier les options d'inscription pour cette personne";
                                }
                                else {
                                    $color=$widget_fgred;
                                    $title="Renseigner les options d'inscription pour cette personne";
                                }
                                
                                echo "<a class='btn btn-default btn-action noprint' href='evenement_option_choix.php?evenement=".$evenement."&pid=".$P_ID."' title=\"".$title."\"><i class ='fa fa-cog ' style='color:".$color."'></i>";
                            }
                        }
                        echo "</td>";
                        // affiche horaires
                        if ( ! $print) {
                            echo "<td align=right>";
                            $url="evenement_horaires.php?evenement=".$EC."&pid=".$P_ID."&vid=0";
                            if (! $changeallowed ) echo "$clock $warnclock";

                            else if ($granted_event or ($P_ID == $id and $E_CLOSED == 0 and $PARENT_CLOSED == 0) or ($granted_update and $E_CLOSED == 0 and $PARENT_CLOSED == 0)
                                or ($granted_inscription and (check_rights($id,15,"$organisateur") or $gardeSP)) ) {
                                print write_modal($url,"Horaires_".$P_ID, $clock) ;
                                echo "$warnclock";
                            }
                            else
                                echo "$clock $warnclock";
                            echo "</td>";
                        }
                        echo "<td align=right>".@$laterprint;
                        // suppression
                        if (($granted_event or ($granted_inscription and (check_rights($id,15,"$organisateur") or $gardeSP)) )
                            and ! $print and $changeallowed and ( $E_CLOSED == 0 or $chef or check_rights($id,14))) {
                            echo "<a class='btn btn-default btn-action noprint' href=\"javascript:desinscrire('".$evenement."','".$EC."','".$P_ID."');\" title='désinscrire' >
                                    <i class='far fa-trash-alt fa-lg'></i></a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    } // ! $print or ! $full_absent
                }
                echo "</div></table>";

                if (! $personnel_visible )
                    echo " <br><div class='alert alert-warning' role='alert'>Attention, vous n'avez pas la permission de voir les noms des inscrits autres que vous même.</div>";
            }
            else echo "Aucun personnel inscrit. (".$cmt.").<br>";

            //=====================================================================
            // inscrire d'autres personnes
            //=====================================================================

            echo "<p class='noprint'>";
            if ( $gardeSP and ($granted_event or $granted_inscription) and $E_EQUIPE > 0) {
                if ( $E_ANOMALIE == 1 ) $checked='checked';
                else $checked='';
                echo  " <label for='evenement_anomalie' style='margin-right: 6px;'>Garde en anomalie </label><input type=checkbox id='evenement_anomalie' name='evenement_anomalie' values='1' $checked 
                        title='Cocher si cette garde présente une anomalie'
                        onchange=\"change_anomalie('".$evenement."')\"
                        style='margin-right: 20px;'>";
            }

            if ( $from == "calendar" ) {
                echo "<input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ";
            }

            if ( ! $print ) {
                // demande de remplacement


                if ( $gardeSP and $E_EQUIPE > 0) {
                    echo "<p class='noprint'>";
                    // garde veille
                    $date_veille = date('Y-m-d', strtotime($year1[1].'-'.$month1[1].'-'.$day1[1].' - 1 days'));
                    $garde_veille=get_garde_jour(0, $E_EQUIPE, $date_veille);
                    if ( $garde_veille  > 0 )
                        echo " <label class='btn btn-default' title='Garde précédente' onclick=\"bouton_redirect('evenement_display.php?evenement=$garde_veille&from=gardes');\">
                            <i class='fa fa-chevron-left' ></i>
                            </label>";

                    // Retour tableau garde
                    echo " <input class='btn btn-default' type='button' value='tableau de garde' style='vertical-align:top;' 
                      onclick='bouton_redirect(\"tableau_garde.php?equipe=".$E_EQUIPE."&filter=".$organisateur."&month=".$month1[1]."&year=".$year1[1]."\");'> ";

                    // garde suivante
                    $date_suivante = date('Y-m-d', strtotime($year1[1].'-'.$month1[1].'-'.$day1[1].' + 1 days'));
                    $garde_suivante=get_garde_jour(0, $E_EQUIPE, $date_suivante);

                    if ( $garde_suivante  > 0 ) {
                        if ( substr($date_suivante,8,2) <> '01' or $granted_event)
                            echo "<label class='btn btn-default' title='Garde suivante' onclick=\"bouton_redirect('evenement_display.php?evenement=$garde_suivante&from=gardes');\">
                            <i class='fa fa-chevron-right' ></i>
                            </label>";
                    }
                }
            }
            if((strlen($listePompiers)-1)>1 and  $granted_event and ! $print and $changeallowed){
                echo "<div align=left style='float:left' class='noprint'>";
                echo "<a class='btn btn-primary dropdown-toggle' id='dropdownMenuButton' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' style='float:right;margin-left:10px'>Message</a>
                  <div class='dropdown-menu' style ='; margin-right:23px' aria-labelledby='dropdownMenuButton'>";
                echo  "<form name='FrmEmail' id='FrmEmail'method='post' action='mail_create.php'>";
                echo  "<input type='hidden' name='Messagesubject' value=\"".str_replace("'","",$E_LIBELLE)."\">";
                if ( $E_WHATSAPP <> "" and ($is_inscrit or $granted_personnel)) {
                    $msg = "\n\n\n\nRejoignez le groupe Whatsapp de cette activité ".$whatsapp_chat_url."/".$E_WHATSAPP;
                    echo  "<input type='hidden' name='Messagebody' value=\"".$msg."\">";
                }
                echo  "<input type='hidden' name='SelectionMail'
                        value=\"".rtrim($listePompiers,',')."\" />";
                if ( check_rights($id, 43)) {
                    echo "<a class='dropdown-item' onClick='document.getElementById(\"FrmEmail\").submit();' title=\"envoyer un message aux inscrits à partir de l'application web\"/>Envoyer</a>";
                    if ( $mailist <> "" ) {
                        echo " <a class='dropdown-item' href='#' onclick=\"DirectMailTo('".rtrim($mailist,',')."','".$evenement."')\"
                    title=\"Envoyer un mail aux inscrits à partir de votre logiciel de messagerie.\">Mail</a>";
                    }

                    echo " <a  class='dropdown-item' onclick=\"getListMails('".rtrim($mailist,',')."');\" title=\"Récupérer la liste des adresses email des inscrits\"><i class=\"fas fa-file-download\"></i> liste TXT</a>";
                    echo " <a class='dropdown-item' onclick=\"getListContacts('".rtrim($mailist,',')."');\" title=\"Récupérer la liste des contacts au format csv, pour les importer dans un groupe de messagerie\"><i class=\"fas fa-file-download\"></i> liste CSV</a>";
                }
                echo "</form>";
                echo "</div>";
                echo "<span class='noprint'><a class='btn btn-default hide_mobile' href='#' onclick='window.print();' ><i class='fas fa-print'></i></a></span>";
                echo "</div>";
            }

            if ( $E_WHATSAPP <> "" and ($is_inscrit or $granted_personnel)) {
                echo " <a class='btn btn-default noprint' href=\"".$whatsapp_chat_url."/".$E_WHATSAPP."\" target='_blank'
                title=\"Rejoindre ou communiquer avec le groupe Whatsapp de cette activité.\">
                <i class='fab fa-whatsapp fa-lg' style='color:#00cc00'></i></a>";
            }
        }
        else {
            echo "<p class='noprint'>Le tableau de garde n'est pas accessible par le personnel.</p>";
        }
    }

    if ($child == 2) {
        if (!$print) {
            $addAction = (!isset($_GET['addAction'])) ? 0 : $_GET['addAction'];
            if (!$addAction) {
                echo "<div align=right class='table-responsive tab-buttons-container'>";
                echo "<a class='btn btn-primary' name='see_all' onclick=\"javascript:self.location.href='remplacements.php';\">Voir</a>";

                if ( $evenement > 0 ) {
                    $label ='';
                    $S_ID=get_section_organisatrice($evenement);
                    if ( $nbsections > 0 and check_rights($id, 6)) $label='<i class="fa fa-plus-circle"></i><span class="hide_mobile"> Remplacement</span>';
                    else if ( $gardes and check_rights($id, 6, $S_ID)) $label='<i class="fa fa-plus-circle"></i><span class="hide_mobile"> Remplacement</span>';
                    else if ( ( $assoc or $army )  and check_rights($id, 15, $S_ID)) $label='<i class="fa fa-plus-circle"></i><span class="hide_mobile"> Remplacement</span>';
                    else if ( is_inscrit($id,$evenement)) $label='Me faire remplacer';
                    if ($label <> '' )
                        echo "<a class='btn btn-success'  onclick='javascript:self.location.href=\"evenement_display.php?pid=$pid&from=$from&tab=$tab&child=2&evenement=$evenement&addAction=1\";'>$label</a>";
                }
                echo "</div>";
                print table_remplacements($evenement, $status='', $date1='', $date2='' , $section=0);
            }else{
                include_once('remplacement_edit.php');
            }
        }
    }

    if ($child == 3) {
        $body = "";
        $list="";//liste des ids des pompiers
        $personnel = array();// put personnel in a array
        $query="select ep.EH_ID, p.P_ID, p.P_PRENOM, p.P_NOM, s.S_CODE, p.P_GRADE, p.P_STATUT,g.G_LEVEL
        from pompier p, section s, evenement_participation ep,grade g 
        where ep.P_ID = p.P_ID
        and ep.E_CODE=".$evenement."
        and ep.EP_ABSENT = 0
        and s.S_ID = p.P_SECTION
        and p.P_OLD_MEMBER = 0
        and g.G_GRADE=p.P_GRADE
        order by g.G_LEVEL desc, p.P_NOM, p.P_PRENOM asc";
        $result=mysqli_query($dbc,$query);//search personnel participating in the event

        while (custom_fetch_array($result)){
            $logoGrade="";
            if ( $grades ) {
                $logoGrade = "<img src=".$grades_imgdir."/".$P_GRADE.".png title='".$P_GRADE."' class='img-max-18'>";
                $grade=" (".$P_GRADE.")";
            }
            $value=$logoGrade." ". strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);//nom complet + logo garde
            $personnel[$EH_ID][$P_ID] = $value;
            $list .= $P_ID.",";
        }
        $list .= '0';
        $comps = array(); // put competences in a array
        $query="select q.P_ID, q.PS_ID , q.Q_VAL from qualification q 
                where ( Q_EXPIRATION is null or DATEDIFF(Q_EXPIRATION, '".$EH_DATE_DEBUT[1]."') > 0 )
                and P_ID in (".$list.")";
        $result=mysqli_query($dbc,$query);//search competences for personnal in $Lists
        while (custom_fetch_array($result)){
            $comps[$P_ID][$PS_ID] = $Q_VAL;
        }
        $_SESSION['list']=$list;
        $_SESSION['personnel']=$personnel;
        $_SESSION['comps']=$comps;
        $showjour = false;
        $shownuit = false;
        if ( $nbsessions == 2 ) {
            $showjour = true;
            $shownuit = true;
        }
        else if ( $nbsessions == 1 ) {
            if ( intval(substr($EH_DEBUT[1],0,2)) > 16 ) $shownuit = true;
            else $showjour = true;
        }
        $body .="<div id='myDiv' class='container-fluid'  >";
        if ( is_iphone() && check_rights($id,6,$organisateur)){//la version mobile peut consulter uniquement les piquets avec affichage sur la largeur du mobile
            $body .= "<div align='center' style='font-weight: bold;color:$mydarkcolor'>Vous pouvez accéder depuis votre ordinateur pour modifier les piquets</div> <br>";
            $body .="<div id='myRow' class='row' ><div class='col-md-12'>";
        }
        else {
            $body .= "<div id='myRow' class='row' ><div class='col-md-12 offset-lg-1 col-lg-6'>";
        }
        $query="SELECT distinct ev.E_CODE, v.TV_CODE, ev.V_ID, tv.TV_ICON, v.V_INDICATIF
                from evenement_vehicule ev, vehicule v, type_vehicule tv
                WHERE E_CODE = ".$evenement." 
                AND v.TV_CODE = tv.TV_CODE
                AND ev.V_ID = v.V_ID
                AND tv.TV_NB > 0
                order by v.TV_CODE, v.V_INDICATIF";
        $result=mysqli_query($dbc,$query);//search vehicules participating in the event
        if ( mysqli_num_rows($result) == 0 ) echo "<div class='alert alert-info' role='alert'> Pour utiliser les affectations, il faut configurer les équipes et les véhicules de l'activité </div>";
        write_debugbox($query);
        while (custom_fetch_array($result)){// a boucle to display the guard table for each vehicule
            if ( $V_INDICATIF <> '' ) $vname = $V_INDICATIF ;
            else $vname = $TV_CODE;
            $body .= "<p><table class='newTable'><tr ><td width=150 align='right'><img src='".$TV_ICON."' class='img-max-40'></td>
                      <td width=250 align='left'><h3>".$vname."</h3></td></tr></table>";
            $body .= display_postes($evenement,$V_ID, $showjour, $shownuit);
        }
        $body .="</div>";
        if (! is_iphone() && check_rights($id,6,$organisateur) ){//un bloc qui ne s'affiche qu'en version PC 
            $body .= "<div class='offset-6 col-5' style='position:fixed;z-index:0;'>";//the div containing personnal
            $body .= " <div class='row'>
                    <div class='col'>
                   <h3 id='titreDiv'  style='text-align:left' >Personnel de Garde</h3>
                   <h3 id='glisser'  style='text-align:left;display:none'class='text-danger'>Glisser ici pour supprimer</h3>
                   <div id='minus' class='p-2 rounded my-2 text-danger ' 
                   style='float:left;border-style:solid;border-width:1px;width:50%;height:400px;display: none;position: relative'>
                   <i style='position: relative;top: 50%;transform: translateY(-50%);' class='fas fa-user-minus fa-10x text-danger'  ></i>
                   </div>
                   <div id='divPersonnel' class='p-2 rounded my-2' style='border: solid;position:relative; 
                   float:left;border-width: 1px;border-color: $mydarkcolor;width:50%;height:400px;overflow: auto;scrollbar-width: thin'>";
            $body .= displayJourNuit($list, $personnel);
            $body .= "</div></div></div><div class='row'>
                <div class='col-3'><button class='btn btn-danger btn-block' id='vide_".$evenement."'>Vider</button></div>
                <div class='col-3'><button class='btn btn-success btn-block' id='affect_".$evenement."'>Affecter</button></div>
                </div></div>";
        }
        $body .="</div>";
        print $body ;
    }
}
//=====================================================================
// véhicules demandés
//=====================================================================
if ( (( $tab == 3 ) or ($print)) and (($vehicules) || ($materiel) || ($consommables)) ) {

    if(!$print) echo "<div style='background:white;' class='table-responsive table-nav table-tabs sub-tabs'>";
    echo "<ul class = 'nav nav-tabs sub-tabs noprint' id='myTab' role = 'tablist'>";

    if ($child == 0) $child = 1;
    if ($child == 1) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }
    if ($vehicules)
        echo "<li class = 'nav-item'>
            <a class = 'nav-link $class' href = 'evenement_display.php?pid=$pid&from=$from&tab=$tab&child=1&evenement=$evenement' role = 'tab'>Véhicule <span class='badge $badgeClass'>$NB2</span></a>
        </li>";

    if ($child == 2) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }

    if ($materiel)
        echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'evenement_display.php?pid=$pid&from=$from&tab=$tab&child=2&evenement=$evenement' role = 'tab'>Matériel <span class='badge $badgeClass'>$NB3</span></a>
        </li>";

    if ($child == 3) {
        $class = 'active';
        $badgeClass = 'active-badge';
    }
    else{
        $class = '';
        $badgeClass = 'inactive-badge';
    }

    if ($consommables)
        echo "<li class = 'nav-item'>
        <a class = 'nav-link $class' href = 'evenement_display.php?pid=$pid&from=$from&tab=$tab&child=3&evenement=$evenement' role = 'tab'>Consommable <span class='badge $badgeClass'>$NB6</span></a>
        </li>";
    echo "</ul>";
    echo "</div>";

    if ($child == 1){

        $laterOut = "";
        $later = 1;

        if (( $E_CANCELED == 0 ) and !$print and  $granted_vehicule) {
            $url="evenement_display.php?evenement=".$evenement."&what=vehicule&tab=50";
            echo "<div align=right class='table-responsive tab-buttons-container'>";
            echo "<a href='".$url."' class='btn btn-success'><i class='fa fa-plus-circle'></i> <span class='hide_mobile'>Véhicule</span></a>";
            echo "</div>";
        }

        $queryf="select TFV_ID, TFV_NAME from type_fonction_vehicule order by TFV_ORDER";
        $resultf=mysqli_query($dbc,$queryf);
        $fonctions=array();
        while ($rowf=@mysqli_fetch_array($resultf)) {
            array_push($fonctions, array($rowf["TFV_ID"],$rowf["TFV_NAME"]));
        }
        $nbfn=sizeof($fonctions);

        $query="select distinct ev.E_CODE as EC,v.V_ID,v.V_IMMATRICULATION,v.TV_CODE, vp.VP_LIBELLE, v.V_MODELE, v.V_INDICATIF,
                vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE, ev.EV_KM,
                DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
                DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
                DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
                ee.EE_ID, ee.EE_NAME,
                tfv.TFV_ID, tfv.TFV_NAME,
                tv.TV_ICON
                from vehicule v, type_vehicule tv, vehicule_position vp, section s, evenement e, evenement_vehicule ev
                left join evenement_equipe ee on (ee.E_CODE in (".$evts_list.") and ee.EE_ID=ev.EE_ID)
                left join type_fonction_vehicule tfv on ev.TFV_ID = tfv.TFV_ID
                where v.V_ID=ev.V_ID
                and e.E_CODE=ev.E_CODE
                and s.S_ID=v.S_ID
                and tv.TV_CODE = v.TV_CODE
                and vp.VP_ID=v.VP_ID
                and ev.E_CODE in (".$evts.")
                order by e.E_PARENT, ev.E_CODE asc";
        $result=mysqli_query($dbc,$query);

        $nbvehic=mysqli_num_rows($result);
        if ( $nbvehic > 0 ) {
            if($print)
                echo "</table></div></div></div>";
            echo "<div class='table-responsive'>";
            echo "<div class='col-sm-12'>";
            echo "<table class='newTableAll'>";
            echo "<tr>
                    <td class='hide_mobile'></td>
                    <td>Véhicule</td>
                    <td>Statut</td>";
            if ( $assoc ) {
                    echo "<td class='hide_mobile'>Indicatif</td>
                        <td class='hide_mobile'>Immatriculation</td>";
            }
            echo "<td align=center class='hide_mobile2'>Fonction</td>
                    <td align=center class='hide_mobile2'>Equipe</td>
                    <td >Kilométrage</td>
                    <td class='hide_mobile'></td>
                    <td ></td>
                </tr>";
            $prevEC='';
            while (custom_fetch_array($result)) {
                if ( $TV_ICON == "" ) $vimg="";
                else $vimg="<img src=".$TV_ICON." class='img-max-50'>";

                if ( $V_MODELE == "" ) $vehicule_string = $TV_CODE;
                else $vehicule_string = $TV_CODE." - ".$V_MODELE;

                // affiche d'où vient le renfort
                if ( $EC <> $prevEC ) {
                    $queryR="select e.E_CANCELED as CE_CANCELED, e.E_CLOSED as CE_CLOSED, eh.EH_ID,
                        s.S_CODE CS_CODE, s.S_DESCRIPTION CS_DESCRIPTION,
                        DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m') as EH_DATE_DEBUT0,
                        DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m') as EH_DATE_FIN0,
                        TIME_FORMAT(eh.EH_DEBUT, '%k:%i') EH_DEBUT0,  
                        TIME_FORMAT(eh.EH_FIN, '%k:%i') EH_FIN0
                        from evenement e, section s, evenement_horaire eh
                        where e.S_ID = s.S_ID
                        and e.E_CODE = eh.E_CODE
                        and e.E_CODE=".$EC;
                    $resultR=mysqli_query($dbc,$queryR);
                    $EH_DATE_DEBUT0 = Array();
                    $EH_DATE_DEBUT0 = Array();
                    $EH_DEBUT0 = Array();
                    $EH_FIN0 = Array();
                    $horaire_renfort = Array();

                    while ( $rowR=@mysqli_fetch_array($resultR)) {
                        $n=$rowR["EH_ID"];
                        $EH_DATE_DEBUT0[$n]=$rowR["EH_DATE_DEBUT0"];
                        $EH_DATE_FIN0[$n]=$rowR["EH_DATE_FIN0"];
                        $EH_DEBUT0[$n]=$rowR["EH_DEBUT0"];
                        $EH_FIN0[$n]=$rowR["EH_FIN0"];
                        $CE_CANCELED=$rowR["CE_CANCELED"];
                        $CE_CLOSED=$rowR["CE_CLOSED"];
                        $CS_CODE=$rowR["CS_CODE"];
                        $CS_DESCRIPTION=$rowR["CS_DESCRIPTION"];
                        if ( $CE_CANCELED == 1 ) {
                            $color="red";
                            $info="activité annulée";
                        }
                        elseif ( $CE_CLOSED == 1 ) {
                            $color=$widget_fgorange;
                            $info="activité clôturée";
                        }
                        else {
                            $color=$widget_fggreen;
                            $info="activité ouverte";
                        }
                        if ( $EH_DATE_DEBUT0[$n] <> $EH_DATE_FIN0[$n] ) $dates_renfort=$EH_DATE_DEBUT0[$n] ." au ".$EH_DATE_FIN0[$n];
                        else $dates_renfort=$EH_DATE_DEBUT0[$n];
                        $horaire_renfort[$n]=$dates_renfort." - ".$EH_DEBUT0[$n]."-".$EH_FIN0[$n];
                    }
                    if ( $EC <> $evenement ) {
                        echo "<tr CLASS='Menu' height=25>
                            <td colspan=10 class='hide_mobile'>
                            <i><a href=evenement_display.php?evenement=$EC&from=inscription>
                            <i class='fa fa-plus-square lg' style='color:".$color."' title='$info' ></i>
                            ".ucfirst($renfort_label)." de ".$CS_CODE."</i></a>
                            </td></tr>";
                    }
                    $prevEC = $EC;
                }

                $fgcolor = $widget_fgred;
                $bgcolor = $widget_bgred;
                if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
                else if ( $VP_ID == "PRE" ) $mytxtcolor="blue";
                else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$red;
                else if ( my_date_diff(getnow(),$V_ASS_DATE) < 0 ) {
                    $mytxtcolor="#f64e60";
                    $fgcolor = $widget_fgred;
                    $bgcolor = $widget_bgred;
                    $VP_LIBELLE = "Assurance périmée";
                }
                else if ( my_date_diff(getnow(),$V_CT_DATE) < 0 ) {
                    $fgcolor = $widget_fgred;
                    $bgcolor = $widget_bgred;
                    $VP_LIBELLE = "CT périmé";
                }
                else if ( $VP_OPERATIONNEL == 2) {
                    $fgcolor = $widget_fgorange;
                    $bgcolor = $widget_bgorange;
                }
                else if (( my_date_diff(getnow(),$V_REV_DATE) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
                    $fgcolor = $widget_fgorange;
                    $bgcolor = $widget_bgorange;
                    $VP_LIBELLE = "Révision à faire";
                }
                else {
                    $fgcolor = $widget_fggreen;
                    $bgcolor = $widget_bggreen;
                }

                // récupérer horaires du véhicule
                $clock="";
                for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
                    if ( isset ($horaire_renfort[$i])) {
                        $query_horaires="select  EH_ID,
                       DATE_FORMAT(EV_DATE_DEBUT,'%d-%m-%Y') EV_DATE_DEBUT, 
                       DATE_FORMAT(EV_DATE_FIN,'%d-%m-%Y') EV_DATE_FIN,
                       TIME_FORMAT(EV_DEBUT, '%k:%i') EV_DEBUT,  
                       TIME_FORMAT(EV_FIN, '%k:%i') EV_FIN,
                       DATE_FORMAT(EV_DATE_DEBUT,'%Y-%m-%d') EV_DATE_DEBUT1,
                       DATE_FORMAT(EV_DATE_FIN,'%Y-%m-%d') EV_DATE_FIN1
                       from evenement_vehicule
                       where E_CODE=".$EC."
                       and EH_ID = ".$i."
                       and V_ID=".$V_ID;
                        $resultH=mysqli_query($dbc,$query_horaires);
                        $rowH=@mysqli_fetch_array($resultH);
                        $EH_ID=@$rowH["EH_ID"];
                        $clock .= "<span class='btn btn-default btn-action noprint'>";
                        if ( $EH_ID <> "" ) {
                            $EV_DATE_DEBUT=$rowH["EV_DATE_DEBUT"];    // DD-MM-YYYY
                            $EV_DATE_FIN=$rowH["EV_DATE_FIN"];
                            $EV_DATE_DEBUT1=$rowH["EV_DATE_DEBUT1"];  // YYYY-MM-DD
                            $EV_DATE_FIN1=$rowH["EV_DATE_FIN1"];
                            $EV_DEBUT=$rowH["EV_DEBUT"];
                            $EV_FIN=$rowH["EV_FIN"];
                            if ($nbsessions == 1 ) $t=" de l'activité";
                            else $t=" de la partie n°$EH_ID";
                            if ( $EV_DATE_DEBUT <> "" ) {
                                if ( $EV_DATE_DEBUT1 == $EH_DATE_DEBUT0[$i] and $EV_DATE_FIN1 == $EH_DATE_FIN0[$i] ) $horaire_v=$EV_DEBUT."-".$EV_FIN;
                                else if ( $EV_DATE_DEBUT == $EV_DATE_FIN ) $horaire_v= substr($EV_DATE_DEBUT,0,5).", ".$EV_DEBUT."-".$EV_FIN;
                                else $horaire_v= substr($EV_DATE_DEBUT,0,5)." au ".substr($EV_DATE_FIN,0,5).", ".$EV_DEBUT."-".$EV_FIN;
                                $clock .="<i class='fa fa-clock fa-lg noprint' style='color:$widget_fgorange'></i>";
                            }
                            else $clock .="<i class='fa fa-clock fa-lg noprint' style='color:$widget_fggreen'></i>";
                        }
                        else $clock .="<i class='fa fa-clock fa-lg noprint' style='color:$widget_fgred'></i>";
                        $clock .= "</span>";
                    }
                }

                if ( $gardeSP ) $myimg="";
                else {
                    $nb = get_nb_engagements('V', $V_ID, $year1[1], $month1[1], $day1[1], $year2[$nummaxpartie], $month2[$nummaxpartie], $day2[$nummaxpartie] , $EC);
                    if ( $nb > 1 )
                        $myimg="<a href=evenement_vehicule.php?vehicule=".$V_ID."&dtdb=".$day1[1]."-".$month1[1]."-".$year1[1]."&dtfn=".$day2[$nummaxpartie]."-".$month2[$nummaxpartie]."-".$year2[$nummaxpartie]."&order=dtdb&filter=".$S_ID.">
                        <i class='fa fa-exclamation noprint' style='color:$widget_fgred' title='attention ce véhicule est parallèlement engagé sur $nb autres activités' border=0></i></a>";
                    else if ( $nb == 1 )
                        $myimg="<a href=evenement_vehicule.php?vehicule=".$V_ID."&dtdb=".$day1[1]."-".$month1[1]."-".$year1[1]."&dtfn=".$day2[$nummaxpartie]."-".$month2[$nummaxpartie]."-".$year2[$nummaxpartie]."&order=dtdb&filter=".$S_ID.">
                        <i class='fa fa-exclamation noprint' style='color:#ff8000' title='attention ce véhicule est parallèlement engagé sur 1 autre activité' ></i></a>";
                    else $myimg="";
                }
                $altcolor=(($S_ID==$organisateur)?"":"<font color=purple>");
                $section = $nbsections == 0 ? $S_CODE : '';
                echo "<tr>
                      <td style='max-width:50px' class='noprint hide_mobile'>".$vimg."</td>
                      <td style='min-width:100px;'><a href=upd_vehicule.php?from=evenement&vid=$V_ID 
                            title=\"$S_CODE - $S_DESCRIPTION\">".$altcolor.$vehicule_string."</a><br>$section</td>";
                
                echo "<td><span class='badge' style='color:$fgcolor; background-color:$bgcolor' >".ucfirst($VP_LIBELLE)."</span></td>";
                if ( $assoc ) {
                    echo "<td style='min-width:40px;max-width:100px;' class='hide_mobile'>".$V_INDICATIF."</td>";
                    echo "<td style='min-width:40px;max-width:60px;' class='hide_mobile'>".$V_IMMATRICULATION."</td>";
                }
                
                // choix fonction
                if ( ! $print ) {
                    // choix fonction
                    $url="evenement_modal.php?&action=vfonction&evenement=".$evenement."&vid=".$V_ID;
                    if ( $TFV_ID == "" or $TFV_ID == 0 ) $TFV_NAME="<div id='divfn".$V_ID."' title='sélectionner une fonction'>Choisir</div>";
                    else $TFV_NAME="<div id='divfn".$V_ID."' title='changer la fonction'>".$TFV_NAME."</div>";
                    echo "<td style='min-width:5px;' align=center class='hide_mobile2'>";
                    print write_modal( $url, "fonction_".$V_ID, $TFV_NAME);
                    echo "</td>";
                }

                // choix équipe
                if ( $nbe > 0 ) {
                    if (! $granted_event or ! $changeallowed or $print) {
                        if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="";
                        echo  "<td style='min-width:80px;' class='hide_mobile2'><font>".$EE_NAME."</font></td>";
                    }
                    else {
                        // choix équipe
                        $url="evenement_modal.php?action=vequipe&evenement=".$evenement."&vid=".$V_ID;
                        if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="<div id='divpe".$V_ID."' title='Choisir une équipe' class='noprint'>Choisir équipe</div>";
                        else $EE_NAME="<div id='divpe".$V_ID."' title='Changer équipe'>".$EE_NAME."</div>";
                        echo "<td align=center style='min-width:80px;' class='hide_mobile2'>";
                        print write_modal( $url, "equipe_".$V_ID, $EE_NAME);
                        echo "</td>";
                    }
                }
                else echo "<td class='hide_mobile2'></td>";

                if ( $granted_vehicule ) $readonly="";
                else $readonly="readonly";

                // kilométrage
                if ( $EV_KM == '' ) $showEV_KM = 'renseigner ';
                else $showEV_KM  = $EV_KM;

                $url="evenement_modal.php?action=km&evenement=".$evenement."&vid=".$V_ID;
                $showEV_KM = "<div id='vkmdiv".$V_ID."' title='Renseigner le kilométrage'>".ucfirst($showEV_KM)." km</div>";
                echo "<td>";
                if ( $readonly == '') print write_modal( $url, "km_".$V_ID, $showEV_KM);
                else if ($EV_KM > 0 ) echo "$EV_KM km";
                echo "</td>";
                
                echo "<td class='hide_mobile' style='max-width:80px;'>".$myimg."</td>";
                
                echo "<td><div align=right >";
 
                // affiche horaires
                if ( ! $print and $changeallowed) {
                    if ($granted_event or ($granted_vehicule and $E_CLOSED == 0)) {
                        $url="evenement_horaires.php?evenement=".$EC."&pid=0&vid=".$V_ID;
                        print write_modal($url,"Horaire_".$V_ID, $clock);
                    }
                    else
                        echo $clock;
                }

                // supprimer 
                if ( $granted_vehicule and ! $print) {
                    echo "<a class='btn btn-default btn-action' href=evenement_vehicule_add.php?evenement=$evenement&EC=$EC&action=remove&V_ID=$V_ID&from=evenement title='désengager ce véhicule'>
                            <i class='far fa-trash-alt fa-lg'></i></a>";
                }
                echo "</div></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        else echo "Aucun véhicule engagé.<br>";
    }

    if ($child == 2){

        if (( $E_CANCELED == 0 ) and !$print and ( $granted_vehicule )) {
            echo "<div align=right class='table-responsive tab-buttons-container'>";
            echo "<a class='btn btn-success'  name='ajouter' title=''
               onclick=\"redirect('evenement_display.php?evenement=".$evenement."&what=materiel&tab=50');\"><i class='fa fa-plus-circle'></i> <span class='hide_mobile'>Matériel</span></a>";
            echo "</div>";
        }

        $query="select em.E_CODE as EC, m.MA_ID, tm.TM_CODE, m.TM_ID, vp.VP_LIBELLE, m.MA_MODELE, m.MA_NUMERO_SERIE,
        vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE, em.EM_NB, m.MA_NB, m.MA_PARENT, tm.TM_LOT,
        cm.TM_USAGE, cm.PICTURE, cm.CM_DESCRIPTION,
        ee.EE_ID, ee.EE_NAME,
        DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE
        from evenement_materiel em left join evenement_equipe ee on ( ee.EE_ID = em.EE_ID and ee.E_CODE=".$evenement.") ,
        materiel m, vehicule_position vp, section s, 
        type_materiel tm, categorie_materiel cm, evenement e
        where m.MA_ID=em.MA_ID
        and e.E_CODE=em.E_CODE
        and cm.TM_USAGE=tm.TM_USAGE
        and tm.TM_ID = m.TM_ID
        and s.S_ID=m.S_ID
        and vp.VP_ID=m.VP_ID
        and em.E_CODE in (".$evts.")
        and MA_PARENT is null
        and e.E_CANCELED = 0
        order by cm.TM_USAGE, tm.TM_CODE, tm.TM_LOT desc, m.S_ID,  m.MA_MODELE";

        $result=mysqli_query($dbc,$query);
        $nbmat=mysqli_num_rows($result);
        if ( $nbmat > 0 ) {
            echo "<div class='table-responsive'>";
            echo "<div class='col-sm-12'>";
            echo "<table class='newTableAll'>";
            echo "<tr><td colspan=10>Matériel</td></tr>";

            $prevTM_USAGE='';
            $prevEC=$evenement;
            while (custom_fetch_array($result)) {
                $myimg="";
                if ( $nbmat < 30 ) {
                    $nb = get_nb_engagements('M', $MA_ID, $year1[1], $month1[1], $day1[1], $year2[$nummaxpartie], $month2[$nummaxpartie], $day2[$nummaxpartie], $EC) ;
                    if ( $nb > 1 ) {
                        $myimg="<a href=evenement_materiel.php?matos=".$MA_ID."&dtdb=".$day1[1]."-".$month1[1]."-".$year1[1]."&dtfn=".$day2[$nummaxpartie]."-".$month2[$nummaxpartie]."-".$year2[$nummaxpartie]."&order=dtdb&filter=".$S_ID.">
                        <i class='fa fa-exclamation-triangle' style='color:#ff8000;' title='attention ce matériel est parallèlement engagé un ou des autres activités' ></i></a>";
                    }
                }

                // affiche catégorie
                if ( $TM_USAGE <> $prevTM_USAGE) {
                    echo "<tr><td colspan=10>".ucfirst($CM_DESCRIPTION)."</td></tr>";
                }
                $prevTM_USAGE=$TM_USAGE;

                if ( $VP_OPERATIONNEL == -1) $mytxtcolor='black';
                else if ( $VP_ID == "PRE" ) $mytxtcolor=$widget_fgblue;
                else if ( $VP_OPERATIONNEL == 1) $mytxtcolor=$widget_fgred;
                else if ( my_date_diff(getnow(),$MA_REV_DATE) < 0 ) {
                    $mytxtcolor=$widget_fgorange;
                    $VP_LIBELLE = "date dépassée";
                }
                else if ( $VP_OPERATIONNEL == 2) {
                    $mytxtcolor=$widget_fgorange;
                }
                else $mytxtcolor=$widget_fggreen;

                $element="<font color=$mylightcolor>.....";
                if ( $TM_LOT == 1 ) $element .="</font><i class='fa fa-plus-square fa-lg' title=\"Ceci est un lot de matériel\"></i> ";
                elseif ( $MA_PARENT > 0  ) $element .="...</font><i class='fa fa-minus'  title=\"élément d'un lot de matériel\"></i> ";
                else $element .="</font><i class='fa fa-caret-right fa-lg' title=\"Ne fait pas partie d'un lot\"></i> ";

                $altcolor=(($S_ID==$organisateur)?"":"<font color=purple>");

                $title = $TM_CODE;
                if ( $MA_MODELE <> "" ) $title .= " - ".$MA_MODELE;
                if ( $MA_NUMERO_SERIE <> "" )  $title .= "- ".$MA_NUMERO_SERIE;
                echo "<tr valign=baseline style='background-color:transparent'><td width=350>".$element."<font><a href=upd_materiel.php?from=evenement&mid=$MA_ID title=\"$S_CODE - $S_DESCRIPTION\">".$altcolor.$title."</a>
                <font color=$mytxtcolor>".$VP_LIBELLE."</font></td>";
                echo "<td width=20>".$myimg."</td>";

                // choix équipe
                if ( $nbe > 0 ) {
                    if (! $granted_event or ! $changeallowed) {
                        if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="";
                        echo  "<td>$EE_NAME</a></td>";
                    }
                    else {
                        // choix équipe
                        $url="evenement_modal.php?action=mequipe&evenement=".$evenement."&mid=".$MA_ID;
                        if ( $EE_ID == "" or $EE_ID == 0 ) $EE_NAME="<div id='divpe".$MA_ID."' title='Choisir une équipe'><i>Choisir équipe</i></div>";
                        else $EE_NAME="<div id='divpe".$MA_ID."' title='Changer équipe'>".$EE_NAME."</div>";
                        echo "<td width=120>";
                        print write_modal( $url, "equipe_".$MA_ID, $EE_NAME);
                        echo "</td>";
                    }
                }
                else echo "<td></td>";

                if ( $granted_vehicule ) $readonly="";
                else $readonly="readonly";

                echo "<td width=100 align=center>";
                if ( $MA_NB > 1 ) {
                    // choix nombre
                    if ( $EM_NB == '' )  $EM_NB = 0;
                    if ( $readonly == ''){
                        $EM_NB="<div id='mnbdiv".$MA_ID."' style='font-size:11px;' title='Renseigner le nombre'>".$EM_NB." unités</div>";
                        $url="evenement_modal.php?action=mnombre&evenement=".$evenement."&mid=".$MA_ID;
                        print write_modal( $url, "nombre_".$MA_ID, $EM_NB);
                    }
                    else echo "$EM_NB pièces";
                }
                echo "</td>";
                if ( $nbsections == 0 ) echo "<td width=120>$S_CODE";
                else echo "<td></td>";

                if ( $granted_vehicule  and (! $print) ) {
                    echo "<td width=20>
                    <a class='btn btn-default btn-action' href=evenement_materiel_add.php?evenement=".$evenement."&EC=".$EC."&action=remove&MA_ID=".$MA_ID."&from=evenement title='désengager ce matériel'>
                    <i class='far fa-trash-alt fa-lg'></i></a></td>";
                }
                else
                    echo "<td width=20></td>";
                echo "</tr>";
            }
            echo "</table></td></tr>";
            echo "</table>";
        }
        else echo "Aucun matériel engagé.<br>";
    }

    if ($child == 3){
        if ( $E_CANCELED == 0 and !$print and  $granted_consommables ) {
            $url="evenement_detail.php?evenement=".$evenement."&what=vehicule";
            echo "<div align=right class='table-responsive tab-buttons-container'>";
            echo "<a class='btn btn-success' name='ajouter'
               onclick=\"redirect('evenement_display.php?evenement=".$evenement."&what=consommables&tab=50');\"><i class='fa fa-plus-circle'></i> <span class='hide_mobile'>Consommable</span></a>";
            echo "</div>";
        }
    
        $query="select ec.E_CODE, ec.EC_ID, ec.C_ID, ec.EC_NOMBRE, ec.EC_DATE_CONSO,
        c.S_ID, tc.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, DATE_FORMAT(c.C_DATE_ACHAT, '%d-%m-%Y') as C_DATE_ACHAT, 
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE,
        tum.TUM_CODE, tum.TUM_DESCRIPTION,tco.TCO_DESCRIPTION,tco.TCO_CODE,cc.CC_NAME, cc.CC_CODE, cc.CC_IMAGE, cc.CC_DESCRIPTION
        from evenement_consommable ec left join consommable c on c.C_ID = ec.C_ID,
        categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, type_consommable tc
        where ec.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and ec.E_CODE in (".$evts.")
        order by cc.CC_NAME, tc.TC_DESCRIPTION";
        echo "<div class='table-responsive'>";
        $result=mysqli_query($dbc,$query);
        $nbmat=mysqli_num_rows($result);
        if ( $nbmat > 0 ) {
            // echo "<div style='max-width: 850px; display:inline-block'>";
            echo "<div class='col-sm-12'>";
            echo "<table class='newTableAll' cellspacing=0 border=0 >";

            // echo "<tr><td colspan=20>Produits consommés sur cette activité</td></tr>";
            echo "<tr><td colspan=20>Consommable";
            $prevCC_NAME='';
            $prevEC=$evenement;
            while (custom_fetch_array($result)) {
                $TC_DESCRIPTION=ucfirst($TC_DESCRIPTION);
                if ( $TCO_CODE == 'PE' ) $label =  $TC_DESCRIPTION." (".$TUM_DESCRIPTION."s) ".$C_DESCRIPTION;
                else if ( $TUM_CODE <> 'un' or $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.") ".$C_DESCRIPTION;
                else $label = $TC_DESCRIPTION." ".$C_DESCRIPTION;

                if ( $C_ID > 0 ) {
                    $query2="select s.S_ID, s.S_CODE, s.S_DESCRIPTION from section s, consommable c
                            where s.S_ID=c.S_ID and c.C_ID=".$C_ID;
                    $result2=mysqli_query($dbc,$query2);
                    custom_fetch_array($result2);
                }
                else {
                    $S_ID=$organisateur;
                    $S_CODE="";
                }

                // affiche catégorie
                if ( $CC_NAME <> $prevCC_NAME) {
                    echo "<tr><td colspan=20><b style='font-weight: 600;'> $CC_NAME</td></tr>";
                }
                $prevCC_NAME=$CC_NAME;
                $altcolor=(($S_ID==$organisateur)?"":"<font color=purple>");
                echo "<tr><td align=right width=30><i class='fa fa-caret-right fa-lg'></i> </td><td style='font-size:11px;' width = 300>";
                if ( $C_ID > 0 )
                    echo "<a href=upd_consommable.php?from=evenement&cid=$C_ID title=\"$S_CODE - $S_DESCRIPTION\"> ".$altcolor.$label."</a>    ";
                else
                    echo "<span title=\"Origine du stock non précisée\">".$label."</span>";
                echo "</td>";
                if ( $granted_consommables ) $readonly="";
                else $readonly="readonly";
                echo "<td width=80 align=center>";

                // nombre d'uités consommées
                if ( $EC_NOMBRE == '' )  $EC_NOMBRE = 0;
                $url="evenement_modal.php?action=cnombre&evenement=".$evenement."&cid=".$EC_ID;
                $EC_NOMBRE = "<div id='cnbdiv".$EC_ID."' style='font-size:11px;' title='Renseigner le nombre'>".intval($EC_NOMBRE)." unités</div>";
                if ( ! $print ) {
                    echo "<td width=120>";
                    if ( $readonly == '') print write_modal( $url, "nombre_".$EC_ID, $EC_NOMBRE);
                    else echo "$EC_NOMBRE unités";
                    echo "</td>";
                }

                // section
                if ( $nbsections == 0 ) echo "<td><font size=1>$S_CODE</td>";
                else echo "<td></td>";

                if ( $granted_consommables  and (! $print) ) {
                    echo "<td width=30 align=center>
                    <a class='btn btn-default btn-action' href=evenement_consommable_add.php?evenement=".$evenement."&action=remove&C_ID=".$C_ID."&EC_ID=".$EC_ID." title='supprimer cette ligne'>
                    <i class='far fa-trash-alt fa-lg'></i></a>";
                    echo "</td>";
                }
                else {
                    echo "<td width=20></td>";
                }

                echo "</tr>";
            }
            echo "</table></td></tr>";
            echo "</table>";
        }
        else echo "Aucun consommable n'est renseigné.<br>";
    }
}

//=====================================================================
// formation / diplômes
//=====================================================================
if (( $tab == 5 ) and (! $print) and ( $TE_CODE == 'FOR' ) and ( $PS_ID <> "") and ($TF_CODE <> "")){

    if ( $granted_event and ( check_rights($id, 4, $organisateur) or $chef )) $disabledtf="";
    else $disabledtf="disabled";
    if ( $E_CLOSED == 0 ) $disabledtf="disabled";

    echo "<p class='noprint'>";
    $query="select p.PS_DIPLOMA, p.PS_NUMERO, p.DAYS_WARNING, p.PS_EXPIRABLE, p.PS_NATIONAL, p.PS_SECOURISME, p.PS_PRINTABLE, p.PS_PRINT_IMAGE, p.PS_ID, p.TYPE, tf.TF_LIBELLE, e.F_COMMENT,
        p.PH_CODE, p.PH_LEVEL, ph.PH_UPDATE_LOWER_EXPIRY, ph.PH_UPDATE_MANDATORY
        from type_formation tf, poste p left join poste_hierarchie ph on ph.PH_CODE = p.PH_CODE,
        evenement e
        where e.PS_ID=p.PS_ID
        and e.TF_CODE=tf.TF_CODE
        and e.E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $_TYPE=$row["TYPE"];
    $_DAYS_WARNING=$row["DAYS_WARNING"];
    $_PS_ID=$row["PS_ID"];
    $_TF_LIBELLE=$row["TF_LIBELLE"];
    $_PS_EXPIRABLE=$row["PS_EXPIRABLE"];
    $_F_COMMENT=$row["F_COMMENT"];
    $_PS_PRINTABLE=$row["PS_PRINTABLE"];
    $_PS_PRINT_IMAGE=$row["PS_PRINT_IMAGE"];
    $_PS_NATIONAL=$row["PS_NATIONAL"];
    $_PS_SECOURISME=$row["PS_SECOURISME"];
    $_PS_DIPLOMA=$row["PS_DIPLOMA"];
    $_PS_NUMERO=$row["PS_NUMERO"];
    $_PH_CODE=$row["PH_CODE"];
    $_PH_LEVEL=$row["PH_LEVEL"];
    $_PH_UPDATE_LOWER_EXPIRY=$row["PH_UPDATE_LOWER_EXPIRY"];
    $_PH_UPDATE_MANDATORY=$row["PH_UPDATE_MANDATORY"];

    $printdiplomes=false;
    if ($_PS_PRINTABLE == 1 ){
        if ( $_PS_NATIONAL == 1 ) {
            if (check_rights($id, 48, "0" )) $printdiplomes=true;
        }
        else if (check_rights($id, 48, "$S_ID")) $printdiplomes=true;
    }

    if ($_TYPE <> "") {
        if ($_TF_LIBELLE <> "") $tt=$_TF_LIBELLE." pour ".$_TYPE;
        else $tt ="formation pour ".$_TYPE;
    }
    else $tt="formation";
    echo "<div class='container-fluid'>";
    echo "<div class='row'>";
    echo "<div class='col-sm-4'>";
    echo "<div class='card hide card-default graycarddefault' align=center >
                <div class='card-header graycard'>
                    <div class='card-title'><strong>Résultats de ".$tt."</strong></div>
                </div>
                <div class='card-body graycard'>
                <table class='noBorder'>";

    if (( $E_PARENT <> '' ) and ( $E_PARENT > 0) and ( $nbsections == 0)) {
        $queryR="select e.TE_CODE, e.E_LIBELLE, s.S_CODE, s.S_DESCRIPTION 
            from evenement e, section s 
            where s.S_ID = e.S_ID
            and e.E_CODE=".$E_PARENT;
        $resultR=mysqli_query($dbc,$queryR);
        $rowR=@mysqli_fetch_array($resultR);
        $ER_LIBELLE=stripslashes($rowR["E_LIBELLE"]);
        $SR_CODE=$rowR["S_CODE"];
        $SR_DESCRIPTION=$rowR["S_DESCRIPTION"];
        echo "<tr><td>Voir activité principale </td><td><a href=evenement_display.php?evenement=".$E_PARENT."&from=formation>".$ER_LIBELLE." organisé par ".$SR_CODE." - ".$SR_DESCRIPTION."</a></td></tr>";
        echo "</table>";
    }
    else {
        if($E_DUREE_TOTALE!=''){
            echo "<tr><td colspan=2>Durée effective: ".$E_DUREE_TOTALE." heures</td></tr>";
        }
        //instructeurs
        $queryi="select distinct ep.E_CODE as EC, p.P_ID,p.P_NOM,".phone_display_mask('p.P_PHONE')." P_PHONE, p.P_PRENOM, p.P_GRADE, s.S_ID, s.S_CODE, p.P_STATUT, c.C_NAME,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS AGE, ep.TP_ID, tp.TP_NUM, tp.TP_LIBELLE
        from evenement_participation ep, pompier p, section s, type_participation tp, company c
        where ep.E_CODE in (".$evts.")
        and tp.TP_ID = ep.TP_ID
        and p.C_ID = c.C_ID
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and ep.TP_ID > 0";
        if ( $evenement_show_absents == 0 )
            $queryi .= " and ep.EP_ABSENT = 0 ";
        $queryi .= " order by p.P_NOM, p.P_PRENOM";
        $resulti=mysqli_query($dbc,$queryi);

        //stagiaires
        $query="select distinct ep.E_CODE as EC, p.P_ID,p.P_NOM,".phone_display_mask('p.P_PHONE')." P_PHONE, p.P_PRENOM, p.P_GRADE, s.S_ID, s.S_CODE, p.P_STATUT, c.C_NAME,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS AGE, ep.TP_ID
        from evenement_participation ep, pompier p, section s, company c
        where ep.E_CODE in (".$evts.")
        and p.C_ID = c.C_ID
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and ep.TP_ID=0";
        if ( $evenement_show_absents == 0 )
            $query .= " and ep.EP_ABSENT = 0 ";
        $query .= " order by p.P_NOM, p.P_PRENOM";
        $result=mysqli_query($dbc,$query);
        $nbstagiaires=mysqli_num_rows($result);

        if ( mysqli_num_rows($resulti) > 0 ) {
            while (custom_fetch_array($resulti)) {
                if ( $P_STATUT == 'EXT' ) {
                    $colorbegin="<font color=$widget_fggreen>";
                    $colorend="</font>";
                    $title="Personnel externe ".$C_NAME." (".$S_CODE.")";
                }
                else {
                    $colorbegin="";
                    $colorend="";
                    $title=$S_CODE;
                }

                echo "<tr><td colspan=2> ".$TP_LIBELLE."";
                echo " <a href=upd_personnel.php?pompier=$P_ID title=\"$title\" style='margin-left:15px;'>".
                    $colorbegin.strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM).$colorend."</a>";
                echo "</td></tr>";
            }
        }
        else if ( count($chefs) > 0 ) {
            echo "<tr><td >Responsable </td>
            <td >";
            for ( $c = 0; $c < count($chefs); $c++ ) {
                echo " <a href=upd_personnel.php?pompier=".$chefs[$c]."> ".my_ucfirst(get_prenom($chefs[$c]))." ".strtoupper(get_nom($chefs[$c]))."</a> ";
            }
            echo "</td></tr>";
        }
        $nbadmis=0;
        $nbdiplomes=0;

        if ( mysqli_num_rows($result) > 0 ) {
            echo "<tr><td colspan=2>
            <form name='diplomes' action='evenement_diplome.php' method='POST'>";
            echo " <input type=hidden name='evenement' value='".$evenement."'>";
            $laterprint='';
            while (custom_fetch_array($result)) {

                $query1="select count(1) as NB from evenement_participation
                where P_ID =".$P_ID."
                and EP_ABSENT = 0 
                and E_CODE in (".$evts.")";
                $result1=mysqli_query($dbc,$query1);
                $row1=@mysqli_fetch_array($result1);
                $n1=$row1["NB"];

                $query1="select count(1) as NB from evenement_participation 
                where P_ID=".$P_ID." 
                and E_CODE in (".$evts.")
                and EP_ABSENT = 0 
                and EP_DATE_DEBUT is not null";
                $result1=mysqli_query($dbc,$query1);
                $row1=@mysqli_fetch_array($result1);
                $n2=$row1["NB"];

                if ( check_rights($id, 10,"$S_ID")) $granted_update=true;
                else $granted_update=false;
                if (($granted_event or ($P_ID == $id and $E_CLOSED == 0) or ($granted_update and $E_CLOSED == 0)) and $changeallowed ) {
                    $url="evenement_horaires.php?evenement=".$evenement."&pid=".$P_ID."&vid=0";
                    
                    if ($n1 < $nbsessions)
                        $clock="<i class='fa fa-clock fa-lg' style='color:grey' title=\"Attention n'est pas présent à toutes les parties de la formation\" border=0></i>";
                    else if ($n2 > 0)
                        $clock="<i class='fa fa-clock fa-lg' style='color:$widget_fgorange'  title='Attention horaires différents de ceux de la formation' border=0></i>";
                    else
                        $clock="<i class='fa fa-clock fa-lg' style='color:$widget_fggreen'  title='Présence totale sur la formation' border=0></i>";
                    $warn=write_modal($url,"Horaire_".$P_ID, $clock);
                }
                else {
                    if ($n1 < $nbsessions) $warn="<i class='fa fa-clock fa-lg' style='color:$widget_fgred'  title=\"Attention n'est pas présent à toutes les parties de la formation\" border=0></i>";
                    else if ($n2 > 0) $warn="<i class='fa fa-clock fa-lg' style='color:$widget_fgorange'  title='Attention horaires différents de ceux de la formation' border=0></i>";
                    else $warn="<i class='fa fa-clock fa-lg' style='color:$widget_fggreen'  title='Présence totale sur la formation' border=0></i>";
                }

                $query1="select PF_ADMIS, PF_DIPLOME, date_format(PF_EXPIRATION,'%d-%m-%Y') PF_EXPIRATION  from personnel_formation pf
                 where pf.P_ID=".$P_ID." and pf.E_CODE=".$evenement;
                $result1=mysqli_query($dbc,$query1);
                $row1=@mysqli_fetch_array($result1);
                $PF_DIPLOME=@$row1["PF_DIPLOME"];
                $PF_EXPIRATION=@$row1["PF_EXPIRATION"];
                if (@$row1["PF_ADMIS"] == 1) {
                    $checked="checked";
                    $nbadmis++;
                }
                else $checked="";
                if ( $PF_DIPLOME <> "" ) $nbdiplomes++;
                $cmt1=""; $cmt2="";
                if ( $AGE <> '' )
                    if ($AGE < 18 )
                        $cmt1="<font color=red>(-18)</font>";

                $for=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
                $laterprint .= "<tr><td><label class='switch'><input type=checkbox value='".$P_ID."' $checked $disabledtf 
                    id='dipl_".$P_ID."' name='dipl_".$P_ID."' 
                    title=\"cochez cette case si ".$for." a réussi la formation\"";

                if ($_PS_EXPIRABLE == 1)
                    $laterprint .= "onchange=\"change_date_exp('".$P_ID."');\"";
                $laterprint .= "><span class='slider round'></span></label>";

                if ( $TF_CODE == 'I' and $_PS_NUMERO == 1) {
                    $laterprint .= " <input type=text id='num_".$P_ID."' name='num_".$P_ID."' class='form-control flex'
                        style='max-width:200px;margin-bottom:3px;' maxlength='25'
                        title=\"saisissez le numéro de diplôme décerné à ".$for."\"
                        value='".$PF_DIPLOME."' $disabledtf>";
                }
                if ($_PS_EXPIRABLE == 1) {
                    $laterprint .= " <input type='text' size='10' id='exp_".$P_ID."' name='exp_".$P_ID."' $disabledtf  
                    value='".$PF_EXPIRATION."' class='form-control datepicker datesize flex'
                    placeholder='JJ-MM-AAAA'
                    autocomplete='off'
                    class='datepicker' data-provide='datepicker'
                    style='margin-bottom:3px;'
                    title =\"saisissez ici la date de validité de la compétence pour ".$for." au format JJ-MM-AAAA\"
                    onchange=\"change_date_exp('".$P_ID."');\">";
                }

                if ( $P_STATUT == 'EXT' ) {
                    $colorbegin="<font color=$widget_fggreen>";
                    $colorend="</font>";
                    $title="Personnel externe ".$C_NAME." (".$S_CODE.")";
                }
                else {
                    $colorbegin="";
                    $colorend="";
                    $title=$S_CODE;
                }

                $query1="select Q_VAL, DATE_FORMAT(Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION, 
                  DATEDIFF(Q_EXPIRATION,NOW()) as NB
                  from qualification
                where P_ID=".$P_ID." 
                and PS_ID=".$PS_ID;
                $result1=mysqli_query($dbc,$query1);
                $row1=@mysqli_fetch_array($result1);
                $Q_VAL=@$row1["Q_VAL"];
                $Q_EXPIRATION=@$row1["Q_EXPIRATION"];
                $NB=@$row1["NB"];
                if ( $Q_VAL <> '' ) {
                    if ( $Q_EXPIRATION <> '') {
                        if ($NB <= 0)
                            $cmt2="<font size=1 color=$widget_fgred>Compétence $_TYPE expirée depuis $Q_EXPIRATION</font>";
                        else if ($NB < $_DAYS_WARNING)
                            $cmt2="<font size=1 color=$widget_fgorange>Compétence $_TYPE expire le $Q_EXPIRATION</font>";
                        else if ( $Q_VAL == 2 )
                            $cmt2="<font size=1 color=$widget_fgblue>Compétence secondaire $_TYPE expire le $Q_EXPIRATION</font>";
                        else if ( $Q_VAL == 1 )
                            $cmt2="<font size=1 color=$widget_fggreen>Compétence principale $_TYPE expire le $Q_EXPIRATION</font>";
                    }
                    else if ( $Q_VAL == 2 )
                        $cmt2="<font size=1 color=$widget_fgblue>Compétence secondaire $_TYPE valide</font>";
                    else if ( $Q_VAL == 1 )
                        $cmt2="<font size=1 color=$widget_fggreen>Compétence principale $_TYPE valide</font>";
                }
                else {
                    $cmt2="<font size=1 color=black>En formation pour obtenir la compétence $_TYPE</font>";
                    // cas particulier: ne pas montrer PSE1 si PSE2 valide
                    if ( $_TYPE == 'PSE1') {
                        $query3="select count(1) as NB from qualification q, poste p
                where q.P_ID=".$P_ID." and p.PS_ID=q.PS_ID and p.TYPE='PSE2'";
                        $result3=mysqli_query($dbc,$query3);
                        $row3=@mysqli_fetch_array($result3);
                        $NB=$row3["NB"];
                        if ( $NB == 1 ) $cmt2="<font size=1 color=blue>Possède la compétence supérieure PSE2</font>";
                    }
                }

                $laterprint .= " <a href=upd_personnel.php?pompier=$P_ID title=\"$title\">".$colorbegin.
                    strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM).$colorend."</a> ".$cmt1." <span class='hide_mobile'>".$cmt2."</span> ".$warn;

                $laterprint .= "</td></tr>";
            }
            echo "<tr><td>Commentaire:</td></tr>";
            echo "<tr><td colspan=2><input type=text class='form-control form-control-sm flex' size=40 name=comment value =\"".$_F_COMMENT."\" $disabledtf></td></tr>";
            if ( $disabledtf == "" ) {
                if ( $_PH_UPDATE_LOWER_EXPIRY == 1 and $_PH_LEVEL > 0 and $_PS_EXPIRABLE == 1 ) {
                    $query1="select p.PH_LEVEL, ph.PH_CODE, ph.PH_NAME, ph.PH_UPDATE_LOWER_EXPIRY, p.PS_ID, p.TYPE
                    from poste_hierarchie ph, poste p
                    where ph.PH_CODE = '".$_PH_CODE."' 
                    and p.PH_LEVEL <= ".$_PH_LEVEL."
                    and p.PS_ID <> ".$_PS_ID."
                    and p.PH_CODE = ph.PH_CODE
                    and p.PS_EXPIRABLE=1
                    order by p.PH_LEVEL asc";
                    $result1=mysqli_query($dbc,$query1);
                    $number=mysqli_num_rows($result1);

                    if ( $number > 0 ) {
                        $competencesH="";
                        while ($row1=@mysqli_fetch_array($result1)) {
                            $hierarchieN = $row1["PH_NAME"];
                            $competencesH .= $row1["TYPE"].",";
                        }
                        if ( $_PH_UPDATE_MANDATORY == 1 )
                            echo "<tr><td><small>La date de validité des compétences inférieures de la hiérarchie ".$hierarchieN." sera automatiquement prolongée (".rtrim($competencesH,',').").</small></td></tr>";
                        else
                            echo "<tr><td><input type='checkbox' name='update_hierarchy' value='1' checked
                                title=\"Cocher pour reporter aussi l'expiration des compétences inférieures de la hiérarchie\"></td>
                                <td class=small2> Prolonger aussi la validité des compétences expirables de la hiérarchie ".$hierarchieN." (".rtrim($competencesH,',').")</td></tr>";
                    }
                }
                echo "<tr><td colspan=2>";
                if ( $evenement_show_absents == 1 ) $checked='checked';
                else $checked='';
                
                echo "Montrer les absents 
                    <label class='switch'>
                        <input type='checkbox' id ='evenement_show_absents' name='evenement_show_absents' class='ml-3' value='1' 
                        onchange=\"show_absents('".$evenement."','5')\" $checked >
                        <span class='slider round' title='Montrer les absents sur cette formation'></span>
                    </label>
                 </td></tr>";
                
            }
            echo "</td></tr>";
        }
        echo "</table></div></div></div>";
        
        echo "<div class='col-sm-8'>
                <table class='newTableAll'>
                <tr><td>Réussite des stagiaires à la formation</td></tr>";
        echo @$laterprint;
        echo "</table></div></div>";
        
        if ( $E_CLOSED == 0 ) {
            echo "<div class='alert alert-warning'>Veuillez fermer les inscriptions sur l'activité, alors vous pourrez sauver les diplômes.</div>";
        }
        else {
            echo "<input type='submit' class='btn btn-success' value='Sauvegarder'>";
            if (( $printdiplomes or $granted_event ) and $nbstagiaires > 0 ) {
                // désactiver les attestation de formation continues de secourisme car plus aux normes
                $enable_this=true;
                if ( isset($no_attestations_continue_secourisme) and $TF_CODE == 'R' ) {
                    if ( in_array(str_replace(" ", "",$_TYPE),array('PSC1','PSE1','PSE2','PAEPSC','PAEPS','FDFPSC','FDFPSE')) and intval($no_attestations_continue_secourisme) <= $YEAR )
                        $enable_this=false;
                }
                if ( $enable_this ) {
                    $t = "Diplômes";
                    if ( $TF_CODE == 'R' ) $t = "Attestation";
                    else $t .= " ou attestations";
                    echo "<div class='dropdown show' style='display: inline-block; white-space: nowrap; align:top'  >
                          <a class='btn btn-primary dropdown-toggle' href='#' role='button' id='dropdownMenuLink2' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' 
                            title='imprimer ".$t.", choisissez un des différents modes proposés' style='margin-bottom:7px; margin:0px;' >
                            <i class='fa fa-print'></i> $t
                          </a>
                          <div class='dropdown-menu' aria-labelledby='dropdownMenuLink2'>";
                    if ( $granted_event ) {
                        $link = "pdf_attestation_formation.php?evenement=".$evenement."&section=".$S_ID;
                        echo "<a class='dropdown-item' href=$link target=_blank
                            title=\"Imprimer des attestations sur papier vierge, possible pour tous les stagiaires ayant réussi ou échoué.\">
                            Attestations de formation</a>";
                    }
                    if ( $printdiplomes and $nbadmis > 0 and
                        ( $TF_CODE == 'I' or  ( $TF_CODE == 'T' and ( $_PS_DIPLOMA == 0 or $_PS_NUMERO == 0)) )
                    ) {
                        if ( $_PS_PRINT_IMAGE == 1 or $printfulldiplome ) {
                            echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=3' target=_blank
                            title=\"Choisissez cette option si vous utilisez de feuilles de papier vierges, l'image du diplôme sera imprimée en même temps que les informations du stagiaire diplômé.\">
                            Diplôme $_TYPE sur papier blanc</a>";
                        }
                        else  if ( $_PS_PRINT_IMAGE == 0 ) {
                            if ( $_PS_SECOURISME == 1 and $_PS_NUMERO == 1 ) {
                                echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=1' target=_blank
                            title=\"Choisissez cette option si vous disposez de feuilles de diplômes pre-imprimées, ayant chacune un numéro unique. 
                            Les n° de diplômes doivent être saisis ci-dessus avant de lancer l'impression. ATTENTION: Les feuilles doivent être introduites dans le bon ordre dans l'imprimante.\">
                            Diplôme $_TYPE sur papier pré-imprimé numéroté</a>";
                            }
                            if ( $_PS_NUMERO == 1 )
                                echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=2' target=_blank
                            title=\"Choisissez cette option si vous disposez de feuilles de diplômes pre-imprimées, sans numéro unique.
                            Les n° de diplômes doivent être saisis ci-dessus avant de lancer l'impression, ils seront imprimés.\">
                            Diplôme $_TYPE sur papier pré-imprimé non numéroté</a>";
                            else
                                echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=2' target=_blank
                            title=\"Choisissez cette option si vous disposez de feuilles de diplômes pre-imprimées non numérotées.\">
                            Diplôme $_TYPE sur papier pré-imprimé</a>";
                            echo "<a class='dropdown-item' href='pdf_diplome.php?evenement=".$evenement."&mode=4' target=_blank
                            title=\"Choisissez cette option si vous utilisez de feuilles de papier vierges, un aperçu du diplôme officiel sera imprimé.
                            Les n° de diplômes doivent être saisis ci-dessus avant de lancer l'impression.\">
                            Aperçu avant impression $_TYPE</a>";
                        }
                    }
                }
                echo "</form></div>
                </div><p>";
            }
        }
    } // if E_PARENT > 0
}

//=====================================================================
// tarif de la formation
//=====================================================================
if (( $tab == 6 ) and (! $print) and ( $E_TARIF > 0 ) and $granted_event){

    if ( $changeallowed ) $disabled_tarif='';
    else $disabled_tarif='disabled';
    
    echo "<div class='table-responsive'>";
    echo "<div class='col-sm-6'>";
    echo "<table class='newTableAll' cellpading=0 cellspacing=0 border=0>";
    echo "<tr>
            <td >Stagiaire</td>
            <td class='hide_mobile'>Entreprise</td>
            <td align=center>Tarif formation</td>
            <td align=center> Convoc. </td>
            <td align=center> Facture </td>
      </tr>";

//stagiaires
    $query="select distinct ep.E_CODE as EC, p.P_ID, p.P_NOM, ".phone_display_mask('p.P_PHONE')." P_PHONE, p.P_PRENOM, 
        p.P_GRADE, s.S_ID, s.S_CODE, p.P_STATUT, c.C_ID, c.C_NAME, ep.EP_TARIF, ep.EP_PAID, ep.MODE_PAIEMENT, tp.TP_DESCRIPTION
        from evenement_participation ep left join type_paiement tp on tp.TP_ID=ep.MODE_PAIEMENT, pompier p, section s, company c
        where ep.E_CODE in (".$evts.")
        and p.C_ID = c.C_ID
        and ep.EP_ABSENT = 0
        and p.P_ID=ep.P_ID
        and p.P_SECTION=s.S_ID
        and ep.TP_ID=0
        order by p.P_NOM, p.P_PRENOM";
    $result=mysqli_query($dbc,$query);
    $nbstagiaires=mysqli_num_rows($result);

    echo "<input type=hidden name='evenement' value='".$evenement."'>";
    $total_to_pay=0;
    $total_paid=0;
    while (custom_fetch_array($result)) {
        $MODE_PAIEMENT = intval($MODE_PAIEMENT);
        if ( $P_STATUT == 'EXT' ) {
            $colorbegin="<font color=$widget_fggreen>";
            $colorend="</font>";
            $title="Personnel externe ".$C_NAME." (".$S_CODE.")";
        }
        else {
            $colorbegin="";
            $colorend="";
            $title=$S_CODE;
        }

        if ( $EP_TARIF == '' ) $tarif = $E_TARIF;
        else $tarif = floatval($EP_TARIF);

        $total_to_pay = $tarif + $total_to_pay;
        if ( $EP_PAID == 1 ) {
            $checked='checked';
            $total_paid = $tarif + $total_paid;
        }
        else $checked='';

        $showtarif=$tarif;
        if ( $EP_PAID == 1 ) {
            $color=$widget_fggreen;
            $title='paiement réalisé ';
            if ( $MODE_PAIEMENT > 0 ) {
                $title.=" - ".$TP_DESCRIPTION;
                $showtarif.=" ".$TP_DESCRIPTION;
            }
        }
        else {
            $color=$widget_fgred;
            $title='pas encore payé';
        }

        if ( $C_ID == 0 ) $company='';
        else $company="<a href=upd_company.php?C_ID=".$C_ID.">".$colorbegin.$C_NAME.$colorend."</a>";
        $for=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
        echo "<tr><td ><a href=upd_personnel.php?pompier=".$P_ID." title=\"$title\">".$colorbegin.$for.$colorend."</a></td>";
        echo "<td class='hide_mobile'>".$company."</td>";

        if ( $changeallowed ) $title .= ". Cliquer pour modifier";
        $displayed="<span class=badge style='background-color:$color;'>".$showtarif."</span>";
        echo "<td align=center>";
        $url="evenement_tarif_formation.php?evenement=".$evenement."&pid=".$P_ID;
        if ( $changeallowed ) print write_modal( $url, $P_ID, $displayed);
        else print $displayed;
        echo "</td>";

        $myimg="<i class='far fa-file-pdf fa-lg' style='color:$widget_fgred;' ></i>";
        $link="pdf_document.php?P_ID=".$P_ID."&section=".$S_ID."&evenement=".$evenement."&mode=18";
        echo "<td align=center><a href='$link' class='btn btn-default btn-action' target='_blank'>".$myimg."</a>";
        $link="pdf_document.php?P_ID=".$P_ID."&section=".$S_ID."&evenement=".$evenement."&mode=7";
        echo "<td align=center><a href='$link' class='btn btn-default btn-action' target='_blank'>".$myimg."</a>";
        echo "</tr>";
    }

    if ( $total_to_pay > $total_paid ) $T="<font color=red>".round($total_paid,2)." ".$default_money_symbol."</font>";
    else $T="<font color=$widget_fggreen>".round($total_paid,2)." ".$default_money_symbol."</font>";

    echo "<tr class='newTabHeader'>
        <td align=left>Total</td>
        <td align=right class='hide_mobile'></td>
        <td align=center>".$T." / ".round($total_to_pay,2)." ".$default_money_symbol."</td>
        <td></td>
        <td></td>";
    echo "</tr></table></div></div>";
}

//=====================================================================
// tab 7 documents
//=====================================================================

if ( $tab == 7  and ! $print ){
    $addAction = (!isset($_GET['addAction'])) ? 0 : $_GET['addAction'];
    if (!$addAction) {
        if (!$print) {
            if ($documentation) {
                echo "<div align=right style='float:right;margin-right:15px;'>";
                echo " <a class='btn btn-success' href='evenement_display.php?pid=$pid&from=$from&tab=$tab&evenement=$evenement&addAction=1&section=$S_ID' id='userfile' name='userfile' title='Attacher de nouveaux fichiers'>
                  <i class='fa fa-plus-circle fa-1x noprint'  ></i> Document</a>";
                echo "</div>";
            }
        }
        include_once ($basedir."/fonctions_documents.php");
        $table="";
        $tableHead = "<div class='table-responsive'>
        <div class='col-sm-12'>
        <table class='newTableAll'>
            <tr class='newTabHeader'>
                <th class='widget-title' style='padding: 12px 5px 12px 5px; pointer-events: none;'>Documents attachés</th>
                <th class='widget-title hide_mobile' style='pointer-events: none;'>Auteur</th>
                <th class='widget-title hide_mobile' style='pointer-events: none;'>Date</th>
                <th class='widget-title'></th>
            </tr>";

        // DOCUMENTS ATTACHES
        $table .= show_attached_docs($evenement);
        if ( intval($E_PARENT) > 0 ) $table .= show_attached_docs($E_PARENT);

        // DOCUMENTS GENERES
        if ($granted_event) {
            if ( $FICHE_PRESENCE == 1  and $E_CLOSED == 1 ) {
                // fiche de présence spécifique SST
                if ( $type_doc == 'SST') $table .=show_auto_doc("Fiche de présence SST", "8", true);
                else if ( $type_doc == 'PRAP') $table .=show_auto_doc("Fiche de présence PRAP", "8", true);
                else $table .=show_auto_doc("Fiche de présence", "1", true);
                if ( $type_doc == 'SST') $table .=show_auto_doc("Attestations de présence SST", "10", true);
                else if ( $type_doc == 'PRAP') $table .=show_auto_doc("Attestations de présence PRAP", "10", true);
            }
            if ( $PROCES_VERBAL == 1  and $E_CLOSED == 1 and  $PS_ID <> '' and in_array($TF_CODE,array('I','C','R','M')))
                $table .=show_auto_doc("Procès verbal", "5", true);
            if ( $EVAL_PAR_STAGIAIRES == 1  and $competences == 1) {
                $level=get_level("$S_ID");
                $sstspec=$basedir."/images/user-specific/documents/fiche_de_fin_de_stage_SST.pdf";
                if ( $type_doc == 'SST' and (file_exists($sstspec)) and $level < 3 ) $table .=show_auto_doc("Fiche d'évaluation de la formation SST", "9", false);
                else if ( $type_doc == 'PRAP' and (file_exists($sstspec)) and $level < 3 ) $table .=show_auto_doc("Fiche d'évaluation de la formation PRAP", "9", false);
                else $table .=show_auto_doc("Fiche d'évaluation de la formation", "3", false);
            }
            if ( $FACTURE_INDIV == 1  and $E_TARIF > 0 ) $table .=show_auto_doc("Factures individuelles", "7", false);

            if ( $CONVENTION == 1  and $E_PARENT == 0 ) {
                if ( $TE_CODE == 'FOR' ) $docnum=26;
                else $docnum=6;
                $table .=show_auto_doc("Convention sans signature", $docnum, $secured=false, $signed=false);
                if ( signature_president_disponible($S_ID))$table .= show_auto_doc("Convention signée par le président", $docnum, $secured=false, $signed=true);
            }
            if ( $EVAL_RISQUE == 1  and dim_ready($evenement)) {
                $table .=show_auto_doc("Grille d'évaluation des risques - complète", "-1", false);
                $table .=show_auto_doc("Grille d'évaluation des risques - page 1", "-2", false);
            }
            if ( $CONVOCATIONS == 1  and $E_CLOSED == 1 ) {
                $table .=show_auto_doc("Convocation collective", "15", false);
                $table .=show_auto_doc("Convocations individuelles", "18", false);
            }
            if ( ! $gardeSP and $TE_PERSONNEL == 1 and $TE_CODE <> 'MC' and $TE_CODE <> 'FOR' and $ORDRE_MISSION == 1 and ( $assoc or $army ) ) {
                if ( $E_ALLOW_REINFORCEMENT == 1 ) $title="Demande de ".$renfort_label."s";
                else $title="Demande de personnels et de moyens";
                $table .=show_auto_doc($title, "25", false);
            }
            if ( $NB6 > 0 ) {
                $table .=show_auto_doc("Produits consommables utilisés", "27", false);
            }
        }
        if ( $granted_event or $is_present ) {
            if ( $ORDRE_MISSION == 1  and $E_CLOSED == 1 ) $table .=show_auto_doc("Ordre de mission", "4", false);
        }

        // DOCUMENTS HARDCODES
        $table .= show_specific_documents($type_doc);

        $tableEnd = "</table></div></div>";

        if ( $table <> "" )
            echo $tableHead.$table.$tableEnd;
        else
            echo "Aucun document";
    }
    else {
        include_once('upd_document.php');
    }
}

//=====================================================================
// tab 8 compte rendu
//=====================================================================
if ( $tab == 8  and ! $print and $TE_MAIN_COURANTE == 1) {
    //DEPLACEMENT DE BOUTONS Left top tabs
    if (!$print) {
        if ( ($granted_event or $is_operateur_pc) ){
            echo "<div align=right class='table-responsive tab-buttons-container'>";
            if ( $granted_event or $is_operateur_pc ) {
                if ( $autorefresh == 1 ) $checked='checked';
                else $checked='';
                echo "<div style = 'float:left;margin-top:10px;margin-left:10px;'> Rafraîchissement automatique
                <label class='switch'>
                    <input type='checkbox' id ='autorefresh' class='ml-3' id='autorefresh' name='autorefresh' 
                    value='1' 
                    onclick=\"autorefresh_interventions('".$evenement."');\" $checked >
                    <span class='slider round' ></span>
                </label>
                </div>";
            }
            if ( ( $granted_event or $is_operateur_pc ) and ! $gardeSP and $TE_VICTIMES == 1 && !(intval($E_PARENT) > 0))
                echo "<a class='btn btn-success' href='evenement_display.php?from=interventions&evenement=".$evenement."&tab=22&numcav=0&action=insert&'><i class='fa fa-plus-circle'></i> Centre d'accueil</a>";

            echo "<a class='btn btn-success' href='intervention_edit.php?from=interventions&evenement=".$evenement."&numinter=0&action=insert&type=M'><i class='fa fa-plus-circle'></i> Rapport</a>";

            if ($TE_VICTIMES == 1 && !(intval($E_PARENT) > 0)) {
                echo "<a class='btn btn-danger' href='intervention_edit.php?from=interventions&evenement=".$evenement."&numinter=0&action=insert&type=I'><i class='fa fa-plus-circle'></i> Intervention</a>";
                
                $nbcav=count_entities("centre_accueil_victime", "E_CODE=".$evenement." and CAV_OUVERT=1");
                if ( $nbcav > 0 )
                    echo "<a class='btn btn-danger' href='victimes.php?from=list&action=insert&evenement_victime=".$evenement."&numcav=0' ><i class='fa fa-plus-circle' ></i> Victime</a>";
                $victimBut = "<a class='btn btn-primary' id='victall' name='victall' title='Voir la liste des victimes'
                    href='evenement_display.php?tab=23&evenement_victime=".$evenement."&evenement=".$evenement."&type_victime=ALL&from=evenement'>
                    <i class='fas fa-binoculars'></i> Victimes</a>";
            }
            echo "<a class='btn btn-default' href='#' style='margin-right:15px;'><i class='far fa-file-pdf pdf-hover' onclick=\"window.open('evenement_display.php?tab=62&evenement=".$evenement."')\"/></i></a>";
            echo "</div>";
        }
        echo "<div class='table-responsive toolbar overflow-hidden'>";
        echo "<div class='left'>";

        if ( intval($E_PARENT) > 0 ) {
            echo "<a class='btn btn-secondary' id='victall' name='victall' value=\"Voir les informations sur l'activité principal\" 
                    onclick=\"javascript:self.location.href='evenement_display.php?evenement=".$E_PARENT."&tab=8';\" 
                    title=\"Voir les informations sur l'activité principale\"><i class='fa fa-eye pr-2'></i>Activité principale</a>";
        }
        echo "</div>";
        echo "</div>";
    }

    echo "<div id='interventions' class='table-responsive'>";
    echo "<div class='container-fluid'>";
    echo "<div class='row'>";

    if ( !(intval($E_PARENT) > 0) ) {
        // ==========================
        // stats
        // ==========================

        $queryN="select tb.TB_NUM, tb.TB_LIBELLE, be.BE_VALUE
            from type_bilan tb left join bilan_evenement be on (be.E_CODE=".$evenement." and be.TB_NUM = tb.TB_NUM)
            where tb.TE_CODE='".$TE_CODE."' 
            order by tb.TB_NUM";

        $resultN=mysqli_query($dbc,$queryN);
        if ( mysqli_num_rows($resultN) > 0 ) {
            if ( $E_PARTIES > 1 ) $cmt = " sur les $E_PARTIES parties";
            else $cmt ="";

            echo "
                <div class='col-sm-6'>
                <div class='card hide card-default graycarddefault' >
                <div class='card-header graycard'>
                    <div class='card-title'><strong> Statistiques </strong></div>
                </div>
                <div class='card-body graycard'>
                <table class='noBorder'>";

            echo "<tr  height=30>
                    <td colspan=6 align=left>";
            $inscrits=array();
            $inscrits=explode(",",get_inscrits($evenement));
            $out = "";
            while ( $rowR=@mysqli_fetch_array($resultN)) {
                $TB_NUM=$rowR["TB_NUM"];
                $TB_LIBELLE=$rowR["TB_LIBELLE"];
                $BE_VALUE=$rowR["BE_VALUE"];
                if ( $granted_event
                    or $is_operateur_pc
                    or ( $TE_CODE == 'AH' and in_array($id, $inscrits) )
                )
                    $out .= "<input type='text' style='width:35px;margin-bottom:2px;' maxlength=4 size=2 id='nombre".$TB_NUM."' name='nombre".$TB_NUM."' value='$BE_VALUE' 
                        
                        onchange='updatenumber(\"nombre".$TB_NUM."\",\"".$evenement."\",\"".$TB_NUM."\",this.value,\"$BE_VALUE\")'> ".$TB_LIBELLE."<br>";
                else {
                    $out .= $BE_VALUE." ".$TB_LIBELLE.", ";
                }
            }
            echo rtrim(rtrim($out),",");
            echo "</td>";
            echo "</table></div></div></div>";
        }
    }
    // ==========================
    // rapport sans interventions
    // ==========================

    if ( $TE_VICTIMES == 0 || $E_PARENT > 0) {
        $query="select e.EL_ID, e.E_CODE, e.TEL_CODE ,
        date_format(e.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(e.EL_DEBUT,'%H:%i') HEURE_DEBUT,
        e.EL_TITLE, e.EL_COMMENTAIRE, e.EL_IMPORTANT,
        tel.TEL_DESCRIPTION, TIMESTAMPDIFF(MINUTE,e.EL_DEBUT,e.EL_DATE_ADD) TIMEDIFF ,
        date_format(e.EL_DATE_ADD,'le %d-%m-%Y à %H:%i') DATE_ADD,
        date_format(e.EL_SLL,'%H:%i') HEURE_SLL,
        TIMESTAMPDIFF(MINUTE,e.EL_DATE_ADD,NOW()) NEW, date_format(e.EL_DEBUT,'%Y-%m-%d') EL_DEBUT,
        p.P_NOM, p.P_PRENOM
        from evenement_log e left join pompier p on p.P_ID = e.EL_AUTHOR, 
        type_evenement_log tel  
        where tel.TEL_CODE = e.TEL_CODE
        and e.E_CODE=".$evenement."
        order by EL_DEBUT desc, HEURE_DEBUT desc";
        $result=mysqli_query($dbc,$query);
        $nbmessages = @mysqli_num_rows($result);
        
        echo "
            <div class='card hide card-default graycarddefault' align=center >
            <div class='card-header graycard'>
                <div class='card-title'><strong> Messages</strong> 
                    <span class='badge alert-green'>".$nbmessages."</span>
                </div>
            </div>
            <div class='card-body graycard'>";
        echo "<table class='noBorder'>";
        if ( $nbmessages == 0 )
            echo "<tr>
                <td colspan=5 class=small>Aucun compte rendu n'a été saisi. Cliquez sur 'Message' pour ajouter un bloc.</td></tr>";
        else
            echo "<tr>
                <td>Date</td>
                <td></td>
                <td align=left>Heure</td>
                <td style='min-width=120px;' align=left>Rédacteur</td>
                <td >Message</td>
            </tr>";

        $prev_DATE_DEBUT="";
        while (custom_fetch_array($result) ) {
            $AUTHOR_NOM=strtoupper($P_NOM);
            $AUTHOR_PRENOM=my_ucfirst($P_PRENOM);

            if ( $DATE_DEBUT <> $prev_DATE_DEBUT ) {
                echo "<tr >
                    <td colspan=5 align=left>".$DATE_DEBUT."</td>
                    </tr>";
                $prev_DATE_DEBUT=$DATE_DEBUT;
            }

            if ( abs($NEW) < 10 ) $new="<i class='fa fa-star' style='color:yellow;' title=\"Cette ligne a été ajoutée il y a moins de 10 minutes\" ></i>";
            else $new='';

            if ( $granted_event or $is_operateur_pc ) $title_msg = "<a href=intervention_edit.php?evenement=".$E_CODE."&numinter=".$EL_ID."&action=update&type=".$TEL_CODE." title='Cliquer pour éditer' >".$EL_TITLE."</a>";
            else $title_msg = $EL_TITLE;

            echo "<tr>
            <td></td>
            <td align=left valign=top><i class='far fa-file-text fa-lg' title='".$TEL_DESCRIPTION."'></i></td>
            <td valign=top><div align=left>".$HEURE_DEBUT." ".$new."</div></td>
            <td valign=top><div align=left>".$AUTHOR_PRENOM." ".$AUTHOR_NOM."</div></td>
            <td align=left>".$title_msg."
                <br><span class=small2>".nl2br(wordwrap($EL_COMMENTAIRE,180,"\n"))."</span></td>
            </tr>";
        }
        echo "</table></div></div></div>";
    }
    // ==========================
    // rapport avec interventions
    // ==========================
    else {

        // centres accueil victimes
        $query="select c.CAV_ID, c.CAV_NAME, c.CAV_ADDRESS, c.CAV_COMMENTAIRE, c.CAV_RESPONSABLE, c.CAV_OUVERT, p.P_NOM, p.P_PRENOM
                from centre_accueil_victime c left join pompier p on p.P_ID =  c.CAV_RESPONSABLE
                where c.E_CODE=".$evenement." order by c.CAV_NAME";
        $result=mysqli_query($dbc,$query);
        $nbcav = @mysqli_num_rows($result);

        if ( $nbcav > 0 ) {
            $query2="select count(1) from victime where CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement.")";
            $result2=mysqli_query($dbc,$query2);
            $row2=@mysqli_fetch_array($result2);
            $nb1=intval($row2[0]);
            echo "
            <div class='col-sm-6' >
            <div class='card hide card-default graycarddefault' >
            <div class='card-header graycard'>
                <div class='card-title'><strong> Centre d'accueil</strong>
                    <span class='badge alert-violet'>".$nbcav."</span>
                </div>
            </div>
            <div class='card-body graycard'>
            <table class='newTableAll'>";

            echo "<tr  style='background-color:inherit'>
                    <td colspan=6 ></td>
                    <td colspan=1 align=left >Responsable</td>
                    <td align=center><a href='liste_victimes.php?evenement_victime=".$evenement."&type_victime=cav&from=evenement'
                        class='Tabheader' style='color:black;' title=\"Voir les victimes de tous les centres d'accueil\" >Victimes: ".$nb1."</a></td>
                    <td align=center>Transports</a></td>
                    </tr>";
            while ($row=@mysqli_fetch_array($result) ) {
                $CAV_ID=$row["CAV_ID"];
                $P_NOM=strtoupper($row["P_NOM"]);
                $P_PRENOM=my_ucfirst($row["P_PRENOM"]);

                $CAV_OUVERT=$row["CAV_OUVERT"];
                $CAV_NAME = $row["CAV_NAME"];
                if ( $CAV_OUVERT == 0 ) {
                    $CAV_CMT = "<span class=small title='le centre est fermé, on ne peut pas ajouter de victimes'>fermé</span>";
                    $color='red';
                    $title="centre accueil victimes fermé, on ne peut pas ajouter de victimes";
                }
                else {
                    $CAV_CMT = "";
                    $color=$widget_fggreen;
                    $title="centre accueil victimes ouvert";
                }
                $CAV_RESPONSABLE=intval($row["CAV_RESPONSABLE"]);
                if ( $CAV_RESPONSABLE <> 0 ) $resp= "<a href=upd_personnel.php?pompier=".$CAV_RESPONSABLE." title='Voir la fiche du responsable'>".$P_PRENOM." ".$P_NOM."</a>";
                else $resp="";

                $query2="select count(1) from victime where CAV_ID =".$CAV_ID;
                $result2=mysqli_query($dbc,$query2);
                $row2=@mysqli_fetch_array($result2);
                $nb=intval($row2[0]);
                if ( $nb > 9 ){
                    $fgcolor=$widget_fgred;
                    $bgcolor=$widget_bgred;
                }
                else if ( $nb > 0 ){
                    $fgcolor=$widget_fgorange;
                    $bgcolor=$widget_bgorange;
                }
                else{
                    $fgcolor='';
                    $bgcolor='';
                };

                $query2="select count(1) from victime where CAV_ID =".$CAV_ID." and VI_TRANSPORT=1";
                $result2=mysqli_query($dbc,$query2);
                $row2=@mysqli_fetch_array($result2);
                $nbt=intval($row2[0]);
                if ($nbt == 0 ) $nbt="";

                echo "<tr bgcolor=>
                        <td></td>
                        <td><i class='fa fa-h-square fa-lg' style='color:".$color.";' title=\"".$title."\"></i></td>
                        <td colspan=4 align=left><a href=cav_edit.php?numcav=".$CAV_ID." title='éditer ce centre'>".$CAV_NAME."</a> ".$CAV_CMT."</td>
                        <td colspan=1 align=left>".$resp."</td>
                        <td align=center><a href='liste_victimes.php?evenement_victime=".$evenement."&type_victime=".$CAV_ID."&from=evenement' title=\"Voir les victimes de ce centre d'accueil\">
                            <span class='badge' style='color:$fgcolor; background-color:$bgcolor'> ".$nb."</span></a></td>
                        <td align=center>".$nbt."</td>
                        </tr>";
            }
            echo "</table></div></div></div>";
        }

        //intervention
        if (isset($_GET['intervention'])) {
            if ($_GET['intervention']=="true") {
                $checkedi="";
                $truefalsei="";
                $truefalser='false';
                $searchsqli=" and tel.TEL_CODE <>'I' ";
            }
            else {
                $checkedi="checked";
                $truefalsei='true';
                $searchsqli="";
            }
        }
        else {
            $checkedi="checked";
            $truefalsei='true';
            $searchsqli="";
        }
        if (isset($_GET['rapport'])) {
            if ($_GET['rapport']=="true") {
                $checkedr="";
                $truefalser='false';
                $searchsqlr=" and tel.TEL_CODE <>'M' ";
            }
            else {
                $checkedr="checked";
                $truefalser='true';
                $searchsqlr='';
            }
        }
        else {
            $checkedr="checked";
            $truefalser='true';
            $searchsqlr="";
        }
        $searchsql=$searchsqli.$searchsqlr;

        $query="select e.EL_ID, e.E_CODE, e.TEL_CODE ,date_format(e.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(e.EL_DEBUT,'%H:%i') HEURE_DEBUT,
        date_format(e.EL_FIN,'%d-%m') DATE_FIN, date_format(e.EL_FIN,'%H:%i') HEURE_FIN, e.EL_IMPORTANT,
        e.EL_TITLE, e.EL_ADDRESS,e.EL_COMMENTAIRE,e.EL_RESPONSABLE, p.P_NOM, p.P_PRENOM,
        tel.TEL_DESCRIPTION, e.EL_ORIGINE, e.EL_DESTINATAIRE, TIMESTAMPDIFF(MINUTE,e.EL_DEBUT,e.EL_DATE_ADD) TIMEDIFF ,
        date_format(e.EL_DATE_ADD,'le %d-%m-%Y à %H:%i') DATE_ADD,
        date_format(e.EL_SLL,'%H:%i') HEURE_SLL,
        TIMESTAMPDIFF(MINUTE,e.EL_DATE_ADD,NOW()) NEW, date_format(e.EL_DEBUT,'%Y-%m-%d') EL_DEBUT,
        date_format(e.EL_DEBUT, '%Y-%m-%d') as M_DATE_DEBUT, date_format(e.EL_FIN, '%Y-%m-%d') as M_DATE_FIN,
        p2.P_NOM AUTHOR_LASTNAME, p2.P_PRENOM AUTHOR_FIRSTNAME
        from evenement_log e 
            left join pompier p on p.P_ID = e.EL_RESPONSABLE
            left join pompier p2 on p2.P_ID = e.EL_AUTHOR,
        type_evenement_log tel
        where tel.TEL_CODE = e.TEL_CODE
        and e.E_CODE=".$evenement.$searchsql."
        order by EL_DEBUT desc, HEURE_DEBUT desc";
        $result=mysqli_query($dbc,$query);
        $nbinter = @mysqli_num_rows($result);

        $query2="select count(1) from victime where EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement.")";
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        $nb2=intval($row2[0]);

        echo "
        <div class='col-sm-6' >
            <div class='card hide card-default graycard-default'>
                <div class='card-header graycard'>
                    <div class='card-title'>
                        <strong> Messages </strong>
                        <span class='badge alert-red'>".$nbinter."</span>
                        <div align=right style='position:relative;bottom:20px;height:35px;'>
                            <span style='position:relative;bottom:6px'> Intervention </span> <label class='switch'> 
                            <input type='checkbox' id='' name='' 
                                value='1' style='height:22px;margin-left:10px' $checkedi
                                onclick='location.href=\"evenement_display.php?tab=8&intervention=".$truefalsei."&evenement=".$evenement."\"'>
                            <span class='slider round' title='Afficher les interventions'></span>
                            </label>
                            <span style='position:relative;bottom:6px'> Rapport </span><label class='switch'> 
                            <input type='checkbox' id='' name='' 
                                value='1' style='height:22px;margin-left:10px' $checkedr 
                                onclick='location.href=\"evenement_display.php?tab=8&rapport=".$truefalser."&evenement=".$evenement."\"'>
                            <span class='slider round' title='Afficher les messages (rapport)'></span>
                            </label>
                        </div>
                    </div>
                    <div class='card-body graycard' style='position: relative;top: -20px;'>";
        
        if ( $nbinter > 0 ) {
            echo "<table class='newTableAll' style='width:100%;margin-top:-10px'>
            <tr style='background-color:inherit'>
            <td> Date </td>
            <td style='min-width:20%;'> Heure </td>
            <td> Titre </td>
            <td class='hide_mobile'> Responsable </td>
            <td> Victime </td>
            <td class='hide_mobile'> NbTransport </td>
            </tr>";

            $prev_DATE_DEBUT="";
            $prev_key="";
            while (custom_fetch_array($result) ) {
                $P_NOM=strtoupper($P_NOM);
                $P_PRENOM=my_ucfirst($P_PRENOM);
                if ( $HEURE_FIN == '00:00' ) $HEURE_FIN ='';
                if ( $EL_ORIGINE <> '' or $EL_DESTINATAIRE <> '' ) {
                    $fromto = "<font size=1>".$EL_ORIGINE;
                    if ( $EL_DESTINATAIRE <> '' ) $fromto .=" => ".$EL_DESTINATAIRE;
                    $fromto .=" : </font>";
                }
                else $fromto='';

                $query2="select d.D_CODE, t.T_CODE, t.T_NAME, d.D_NAME
                    from victime v, destination d, transporteur t
                    where d.D_CODE=v.D_CODE
                    and v.VI_TRANSPORT=1
                    and t.T_CODE=v.T_CODE 
                    and v.EL_ID=".$EL_ID;

                $result2=mysqli_query($dbc,$query2);
                $nbt=@mysqli_num_rows($result2);
                $cmt="";
                while ($row2=@mysqli_fetch_array($result2)) {
                    $trans=$row2["T_NAME"];
                    $dest=$row2["D_NAME"];
                    if ( $cmt == "" ) $v="";
                    else $v=", ";
                    $cmt .= $v."Un transport par ".$trans." vers ".$dest;
                }
                if ($nbt > 0 ) {
                    if ( $granted_event or $is_operateur_pc )
                        $transports="<a href=intervention_edit.php?evenement=".$E_CODE."&numinter=".$EL_ID."&action=update&type=".$TEL_CODE." title=\"".$cmt."\">".$nbt."</a>";
                    else
                        $transports="<span title=\"".$cmt."\">".$nbt."</span>";
                }
                else $transports="";

                $TEL_DESCRIPTION = "Enregistré par ".my_ucfirst($AUTHOR_FIRSTNAME)." ".strtoupper($AUTHOR_LASTNAME)." - ".$DATE_ADD." - ".$TEL_DESCRIPTION;

                if ( $TEL_CODE == 'I' ) {
                    if ( $EL_IMPORTANT == 1 ) {
                        $img="class='fa fa-medkit' style='color:$widget_fgred'";
                        $TEL_DESCRIPTION .= " important, sera imprimé dans le bulletin de renseignements quotidiens";
                    }
                    else $img="class='fa fa-medkit' ";
                    $query2="select VI_ID, VI_NUMEROTATION, VI_SEXE, VI_AGE
                        from victime where EL_ID=".$EL_ID." order by VI_NUMEROTATION" ;
                    $result2=mysqli_query($dbc,$query2);
                    $nbv="";
                    while ($row2=@mysqli_fetch_array($result2) ) {
                        $VI_ID=$row2["VI_ID"];
                        $VI_NUMEROTATION=$row2["VI_NUMEROTATION"];
                        if (intval($VI_NUMEROTATION) == 0 ) $VI_NUMEROTATION='?';
                        $VI_SEXE=$row2["VI_SEXE"];
                        $age=$row2["VI_AGE"];
                        if ( $age <> '' ) $age .=" ans";
                        if ( $granted_event or $is_operateur_pc )
                            $nbv .= "<a href='victimes.php?victime=".$VI_ID."&from=evenement' title='".$VI_SEXE." ".$age." : voir la fiche de la victime ".$VI_NUMEROTATION."'>V".$VI_NUMEROTATION."</a> ";
                        else
                            $nbv .= "<a href='pdf_document.php?numinter=".$EL_ID."&evenement=".$evenement."&section=".$S_ID."&mode=17&victime=".$VI_ID."' target=_blank title='".$VI_SEXE." ".$age.", voir la fiche victime'>V".$VI_NUMEROTATION."</a> ";
                    }
                }

                else if ( $TEL_CODE == 'M' ) {
                    if ( $EL_IMPORTANT == 1 ) {
                        $img="class='far fa-file-text' style='color:$widget_fgred'";
                        $TEL_DESCRIPTION .= " important, sera imprimé dans le bulletin de renseignements";
                    }
                    else $img="class='far fa-file-text'";
                    $nbv="";
                }
                if ( $E_PARTIES > 1 ) {
                    $key = array_search($M_DATE_DEBUT, $EH_DATE_DEBUT);
                    if ( $key == "" ) $key=array_search(end($EH_DATE_DEBUT), $EH_DATE_DEBUT);
                    if ( $key <> $prev_key ) {
                        $tmp=explode ( "-",$EH_DATE_DEBUT[$key]); $month1=$tmp[1]; $day1=$tmp[2]; $year1=$tmp[0];
                        $tmp=explode ( "-",$EH_DATE_FIN[$key]); $month2=$tmp[1]; $day2=$tmp[2]; $year2=$tmp[0];
                        $date1=date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1." ".$EH_DEBUT[$key];
                        $date2=date_fran($month2, $day2 ,$year2)." ".moislettres($month2)." ".$year2." ".$EH_FIN[$key];
                        $prev_key=$key;
                    }
                }
                else if ( $DATE_DEBUT <> $prev_DATE_DEBUT ) {
                    $tmp=explode ( "-",$DATE_DEBUT); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
                    $date1=date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1;
                    $prev_DATE_DEBUT=$DATE_DEBUT;
                }

                $td=abs($TIMEDIFF);
                if ( ($td > 10 and $TEL_CODE == 'M') or ($td > 120 and $TEL_CODE == 'I'))
                    $warn=" <i class='fa fa-exclamation' style='color:$widget_fgorange' title=\"Attention cette ligne n'a pas été enregistrée en direct, mais ".$DATE_ADD."\" ></i>";
                else $warn='';

                if ( abs($NEW) < 10 ) $new="<i class='fa fa-star' style='color:$widget_fgorange' title=\"Cette ligne a été ajoutée il y a moins de 10 minutes\" ></i>";
                else $new='';
                if($TEL_CODE=="I"){
                    $styletitle2="style='background-color:#ffe2e5;color:#f64e60;padding:4px;border-radius:5px;'";
                    //$styletitle1="style='background-color:#ffe2e5'";
                }
                else {
                    //$styletitle1="style='background-color:#e1f0ff'";
                    $styletitle2="style='background-color:#e1f0ff;color:#3699ff;padding:4px;border-radius:5px;'";
                }
                if ($nbt==0)$nbt="-";
                $dateaffiche = $tmp[0]."-".$tmp[1]."-".$tmp[2];
                if (!isset($olddate))$olddate="";
                if ($dateaffiche==$olddate){
                    $olddate=$dateaffiche;
                    $datedisplay ="";
                }
                else $datedisplay=$dateaffiche;

                if ( $HEURE_SLL <> "" ) $HEURE_SLL = "<br><span title='heure sur les lieux'>".$HEURE_SLL."</span>";
                if ( $HEURE_FIN <> "" ) $HEURE_FIN = "<br><span title='heure de fin'>".$HEURE_FIN."</span>";
                echo "<tr style='background-color:#f5f4f6'>
                <td style='border-bottom:0.5px solid white'>$datedisplay</td>
                <td style='border-bottom:0.5px solid white'>".$HEURE_DEBUT.$HEURE_SLL.$HEURE_FIN."</td>
                <td style='border-bottom:0.5px solid white'><a $styletitle2 href=intervention_edit.php?evenement=".$E_CODE."&numinter=".$EL_ID."&action=update&type=".$TEL_CODE." title='Cliquer pour éditer' >".$EL_TITLE." ".$warn."</a></td>
                <td style='border-bottom:0.5px solid white' class='hide_mobile'><a href=upd_personnel.php?pompier=".$EL_RESPONSABLE." title='Voir la fiche'>".$P_PRENOM." ".$P_NOM."</a></td>
                <td style='border-bottom:0.5px solid white'> $nbv </td>
                <td style='border-bottom:0.5px solid white' class='hide_mobile'> $nbt </td>
                </tr>";
                $olddate=$dateaffiche;
            }
            echo "</table><p>";
        }
        echo "</div></div></div></div>";
        
        //tableau victime
        if ($TE_VICTIMES == 1 ){
            $query ="SELECT VI_NOM, VI_PRENOM , VI_ID, CAV_ENTREE, CAV_SORTIE, VI_SEXE, VI_AGE, cav.CAV_NAME,VI_NUMEROTATION, VI_DETRESSE_VITALE, VI_DECEDE,VI_MALAISE,VI_MEDICALISE,
            VI_IMPLIQUE,VI_TRAUMATISME,VI_SOINS,VI_TRANSPORT,VI_REPOS,CAV_REGULATED  from victime left join evenement_log el on el.EL_ID = victime.EL_ID 
            left join centre_accueil_victime cav on cav.CAV_ID = victime.CAV_ID left join pays p on p.ID = victime.VI_PAYS, destination , transporteur where victime.D_CODE = destination.D_CODE 
            and victime.T_CODE = transporteur.T_CODE 
            and ( victime.EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement.") or victime.CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement."))
            order by VI_NUMEROTATION asc";
            $result=mysqli_query($dbc,$query);
            $nbrow=mysqli_num_rows($result);
            if ( $nbrow > 0 ){
                echo "<div class='col-sm-6'>
                <div class='card hide card-default graycarddefault'>
                <div class='card-header graycard'>
                    <div class='card-title'>
                        <strong> Victime </strong>
                        <span class='badge alert-orange'>".$nbrow."</span>
                        <span style='float:right'>".@$victimBut."</span> 
                    </div>
                </div>
                <div class='card-body graycard'>
                <table class='newTableAll' style='width:100%'>
                <tr >
                    <td> Num. </td>
                    <td> Id </td>
                    <td> Localisation </td>
                    <td> Date </td>
                    <td> Entrée </td>
                    <td> Sortie </td>
                    <td> Identité </td>
                    <td> Age </td>
                    <td> Sexe </td>
                    <td></td>
                    <td></td>
                </tr>";
                while (custom_fetch_array($result)) {
                    if ( $CAV_NAME == '' ) $CAV_NAME='Sur intervention';
                    $VI_IDENTITE=$VI_NOM." ".$VI_PRENOM;
                    if ( $CAV_ENTREE <> '' ) {
                        $CAV_DATE=explode(" ", $CAV_ENTREE);
                        $CAV_ENTREE=explode(":",$CAV_DATE[1]);
                        if ($CAV_ENTREE[1]<>NULL )$CAV_ENTREE=$CAV_ENTREE[0].":".$CAV_ENTREE[1];
                        else $CAV_ENTREE="";
                        $CAV_DATE[0]=date_create($CAV_DATE[0]);
                        $CAV_DATE=date_format($CAV_DATE[0],"d-m-Y");
                    }
                    else
                        $CAV_DATE = "";
                    if ( $CAV_SORTIE <> '' ) {
                        $CAV_SORTIE=explode(":",explode(" ", $CAV_SORTIE)[1]);
                        if ($CAV_SORTIE[1]<>NULL )$CAV_SORTIE=$CAV_SORTIE[0].":".$CAV_SORTIE[1];
                        else $CAV_SORTIE="";
                    }
                    if ($CAV_REGULATED==1) $CAV_REGULATED = "<i style='color:#dc3545' class='far fa-registered fa-lg' title='A été régulé par le médecin'></i>";
                    else $CAV_REGULATED ="";

                    if($VI_DETRESSE_VITALE==1 or $VI_DECEDE==1 or $VI_MALAISE==1 or $VI_MEDICALISE==1 or $VI_IMPLIQUE==1 or $VI_TRAUMATISME==1 or $VI_SOINS==1 or $VI_TRANSPORT==1 or $VI_REPOS==1 ){
                        $displayinfo=1;
                        $infotitle="";
                        if ($VI_DETRESSE_VITALE==1) $infotitle.="Détresse vitale <br>";
                        if ($VI_DECEDE==1) $infotitle.="Décédé.e <br>";
                        if ($VI_MALAISE==1) $infotitle.="Malaise <br>";
                        if ($VI_MEDICALISE==1) $infotitle .="Médicalisé.e <br>";
                        if ($VI_IMPLIQUE==1) $infotitle.="Impliqué.e <br>";
                        if ($VI_TRAUMATISME==1) $infotitle.="Traumatisme <br>";
                        if ($VI_SOINS==1) $infotitle.="Soins <br>";
                        if ($VI_TRANSPORT==1) $infotitle.="Transporté.e <br>";
                        if ($VI_REPOS==1) $infotitle.="Repos <br>";
                    }
                    else $displayinfo=0;
                    echo"<tr>
                        <td><a href='victimes.php?victime=".$VI_ID."&from=evenement' title='Voir la fiche victime'> V$VI_NUMEROTATION </a></td>
                        <td>$VI_ID </td>
                        <td>$CAV_NAME </td>
                        <td>$CAV_DATE </td>
                        <td>$CAV_ENTREE </td>
                        <td>$CAV_SORTIE </td>
                        <td>$VI_IDENTITE </td>
                        <td>$VI_AGE </td>
                        <td>$VI_SEXE </td>
                        <td align=center>$CAV_REGULATED</td>
                        <td align=center>";
                    if ($displayinfo==1) echo "<a href=# data-html='true' title data-original-title='$infotitle'> <i class='fa fa-info-circle fa-lg'></i></a>";
                    echo"</td></tr>";
                }
                echo"</table>";
            }
        }
    }

    echo "</div></div></div>";
}
if ($tab == 21) {
    include_once('intervention_edit.php');
}
if ($tab == 22) {
    include_once('cav_edit.php');
}
if ($tab == 23) {
    include_once('liste_victimes.php');
}
if ($tab== 50) {
    include_once('evenement_detail.php');
}
if ($tab== 51) {
    include_once('evenement_detail.php');
}
if ($tab == 52) {
    include_once('evenement_options.php');
}
if ($tab == 53) {
    include_once('evenement_competences.php');
}
if ($tab == 54) {
    include_once('demande_renfort.php');
}
if ($tab == 55) {
    include_once('evenement_equipes.php');
}
if ($tab == 56) {
    include_once('remplacement_edit.php');
}
if ($tab == 57) {
    include_once('evenement_facturation_detail.php');
}
if ($tab == 58) {
    include_once('evenement_competences.php');
}
if($tab == 59){
    include_once('evenement_multi_renforts.php');
}
if($tab == 60){
    include_once('evenement_edit.php');
}
if($tab == 61){
    include_once('remplacement_edit.php');
}
if($tab == 62){
    include_once('evenement_rapport.php');
}
if($tab == 63){
    include_once('note_frais_edit.php');
}
//=====================================================================
// historique de l'activité
//=====================================================================
if ( $tab == 9  and check_rights($id,49)) {
    $_GET['evenement'] = $evenement;
    include_once('history.php');
}

//=====================================================================
// carte
//=====================================================================
if ( $tab == 15 ) {
    include_once('sitac.php');
}

//=====================================================================
// DPS
//=====================================================================
if ( $tab == 16 ) {
    include_once('dps.php');
}

//=====================================================================
// DPS
//=====================================================================
if ( $tab == 17 ) {
    include_once('evenement_facturation.php');
}
if ( $print ) {
    echo "<p><div><span class=small >Imprimé par: ".my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id))."</span></div>";
    echo "<p><div class='noprint' ><input type='button' value='fermer cette page' onclick='fermerfenetre();' ></div> ";
}
echo "<p>";
if((check_rights($id,6,$organisateur))&&(!is_iphone())) {//il faut avoir le droit et en version PC
    echo "<script type='text/javascript' src='js/dragdropPiquet.js'></script>";//le fichier js containing all dragdrop functions
}
echo @$laterOut;
writefoot();

?>



