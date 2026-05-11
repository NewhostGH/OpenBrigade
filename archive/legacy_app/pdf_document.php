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
check_all(0);
$id=$_SESSION['id'];

$printed_by="imprimé par ".my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id)). " le ".date("d-m-Y à H:i");
$mode=intval($_GET["mode"]);

if ( isset($_GET["P_ID"])) $pid=intval($_GET["P_ID"]);
else $pid=0;

if ( isset($_GET["note"])) $note=intval($_GET["note"]);
else $note=0;

if ( isset($_GET["victime"])) $victime=intval($_GET["victime"]);
else $victime=0;

if ( isset($_GET["numinter"])) $numinter=intval($_GET["numinter"]);
else $numinter=0;

if ( isset($_GET["signed"])) $signed=intval($_GET["signed"]);
else $signed=1;

if ( isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;

if ( $evenement < 0 ) $evenement=0;

if ( isset($_GET["tofile"])) $tofile=intval($_GET["tofile"]);
else $tofile=0;

// specifique rapport
if ( isset($_GET["show_responsable"])) $show_responsable=intval($_GET["show_responsable"]);
else $show_responsable=0;
if ( isset($_GET["show_nombres"])) $show_nombres=intval($_GET["show_nombres"]);
else $show_nombres=0;
if ( isset($_GET["show_statistiques"])) $show_statistiques=intval($_GET["show_statistiques"]);
else $show_statistiques=0;
if ( isset($_GET["show_vehicules"])) $show_vehicules=intval($_GET["show_vehicules"]);
else $show_vehicules=0;
if ( isset($_GET["show_materiel"])) $show_materiel=intval($_GET["show_materiel"]);
else $show_materiel=0;
if ( isset($_GET["show_cav"])) $show_cav=intval($_GET["show_cav"]);
else $show_cav=0;

require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");


function write_victime_comments($comments) {
    global  $VI_DETRESSE_VITALE, $VI_SOINS, $VI_MEDICALISE, $VI_INFORMATION, $VI_DECEDE, $VI_MALAISE, 
            $VI_TRANSPORT, $VI_VETEMENT, $VI_ALIMENTATION, $VI_TRAUMATISME, $VI_REFUS, $VI_IMPLIQUE, $VI_REPOS, $VI_REPARTI, $T_NAME, $D_NAME, $HEURE_HOPITAL;
    if ( $comments <> "" ) $comments = rtrim($comments,'.').". ";
    if ( $VI_DETRESSE_VITALE == 1 ) $comments .= "La victime est en détresse vitale. ";
    if ( $VI_SOINS == 1 ) $comments .= "Des soins ont été réalisés par l'équipe de secouristes. ";
    if ( $VI_MEDICALISE == 1 ) $comments .= "La victime a été médicalisée. ";
    if ( $VI_INFORMATION == 1 ) $comments .= "La personne a été assistée, ou des renseignements et informations lui ont été donnés. ";
    if ( $VI_DECEDE == 1 ) $comments .= "La victime est décédée. ";
    if ( $VI_MALAISE == 1 ) $comments .= "La victime a eu un malaise. ";
    if ( $VI_TRAUMATISME == 1 ) $comments .= "La victime souffre d'un traumatisme. ";
    if ( $VI_REPOS == 1 ) $comments .= "La victime a été mise au repos sous surveillance. ";
    if ( $VI_TRANSPORT == 1 ) $comments .= "La victime a été transportée par ".$T_NAME.", destination: ".$D_NAME.". ";
    if ( $HEURE_HOPITAL <> '' ) $comments .= "Arrivée à ".$HEURE_HOPITAL.". ";
    if ( $VI_VETEMENT == 1 ) $comments .= "Des vêtements ou une couverture ont été offerts à la victime. ";
    if ( $VI_ALIMENTATION == 1 ) $comments .= "Des aliments ou une boisson ont été offerts à la victime. ";
    if ( $VI_REFUS == 1 ) $comments .= "La victime a refusé d'être prise en charge. ";
    if ( $VI_IMPLIQUE == 1 ) $comments .= "La personne est seulement impliquée, indemne. ";
    if ( $VI_REPARTI == 1 ) $comments .= "La victime est repartie par ses propres moyens. ";
    $comments = str_replace ("  "," ",$comments);
    $comments = str_replace (" .",".",$comments);
    $comments = str_replace ("..",".",$comments);
    return $comments;
}

// dates et infos événement
$query = "SELECT distinct e.PS_ID, eh.EH_ID, DATE_FORMAT(eh.EH_DATE_DEBUT, '%d-%m-%Y') as EH_DATE_DEBUT,
          DATE_FORMAT(eh.EH_DATE_FIN, '%d-%m-%Y') as EH_DATE_FIN, e.E_LIEU, sf.NIV, s.S_PARENT,
          s.S_DESCRIPTION, s.S_ID, s.S_CODE, s.S_CITY, s.S_PDF_PAGE, e.E_LIBELLE,
          s.S_URL, s.S_PHONE, s.S_EMAIL2, s.S_FAX, s.S_EMAIL, s.S_ADDRESS, s.S_ADDRESS_COMPLEMENT, s.S_CITY, s.S_ZIP_CODE, 
          e.E_NB_VPSP, e.E_NB_AUTRES_VEHICULES, e.E_CONSIGNES, e.E_CUSTOM_HORAIRE, e.E_REPRESENTANT_LEGAL, e.E_REPAS, e.E_TRANSPORT, e.E_MOYENS_INSTALLATION, e.E_CLAUSES_PARTICULIERES, e.E_CLAUSES_PARTICULIERES2, 
          e.E_NB, ef.devis_montant, ef.E_ID, e.E_ADDRESS, e.E_CONVENTION, 
          TIME_FORMAT(eh.EH_DEBUT, '%k:%i') as HEURE_DEB, eh.EH_DUREE, eh.EH_DESCRIPTION,
          TIME_FORMAT(eh.EH_FIN, '%k:%i') as HEURE_FIN,
          te.TE_LIBELLE, e.TE_CODE, e.E_FLAG1, e.E_COMMENT, te.CEV_CODE,
          e.C_ID, e.E_CONTACT_LOCAL, e.E_CONTACT_TEL, c.C_NAME, c.C_ADDRESS, c.C_ZIP_CODE, c.C_CITY, c.C_EMAIL, c.C_PHONE, c.C_CONTACT_NAME, 
          ef.dimNbISActeurs, e.TAV_ID, e.E_TARIF,
          te.ACCES_RESTREINT, e.E_CREATED_BY,
          e.E_LIEU_RDV, e.E_HEURE_RDV, e.E_ALLOW_REINFORCEMENT, e.E_PARENT, te.TE_VICTIMES
          FROM section s, section_flat sf, type_evenement te, evenement_horaire eh, evenement e
          left join evenement_facturation ef on ef.E_ID=e.E_CODE
          left join company c on e.C_ID = c.C_ID
          WHERE e.S_ID = s.S_ID
          and e.E_CODE = eh.E_CODE
          and te.TE_CODE = e.TE_CODE
          and sf.S_ID = s.S_ID
          and e.E_CODE=".$evenement."
          order by eh.EH_DATE_DEBUT, eh.EH_DEBUT, eh.EH_ID" ;

$result=mysqli_query($dbc,$query);

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
$dates_heures="";
while ($row = mysqli_fetch_array($result)) {
    $te_code=$row["TE_CODE"];
    $CEV_CODE=$row["CEV_CODE"];
    $lieu=stripslashes($row["E_LIEU"]);
    $E_COMMENT=stripslashes($row["E_COMMENT"]);
    $type_evenement=$row["TE_LIBELLE"];
    $ACCES_RESTREINT=$row["ACCES_RESTREINT"];
    $E_ALLOW_REINFORCEMENT=$row["E_ALLOW_REINFORCEMENT"];
    $E_CREATED_BY=$row["E_CREATED_BY"];
    $E_LIEU_RDV=$row["E_LIEU_RDV"];
    $E_HEURE_RDV=substr($row["E_HEURE_RDV"],0,5);
    $organisateur=$row["S_DESCRIPTION"];
    $organisateur_city=$row["S_CITY"];
    $cid=$row["C_ID"];
    if ( intval($cid) > 0 )  $company=$row["C_NAME"];
    else $company="l'organisateur";
    $company_address=stripslashes($row["C_ADDRESS"]);
    $company_cp=$row["C_ZIP_CODE"];
    $company_city=stripslashes($row["C_CITY"]);
    $company_phone=$row["C_PHONE"];
    $company_email=$row["C_EMAIL"];
    $company_representant=$row["C_CONTACT_NAME"];
    $contact=$row["E_CONTACT_LOCAL"];
    $num_convention=$row["E_CONVENTION"];
    $contact_tel=$row["E_CONTACT_TEL"];
    if ($contact_tel <> "" ) $contact = "".$row["E_CONTACT_LOCAL"]." (tél. ".$contact_tel.")";
    $section=$row["S_ID"];
    $description=stripslashes($row["E_LIBELLE"]);
    $S_URL=$row["S_URL"];
    if ( $row["S_EMAIL2"] <> "" ) $S_EMAIL=$row["S_EMAIL2"];
    else $S_EMAIL=$row["S_EMAIL"];
    $S_PHONE=$row["S_PHONE"];
    $S_FAX=$row["S_FAX"];
    $S_CODE=$row["S_CODE"];
    $S_ADDRESS=stripslashes($row["S_ADDRESS"]);
    $S_ADDRESS_COMPLEMENT=stripslashes($row["S_ADDRESS_COMPLEMENT"]);
    $S_CITY=stripslashes($row["S_CITY"]);
    $S_PARENT=$row["S_PARENT"];
    $E_ADDRESS=$row["E_ADDRESS"];
    $E_CONSIGNES=$row["E_CONSIGNES"];
    $S_ZIP_CODE=$row["S_ZIP_CODE"];
    $niv=$row['NIV'];
    $evt_principal=intval($row['E_PARENT']);
    $s_description=$row['S_DESCRIPTION'];
    $psid=$row["PS_ID"];
    $nb_vpsp=$row["E_NB_VPSP"];
    $TE_VICTIMES=$row['TE_VICTIMES'];
    $E_TARIF=$row["E_TARIF"];
    if ( $nb_vpsp == 0 ) {
        $q="select count(distinct ev.V_ID) as NB from evenement_vehicule ev, vehicule v, type_vehicule tv
             where ev.E_CODE='$evenement'
             and ev.V_ID = v.V_ID
             and v.TV_CODE=tv.TV_CODE
             and tv.TV_USAGE='SECOURS'";
        $r = mysqli_query($dbc,$q);
        $ro = mysqli_fetch_array($r);
        $nb_vpsp = $ro['NB'];
    }
    $nb_autres_vehicules=$row["E_NB_AUTRES_VEHICULES"];
    if ( $nb_autres_vehicules == 0 ) {
        $q="select count(distinct ev.V_ID) as NB from evenement_vehicule ev, vehicule v, type_vehicule tv
             where ev.E_CODE='$evenement'
             and ev.V_ID = v.V_ID
             and v.TV_CODE=tv.TV_CODE
             and tv.TV_USAGE <> 'SECOURS'";
        $r = mysqli_query($dbc,$q);
        $ro = mysqli_fetch_array($r);
        $nb_autres_vehicules = $ro['NB'];
    }
    $moyen_installation_1=stripslashes($row["E_MOYENS_INSTALLATION"]);
    $repas=$row["E_REPAS"];
    $transport=$row["E_TRANSPORT"];
    $clause_particuliere_1=stripslashes($row["E_CLAUSES_PARTICULIERES"]);
    $clause_particuliere_2=stripslashes($row["E_CLAUSES_PARTICULIERES2"]);
    $consignes=stripslashes($row["E_CONSIGNES"]);
    $custom_horaire=stripslashes($row["E_CUSTOM_HORAIRE"]);
    $representant_legal=stripslashes($row["E_REPRESENTANT_LEGAL"]);
    $nb_is=$row["E_NB"];
    $montant_devis=$row["devis_montant"];
    $is_acteurs=$row["dimNbISActeurs"];
    $TAV_ID=$row["TAV_ID"];
    if ( $row["E_FLAG1"] == 1 ) $interassociatif=true;
    else $interassociatif=false;

    // tableau des sessions
    $EH_ID[$i]=$row["EH_ID"];
    $description_partie[$i]=$row["EH_DESCRIPTION"];
    $EH_DEBUT[$i]=$row["HEURE_DEB"];
    $EH_DATE_DEBUT[$i]=$row["EH_DATE_DEBUT"];
    if ( $row["EH_DATE_FIN"] == '' ) 
        $EH_DATE_FIN[$i]=$row["EH_DATE_DEBUT"];
    else 
        $EH_DATE_FIN[$i]=$row["EH_DATE_FIN"];
    $EH_FIN[$i]=$row["HEURE_FIN"];
    $EH_DUREE[$i]=$row["EH_DUREE"];
    if ( $EH_DUREE[$i] == "") $EH_DUREE[$i]=0;
    $E_DUREE_TOTALE = $E_DUREE_TOTALE + $EH_DUREE[$i];
    $tmp=explode ( "-",$EH_DATE_DEBUT[$i]); $year1[$i]=$tmp[2]; $month1[$i]=$tmp[1]; $day1[$i]=$tmp[0];
    $date1[$i]=mktime(0,0,0,$month1[$i],$day1[$i],$year1[$i]);
    $tmp=explode ( "-",$EH_DATE_FIN[$i]); $year2[$i]=$tmp[2]; $month2[$i]=$tmp[1]; $day2[$i]=$tmp[0];
    $date2[$i]=mktime(0,0,0,$month2[$i],$day2[$i],$year2[$i]);

    if ( $EH_DATE_DEBUT[$i] == $EH_DATE_FIN[$i])
        $horaire_evt[$i]=date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$year1[$i]." de ".$EH_DEBUT[$i]." à ".$EH_FIN[$i];
    else
        $horaire_evt[$i]="du ".date_fran($month1[$i], $day1[$i] ,$year1[$i])." ".moislettres($month1[$i])." ".$EH_DEBUT[$i]." au "
                         .date_fran($month2[$i], $day2[$i] ,$year2[$i])." ".moislettres($month2[$i])." ".$year2[$i]." ".$EH_FIN[$i];
    $dates_heures .= " ".$horaire_evt[$i].",";
    $i++;
}
$dates_heures = rtrim($dates_heures,',');
$chefs=get_chefs_evenement($evenement);
if (count($chefs) > 0 ) $id_responsable_evt=intval($chefs[0]);
else $id_responsable_evt= 0;

if ( $id_responsable_evt > 0 ) $responsable_evt_phone = get_phone($id_responsable_evt);
else $responsable_evt_phone = "";

$responsable_evt="";
if ( $id_responsable_evt > 0 ) {
    $responsable_evt = my_ucfirst(get_prenom($id_responsable_evt))." ".strtoupper(get_nom($id_responsable_evt));
}

// avoid errors when loading config_doc.php
$date_pv="";
$next_year=date("Y")+1;

if ( $evenement == 0 ) {
    $query2="select p.P_SECTION, s2.S_PARENT, s.NIV, s2.S_DESCRIPTION, s2.S_PHONE, s2.S_FAX, s2.S_CITY, s2.S_ADDRESS, s2.S_ADDRESS_COMPLEMENT, s2.S_ZIP_CODE, s2.S_URL, s2.S_EMAIL
            from pompier p, section_flat s, section s2
            where s.S_ID = p.P_SECTION
            and s.S_ID = s2.S_ID
            and p.P_ID=".$pid;
     $res2 = mysqli_query($dbc,$query2);
    $row2 = mysqli_fetch_array($res2);
    $S_PARENT=$row2["S_PARENT"];
    $section=$row2["P_SECTION"];
    $S_URL=$row2["S_URL"];
    if ( $row["S_EMAIL2"] <> "" ) $S_EMAIL=$row2["S_EMAIL2"];
    else $S_EMAIL=$row2["S_EMAIL"];
    $S_PHONE=$row2["S_PHONE"];
    $S_FAX=$row2["S_FAX"];
    $S_ADDRESS=stripslashes($row2["S_ADDRESS"]);
    $S_ADDRESS_COMPLEMENT=stripslashes($row2["S_ADDRESS_COMPLEMENT"]);
    $S_CITY=stripslashes($row2["S_CITY"]);
    $niv = $row2["NIV"];
    $s_description=$row2['S_DESCRIPTION'];
    $S_ZIP_CODE=$row2["S_ZIP_CODE"];
}

// responsables

if ( $niv == $nbmaxlevels -1 ) {
        // cas antenne locale, on donne les infos du département
        $query2="select S_ID, S_CODE, S_DESCRIPTION from section where S_ID=".$S_PARENT;
        $res2 = mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($res2);
        $section_affiche = $row2['S_DESCRIPTION'];
        $antenne_affiche = ", ".$s_description;
        if(stristr($antenne_affiche, 'canotier') <> '' ) $antenne_affiche='';
        else if(stristr($antenne_affiche, 'non embarqué') <> '' ) $antenne_affiche='';
        $tmpS=$row2["S_ID"];
        // mais on récupère le responsable de l'antenne
        $queryy="select p.P_ID, p.P_PRENOM, p.P_NOM, g.GP_DESCRIPTION, p.P_SEXE
        from pompier p, groupe g, section_role sr
        where sr.GP_ID = g.GP_ID
        and sr.P_ID = p.P_ID
        and sr.S_ID = ".$section."
        and sr.GP_ID = 102
        order by sr.GP_ID asc";
        
        $resulty = mysqli_query($dbc,$queryy);
        $num_resulty = mysqli_num_rows($resulty);
        $data2 = mysqli_fetch_array($resulty);
        $responsable_antenne = my_ucfirst($data2["P_PRENOM"])." ".strtoupper($data2["P_NOM"]);
}
else {
        // cas département ou plus haut dans l'organigramme
        $section_affiche = $s_description;
        $antenne_affiche = "";
        $responsable_antenne = "";
        $tmpS=$section;
}
$section_prefix=get_prefix_section($section_affiche);

$section_president= $section_affiche;

// chercher le chef ou président départemental
$queryy="select p.P_ID, p.P_PRENOM, p.P_NOM, g.GP_DESCRIPTION, p.P_SEXE, 
        sr.S_ID, s.S_DESCRIPTION, s.S_IMAGE_SIGNATURE
        from pompier p, groupe g, section_role sr, section s
        where sr.GP_ID = g.GP_ID
        and sr.S_ID = s.S_ID
        and sr.P_ID = p.P_ID
        and sr.S_ID in ('".$tmpS."','0')
        and sr.GP_ID = 102
        order by sr.S_ID desc";

$resulty = mysqli_query($dbc,$queryy);

$num_resulty = mysqli_num_rows($resulty);
if ( $num_resulty == 0) {
        $chef = my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id));
        $chef_long = $chef.", de ";
        $titre_prefix = "";
        $titre = "";
        $soussigne="soussigné(e)";
        $S_IMAGE_SIGNATURE="";
}
else {
        $data2 = mysqli_fetch_array($resulty);
        $section_president = $data2["S_DESCRIPTION"];
        if ( $data2["P_SEXE"] == 'F' ) {
            $titre = rtrim(str_replace(" (e)","e", $data2["GP_DESCRIPTION"]));
            $titre = rtrim(str_replace("(e)","e", $titre));
            $titre_prefix = "La ";
            $soussigne="soussignée";
        }
        else {
            $titre = rtrim(str_replace(" (e)", "", $data2["GP_DESCRIPTION"]));
            $titre = rtrim(str_replace("(e)", "", $titre));
            $titre_prefix = "Le ";
            $soussigne="soussigné";
        }
        $S_IMAGE_SIGNATURE=$data2["S_IMAGE_SIGNATURE"];
        $chef = my_ucfirst($data2["P_PRENOM"])." ".strtoupper($data2["P_NOM"]);
        $chef_long = $chef.", ".$titre." de ";
        if ( $responsable_antenne == "" ) $responsable_antenne = $chef;
}

$section_president_prefix = get_prefix_section($section_president);
$chef_long .=$section_president_prefix." ".$section_president;

$customlocal=$basedir."/images/user-specific/".@$row['S_PDF_PAGE'];
$customdefault=$basedir."/images/user-specific/pdf_page.pdf";
$generic=$basedir."/lib/fpdf/pdf_page.pdf";
$fondpdf=((file_exists($customlocal) && @$row['S_PDF_PAGE']!="")?$customlocal:(file_exists($customdefault)?$customdefault:$generic));

$mailinfos="";
if ( $S_URL <> "" or $S_EMAIL <> "" ) {
        if ( $S_EMAIL <> "" ) $mailinfos .= "Email : ".$S_EMAIL;
        if ( $S_URL <> "" and $S_EMAIL <> "" )  $mailinfos .= " - ";
        if ( $S_URL <> "" ) $mailinfos .= "Site : ".$S_URL;
}

if ( $S_PHONE <> "" or $S_FAX <> "" ) {
        if ( $S_PHONE <> "" ) $phoneinfos = "Téléphone : ".$S_PHONE;
        if ( $S_FAX <> "" and $S_PHONE <> "" )  $phoneinfos .= " - ";
        if ( $S_FAX <> "" ) $phoneinfos .= "Télécopie : ".$S_FAX;
}
else $phoneinfos="";

if ( $S_ADDRESS_COMPLEMENT <> '' ) $C = $S_ADDRESS_COMPLEMENT."\n";
else $C='';
$adr = $S_ADDRESS."\n".$C.$S_ZIP_CODE." ".$S_CITY;


// cas note de frais, pas forcément liée à un événement
if ( ($mode == 13 or $mode == 19 or $mode == 20) and $evenement == 0 ) {
    $section=get_section_of("$pid");
    if ( $pid <> $id ) {
        check_all(59);
        if (! check_rights($id,59,$section)) check_all(24);
    }
}
else {
    // cas général, toujours un événement
    $nbsessions=sizeof($EH_ID);
    $last=$i-1;

    $periode='';
    for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
       if (isset($horaire_evt[$i]))
           $periode .=$horaire_evt[$i].", ";
    }
    $periode = substr($periode,0,strlen($periode) -2);
    $periode_long=$periode;
    $level=get_level("$section");

    if ( $last > 1 ) 
    $periode="Du ".$EH_DATE_DEBUT[1]." au ".$EH_DATE_FIN[$last];

    $query2="select tf.TF_CODE, tf.TF_LIBELLE from type_formation tf, evenement e
    where e.TF_CODE= tf.TF_CODE
    and e.E_CODE=".$evenement;
    $result2 = mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    $TF_LIBELLE=@$row2['TF_LIBELLE'];
    $TF_CODE=@$row2['TF_CODE'];

    if ( $psid <> "" ){
        $query2="select TYPE, DESCRIPTION, PS_NATIONAL,PS_SECOURISME , PS_RECYCLE
            from poste where PS_ID=".$psid;
        $result2=mysqli_query($dbc,$query2); 
        $row2 = mysqli_fetch_array($result2);
        $national=$row2["PS_NATIONAL"];
        $secourisme=$row2["PS_SECOURISME"];
        $recyclage=$row2["PS_RECYCLE"];
        $description=$row2["DESCRIPTION"];
        $type=str_replace(" ", "",$row2["TYPE"]);
    }
    else {
        $national=0;
        $secourisme=0;
        $recyclage=0;
        $type="";
    }

    // mode imprimer seulement 1 attestation pour une personne
    if ( $note > 0) {
        if ( $pid <> $id ) {
            check_all(59);
            if (! check_rights($id,59,get_section_of($pid))) check_all(24);
        }
    }
    else if ( $mode == 4 ) {
        if ( is_chef_evenement($id, $evenement)  ) $allowed = true;
        else if (check_rights($id,15,$section)) $allowed = true;
        else if (is_present($id,$evenement)) $allowed = true;
        else {
            check_all(15);
            if (! check_rights($id,15,$section))
                check_all(24);
        }
    }
    else if ( $pid > 0 and $mode==2 ) {
        if (! check_rights($id,4,get_section_of($pid)) and ! check_rights($id,48,get_section_of($pid)) and $pid <> $id) {
            check_all(24);
        }
    }
    // mode général imprimer tous les documents
    else if (! is_chef_evenement($id, $evenement) ) {
        if ( $mode == 11  and (is_operateur_pc($id,$evenement))) {
            check_all(0);
        }
        else if (  $mode == 16 or $mode == 17 ) {  // voir fiche victime tout le monde peut
            check_all(0);
        }
        else {
            check_all(15);
            if ((! check_rights($id, 15, "$section"))) check_all(24);
        }
    }

    // On récupère le type d'évènement

    $querydps="select TAV_ID, TA_VALEUR from type_agrement_valeur
               where TA_CODE = 'D'
               and TAV_ID='".$TAV_ID."'";
    $resultdps=mysqli_query($dbc,$querydps);
    $rowdps=mysqli_fetch_array($resultdps);
    $tdpsid = $rowdps["TAV_ID"];
    $tdps = $rowdps["TA_VALEUR"];
}



//=============================
// convocations
//=============================

if ( $mode == 15 ) {
    $printPageNum=true;
    $pdf=new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator($organisateur);
    $pdf->SetAuthor($organisateur);
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Convocation");
    $pdf->SetAutoPageBreak(0);
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',25);
    $pdf->SetXY(40,48);

    if ( $nbsessions > 1 ) {
        for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
            if ( isset($horaire_evt[$i])) {
                if ( $description_partie[$i] <> '' ) $dp = " - ".$description_partie[$i];
                else $dp="";
                $E_COMMENT .="\nDate Partie ".$EH_ID[$i].": ".$horaire_evt[$i].$dp;    
            }
        }
    }

    // liste des participants
    $query="SELECT distinct ep.E_CODE, s.S_CODE, ep.TP_ID, tp.TP_LIBELLE, p.P_ID, p.P_SEXE, p.P_NOM, p.P_PRENOM, tp.INSTRUCTOR, c.C_NAME, c.C_ID
             from pompier p left join company c on p.C_ID = c.C_ID, 
             evenement e, section s, evenement_participation ep
             left join type_participation tp on ep.TP_ID = tp.TP_ID
             where ep.P_ID = p.P_ID
             and e.E_CODE = ep.E_CODE
             and e.S_ID = s.S_ID
             and ep.EP_ABSENT = 0
             and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")";
    $query .= " order by p.P_STATUT, ep.TP_ID desc, p.P_NOM, p.P_PRENOM";
    $result = mysqli_query($dbc,$query);
    $numrows = mysqli_num_rows($result);
    $t = "Convocation";

    $pdf->MultiCell(120,14,$t,"1","C");
    $pdf->SetFont('Arial','',11);
    $pdf->SetXY(25,67);
    $pdf->MultiCell(160,6,"Je ".$soussigne.", ".$chef_long.", convoque les personnes désignées ci-dessous à participer à :","","J");            
    $pdf->SetFont('Arial','U',11);
    $pdf->SetXY(25,88);

    if ( strlen($E_ADDRESS) > 80 ) {
        $interligne= "\n";
        $y=142;
    }
    else {
        $interligne= "";
        $y=138;
    }
    $pdf->MultiCell(50,6,
    "Type d'activité: ".
    "\nTitre:".
    "\nLieu:".
    "\nAdresse exacte:".$interligne.
    "\nDates:".
    "\nPour le compte de:".
    "\nContact sur place:".
    "\nPersonnes convoquées:","","L");

    if ($cid == 0 ) $company="";
    $pdf->SetXY(75,88);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell(125,6,
    $type_evenement.
    "\n".$description.
    "\n".$lieu.
    "\n".$E_ADDRESS.
    "\n".$periode.
    "\n".$company.
    "\n".$contact,"","L");

    // afficher personnel engagés
    $pdf->SetFont('Arial','',11);
    $pdf->SetTextColor(0,0,200);
    $nom_prenom=""; $i=0; 
    while ($data = mysqli_fetch_array($result)) { 
        $nom_prenom=strtoupper($data['P_NOM'])." ".my_ucfirst($data['P_PRENOM']);
        if ($data["TP_ID"] > 0 ) $fonction=" - ".$data['TP_LIBELLE'];
        else $fonction="";
        if ( $data["E_CODE"] <> $evenement ) $renfort = " (renfort ".$data['S_CODE'].")";
        else $renfort="";
        if ( $data["C_NAME"] <> '' and intval($data["C_ID"]) > 0 ) $c=" (".$data["C_NAME"].")";
        else $c="";
        
        if ( $y > 260 ) {
            $y=50;
            $pdf->SetTextColor(0,0,0);
            $pdf->AddPage();
            $pdf->SetTextColor(0,0,200);
            $pdf->SetXY(30,$y);
        }
        else
            $pdf->SetXY(30,$y);
        $pdf->MultiCell(170,6,$nom_prenom.$fonction.$renfort.$c,"","L");
        $y = $y+6;
    }
    $pdf->SetTextColor(0,0,0);
    if ( $E_COMMENT <> "" ) {
        if ( $y > 200 ) {
            $y=50;
            $pdf->AddPage();
            $pdf->SetXY(30,$y);
        }
        else
            $y = $y +10;
        $pdf->SetXY(25,$y);
        $pdf->SetFont('Arial','iu',9);
        $pdf->MultiCell(120,7,"Détail:","","L");
        $pdf->SetFont('Arial','i',9);
        $pdf->SetXY(30,$y+6);
        $pdf->MultiCell(170,4,$E_COMMENT,"","L");
        $y=$y + 4 * substr_count( $E_COMMENT, "\n" ) + strlen($E_COMMENT) / 20;
    }
    if ( $consignes <> "" ) {
        if ( $y > 200 ) {
            $y=50;
            $pdf->AddPage();
            $pdf->SetXY(30,$y);
        }
        else
            $y = $y +10;
        $pdf->SetXY(25,$y);
        $pdf->SetFont('Arial','iu',9);
        $pdf->MultiCell(120,7,"Consignes pour les intervenants:","","L");
        $pdf->SetFont('Arial','i',9);
        $pdf->SetXY(30,$y+6);
        $pdf->MultiCell(170,4,$consignes,"","L");
        $y=$y+ 4 * substr_count( $consignes, "\n" ) + strlen($consignes) / 20;
    }
    $pdf->SetTextColor(0,0,0);
    
    if ( $y > 240 ) {
        $y=50;
        $pdf->SetTextColor(0,0,0);
        $pdf->AddPage();
        $pdf->SetXY(30,$y);
    }
    else    
    $y = $y +15;
    $pdf->SetXY(25,$y);
    $pdf->SetFont('Arial','',11);
    $pdf->MultiCell(200,8,"Fait le ".date('d-m-Y')." ".$organisateur_city,"","J");
    $y = $y + 8;        
    $pdf->SetXY(100,$y);
    $pdf->MultiCell(100,8,$titre_prefix." ".$titre.", ".$chef,"","L");
    
    if ( $S_IMAGE_SIGNATURE <> "" and $signed == 1 ) {
        $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
        if ( @is_file($signature_file)) $pdf->Image($signature_file, 100, $y+6, 40);
    }
    
    $pdf->SetXY(10,265);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->Output("Convocations_".$evenement.".pdf",'I');
}

//=============================
// demande de moyens ou de renfort
//=============================

if ( $mode == 25 ) {
    if ( $E_ALLOW_REINFORCEMENT == 1 ) $title="Demande de renfort au titre de la Solidarité Nationale";
    else $title="Demande de personnels et de moyens";
    $printPageNum=true;
    $pdf=new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator($organisateur);
    $pdf->SetAuthor($organisateur);
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle($title);
    $pdf->SetAutoPageBreak(0);
    $pdf->AddPage();

    if ( $nbsessions > 1 ) {
        for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
            if ( isset($horaire_evt[$i])) {
                if ( $description_partie[$i] <> '' ) $dp = " - ".$description_partie[$i];
                else $dp="";
                $E_COMMENT .="\nDate Partie ".$EH_ID[$i].": ".$horaire_evt[$i].$dp;    
            }
        }
    }

    if ( $id_responsable_evt > 0 ) $_chef = $responsable_evt;
    else $_chef = $section_president_prefix." ".$section_president;
    
     // personnel requis
    $perso="";
    $queryp="select ec.PS_ID, p.TYPE, p.DESCRIPTION, ec.NB 
                from evenement_competences ec left join poste p on ec.PS_ID = p.PS_ID
                where ec.E_CODE=".$evenement." 
                and ec.EH_ID=1
                order by ec.PS_ID";
    $resultp=mysqli_query($dbc,$queryp);
    $nbp=mysqli_num_rows($resultp);
    while ( $rowp=mysqli_fetch_array($resultp) ) {
        $nb=intval($rowp["NB"]);
        $poste=intval($rowp["PS_ID"]);
        $TYPE=$rowp["TYPE"];
        $DESCRIPTION=$rowp["DESCRIPTION"];
        if ( $poste == 0 ){
            $perso .= $nb ;
            if ( $nbp > 1 ) $perso .= " dont";
        }
        else {
            $query2="select TYPE, DESCRIPTION from poste where PS_ID=".$poste;
            $result2=mysqli_query($dbc,$query2);
            $nb2=mysqli_num_rows($result2);
            $row2=mysqli_fetch_array($result2);
            $type=$row2["TYPE"];
            $perso .= " ".$nb." ".$type.",";
        }
    }
    $perso = rtrim($perso,','); 
    
    // Véhicules requis
    $vehic=""; $point="";$DEMANDE_SPECIFIQUE="";
    if ( $vehicules == 1 ) {
        $vehic="";
        $querym="select NB_VEHICULES, POINT_REGROUPEMENT, DEMANDE_SPECIFIQUE
            from demande_renfort_vehicule
            where TV_CODE = '0'
            and E_CODE=".$evenement;
        $resultm=mysqli_query($dbc,$querym);
        $rowm=mysqli_fetch_array($resultm);
        $point=$rowm["POINT_REGROUPEMENT"];
        $DEMANDE_SPECIFIQUE=$rowm["DEMANDE_SPECIFIQUE"];
        $vehic .= intval($rowm["NB_VEHICULES"]);
        
        $querym="select t.TV_CODE, t.TV_LIBELLE, t.TV_USAGE , d.NB_VEHICULES
                from type_vehicule t, demande_renfort_vehicule d
                where d.TV_CODE = t.TV_CODE
                and E_CODE=".$evenement."
                order by t.TV_LIBELLE";
        $resultm=mysqli_query($dbc,$querym);
        $nbm=mysqli_num_rows($resultm);
        if ( $nbm > 0 ) {
            $vehic .= " dont";
            while ( $rowm=mysqli_fetch_array($resultm) ) {
                $TV_CODE=$rowm["TV_CODE"];
                $TV_LIBELLE=$rowm["TV_LIBELLE"];
                $TV_USAGE=$rowm["TV_USAGE"];
                $demandes=$rowm["NB_VEHICULES"];
                $vehic .= " ".$demandes." ".$TV_CODE.",";
            }
        }
        $vehic = rtrim($vehic,','); 
    }
    // Matériel requis
    $mat="";
    if ( $materiel == 1 ) {
        $querym="select tm.TM_ID, tm.TM_CODE
                from type_materiel tm, demande_renfort_materiel drm 
                where tm.TM_ID = drm.TYPE_MATERIEL
                and tm.TM_USAGE not in ('Habillement','Promo-Com','ALL')
                and drm.E_CODE = ".$evenement."
                order by tm.TM_USAGE, tm.TM_CODE"; 
        $resultm=mysqli_query($dbc,$querym);
        while ( $rowm=mysqli_fetch_array($resultm)) {
            $mat .=  " ".$rowm["TM_CODE"].",";
        }
        $querym="select cm.TM_USAGE, cm.CM_DESCRIPTION
                from categorie_materiel cm, demande_renfort_materiel drm 
                where cm.TM_USAGE = drm.TYPE_MATERIEL
                and cm.TM_USAGE not in ('Habillement','Promo-Com','ALL')
                and drm.E_CODE = ".$evenement."
                order by cm.TM_USAGE"; 
        $resultm=mysqli_query($dbc,$querym);
        while ( $rowm=mysqli_fetch_array($resultm)) {
            $mat .=  " ".$rowm["TM_USAGE"].",";
        }
        $mat = rtrim($mat,',');
        $mat = ltrim($mat,' ');
    }

    // print info on PDF 
    $pdf->SetFont('Arial','U',11); 
    $pdf->SetXY(25,40);
    $pdf->MultiCell(15,9,"Objet:","","L");
    $pdf->SetFont('Arial','',11); 
    $pdf->SetXY(40,40);
    $pdf->MultiCell(160,9,"Participation à \"".$description."\"","","L");
    $pdf->SetFont('Arial','B',15); 
    $pdf->SetXY(25,52);
    $pdf->MultiCell(160,12,$title,"1","C");
    $pdf->SetFont('Arial','',11);
    $pdf->SetXY(25,67);
    $txt = $_chef." sollicite les moyens matériels et personnels comme détaillé ci-dessous.";
    if ( $E_ALLOW_REINFORCEMENT == 1 ) $txt = "Dans le cadre d'une demande de renfort, ".$txt;
    else $txt = "Dans le cadre de l'événement  \"".$description."\", ".$txt;
    $pdf->MultiCell(160,5,$txt,"","J");
    $pdf->SetFont('Arial','U',11);
    $pdf->SetXY(25,83);
    $pdf->MultiCell(50,5,
    "Type de mission: ".
    "\nMission:".
    "\nDates:".
    "\nLieu:".
    "\nPoint regroupement:".
    "\nPersonnel Requis:".
    "\nVéhicules Requis:".
    "\nMatériel Requis:","","L");
    
    $pdf->SetXY(65,83);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell(125,5,
    $type_evenement.
    "\n".$description.
    "\n".$periode.
    "\n".$lieu.
    "\n".$point.
    "\n".$perso.
    "\n".$vehic.
    "\n".$mat,"","L");
    
    $pdf->SetFont('Arial','',9);
    $y=135;
    if ( $consignes <> "" ) {
        $pdf->SetXY(25,$y);
        $pdf->MultiCell(120,4,"Consignes particulières: ".$consignes,"","J");
        $y= $y + 4 * substr_count( $consignes, "\n" ) + 10;
    }
    if ( $DEMANDE_SPECIFIQUE <> "" ) {
        $pdf->SetXY(25,$y);
        $pdf->MultiCell(120,4,"Demande spécifique: ".$DEMANDE_SPECIFIQUE,"","J");
        $y=$y + 4 * substr_count( $DEMANDE_SPECIFIQUE, "\n" );
    }
    
    $pdf->SetFont('Arial','',11);
    $pdf->SetXY(25,$y + 15);
    if ( $E_ALLOW_REINFORCEMENT == 1 or $evt_principal > 0 ) $t = ", au titre de la mutualisation des compétences et moyens opérationnels des ".$levels[3]."s ";
    else $t=" ";
    $pdf->MultiCell(160,5,"Ils s'engagent à participer".$t."à la mise en place de \"".$description."\", événement ".$application_title." n°".$evenement,"","J");        
    
    if ( $E_ALLOW_REINFORCEMENT == 1 or $evt_principal > 0 ) {
        $y=175;
        $pdf->SetFont('Arial','B',14); 
        $pdf->SetXY(25,$y);
        if (  $evt_principal > 0 ) $t=$S_CODE;
        else $t = "N° . . . .";
        $pdf->MultiCell(160,9,"Engagement souhaité ".$levels[3]." ".$t." ","1","C");

        $pdf->SetFont('Arial','',11);
        $pdf->SetXY(25,$y + 15);
        if (  $evt_principal > 0 ) {
            if ( $nbsessions == 1 ) $t = "le ".$EH_DATE_DEBUT[1];
            else $t="du ".$EH_DATE_DEBUT[1]." au ".$EH_DATE_FIN[$nbsessions];
        }
        else $t = "du . . . . . . . . . . . . . .   au . . . . . . . . . . . . ";
        $pdf->MultiCell(160,5,"- Confirmation de la durée d'engagement de tous les bénévoles inscrits ".$t.".","","J");        
        $pdf->SetXY(25,$y + 27);
        if (  $evt_principal > 0 ) $t=$evenement;
        else $t=". . . . . .";
        $pdf->MultiCell(160,5,"- Evenement ".$application_title." créé avec personnels, véhicules et matériel sous le numéro: ".$t,"","J");   
        $pdf->SetXY(25,$y + 35);
        if (  $evt_principal > 0 ) $t=$EH_DATE_DEBUT[1]." à ".$EH_DEBUT[1];
        else $t=". . . . . . . . . . . . . ";
        $pdf->MultiCell(160,5,"- Arrivée souhaitée (date et heure): ".$t.".","","J");
        $pdf->SetXY(25,$y + 42);
        $t="par email: . . . . . . . . . . . . . . . . . . . . . .par TPH . . . . . . . . . . . . . . . . .";
        if ( $evt_principal > 0 ) {
            $queryA="select P_ID, P_EMAIL, P_PHONE, P_NOM, P_PRENOM from pompier where P_ID in (select E_CHEF from evenement_chef where E_CODE=".$evt_principal.")";
            $resultA=mysqli_query($dbc,$queryA);
            $rowA=mysqli_fetch_array($resultA);
            if ($rowA["P_ID"] <> '' ) 
                $t="de ".my_ucfirst($rowA["P_PRENOM"])." ".strtoupper($rowA["P_NOM"])." par email ".$rowA["P_EMAIL"]." ou par TPH au ".phone_display_format($rowA["P_PHONE"]);
        } 
        $pdf->MultiCell(160,5,"- Attendre avant engagement le retour du présent document pour validation ".$t." et par rattachement du renfort sur l'événement principal ".$application_title.".","","J");     

        $y=250;
        $pdf->SetFont('Arial','B',14); 
        $pdf->SetXY(25,$y);
        $pdf->MultiCell(160,9,"Validation \"Responsable événement principal ".$application_title."\"","1","C");
        $pdf->SetXY(25,$y+ 10);
        $pdf->MultiCell(160,10,"OUI - NON","","C");
    }
    $pdf->SetXY(10,270);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    if ( $E_ALLOW_REINFORCEMENT == 1 ) $name="Demande_de_renfort.pdf";
    else $name="Demande_de_personnels_et_de_moyens.pdf";
    $pdf->Output($name,'I');
}


//=============================
// produits consommés
//=============================

if ( $mode == 27 ) {
    $printPageNum=true;    
    $pdf=new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator($organisateur);
    $pdf->SetAuthor($organisateur);
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Consommables utilisés");
    $pdf->SetAutoPageBreak(0);    
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',20); 
    $pdf->SetXY(40,48);

    if ( $nbsessions > 1 ) {
        for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
            if ( isset($horaire_evt[$i])) {
                if ( $description_partie[$i] <> '' ) $dp = " - ".$description_partie[$i];
                else $dp="";
                $E_COMMENT .="\nDate Partie ".$EH_ID[$i].": ".$horaire_evt[$i].$dp;    
            }
        }
    }

    $pdf->MultiCell(120,14,"Produits Consommables utilisés","1","C");
    $pdf->SetFont('Arial','',11);
    $pdf->SetXY(25,67);
    $pdf->MultiCell(160,6,"Voici la liste des produits consommables utilisés au cours de la mission suivante:","","J");            
    $pdf->SetFont('Arial','U',11);
    $pdf->SetXY(25,75);
    $pdf->MultiCell(50,6,
    "Mission: ".
    "\nType de mission:".
    "\nOrganisateur:".
    "\nLieu:".
    "\nDates:".
    "\nPour le compte de:","","L");

    if ($cid == 0 ) $company="";
    $pdf->SetXY(65,75);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell(125,6,
    $description.
    "\n".$type_evenement.
    "\n".$S_CODE." - ".$section_affiche.
    "\n".$lieu.
    "\n".$periode.
    "\n".$company,"","L");

    // afficher produits consommés
    $pdf->SetFont('Arial','',9);
    $pdf->SetTextColor(0,0,200);
    $y=115;
    
    $evts=get_event_and_renforts($evenement);    
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
        
    $result=mysqli_query($dbc,$query);
    $nbmat=mysqli_num_rows($result);
    if ( $nbmat > 0 ) {
        $prevCC_NAME='';
        $prevEC=$evenement;
        while (custom_fetch_array($result)) {
            $TC_DESCRIPTION=ucfirst($TC_DESCRIPTION);
            if ( $TCO_CODE == 'PE' ) $label =  $TC_DESCRIPTION." (".$TUM_DESCRIPTION."s) ".$C_DESCRIPTION;
            else if ( $TUM_CODE <> 'un' or $TC_QUANTITE_PAR_UNITE <> 1 ) $label = $TC_DESCRIPTION." (".$TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION.") ".$C_DESCRIPTION;
            else $label = $TC_DESCRIPTION." ".$C_DESCRIPTION;
            if ( $EC_NOMBRE == '' )  $EC_NOMBRE = 0;
            if ( $C_ID > 0 ) {
                $query2="select s.S_ID, s.S_CODE, s.S_DESCRIPTION from section s, consommable c
                        where s.S_ID=c.S_ID and c.C_ID=".$C_ID;
                $result2=mysqli_query($dbc,$query2);
                custom_fetch_array($result2);        
            }
            else {
                $S_ID=$organisateur;
            }
            // affiche catégorie
            if ( $CC_NAME <> $prevCC_NAME) {
                if ( $y > 240 ) {
                    $y=40;
                    $pdf->AddPage();
                }
                $pdf->SetFont('Arial','',11);
                $pdf->SetTextColor(0,0,200);
                $y = $y + 3;
                $pdf->SetXY(20,$y);
                $y = $y + 6;
                $pdf->MultiCell(125,6, $CC_NAME);
            }
            $prevCC_NAME=$CC_NAME;
            
            if ( $nbsections == 0 ) {
                if ( $C_ID > 0 ) $label .= " ( stock de ".$S_CODE." )";
            }
            // affiche produit
            if ( $y > 250 ) {
                $y=40;
                $pdf->AddPage();
            }
            $pdf->SetXY(30,$y);
            $y = $y + 5;
            $pdf->SetFont('Arial','',9);
            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(125,6, $EC_NOMBRE." ".$label );  
        }
    }

    $pdf->SetXY(10,265);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->Output("Produits_consommables_utilisés_".$evenement.".pdf",'I');
}    
    
//=============================
// ordre de mission
//=============================

if ( $mode == 4 ) {
    $printPageNum=true;
    $pdf=new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator($organisateur);
    $pdf->SetAuthor($organisateur);
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Ordre de Mission");
    $pdf->SetAutoPageBreak(0);
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',25);
    $pdf->SetXY(40,48);

    if ( $nbsessions > 1 ) {
        for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
            if ( isset($horaire_evt[$i])) {
                if ( $description_partie[$i] <> '' ) $dp = " - ".$description_partie[$i];
                else $dp="";
                $E_COMMENT .="\nDate Partie ".$EH_ID[$i].": ".$horaire_evt[$i].$dp;    
            }
        }
    }

    // liste des participants
    $evts=get_event_and_renforts($evenement);
    $query="SELECT distinct ep.E_CODE, s.S_CODE, ep.TP_ID, tp.TP_LIBELLE, p.P_ID, p.P_SEXE, p.P_NOM, p.P_PRENOM, p.P_PHONE, p.P_HIDE, tp.INSTRUCTOR, e.E_PARTIES
             from pompier p, evenement e, section s, evenement_participation ep
             left join type_participation tp on ep.TP_ID = tp.TP_ID
             where ep.P_ID = p.P_ID
             and e.E_CODE = ep.E_CODE
             and e.S_ID = s.S_ID
             and ep.EP_ABSENT = 0
             and e.E_CODE in (".$evts.")";
    if ( $te_code == 'FOR' ) $query .= " and p.P_STATUT <> 'EXT' ";
    $query .= " order by p.P_NOM, p.P_PRENOM";
    $result = mysqli_query($dbc,$query);
    $numrows = mysqli_num_rows($result);
    if ($numrows < 2 ) $t = "Ordre de Mission";
    else $t = "Ordre de Mission Collectif";


    if ( strlen($E_ADDRESS) > 80 ) {
        $interligne= "\n";
        $y=154;
    }
    else {
        $interligne= "";
        $y=150;
    }

    $pdf->MultiCell(120,14,$t,"1","C");
    $pdf->SetFont('Arial','',11);
    $pdf->SetXY(25,67);
    $pdf->MultiCell(160,6,"Je ".$soussigne.", ".$chef_long.", autorise les personnes désignées ci-dessous à participer à la mission suivante.","","J");            
    $pdf->SetFont('Arial','U',11);
    $pdf->SetXY(25,88);
    $pdf->MultiCell(50,6,
    "Type de mission: ".
    "\nMission:".
    "\nLieu:".
    "\nAdresse exacte:".$interligne.
    "\nDates:".
    "\nLieu RDV:".
    "\nHeure RDV:".
    "\nPour le compte de:".
    "\nContact sur place:".
    "\nParticipants:","","L");

    if ($cid == 0 ) $company="";
    $pdf->SetXY(65,88);
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell(125,6,
    $type_evenement.
    "\n".$description.
    "\n".$lieu.
    "\n".$E_ADDRESS.
    "\n".$periode.
    "\n".$E_LIEU_RDV.
    "\n".$E_HEURE_RDV.
    "\n".$company.
    "\n".$contact,"","L");



    // afficher personnel engagés
    $pdf->SetFont('Arial','',9);
    $pdf->SetTextColor(0,0,200);
    $nom_prenom=""; $i=0;
    while ($data = mysqli_fetch_array($result)) {
        if ( $data["E_PARTIES"] == 1 ) $horaires=get_horaires_personne( $data['P_ID'], $evts );
        else $horaires="";
        $nom_prenom=strtoupper($data['P_NOM'])." ".my_ucfirst($data['P_PRENOM']);
        if ($data["TP_ID"] > 0 ) $fonction=" - ".$data['TP_LIBELLE'];
        else $fonction="";
        if ($data["P_PHONE"] <> "" and intval($data["P_HIDE"]) == 0) $phone = " - ".phone_display_format($data["P_PHONE"]);
        else $phone = "";
        if ( $data["E_CODE"] <> $evenement ) $renfort = " (renfort ".$data['S_CODE'].")";
        else $renfort="";
        
        if ( $y > 260 ) {
            $y=50;
            $pdf->SetTextColor(0,0,0);
            $pdf->AddPage();
            $pdf->SetTextColor(0,0,200);
            $pdf->SetXY(30,$y);
        }
        else
            $pdf->SetXY(30,$y);

        $pdf->MultiCell(170,6,$nom_prenom.$phone.$fonction.$renfort." ".$horaires,"","L");
        $y = $y+6;
    }
    $pdf->SetTextColor(0,0,0);
        
    // véhicules engagés
    $query="select distinct ev.E_CODE, s.S_CODE, v.V_ID, v.V_IMMATRICULATION, v.TV_CODE, v.V_MODELE, v.V_INDICATIF
        from evenement_vehicule ev, vehicule v, evenement e, section s
        where v.V_ID = ev.V_ID
        and e.E_CODE = ev.E_CODE
        and e.S_ID = s.S_ID
        and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")";
    $result=mysqli_query($dbc,$query);
    $nbvehic=mysqli_num_rows($result);
    if ( $nbvehic > 0 ) {
        if ( $y > 200 ) {
            $y=50;
            $pdf->AddPage();
            $pdf->SetXY(30,$y);
        }
        else
            $y = $y +2;
        $pdf->SetXY(25,$y);
        $pdf->SetFont('Arial','U',11);
        $pdf->SetTextColor(0,0,0);
        $pdf->MultiCell(120,7,"Les véhicules suivants seront utilisés:","","L");
        $pdf->SetFont('Arial','',9);
        $pdf->SetTextColor(0,0,200);
        while ($data = mysqli_fetch_array($result)) {
            $y = $y+6;
            $pdf->SetXY(30,$y);
            $vehicule=$data["TV_CODE"]." - ".$data["V_MODELE"]."  ".$data["V_IMMATRICULATION"];
            if ( $data["E_CODE"] <> $evenement ) $vehicule .= "(renfort ".$data['S_CODE'].")";
            $pdf->MultiCell(170,7,$vehicule,"","L");
        }
    }
    if ( $E_COMMENT <> "" ) {
        if ( $y > 200 ) {
            $y=50;
            $pdf->AddPage();
            $pdf->SetXY(30,$y);
        }
        else
            $y = $y +10;
        $pdf->SetXY(25,$y);
        $pdf->SetFont('Arial','iu',9);
        $pdf->SetTextColor(0,0,0);
        $pdf->MultiCell(120,7,"Détail:","","L");
        $pdf->SetFont('Arial','i',9);
        $pdf->SetXY(30,$y+6);
        $pdf->MultiCell(170,4,$E_COMMENT,"","L");
        $y=$y + 4 * substr_count( $E_COMMENT, "\n" ) + strlen($E_COMMENT) / 20;
    }
    if ( $consignes <> "" ) {
        if ( $y > 200 ) {
            $y=50;
            $pdf->AddPage();
            $pdf->SetXY(30,$y);
        }
        else
            $y = $y +10;
        $pdf->SetXY(25,$y);
        $pdf->SetFont('Arial','iu',9);
        $pdf->SetTextColor(0,0,0);
        $pdf->MultiCell(120,7,"Consignes pour les intervenants:","","L");
        $pdf->SetFont('Arial','i',9);
        $pdf->SetXY(30,$y+6);
        $pdf->MultiCell(170,4,$consignes,"","L");
        $y=$y+ 4 * substr_count( $consignes, "\n" ) + strlen($consignes) / 20;
    }
    $pdf->SetTextColor(0,0,0);
    
    if ( $y > 260 ) {
        $y=50;
        $pdf->SetTextColor(0,0,0);
        $pdf->AddPage();
        $pdf->SetXY(30,$y);
    }
    else
    $y = $y +15;
    $pdf->SetXY(25,$y);
    $pdf->SetFont('Arial','',11);
    $pdf->MultiCell(200,8,"Fait le ".date('d-m-Y')." ".$organisateur_city,"","J");
    $y = $y + 8;
    $pdf->SetXY(100,$y);
    $pdf->MultiCell(100,8,$titre_prefix." ".$titre.", ".$chef,"","L");
    
    if ( $S_IMAGE_SIGNATURE <> "" and $signed == 1 ) {
        $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
        if ( @is_file($signature_file)) $pdf->Image($signature_file, 100, $y+6, 40);
    }
    
    $pdf->SetXY(10,265);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    
    if ( $tofile == 1 ) {
        $evt_dir = $filesdir."/files/".$evenement."/";
        @mkdir($filesdir."/files/",0755);
        @mkdir($evt_dir,0755);
        $evt_file = $evt_dir."/Ordre_de_mission_".$evenement.".pdf";
        if ( @is_file($evt_file)) unlink($evt_file);
        $pdf->Output($evt_file,'F');
    }
    else
        $pdf->Output("Ordre_de_mission_".$evenement.".pdf",'I');
}

//=============================
// Convention
//=============================

if ( $mode == 6 or $mode == 26 ) {
    
    $evts=get_event_and_renforts($evenement);
    // stagiaires
    $query="select count(distinct P_ID) as NB from evenement_participation
        where E_CODE in (".$evts.") and EP_ABSENT=0 and TP_ID = 0 ";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $nb_stagiaires=$row["NB"];
        
    include_once ("config_doc.php");
    $printPageNum=true;
        
    $pdf=new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Convention");
    $pdf->SetAutoPageBreak(0);    

    $count = count($ct);
    for ($i = 0; $i < $count; $i++) {
        $pdf->SetX(15);
        if ( $cs[$i] == "setxy" ) {
            list($x, $y) = explode(",",$ct[$i]);
            $pdf->SetXY($x,$y);
        }
        elseif ( $cs[$i] == "addpage" ) {
            $pdf->AddPage();
        }
        elseif ( $cs[$i] == "right" ) {
            $pdf->SetFont('Arial','',10);
            $pdf->MultiCell(180,6,$ct[$i],"","R");
        }
        elseif ( $cs[$i] == "righti" ) {
            $pdf->SetFont('Arial','i',9);
            $pdf->MultiCell(180,6,$ct[$i],"","R");
        }
        elseif ( $cs[$i] == "title1" ) {
            $pdf->SetFont('Arial','B',10);
            $pdf->MultiCell(180,10,$ct[$i],"1","C");
        }
        elseif ( $cs[$i] == "title2" ) {
            $pdf->SetFont('Arial','B',10);
            $pdf->SetX(22);
            $pdf->MultiCell(180,10,$ct[$i],"","J");
        }
        elseif ( $cs[$i] == "title3" ) {
            $pdf->SetFont('Arial','Bi',9);
            $pdf->MultiCell(180,8,$ct[$i],"","J");
        }
        elseif ( $cs[$i] == "italic" ) {
            $pdf->SetFont('Arial','i',9);
            $pdf->MultiCell(180,6,$ct[$i],"","J");
        }
        elseif ( $cs[$i] == "bold" ) {
            $pdf->SetFont('Arial','B',9);
            $pdf->MultiCell(180,6,$ct[$i],"","J");
        }
        elseif ( $cs[$i] == "image_signature" ) {
            if ( $S_IMAGE_SIGNATURE <> "" and $signed == 1 ) {
                $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
                list($x, $y) = explode(",",$ct[$i]);
                if ( @is_file($signature_file)) $pdf->Image($signature_file, $x,$y,40);
            }
        }
        elseif ( $cs[$i] == "chapter" ) {
            $pdf->SetFont('Arial','',9);
            $pdf->SetTextColor(13,53,148);
            $pdf->SetX(30);
            $pdf->MultiCell(165,5,$ct[$i],"","L");
            $pdf->SetTextColor(0,0,0);
        }
        elseif ( $cs[$i] == "small" ) {
            $pdf->SetFont('Arial','',9);
            $pdf->MultiCell(180,5,$ct[$i],"","J");
        }
        else { // normal
            $pdf->SetFont('Arial','',9);
            $pdf->MultiCell(180,5,$ct[$i],"","J");
        }
    }

$pdf->Output("Convention_".$evenement.".pdf",'I');
}

//=============================
// Fiche évaluation formation
//=============================

else if ( $mode == 3 ) {

$pdf=new PDF_Ellipse();
$pdf->AliasNbPages();
$pdf->SetCreator($organisateur);
$pdf->SetAuthor($organisateur);
$pdf->SetDisplayMode('fullpage','single');
$pdf->SetTitle("Evaluation formation");
$pdf->SetAutoPageBreak(0);
$pdf->AddPage();
$pdf->SetFont('Arial','B',11);
$libelle=$description." ".$periode;
if ( $lieu <> "" and strlen($libelle) < 60) $libelle .= " (".substr($lieu,0, 75 - strlen($libelle) ).".)";
$pdf->Text(5,50,$libelle);
$pdf->SetFillColor(255,255,255);
$pdf->SetFont('Arial','',9);
$pdf->SetXY(5,54);
if ( $cid > 0 ) $pdf->Text(5,55,"pour le compte de : ".$company );

$pdf->SetDrawColor(0,0,0);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',12);
$pdf->SetXY(10,200);
$pdf->Write(5,"- Quelle est votre satisfaction globale vis à vis du stage ?");
$pdf->SetXY(10,215);
$pdf->Write(5,"- Quels sont les points positifs de cette formation ?");
$pdf->SetXY(10,230);
$pdf->Write(5,"- Quels sont les points négatifs de cette formation ?");
$pdf->SetXY(10,245);
if ( $type == 'PSC1' ) 
$pdf->Write(5,"- Etes-vous prêt à réaliser une activité de citoyen de sécurité civile ? Justifier.");
else
$pdf->Write(5,"- Autres commentaires.");

$pdf->SetFont('Arial','B',10);
$pdf->Text(140,260,"Nom du stagiaire: ............... ");
$pdf->Circle(105,120,14,'D');
$pdf->Circle(105,120,28,'D');
$pdf->Circle(105,120,42,'D');
$pdf->Circle(105,120,56,'D');
$pdf->Line(49,120, 160.9,120);
$pdf->Line(105,64, 105,176);
$pdf->Line(65.4,80.4, 145,159.6);
$pdf->Line(65.4,159.6, 145,80.4);
$pdf->SetXY(105-48,120-18.8);
$pdf->Write(5,"1");
$pdf->SetXY(105+44,120-19);
$pdf->Write(5,"1");
$pdf->SetXY(105-48,120+18.8);
$pdf->Write(5,"1");
$pdf->SetXY(105+42,120+18);
$pdf->Write(5,"1");
$pdf->SetXY(105-18.8,120-48);
$pdf->Write(5,"1");
$pdf->SetXY(105+15.8,120-48);
$pdf->Write(5,"1");
$pdf->SetXY(105-18.8,120+44);
$pdf->Write(5,"1");
$pdf->SetXY(105+18.8,120+42.5);
$pdf->Write(5,"1");
$pdf->SetXY(105-33.6,120-13.4);
$pdf->Write(5,"2");
$pdf->SetXY(105+30.4,120-13.4);
$pdf->Write(5,"2");
$pdf->SetXY(105-33.6,120+13);
$pdf->Write(5,"2");
$pdf->SetXY(105+30.4,120+13);
$pdf->Write(5,"2");
$pdf->SetXY(105-14,120-34);
$pdf->Write(5,"2");
$pdf->SetXY(105+13.4,120-33.5);
$pdf->Write(5,"2");
$pdf->SetXY(105-13.4,120+30.5);
$pdf->Write(5,"2");
$pdf->SetXY(105+12.5,120+30.5);
$pdf->Write(5,"2");
$pdf->SetXY(105-20.3,120-8.0);
$pdf->Write(5,"3");
$pdf->SetXY(105+18.5,120-8.0);
$pdf->Write(5,"3");
$pdf->SetXY(105-20.3,120+8.0);
$pdf->Write(5,"3");
$pdf->SetXY(105+18.5,120+8.0);
$pdf->Write(5,"3");
$pdf->SetXY(105-9.9,120-19.9);
$pdf->Write(5,"3");
$pdf->SetXY(105+6.8,120-19.9);
$pdf->Write(5,"3");
$pdf->SetXY(105-9.9,120+18.8);
$pdf->Write(5,"3");
$pdf->SetXY(105+6.8,120+18.8);
$pdf->Write(5,"3");
$pdf->SetXY(105-10.4,120-6);
$pdf->Write(5,"4");
$pdf->SetXY(105+6.4,120-6);
$pdf->Write(5,"4");
$pdf->SetXY(105-10.4,120+2.5);
$pdf->Write(5,"4");
$pdf->SetXY(105+6.4,120+2.5);
$pdf->Write(5,"4");
$pdf->SetXY(105-6.7,120-11);
$pdf->Write(5,"4");
$pdf->SetXY(105+1.8,120-11);
$pdf->Write(5,"4");
$pdf->SetXY(105-5.7,120+6.4);
$pdf->Write(5,"4");
$pdf->SetXY(105+1.8,120+6.4);
$pdf->Write(5,"4");
$pdf->SetDrawColor(0,0,0);
$pdf->SetFont('Arial','B',12);
$pdf->SetXY(136.5,57);
$pdf->MultiCell(55,6,"Pertinence des méthodes pédagogiques","1","C");
$pdf->SetXY(159.5,80);
$pdf->MultiCell(47,6,"Conditions d’emploi
et qualité des outils pédagogiques","1","C");
$pdf->SetXY(163.5,128);
$pdf->MultiCell(42,6,"Niveau d’acquisition des savoirs","1","C");
$pdf->SetXY(124.5,175);
$pdf->MultiCell(30,6,"Niveau de la logistique","1","C");
$pdf->SetXY(39.5,168);
$pdf->MultiCell(30,6,"Intérêt des contenus","1","C");
$pdf->SetXY(19.5,138);
$pdf->MultiCell(28,6,"Qualité des formateurs","1","C");
$pdf->SetXY(26,80);
$pdf->MultiCell(27,6,"Clarté des objectifs","1","C");
$pdf->SetXY(42,62);
$pdf->MultiCell(30,6,"Qualité de l’organisation","1","C");
$pdf->SetFillColor(192,192,192);
$pdf->SetDrawColor(0,0,0);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',10);
$pdf->SetXY(153,153);
$pdf->MultiCell(52,5,"Veuillez hachurer les cases
qui correspondent à votre
appréciation, s’il vous plait !
Merci de votre collaboration !","1","C","True");
$pdf->SetXY(3,105);
$pdf->MultiCell(42,5,"1 = Pas du tout satisfait
2 = Peu satisfait
3 = Satisfait
4 = Très satisfait","1","L","True");

$pdf->SetXY(10,265);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',6);
$pdf->MultiCell(100,5,$printed_by,"","L");

$pdf->Output("Fiche_evaluation_formation_".$evenement.".pdf",'I');

}

// évaluation SST
else if ( $mode == 9 ) {
//prendre le template PDF fiche de fin de stage SST qui doit exister
$special_template=$basedir."/images/user-specific/documents/fiche_de_fin_de_stage_SST.pdf";
$pdf = new PDFEB();
$pdf->SetCreator("EBrigade - FPDF");
$pdf->SetAuthor("$cisname");
$pdf->SetTitle("Fiche de de fin de stage SST");
$pdf->SetSubject("evaluation");
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0,0,0);
$pdf->AddPage();
$pdf->SetXY(38,49);
$pdf->Write(6,$type);
$pdf->SetXY(118,49);
$pdf->Write(6,$periode);
$pdf->Output("fiche_fin_de_stage_SST.pdf",'I');
}
//=============================
// fiche de présence
//=============================

else if ( $mode == 1 ) {

    $logo=get_logo();

    $i=0;
    if ( $nbsessions > 1 ) $nb_cours=$nbsessions;
    else if ($E_DUREE_TOTALE > 4 ) $nb_cours=2;
    else $nb_cours=1;
    if ( $nb_cours > 8 ) $largeur = 180 / $nb_cours;
    else $largeur = 150 / $nb_cours;
    $hauteur=10;
    $smallhauteur=8;
        
    $pdf= new FPDF('L','mm','A4');
    $pdf->AliasNbPages();
    $pdf->SetCreator("$cisname - $organisateur");
    $pdf->SetAuthor("$cisname");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Fiche de présence");
    $pdf->SetAutoPageBreak(0);
    $pdf->AliasNbPages();

    $formateurs="";
    if ( $te_code == 'FOR' ) {
        // liste des formateurs
        $query="SELECT distinct p.P_ID, p.P_NOM, p.P_PRENOM, tp.TP_LIBELLE, tp.INSTRUCTOR, tc.TC_SHORT
                 from evenement_participation ep, pompier p, type_civilite tc, type_participation tp, evenement e
                 where ep.P_ID = p.P_ID
                 and e.E_CODE = ep.E_CODE
                 and tc.TC_ID = p.P_CIVILITE
                 and ep.TP_ID > 0 
                 and ep.TP_ID = tp.TP_ID
                 and tp.INSTRUCTOR = 1
                 and ( e.E_CODE=".$evenement."  or e.E_PARENT=".$evenement.")
                 order by p.P_NOM, p.P_PRENOM asc";
        $result = mysqli_query($dbc,$query);
        
        while ($data = mysqli_fetch_array($result)) {
            if ( $formateurs <> "" ) $formateurs .= ", ";
            $formateurs .= my_ucfirst($data['P_PRENOM'])." ".strtoupper($data['P_NOM'])." (".$data['TP_LIBELLE'].")";
        }
    }

    // liste des stagiaires
    $query="SELECT distinct e.E_CODE, s.S_CODE, p.P_ID, p.P_NOM, p.P_PRENOM, date_format(p.P_BIRTHDATE, '%d-%m-%Y') P_BIRTHDATE, p.P_BIRTHPLACE, tc.TC_SHORT
             from evenement_participation ep, pompier p, type_civilite tc, evenement e, section s
             where ep.P_ID = p.P_ID
             and s.S_ID = e.S_ID
             and tc.TC_ID = p.P_CIVILITE
             and e.E_CODE = ep.E_CODE";
    if ( $te_code == 'FOR' ) $query .= " and ep.TP_ID = 0 ";
    $query .= " and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")
             order by p.P_NOM, p.P_PRENOM asc";
    $result = mysqli_query($dbc,$query);
    $nbstagiaires=mysqli_num_rows($result);

    if ( $nbstagiaires == 0 ) {
        $empty=true;
        $query="select null as P_ID, ' ' as P_NOM, ' ' as p_PRENOM, null as P_BIRTHDATE, null as TC_LIBELLE";
        $result = mysqli_query($dbc,$query);
    }
    else $empty=false;

    if ( $TF_CODE <> "" ) $value=$TF_LIBELLE;
    else $value= $type_evenement;

    while ($data = mysqli_fetch_array($result) or ($i < 12)) {
        if ( $i % 12 == 0) { // nouvelle page
            $y=54;
            $pdf->AddPage();
            $pdf->Image($logo,5,5,20);
            $pdf->SetTextColor(13,53,148);
            $pdf->SetFont('Arial','B',12);
            $pdf->Text(40,14,$section_affiche." ".$antenne_affiche);
            $pdf->SetFont('Arial','B',10);
            $pdf->Text(40,20,"$value: ".$description);
            if ( $cid > 0 ) $pdf->Text(55,26,"pour le compte de : ".$company );
            $pdf->SetXY(0,10);
            $pdf->SetFont('Arial','',10);
            $pdf->MultiCell(0,4,$adr."\n".$phoneinfos."\n".$mailinfos,0,"R",0);
            $pdf->SetFont('Arial','B',10);

            $pdf->Text(10,40,"Date:");
            $pdf->Text(10,45,"Lieu:");
            $pdf->Text(130,40,"Responsable administratif:");
            if ( $te_code == 'FOR' ) $pdf->Text(130,45,"Formateurs:");
            $pdf->SetFont('Arial','I',10);
            $pdf->Text(25,40,$periode);
            $pdf->Text(25,45,$lieu);
            $pdf->Text(190,40,$responsable_evt);
            $pdf->SetXY(155,42);
            $pdf->SetFont('Arial','I',8);
            if (  $te_code == 'FOR' ) $pdf->MultiCell(140,5,$formateurs,0,"L",0);
            $pdf->SetDrawColor(0,0,0);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(200);
            $pdf->SetXY(10,$y);
            $pdf->SetFont('Arial','B',14);
            $pdf->MultiCell(70,$smallhauteur * 2,"Nom Prénom",1,"L",true);
            $pdf->SetFont('Arial','B',8);
            $pdf->SetXY(80,$y);
            $pdf->MultiCell(25,$smallhauteur * 2,"Date naissance",1,"C",true);
            if ( $nb_cours < 9 ) {
                $pdf->SetXY(105,$y);
                $pdf->MultiCell(30,$smallhauteur * 2,"Lieu naissance",1,"C",true);
                $x=135;
            }
            else $x=105;
            if ($nbsessions > 6) $pdf->SetFont('Arial','B',8);
            for($k=1; $k != $nb_cours+1; $k++) { 
                $pos=$x+($k-1)*$largeur;
                $pdf->SetXY($pos,$y);
                if ( $nbsessions==1 and $EH_DATE_DEBUT[1] == $EH_DATE_FIN[1]) {
                    $day=$EH_DATE_DEBUT[1];
                    if ( $nb_cours == 2 and $k == 1) $day = $EH_DATE_DEBUT[1]." - matin";
                    if ( $nb_cours == 2 and $k == 2) $day = $EH_DATE_DEBUT[1]." - après-midi";
                }
                elseif ( $k == 1 or $nbsessions > 1) {
                    if ( $EH_DATE_DEBUT[$k] == $EH_DATE_FIN[$k]) $day=$EH_DATE_DEBUT[$k];
                    else $day=$EH_DATE_DEBUT[$k];
                    if ( $nbsessions > 6 ) $day = substr($day,0,5);
                }
                else $day="";
                $pdf->SetFont('Arial','B',8);
                $pdf->MultiCell($largeur,$smallhauteur,"$day",1,"C",true);
            };
            $y=$y+$smallhauteur;
            if ($nbsessions > 6) $pdf->SetFont('Arial','B',8);
            for($k=1; $k != $nb_cours+1; $k++) { 
                $pos=$x+($k-1)*$largeur;
                $pdf->SetXY($pos,$y);
                $time="";
                if ( $nbsessions==1 and $EH_DATE_DEBUT[1] == $EH_DATE_FIN[1]) {
                    if ( $nb_cours == 2 and $k == 1) $time = "matin";
                    if ( $nb_cours == 2 and $k == 2) $time = "après-midi";
                }
                elseif ( $k == 1 or $nbsessions > 1) {
                    $time =$EH_DEBUT[$k]."-".$EH_FIN[$k];
                }
                $pdf->SetFont('Arial','B',8);
                $pdf->MultiCell($largeur,$smallhauteur,"$time",1,"C",true);
            };
            $y=$y+$smallhauteur;
        }
        else $y=$y+$hauteur;
        $i=$i+1;
        if (! $empty){
            $n=$data['TC_SHORT']." ".strtoupper($data['P_NOM'])." ".my_ucfirst($data['P_PRENOM']);
            if ( $data['E_CODE'] <> $evenement ) $n .= " - ".$data['S_CODE'];
            $nom_prenom=substr($n,0,28);
            if (strlen($n) > 28 )  $nom_prenom .= ".";
            $date_nai=$data['P_BIRTHDATE'];
            $lieu_nai=substr($data['P_BIRTHPLACE'],0,21);
            $pid=$data['P_ID'];
        }
        else {
            $nom_prenom="";
            $date_nai="";
            $lieu_nai="";
            $pid=0;
        }
        $pdf->SetXY(10,$y);
        $pdf->SetFont('Arial','B',11);
        $pdf->SetFillColor(240);
        $pdf->MultiCell(75,$hauteur,"$nom_prenom",1,"L",true);
        $pdf->SetFont('Arial','B',8);
        $pdf->SetXY(80,$y);
        $pdf->MultiCell(25,$hauteur,"$date_nai",1,"C",true);
        if ( $nb_cours < 9 ) {
            $pdf->SetXY(105,$y);
            $pdf->MultiCell(30,$hauteur,"$lieu_nai",1,"C",true);
            $x=135;
        }
        else $x=105;
        for($k=1; $k != $nb_cours+1; $k++) {
            $pos=$x+($k-1)*$largeur;
            $pdf->SetXY($pos,$y);
            if ($nbsessions == 1 ) $l=1;
            else $l=$k;
            if ( $pid <> "" ) {
                $query2="select ep.EP_ABSENT from evenement_participation ep, evenement e
                     where ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")
                     and e.E_CODE = ep.E_CODE
                     and ep.EH_ID=".$l." and ep.P_ID=".$pid;
                $result2 = mysqli_query($dbc,$query2);
                $data2 = mysqli_fetch_array($result2);
                if ( mysqli_num_rows($result2) == 0 ) $abs='---';
                else if ( $data2['EP_ABSENT'] == 1 ) $abs='Absent';
                else if ( $data2['EP_ABSENT'] == 0 ) $abs='';
                else $abs='---';
            }
            else $abs="";
            $pdf->MultiCell($largeur,$hauteur,$abs,1,"C");
        };
    }
    $y=min(196,$y+20);
    $pdf->SetXY(120,$y);
    $pdf->MultiCell(60,10,"Signatures des responsables:",0,"L");
    $pdf->SetXY(10,200);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->Output("Fiche_de_presence_".$evenement.".pdf",'I');
}

// fiche de présence spécifique SST
else if ( $mode == 8 ) {
    $i=0;
    $nb_cours=4;
    $largeur = 22;
    $hauteur=11;
    $fontsize=10;

    //prendre le template PDF national SST si il existe
    if ( $level < 3 ) $special_template=$basedir."/images/user-specific/documents/template_SST.pdf";
    $pdf = new PDFEB();
    $pdf->SetCreator("EBrigade - FPDF");
    $pdf->SetAuthor("$cisname");
    $pdf->SetTitle("Fiche de présence");
    $pdf->SetSubject("signatures présence");
    $pdf->SetFont('Arial','',$fontsize);
    $pdf->SetTextColor(0,0,0); 
    $pdf->SetFillColor(255,255,255); 

    // liste des formateurs
    $query="SELECT distinct p.P_ID, p.P_NOM, p.P_PRENOM, tp.TP_LIBELLE, tp.INSTRUCTOR
             from evenement_participation ep, pompier p, type_participation tp, evenement e
             where ep.P_ID = p.P_ID
             and e.E_CODE = ep.E_CODE
             and ep.TP_ID > 0 
             and ep.TP_ID = tp.TP_ID
             and tp.INSTRUCTOR = 1
             and ( e.E_CODE=".$evenement."  or e.E_PARENT=".$evenement.")
             order by p.P_NOM, p.P_PRENOM asc";
    $result = mysqli_query($dbc,$query);
    $formateurs="";
    while ($data = mysqli_fetch_array($result)) {
        if ( $formateurs <> "" ) $formateurs .= ", ";
        $formateurs .= my_ucfirst($data['P_PRENOM'])." ".strtoupper($data['P_NOM'])." (".$data['TP_LIBELLE'].")";
    }

    // liste des stagiaires
    $query="SELECT distinct e.E_CODE, s.S_CODE, p.P_ID, p.P_NOM, p.P_PRENOM, c.C_NAME
             from evenement_participation ep, evenement e, section s,
             pompier p left join company c on (p.C_ID = c.C_ID)
             where ep.P_ID = p.P_ID
             and s.S_ID = e.S_ID
             and e.E_CODE = ep.E_CODE";
    if ( $te_code <> 'REU' and $te_code <> 'CER' and $CEV_CODE <> 'C_STA') $query .= " and ep.TP_ID = 0 ";
    $query .= " and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")
             order by p.P_NOM, p.P_PRENOM asc";
    $result = mysqli_query($dbc,$query);
    $nbstagiaires=mysqli_num_rows($result);

    if ( $nbstagiaires == 0 ) {
        $empty=true;
        $query="select null as P_ID, ' ' as P_NOM, ' ' as p_PRENOM, null as P_BIRTHDATE, '' as C_NAME";
        $result = mysqli_query($dbc,$query);
    }
    else $empty=false;
    $value="Formation";

    $pdf->AddPage();
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetXY(10,41);
    $pdf->SetFont('Arial','UB',11);
    $pdf->MultiCell(200,$hauteur,"FEUILLE D'EMARGEMENT",0,"C",false);
    $pdf->SetXY(10,50);
    $pdf->SetFont('Arial','',10);
    if ( $TF_CODE == 'I' ) $TF_LIBELLE="formation initiale";
    $pdf->MultiCell(200,$hauteur,"FORMATION:  ".$TF_LIBELLE." ".$description,0,"L",false);
    $pdf->SetXY(10,56);
    $pdf->MultiCell(200,$hauteur,"POUR LE COMPTE DE:  ".$company,0,"L",false);
    $pdf->SetXY(10,66);
    $pdf->MultiCell(200,3,"LIEU:  ".$lieu." - ".$E_ADDRESS,0,"L",false);
    if ( strlen($E_ADDRESS) > 160 ) $y = 82;
    else $y = 70;
    $y=70;
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(200,$hauteur,"RESPONSABLE DE L’ENTREPRISE:  ".$company_representant,0,"L",false);
    $y = $y + 6;
    if ( $cisname == 'Protection Civile' ) $detail1=" A.D.P.C/F.N.P.C-DFE";
    else $detail1=$cisname;
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(200,$hauteur,"FORMATEUR  ".$detail1.":  ".$responsable_evt,0,"L",false);

    while ($data = mysqli_fetch_array($result)) {
        if ( $i % 12 == 0) { // nouvelle page
            if ( $i == 0 ) $y=90;
            else {
                $y=50;
                $pdf->AddPage();
            }
            $pdf->SetFont('Arial','B',9);
            
            $pdf->SetXY(65,$y);
            for($k=1; $k != $nb_cours+1; $k++) {
                $pos=65+($k-1)*$largeur;
                $pdf->SetXY($pos,$y);
                if ( isset($EH_DATE_DEBUT[$k])) $day=$EH_DATE_DEBUT[$k];
                else $day="";
                $pdf->MultiCell($largeur,8,"$day",1,"C",true);
            }
            $y=$y+8;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(55,8+6,"Nom Prénom",1,"C",true);
            $pdf->SetXY(65,$y);
            for($k=1; $k != $nb_cours+1; $k++) {
                $pos=65+($k-1)*$largeur;
                $pdf->SetXY($pos,$y);
                if ( isset($EH_DEBUT[$k])) $horaires=$EH_DEBUT[$k]." à ".$EH_FIN[$k];
                else $horaires="";
                $pdf->MultiCell($largeur,8,"$horaires",1,"C",true);
            }
            $pdf->SetXY(65+4*$largeur,$y);
            $pdf->MultiCell(50,8+6,"Entreprise",1,"C",true);
            $y = $y+8;
            for($k=1; $k != $nb_cours+1; $k++) { 
                $pos=65+($k-1)*$largeur;
                $pdf->SetXY($pos,$y);
                $pdf->MultiCell($largeur,6,"signature",1,"C",true);
            };
            $y=$y+6;
        }
        else $y=$y+$hauteur;
        
        $i=$i+1;
        if (! $empty){
            $n=strtoupper($data['P_NOM'])." ".my_ucfirst($data['P_PRENOM']);
            if ( $data['E_CODE'] <> $evenement ) $n .= " - ".$data['S_CODE'];
            $nom_prenom=substr($n,0,28);
            if (strlen($n) > 28 )  $nom_prenom .= ".";
            $pid=$data['P_ID'];
        }
        else {
            $nom_prenom="";
            $pid=0;
        }
        $pdf->SetXY(10,$y);
        $pdf->SetFont('Arial','B',9);
        $pdf->MultiCell(55,$hauteur,"$nom_prenom",1,"L",true);
        $pdf->SetFont('Arial','',9);
        for($k=1; $k != $nb_cours+1; $k++) {
            $pos=65+($k-1)*$largeur;
            $pdf->SetXY($pos,$y);
            $pdf->MultiCell($largeur,$hauteur,"",1,"C");
        };
        $pdf->SetXY(65+4*$largeur,$y);
        $pdf->MultiCell(50,$hauteur, $data['C_NAME'],1,"C",true);
    }
    $y=$y+$hauteur;
    $pdf->SetXY(10,$y);
    $pdf->SetFont('Arial','B',9);
    $pdf->MultiCell(55,$hauteur,"FORMATEUR",1,"L",true);
    $pdf->SetFont('Arial','',9);
    for($k=1; $k != $nb_cours+1; $k++) {
        $pos=65+($k-1)*$largeur;
        $pdf->SetXY($pos,$y);
        $pdf->MultiCell($largeur,$hauteur,"",1,"C");
    };
    $pdf->SetXY(65+4*$largeur,$y);
    $pdf->MultiCell(50,$hauteur, "",1,"L",true);

    $pdf->SetXY(10,270);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->Output("Fiche_de_presence_".$evenement.".pdf",'I');    
}

//=============================
// procès verbal
//=============================

else if ( $mode == 5 ) {

    $logo=get_logo();

    $i=0;
    $hauteur=9;

    $pdf= new FPDF('L','mm','A4');
    $pdf->AliasNbPages();
    $pdf->SetCreator("$cisname - $organisateur");
    $pdf->SetAuthor("$cisname");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Procès verbal");
    $pdf->SetAutoPageBreak(0);
    $pdf->AliasNbPages();

    // liste des formateurs
    $query="SELECT distinct p.P_ID, p.P_NOM, p.P_PRENOM, tp.TP_LIBELLE
             from evenement_participation ep, pompier p, type_participation tp, evenement e
             where ep.P_ID = p.P_ID
             and e.E_CODE = ep.E_CODE
             and ep.TP_ID > 0 
             and ep.TP_ID = tp.TP_ID
             and ( e.E_CODE=".$evenement."  or e.E_PARENT=".$evenement.")
             and tp.INSTRUCTOR = 1
             order by p.P_NOM, p.P_PRENOM asc";
             
    $result = mysqli_query($dbc,$query);
    $formateurs="";
    while ($data = mysqli_fetch_array($result)) {
        if ( $formateurs <> "" ) $formateurs .= ", ";
        $formateurs .= my_ucfirst($data['P_PRENOM'])." ".strtoupper($data['P_NOM'])." (".$data['TP_LIBELLE'].")";
    }

    // liste des stagiaires
    $query="SELECT distinct s.S_CODE, e.E_CODE, p.P_ID, p.P_NOM, p.P_PRENOM, date_format(p.P_BIRTHDATE, '%d-%m-%Y') P_BIRTHDATE, P_BIRTHPLACE 
             from evenement_participation ep, pompier p, evenement e, section s
             where ep.P_ID = p.P_ID
             and s.S_ID = e.S_ID
             and e.E_CODE = ep.E_CODE
             and ep.TP_ID = 0
             and ( e.E_CODE=".$evenement."  or e.E_PARENT=".$evenement.")
             order by p.P_NOM, p.P_PRENOM asc";
    $result = mysqli_query($dbc,$query);
    $nbstagiaires=mysqli_num_rows($result);

    if ( $nbstagiaires == 0 ) {
        $empty=true;
        $query="select null as P_ID, ' ' as P_NOM, ' ' as p_PRENOM, null as P_BIRTHDATE, null as P_BIRTHPLACE";
        $result = mysqli_query($dbc,$query);
    }
    else $empty=false;

    if ( ! $empty ) {
      $evts=get_event_and_renforts($evenement);
      while ($data = mysqli_fetch_array($result)) {
        $query2="select PF_ADMIS, PF_DIPLOME from personnel_formation
                 where P_ID=".$data['P_ID']."
                 and E_CODE =".$evenement."
                 union
                 select pf.PF_ADMIS, pf.PF_DIPLOME from personnel_formation pf, evenement e
                 where pf.P_ID=".$data['P_ID']."
                 and e.E_CODE =".$evenement."
                 and e.E_PARENT=pf.E_CODE
                 ";
        $result2 = mysqli_query($dbc,$query2);
        $row2 = mysqli_fetch_array($result2);
        if ( $row2['PF_ADMIS'] == 1 ) $admis='OUI'; else $admis='NON';
        $diplome=$row2['PF_DIPLOME'];
        if ( $diplome == '')  $diplome ='-';
        if ( $i % 12 == 0) { // nouvelle page
            $y=54;    
            $pdf->AddPage();
            $pdf->Image($logo,5,5,20);
            $pdf->SetTextColor(13,53,148);
            $pdf->SetFont('Arial','B',12);
            $pdf->SetXY(60,10);
            $pdf->Text(30,11,$section_affiche." ".$antenne_affiche);
            $pdf->Text(30,16,"Procès verbal de la formation ".$description);
            $pdf->SetFont('Arial','',10);
            
            
            $type_formation="une formation";    
            if ( $TF_CODE == 'I' ) $type_formation = "une formation initiale";
            elseif ( $TF_CODE <> "" ) $type_formation = "une ".$TF_LIBELLE;
            $txt="à ".$type_formation;
            if (substr($type,0,3) == 'PSE') $txt .=" aux premiers secours en équipe (".$type.")";
            $pdf->Text(30,21,"Suite ".$txt.",");
            $pdf->Text(30,25,"qui s'est déroulée ".$periode);
            $pdf->Text(30,29,"à ".$lieu);
            if ( $cid > 0 ) $pdf->Text(30,33,"pour le compte de : ".$company );
            $pdf->SetXY(0,10);            
            $pdf->SetFont('Arial','',10);
            $pdf->MultiCell(0,4,$adr."\n".$phoneinfos."\n".$mailinfos,0,"R",0);
            $pdf->SetFont('Arial','B',11);

            $pdf->Text(10,40,"Responsable administratif:");
            $pdf->Text(10,45,"Formateurs:");
            $pdf->SetFont('Arial','I',10);
            $pdf->Text(70,40,$responsable_evt);
            $pdf->SetXY(69,41);
            $pdf->MultiCell(190,5,$formateurs,0,"L",0);
            $pdf->SetDrawColor(0,0,0);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor(200);
            $pdf->SetXY(10,$y);
            $pdf->SetFont('Arial','B',14);
            $pdf->MultiCell(75,$hauteur,"Nom Prénom",1,"L",true);
            $pdf->SetFont('Arial','B',10);    
            $pdf->SetXY(85,$y);
            $pdf->MultiCell(35,$hauteur,"Date de naissance",1,"C",true);
            $pdf->SetXY(120,$y);
            $pdf->MultiCell(60,$hauteur,"Lieu de naissance",1,"C",true);
            $pdf->SetXY(180,$y);
            $pdf->MultiCell(30,$hauteur,"Apte",1,"C",true);
            $pdf->SetXY(210,$y);
            $pdf->MultiCell(70,$hauteur,"N°diplôme",1,"C",true);
            
        }
        $i=$i+1;$y=$y+$hauteur;
        if (! $empty){
            $n=strtoupper($data['P_NOM'])." ".my_ucfirst($data['P_PRENOM']);
            if ( $data['E_CODE'] <> $evenement ) $n .= " - ".$data['S_CODE'];
            $nom_prenom=substr($n,0,30);
            if (strlen($n) > 30 )  $nom_prenom .= ".";
            $date_nai=$data['P_BIRTHDATE'];
            $lieu_nai=$data['P_BIRTHPLACE'];
        }
        else {
            $nom_prenom="";
            $date_nai="";
        }
        $pdf->SetXY(10,$y);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(240);
        $pdf->MultiCell(75,$hauteur,"$nom_prenom",1,"L",true);
        $pdf->SetFont('Arial','B',10);    
        $pdf->SetXY(85,$y);
        $pdf->MultiCell(35,$hauteur,"$date_nai",1,"C",true);
        $pdf->SetXY(120,$y);
        $pdf->MultiCell(60,$hauteur,"$lieu_nai",1,"C",true);
        $pdf->SetXY(180,$y);
        $pdf->MultiCell(30,$hauteur,$admis,1,"C",true);
        $pdf->SetXY(210,$y);
        $pdf->MultiCell(70,$hauteur,$diplome,1,"C",true);
      }
    }
    else {
        $y=180;
        $pdf->AddPage();
    }
    $pdf->SetFont('Arial','B',10);
    $pdf->SetTextColor(0,0,0);
    $y=min(183,$y+8);
    $pdf->SetXY(100,$y);
    $pdf->MultiCell(50,10,"Fait à ",0,"L");
    $pdf->SetXY(150,$y);
    $pdf->MultiCell(80,10,"le ",0,"L");
    $y = $y + 8;
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(80,10,"Signature du responsable pédagogique:",0,"L");
    $pdf->SetXY(120,$y);
    $pdf->MultiCell(80,10,"Signatures formateurs:",0,"L");
    $pdf->SetXY(220,$y);
    $pdf->MultiCell(80,10,"Signature du ".$titre.":",0,"L");

    if ( $S_IMAGE_SIGNATURE <> "" and $signed == 1 ) {
        $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
        if ( @is_file($signature_file)) $pdf->Image($signature_file, 220, $y+8, 40);
    }
    $pdf->SetXY(10,200);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->Output("Proces_verbal_".$evenement.".pdf",'I');    
}

//=============================
// factures ou convocations individuelles
//=============================

if ( $mode == 7 or $mode == 18) {
    include_once ("config_doc.php");
    include_once ("lib/fpdf/nel.php");

    if ( $mode == 7 ) $t = 'Facture de formation';
    else $t = 'Convocation formation';
    $pdf= new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator($organisateur);
    $pdf->SetAuthor($organisateur);
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle($t);
    $pdf->SetAutoPageBreak(0);

    // liste des stagiaires
    $query="SELECT distinct p.P_ID, p.P_SEXE, p.P_NOM, p.P_PRENOM, ep.EP_TARIF, e.E_TARIF , ep.EP_PAID,
             tc.TC_LIBELLE, p.P_CITY, p.P_ZIP_CODE, p.P_ADDRESS, ep.MODE_PAIEMENT, ep.NUM_CHEQUE, ep.NOM_PAYEUR, tp.TP_DESCRIPTION
             from evenement_participation ep left join type_paiement tp on tp.TP_ID=ep.MODE_PAIEMENT, pompier p, evenement e, type_civilite tc
             where ep.P_ID = p.P_ID
             and p.P_CIVILITE = tc.TC_ID
             and e.E_CODE = ep.E_CODE
             and ep.EP_ABSENT = 0
             and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")";
    if ( $mode == 7 ) $query .= " and ( ep.TP_ID = 0 or e.TE_CODE <> 'FOR' )";
             
    if ( $pid > 0 ) $query .= " and p.P_ID = ".$pid;

    $query .= " order by p.P_NOM, p.P_PRENOM";
    $result = mysqli_query($dbc,$query);

    while (custom_fetch_array($result)) { 
        $expcomplement="";
        $nom_prenom=my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM);
        if ( $P_CITY <> "" ) $P_CITY = " - ".$P_CITY;
        if ( $EP_TARIF == '' )$tarif = $E_TARIF;
        else $tarif=$EP_TARIF;

        if ( $te_code == 'FOR' ) {
            if ( $TF_CODE == 'P' ) $type_evenement = $TF_LIBELLE;    
            elseif ( $TF_CODE == 'I' ) $type_evenement = "formation initiale";
            elseif ( $TF_CODE <> "" ) $type_evenement = $TF_LIBELLE;
        }
        
        $pdf->AddPage();
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','',12);
        $y=50;
        $pdf->SetXY(120,$y);
        $pdf->MultiCell(90,6,$TC_LIBELLE." ".$nom_prenom,"","L");
        if ( strlen($P_ADDRESS) > 0 ) {
            $y = $y + 6;
            $pdf->SetXY(120,$y);
            $pdf->MultiCell(90,6,$P_ADDRESS,"","L");
            $nbret = count_returns($P_ADDRESS);
            if ($nbret == 0 and strlen($P_ADDRESS) > 35 ) $nbret=1;
            $y = $y + 6 * $nbret;
        }
        $y = $y + 6;
        $pdf->SetXY(120,$y);
        $pdf->MultiCell(90,6,$P_ZIP_CODE.$P_CITY ,"","L");
        $pdf->SetFont('Arial','B',18); 
        $pdf->SetXY(35,80);
        if ( $mode == 7 ) { // facture
            $pdf->MultiCell(140,10,"Facture ".$type_evenement." ".$type,"1","C");
            $pdf->SetFont('Arial','',11);
            $pdf->SetXY(55,90);
            $pdf->MultiCell(100,10,"Facture n° ".$evenement."-".$P_ID,"","C");    
            $pdf->SetXY(25,105);
            $pdf->MultiCell(160,8,$facture1,"","J");
            $y=120;
        }
        else { // convocations
            $pdf->MultiCell(140,8,"Convocation ".$type_evenement." ".$type."\npour ".$nom_prenom,"1","C");
            $pdf->SetXY(25,110);
            $pdf->SetFont('Arial','',11);
            $pdf->MultiCell(160,6,"Je ".$soussigne.", ".$chef_long.", convoque ".$TC_LIBELLE." ".$nom_prenom." à participer à l'événement suivant:","","J");
            $y=130;
        
        }
        $pdf->SetFont('Arial','I',9);
        $pdf->SetXY(25,$y); 
        $pdf->MultiCell(160,7,"Type: ".$type_evenement." ".$type,"","J");
        $y = $y + 6;
        $pdf->SetXY(25,$y);
        $pdf->MultiCell(160,7,"Description: ".$description,"","J");
        if($lieu!=''){
            $y = $y + 6;
            $pdf->SetXY(25,$y); 
            $pdf->MultiCell(160,7,"Lieu: ".$lieu,"","J");
        }
        if($E_ADDRESS!=''){
            $y = $y + 6;
            $pdf->SetXY(25,$y); 
            $pdf->MultiCell(160,6,"Adresse exacte: ".$E_ADDRESS,"","J");
            if ( strlen($E_ADDRESS) > 180 ) $y = $y + 12;
            else if ( strlen($E_ADDRESS) > 80 ) $y = $y + 6;
        }

        for ($i=1; $i <= $nbmaxsessionsparevenement; $i++) {
            if ( $nbsessions == 1 ) $t="Dates et heures";
            else if (isset($EH_ID[$i])) $t="Date Partie ".$EH_ID[$i];
            if ( isset($horaire_evt[$i])) {
                $y = $y + 5;
                $pdf->SetXY(25,$y);
                if ( $description_partie[$i] <> '' ) $dp = " - ".$description_partie[$i];
                else $dp="";
                $pdf->MultiCell(160,7,$t.": ".$horaire_evt[$i].$dp,"","J");
            }
        }
        if($E_DUREE_TOTALE!=''){
            $y = $y + 6;
            $pdf->SetXY(25,$y); 
            $pdf->MultiCell(160,7,"Durée totale: ".$E_DUREE_TOTALE." heures","","J");
        }
        
        if ( $mode == 7 ) {
            $pdf->SetXY(25,$y+10);
            $pdf->SetFont('Arial','B',12);
            $pdf->MultiCell(0, 8, "Total = ".my_number_format($tarif)." Euros",0,"C",false);     
            $entier=intval($tarif); 
            $decimale=100 * round(($tarif - $entier), 2);
            if ( $decimale == 100 ) {
                $entier++;
                $decimale = 0;
            }
            $pdf->SetFont('Arial','I',11);
            $pdf->MultiCell(0, 8, "( ".enlettres($entier)." Euros et ".enlettres($decimale)." Cents )",0,"C",false); 
            
            $pdf->SetFont('Arial','',11);
            $pdf->SetXY(25,$y+35);
            $pdf->MultiCell(100,8,$facture2,"","L");
            $pdf->SetXY(25,$y+41);
            if ( $EP_PAID == 1 ) {
                $txt=$facture4;
                if ( intval($MODE_PAIEMENT) > 0 )  $txt .= " par ".$TP_DESCRIPTION;
                if ( $NUM_CHEQUE <> "" ) $txt .= ", numéro de chèque: ".$NUM_CHEQUE;
                $txt .=".";
            }
            else $txt=$facture3;
            
            $pdf->MultiCell(150,8,$txt,"","L");
            
            if ( $NOM_PAYEUR <> "" ) {
                $pdf->SetXY(25,$y+47);
                $txt = "Payement par: ".$NOM_PAYEUR;
                $pdf->MultiCell(150,8,$txt,"","L");
            }
        }
        
        if ( $mode == 18 and $consignes <> "" ) {
            if ( $y > 200 ) {
                $y=50;
                $pdf->AddPage();
                $pdf->SetXY(30,$y);
            }
            else
                $y = $y +6;
            $pdf->SetXY(25,$y);
            $pdf->SetFont('Arial','iu',9);
            $pdf->MultiCell(120,7,"Consignes pour les intervenants:","","L");
            $pdf->SetFont('Arial','i',9);
            $pdf->SetXY(25,$y+6);
            $pdf->MultiCell(170,4,$consignes,"","L");
            $y=$y+ 4 * substr_count( $consignes, "\n" ) + strlen($consignes) / 20;
        }

        $y=180;
        $pdf->SetXY(25,$y+65);
        $pdf->MultiCell(200,8,"Fait le ".date('d-m-Y')." ".$organisateur_city,"","J");
        $pdf->SetXY(110,$y+54);
        $pdf->MultiCell(100,8,$titre_prefix." ".$titre.", ".$chef,"","L");

        if ( $S_IMAGE_SIGNATURE <> "" and $signed == 1 ) {
            $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
            if ( @is_file($signature_file)) $pdf->Image($signature_file, 110, $y+60, 40);
        }
        
        $pdf->SetXY(10,265);
        $pdf->SetFont('Arial','',6);
        $pdf->MultiCell(100,5,$printed_by,"","L");
    };
    $pdf->SetDisplayMode('fullpage','single');
    if ($mode == 7 ) $name="Facture.pdf";
    else $name="Convocation.pdf";
    $pdf->Output($name,'I');
}


//=============================
// reçu adhésion
//=============================

if ( $mode == 19) {
    include_once ("config_doc.php");
    include_once ("lib/fpdf/nel.php");

    $pdf= new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator("$cisname");
    $pdf->SetAuthor("$cisname");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Reçu d'adhésion");
    $pdf->SetAutoPageBreak(0);

    $query="SELECT p.P_ID, p.P_SEXE, p.P_NOM, p.P_PRENOM, date_format(p.P_DATE_ENGAGEMENT , '%d-%m-%Y') P_DATE_ENGAGEMENT,
         tc.TC_LIBELLE, p.P_CITY, p.P_ZIP_CODE, p.P_ADDRESS, p.P_OLD_MEMBER, date_format(p.P_FIN , '%d-%m-%Y') P_FIN
         from pompier p, type_civilite tc
         where p.P_CIVILITE = tc.TC_ID
         and p.P_ID=".$pid;
    $result = mysqli_query($dbc,$query);

    $data = mysqli_fetch_array($result); 
    $expcomplement="";
    $nom_prenom=my_ucfirst($data['P_PRENOM'])." ".strtoupper($data['P_NOM']);
    $P_ID=$data['P_ID'];
    $TC_LIBELLE=$data['TC_LIBELLE'];
    $P_ZIP_CODE=$data['P_ZIP_CODE'];
    $P_CITY=$data['P_CITY'];
    if ( $P_CITY <> "" ) $P_CITY = " - ".$P_CITY;
    $P_ADDRESS=$data['P_ADDRESS'];
    $P_DATE_ENGAGEMENT=$data['P_DATE_ENGAGEMENT'];
    $P_OLD_MEMBER=$data['P_OLD_MEMBER'];
    $P_FIN=$data['P_FIN'];
    $P_SEXE=$data['P_SEXE'];
    
    $pdf->AddPage();
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',12);
    $y=50;
    $pdf->SetXY(120,$y);
    $pdf->MultiCell(90,6,$TC_LIBELLE." ".$nom_prenom,"","L");
    if ( strlen($P_ADDRESS) > 0 ) {
        $y = $y + 6;
        $pdf->SetXY(120,$y);
        $pdf->MultiCell(90,6,$P_ADDRESS,"","L");
        $nbret = count_returns($P_ADDRESS);
        if ($nbret == 0 and strlen($P_ADDRESS) > 35 ) $nbret=1;
        $y = $y + 6 * $nbret;
    }
    $y = $y + 6;
    $pdf->SetXY(120,$y);
    $pdf->MultiCell(90,6,$P_ZIP_CODE.$P_CITY ,"","L");
    $pdf->SetFont('Arial','B',18); 
    $pdf->SetXY(35,80);
    
    $pdf->MultiCell(140,10,"Reçu d'adhésion\npour ".$nom_prenom,"1","C");
    $pdf->SetXY(25,125);
    $pdf->SetFont('Arial','',11);
    $msg="Je ".$soussigne.", ".$chef_long.", atteste que ".$TC_LIBELLE." ".$nom_prenom;
    $adherent="membre bénévole";

    if ( $P_OLD_MEMBER > 0 )
        $msg .= " a été $adherent de notre association du ".$P_DATE_ENGAGEMENT." au ".$P_FIN;
    else 
        $msg .=" est $adherent de notre association depuis le ".$P_DATE_ENGAGEMENT.".";

    $pdf->MultiCell(160,6, $msg ,"","J");
    $y = 130;

    
    $pdf->SetXY(25,$y+67);
    $pdf->MultiCell(200,8,"Fait le ".date('d-m-Y')." ".$S_CITY,"","J");
    $pdf->SetXY(100,$y+73);
    $pdf->MultiCell(100,8,$titre_prefix." ".$titre.", ".$chef,"","L");

    if ( $S_IMAGE_SIGNATURE <> "" ) {
        $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
        if ( @is_file($signature_file)) $pdf->Image($signature_file, 100, $y+79, 40);
    }
    
    $pdf->SetXY(10,265);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->Output("recu_adhesion.pdf",'I');
}


//=============================
// facture cotisation
//=============================

if ( $mode == 20) {
    include_once ("config_doc.php");
    include_once ("lib/fpdf/nel.php");
    
    $paiement_id=intval($_GET["paiement_id"]);

    $pdf= new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator("$cisname");
    $pdf->SetAuthor("$cisname");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Facture cotisation");
    $pdf->SetAutoPageBreak(0);
    
    $query="select po.P_NOM, po.P_PRENOM, pc.PC_ID, pc.ANNEE, pc.PERIODE_CODE, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE,
            pc.COMMENTAIRE , pc.NUM_CHEQUE , pc.MONTANT, tp.TP_ID, tp.TP_DESCRIPTION, tc.TC_LIBELLE,
            po.P_CITY, po.P_ZIP_CODE, po.P_ADDRESS, p.P_DESCRIPTION, P_DESCRIPTION
            from personnel_cotisation pc, periode p, pompier po, type_paiement tp, type_civilite tc
            where tp.TP_ID = pc.TP_ID
            and po.P_CIVILITE = tc.TC_ID
            and po.P_ID = pc.P_ID
            and pc.PERIODE_CODE=p.P_CODE
            and pc.P_ID=".$pid."
            and pc.PC_ID=".$paiement_id;

    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nom_prenom=my_ucfirst($row["P_PRENOM"])." ".strtoupper($row['P_NOM']);
    $ANNEE=$row["ANNEE"];
    $PERIODE_CODE=$row["PERIODE_CODE"];
    $PC_DATE=$row["PC_DATE"];
    $COMMENTAIRE=$row["COMMENTAIRE"];
    $NUM_CHEQUE=$row["NUM_CHEQUE"];
    $MONTANT=$row["MONTANT"];
    $TP_DESCRIPTION=$row["TP_DESCRIPTION"];
    $TP_ID=strtoupper($row["TP_ID"]);
    $P_CITY=$row["P_CITY"];
    $P_ZIP_CODE=$row["P_ZIP_CODE"];
    $P_ADDRESS=$row["P_ADDRESS"];
    $P_DESCRIPTION=$row["P_DESCRIPTION"];
    $TC_LIBELLE=$row["TC_LIBELLE"];
    
    $pdf->AddPage();
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',12);
    $y=50;
    $pdf->SetXY(120,$y);
    $pdf->MultiCell(90,6,$nom_prenom,"","L");
    if ( strlen($P_ADDRESS) > 0 ) {
        $y = $y + 6;
        $pdf->SetXY(120,$y);
        $pdf->MultiCell(90,6,$P_ADDRESS,"","L");
        $nbret = count_returns($P_ADDRESS);
        if ($nbret == 0 and strlen($P_ADDRESS) > 35 ) $nbret=1;
        $y = $y + 6 * $nbret;
    }
    $y = $y + 6;
    $pdf->SetXY(120,$y);
    $pdf->MultiCell(90,6,$P_ZIP_CODE.$P_CITY ,"","L");
    $pdf->SetFont('Arial','B',14); 
    $pdf->SetXY(35,90);
    
    $pdf->MultiCell(140,10,"Facture cotisation\npour ".$nom_prenom,"1","C");
    $pdf->SetXY(25,125);
    $pdf->SetFont('Arial','',11);
    $msg=$TC_LIBELLE." ".strtoupper($row['P_NOM']).", veuillez trouver la facture correspondant à votre cotisation pour ".$section_prefix." ".$section_affiche;
    $pdf->MultiCell(160,6, $msg ,"","J");

    $hauteurligne=8;
    $pdf->SetXY(25,$pdf->GetY()+8);
    $pdf->SetFillColor(221,221,221); 
    $colonnes=array(90,20,20,15,30);
    $pdf->Cell($colonnes[0],$hauteurligne,"Libellé",1,0,"L",true);
    $pdf->Cell($colonnes[1],$hauteurligne,"Qté",1,0,"C",true);
    $pdf->Cell($colonnes[2],$hauteurligne,"PU",1,0,"C",true);
    $pdf->Cell($colonnes[4],$hauteurligne,"Total",1,1,"C",true);
    $pdf->SetXY(25,$pdf->GetY());
    $pdf->Cell($colonnes[0],$hauteurligne,"Cotisation ".$P_DESCRIPTION." ".$ANNEE,1,0,"L");
    $pdf->Cell($colonnes[1],$hauteurligne,1,1,0,"C");
    $pdf->Cell($colonnes[2],$hauteurligne,my_number_format($MONTANT),1,0,"C");
    $pdf->Cell($colonnes[4],$hauteurligne,$MONTANT." EUR",1,1,"C");
    
    $entier=intval($MONTANT); 
    $decimale=100 * round(($MONTANT - $entier), 2);
    if ( $decimale == 100 ) {
        $entier++;
        $decimale = 0;
    }
    
    $pdf->SetFont('Arial','I',9);
    $pdf->MultiCell(0, $hauteurligne, 
                    "( ".enlettres($entier)." Euros et ".enlettres($decimale)." Cents )",0,"C",false);
    
    $pdf->SetFont('Arial','',11);
    $y=180;
    $pdf->SetXY(25,$y);
    $pdf->MultiCell(170,$hauteurligne,"Type de paiement: ".$TP_DESCRIPTION." ".$NUM_CHEQUE.". ".$COMMENTAIRE);

    $y = 230;
    $pdf->SetXY(25,$y);
    
    $pdf->MultiCell(200,8,"Fait le ".date('d-m-Y')." ".$S_CITY,"","J");
    $pdf->SetXY(100,$y+73);
    $pdf->MultiCell(100,8,$titre_prefix." ".$titre.", ".$chef,"","L");

    if ( $S_IMAGE_SIGNATURE <> "" ) {
        $signature_file="images/user-specific/".$S_IMAGE_SIGNATURE;
        if ( @is_file($signature_file)) $pdf->Image($signature_file, 100, $y+79, 40);
    }
    
    $pdf->SetXY(10,265);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->Output("Facture_cotisation.pdf",'I');
}

//=============================
// attestations de présence SST
//=============================

if ( $mode == 10 ) {

    $query2="select date_format(max(EH_DATE_DEBUT),'%d-%m-%Y') from evenement_horaire where E_CODE='$evenement'";
    $result2 = mysqli_query($dbc,$query2);
    $data2 = mysqli_fetch_array($result2);
    $maxdate=$data2['0'];

    include_once ("config_doc.php");
    include_once ("lib/fpdf/nel.php");

    if ( $level < 3 ) $special_template=$basedir."/images/user-specific/documents/template_SST.pdf";
    $pdf= new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator($organisateur);
    $pdf->SetAuthor($organisateur);
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Liste Stagiaire formation");
    $pdf->SetAutoPageBreak(0);

    // liste des stagiaires
    $query="SELECT distinct p.P_ID, p.P_SEXE, p.P_NOM, p.P_NOM_NAISSANCE, p.P_PRENOM,tc.TC_LIBELLE, c.C_ID, c.C_NAME, 
             date_format(p.P_BIRTHDATE,'%d-%m-%Y') P_BIRTHDATE, p.P_BIRTHPLACE, p.P_SEXE
             from evenement_participation ep, evenement e, type_civilite tc,
             pompier p left join company c on c.C_ID=p.C_ID
             where ep.P_ID = p.P_ID
             and p.P_CIVILITE = tc.TC_ID
             and e.E_CODE = ep.E_CODE
             and ep.EP_ABSENT = 0
             and ep.TP_ID = 0 
             and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")";

    if ( $pid > 0 ) $query .= " and p.P_ID = ".$pid;

    $query .= " order by p.P_NOM, p.P_PRENOM";
    $result = mysqli_query($dbc,$query);

    while ($data = mysqli_fetch_array($result)) { 
        $expcomplement="";
        $nom_prenom=my_ucfirst($data['P_PRENOM'])." ".strtoupper($data['P_NOM']);
        $nom_naissance=strtoupper($data['P_NOM_NAISSANCE']);
        $P_ID=$data['P_ID'];
        $TC_LIBELLE=$data['TC_LIBELLE'];
        $C_ID=$data['C_ID'];
        $C_NAME=$data['C_NAME'];
        $P_SEXE=$data['P_SEXE'];
        $P_BIRTHDATE=$data['P_BIRTHDATE'];
        $P_BIRTHPLACE=$data['P_BIRTHPLACE'];

        if ( $P_SEXE == 'M' ) $born ="Né";
        else $born ="Née";
        if ( $nom_naissance <> "" ) $born .= " ".$nom_naissance.",";
        
        $born .= " le ".$P_BIRTHDATE;
        if ( $P_BIRTHPLACE <> '' ) $born .= " à ".$P_BIRTHPLACE;
        
        $pdf->AddPage();
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','UB',12);
        $y=50;
        $pdf->SetXY(60,$y);
        $pdf->MultiCell(90,6,"ATTESTATION DE PRESENCE","","C");
        $y = $y+20;
        $pdf->SetXY(13,$y);
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(180,6,$lig1,"","J");
        $y = $y + 13;
        $pdf->SetXY(13,$y);
        $pdf->MultiCell(180,6,$lig2,"","J");
        $y = $y + 14; //97
        $pdf->SetXY(13,$y);
        $pdf->MultiCell(180,6,$lig3,"","J");
        
        $y = $y + 7; //104
        $pdf->SetXY(40,$y);
        $pdf->MultiCell(90,6,$TC_LIBELLE." ".$nom_prenom,"","L");
        $y = $y + 6; //110
        $pdf->SetXY(40,$y);
        $pdf->MultiCell(90,6,$born,"","L");
        
        if ( $C_NAME <> 'Particulier' ) {
            $y = $y + 10;//117
            $pdf->SetXY(40,$y);
            $pdf->MultiCell(90,6,"De l'entreprise : ".$C_NAME,"","L");
        }
        
        $pdf->SetFont('Arial','B',10);
        $y = $y + 16; //133
        $pdf->SetXY(13,$y);
        $pdf->MultiCell(180,6,$lig4,"","L");    
        
        $pdf->SetFont('Arial','',10);
        $y = $y + 9;
        $pdf->SetXY(40,$y);
        $pdf->MultiCell(180,6,"Session du ".$periode,"","L");
        $y = $y + 6; //148
        $pdf->SetXY(40,$y); 
        $pdf->MultiCell(160,6,"Lieu: ".$lieu." ".$E_ADDRESS,"","L");
        if ( strlen($E_ADDRESS) > 80 ) $y = $y+ 12;
        else $y = $y +6;
        $pdf->SetXY(40,$y);
        $pdf->MultiCell(180,6,"Durée: ".$E_DUREE_TOTALE." heures.","","L");

        $pdf->SetXY(110,208);    
        $pdf->MultiCell(100,6,$lig5,"","J");
        $pdf->SetXY(110,214);
        $pdf->SetFont('Arial','U',10);
        $pdf->MultiCell(100,6,$lig6,"","J");
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(110,222);    
        $pdf->MultiCell(100,6,"Fait à ".$lieu.", le ".$maxdate,"","J");

        
        $pdf->SetXY(13,265);
        $pdf->SetFont('Arial','',6);
        $pdf->MultiCell(100,5,$printed_by,"","L");
    };
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->Output("Fiche_de_presence.pdf",'I');
}


//=====================================================
// Rapport main courante
//=====================================================

if ( $mode == 11 ) {
    include_once ("config_doc.php");
    include_once ("lib/fpdf/nel.php");
    $printPageNum=true;

    if ( $ACCES_RESTREINT == 1 ) {
        if (! check_rights($id,26, intval("$section")) and ! is_inscrit($id,$evenement) and ! in_array($id, $chefs) and $E_CREATED_BY <> $id) {
                write_msgbox("ERREUR", $error_pic, "Vous n'avez pas les permissions pour voir <br>l'événement n°".$evenement."<br>
                    Car son accès est restreint aux inscrits et aux personnes ayant la permission n°26.<p align=center>
                    <a href=\"javascript:history.back(1)\">Retour</a> ",10,0);
                exit;
        }
    }
    $pdf= new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator($organisateur);
    $pdf->SetAuthor($organisateur);
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Rapport");
    $pdf->SetAutoPageBreak(0);

    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14); 
    $pdf->SetXY(30,48);
    if ( $te_code == 'REU' or $te_code == 'WEB' or $CEV_CODE == 'C_STA') $pdf->MultiCell(150,12,"Compte rendu de réunion","1","C");
    else $pdf->MultiCell(150,12,"Rapport: ".$type_evenement,"1","C");
        
    $pdf->SetFont('Arial','',11);
    $pdf->SetXY(15,64);

    $pdf->MultiCell(180,5,$description.", ".$periode.", ".$lieu,"","J");
    $y=76;
    
    $evts=get_event_and_renforts($evenement);
    // responsable
    if ( $id_responsable_evt > 0 and $show_responsable == 1) {
        $org=$organisation_name;
        $pdf->SetXY(20,$y);
        $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
        $pdf->MultiCell(180,5,"Responsable $org: ".$responsable_evt,"","L");
        $y=$y+5;
    }
    if ( $te_code == 'REU' or $te_code == 'WEB' or $CEV_CODE == 'C_STA') {
        $pdf->SetXY(15,$y);
        $pdf->MultiCell(180,5,"Veuillez trouver ci dessous le compte rendu de la réunion:","","L");
        $y=$y+5;
    }
    else {
        if ( $show_nombres == 1) {
            // nb intervenants
            $query="select count(distinct P_ID) as NB from evenement_participation
            where E_CODE in (".$evts.") and EP_ABSENT=0";
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $NB1=$row["NB"];
            
            if ( $TE_VICTIMES == 1 ) $t="Intervenants";
            else $t="Participants";
            
            $pdf->SetXY(20,$y);
            $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
            $pdf->MultiCell(180,5,$t.": ".$NB1." personnes","","L");
            $y=$y+5;
            
            // messages
            $pdf->SetXY(20,$y);
            if ( $mode == 12 or $mode == 14) $msg = get_messages_stats($evenement, $important=true );
            else $msg = get_messages_stats($evenement, $important=false );
            $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
            $pdf->MultiCell(180,5, $msg,"","L");
            $y = $y+5;
            
            // statistiques principales
            $nbstats=count_entities('type_bilan', $where_clause="TE_CODE='".$te_code."'");
            if ( $nbstats > 0 ) {
                $pdf->SetXY(20,$y);
                $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
                $pdf->MultiCell(180,5, "Statistiques globales:","","L");
                $y = $y+5;
                $stats=get_main_stats($evenement,$html=false);
                $pdf->SetFont('Arial','',10);
                $lst = explode (", ", $stats);
                $m =  count($lst);
                for($i=0; $i < $m ; $i++){
                    $pdf->SetXY(30,$y);
                    $pdf->Image("./images/bullet_black_small.png", 25, $y, 3);
                    $pdf->MultiCell(180,5, $lst[$i],"","L");
                    $y = $y+5;
                }
            }
            $y = $y+3;
        }
    
        if ( $show_statistiques == 1) {
            // interventions et victimes
            $pdf->SetFont('Arial','',11);
            $pdf->SetXY(20,$y);
            $iv=get_inter_victimes_stats($evenement);
            $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
            $pdf->MultiCell(180,5, $iv,"","L");
            $y = $y+5;
            // détail victimes
            $stats=get_detailed_stats($evenement);
            $pdf->SetFont('Arial','',10);
            $lst = explode (", ", $stats);
            $m =  count($lst);
            for($i=0; $i < $m ; $i++){
                if ( $lst[$i]<> '' ) {
                    $pdf->SetXY(30,$y);
                    $pdf->Image("./images/bullet_black_small.png", 25, $y, 3);
                    $pdf->MultiCell(180,5, $lst[$i],"","L");
                    $y = $y+5;
                }
            }
        }

    }
    
    if ( $show_vehicules == 1) {
        // nb véhicules
        $query="select TV_CODE, count(distinct evenement_vehicule.V_ID)
                from evenement_vehicule, vehicule
                where E_CODE in (".$evts.") 
                and vehicule.V_ID = evenement_vehicule.V_ID
                group by TV_CODE";
        $result=mysqli_query($dbc,$query);
        $NB=0;$detail="";
        while ($row=mysqli_fetch_array($result)) {
            $TV_CODE=$row[0];
            $NB1=$row[1];
            $NB=$NB+$NB1;
            $detail .= $NB1." ".$TV_CODE.", ";
        }
        
        $y=$y+5;
        $pdf->SetXY(20,$y);
        $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
        if ( $NB > 1 ) $engages="véhicules";
        else $engages="véhicule";
        $pdf->MultiCell(180,5,"Véhicules engagés: ".$NB." ".$engages,"","L");
        if ( $detail <> "") {
            $detail = substr($detail,0,strlen($detail)-2);
            $y=$y+5;
            $pdf->SetXY(23,$y);
            $pdf->SetFont('Arial','I',9);
            $pdf->MultiCell(180,5,$detail,"","L");
        }
    }

    if ( $show_materiel == 1) {
        // nb matériels
        $query="select tm.TM_CODE, sum(em.EM_NB) as NB 
        from evenement_materiel em, materiel m, type_materiel tm
        where em.E_CODE in (".$evts.")
        and em.MA_ID = m.MA_ID
        and tm.TM_ID = m.TM_ID
        and m.MA_PARENT is null
        group by tm.TM_CODE";
        $result=mysqli_query($dbc,$query);
        $NB=0;$detail="";
        while ($row=mysqli_fetch_array($result)) {
            $TV_CODE=$row[0];
            $NB1=$row[1];
            $NB=$NB+$NB1;
            $detail .= $NB1." ".$TV_CODE.", ";
        }
        if ($mode == 14) $detail="";
        
        $y=$y+5;
        $pdf->SetXY(20,$y);
        $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
        $pdf->SetFont('Arial','',11);
        if ( $NB > 1 ) $engages="éléments";
        else $engages="élément";
        $pdf->MultiCell(180,5,"Matériel engagé: ".$NB." ".$engages,"","L");
        if ( $detail <> "" ) {
            $detail = substr($detail,0,strlen($detail)-2);
            $y=$y+5;
            $pdf->SetXY(23,$y);
            $pdf->SetFont('Arial','I',9);
            $pdf->MultiCell(180,5,$detail,"","L");
        }
        $y= $y + round(strlen($detail)/ 200) * 8 + 3;
    }
    $rl = 75; // nb max de caractères par ligne de commentaire
    $np = 45; // $y sur nouvelle page

    if ( $show_cav ==  1 ) {
        // centre accueil victimes
        $query="select victime.CAV_ID, VI_ID, VI_NOM, VI_PRENOM, VI_SEXE, VI_ADDRESS, date_format(VI_BIRTHDATE, '%d-%m-%Y') VI_BIRTHDATE, VI_SEXE,
                VI_DETRESSE_VITALE, VI_INFORMATION, VI_SOINS, VI_MEDICALISE, VI_TRANSPORT, VI_VETEMENT, VI_ALIMENTATION, VI_TRAUMATISME, VI_REPOS, VI_DECEDE, VI_MALAISE, VI_PAYS,
                victime.D_CODE,victime.T_CODE,VI_COMMENTAIRE, VI_REFUS, VI_IMPLIQUE, VI_NUMEROTATION, destination.D_NAME, transporteur.T_NAME, VI_AGE, pays.NAME NOM_PAYS, VI_REPARTI,
                date_format(CAV_ENTREE, '%d-%m-%Y') DATE_ENTREE, date_format(CAV_ENTREE, '%H:%i') HEURE_ENTREE,
                date_format(CAV_SORTIE, '%d-%m-%Y') DATE_SORTIE, date_format(CAV_SORTIE, '%H:%i') HEURE_SORTIE,
                date_format(HEURE_HOPITAL, '%H:%i') HEURE_HOPITAL,
                CAV_REGULATED, CAV_NAME
                from victime left join destination on victime.D_CODE = destination.D_CODE
                left join transporteur on victime.T_CODE = transporteur.T_CODE
                join centre_accueil_victime cav on victime.CAV_ID = cav.CAV_ID,
                pays
                where pays.ID=victime.VI_PAYS
                and cav.E_CODE=".$evenement."
                order by cav.CAV_NAME, DATE_ENTREE";
        $result=mysqli_query($dbc,$query);
        if ( @mysqli_num_rows($result) > 0 ) {
            $prev_CAV_NAME="";
            while (custom_fetch_array($result) ) {  
                $VI_NOM=strtoupper($VI_NOM);
                $VI_PRENOM=my_ucfirst($VI_PRENOM);
                $VI_COMMENTAIRE=remove_returns($VI_COMMENTAIRE);
                $numcav=intval($CAV_ID);
         
                if ( $y > 240 ) {
                    $pdf->AddPage();
                    $y=$np;
                }
                if ( $CAV_NAME <> $prev_CAV_NAME ) {
                    $y=$y+7;
                    $pdf->Image("./images/cav_small.png", 15, $y+1, 4);
                    $pdf->SetFont('Arial','B',14);
                    $pdf->SetTextColor(0,0,0);
                    $pdf->SetXY(20,$y);
                    $pdf->MultiCell(180,6,"Centre d'accueil des victimes - ".$CAV_NAME,"","L");
                    $prev_CAV_NAME=$CAV_NAME;
                }
                
                $pdf->SetTextColor(0,0,200);
                $pdf->SetFont('Arial','I',9);
                $txt=$DATE_ENTREE." à ".$HEURE_ENTREE;
                if ( $HEURE_SORTIE <> '' ) $txt .= ", sortie à ".$HEURE_SORTIE;
                $txt .=" - Victime: ";
                if ( $VI_NUMEROTATION <> "" ) $txt .= "V".$VI_NUMEROTATION.", ";
                $txt .= $VI_PRENOM." ".$VI_NOM;
                if ( $VI_SEXE == 'F' ) $txt .= ", féminin";
                else $txt .= ", masculin";
                if ($VI_AGE <> "" ) $txt .= " ".$VI_AGE." ans";
                if ( $VI_PAYS > 0 ) $txt .= ", ".$NOM_PAYS;
                
                if ( $VI_BIRTHDATE <> '' ) {
                    if ( $VI_SEXE == 'M' ) $txt .= ", né le ";
                        else  $txt .= ", née le ";
                        $txt .= $VI_BIRTHDATE;
                }
                $y=$y+7;
                $pdf->SetXY(25,$y);
                $pdf->Image("./images/bullet_black_small.png", 20, $y+1, 4);
                $pdf->MultiCell(170,5, $txt,"","J");
                if ( $VI_ADDRESS <> "" ) {
                    $y = $y+5;
                    $pdf->SetXY(25,$y);
                    $pdf->MultiCell(170,5, "Adresse: ".$VI_ADDRESS,"","J");
                }
                $comments = write_victime_comments($VI_COMMENTAIRE);
                $hauteur_commentaire=(strlen($comments) / $rl) * 4;
                if ( $hauteur_commentaire + $y > 255 ) {
                    $pdf->AddPage();
                    $y=$np;
                }
                else {
                    $y = $y+5;
                }
                $pdf->SetXY(25,$y);
                $pdf->MultiCell(170,5,$comments,"","J");
                $y = $y + $hauteur_commentaire ;
            }
            $pdf->SetTextColor(0,0,0);
        }
    }
    // interventions
    $query="select e.EL_ID, e.E_CODE, e.TEL_CODE ,date_format(e.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(e.EL_DEBUT,'%H:%i') HEURE_DEBUT,
        date_format(e.EL_FIN,'%d-%m') DATE_FIN, date_format(e.EL_FIN,'%H:%i') HEURE_FIN, e.EL_IMPORTANT,
        e.EL_TITLE, e.EL_ADDRESS,e.EL_COMMENTAIRE,e.EL_RESPONSABLE, p.P_NOM, p.P_PRENOM, Null as E_LIBELLE, 
        tel.TEL_DESCRIPTION, e.EL_ORIGINE, e.EL_DESTINATAIRE, TIMESTAMPDIFF(MINUTE,e.EL_DEBUT,e.EL_DATE_ADD) TIMEDIFF ,
        date_format(e.EL_DATE_ADD,'le %d-%m-%Y à %H:%i') DATE_ADD,
        date_format(e.EL_SLL,'%H:%i') HEURE_SLL,
        TIMESTAMPDIFF(MINUTE,e.EL_DATE_ADD,NOW()) NEW, date_format(e.EL_DEBUT,'%Y-%m-%d') EL_DEBUT
        from evenement_log e left join pompier p on p.P_ID = e.EL_RESPONSABLE,
        type_evenement_log tel
        where tel.TEL_CODE = e.TEL_CODE
        and e.EL_IMPRIMER=1
        and e.E_CODE=".$evenement;
    $query.=" union select e.EL_ID, e.E_CODE, e.TEL_CODE ,date_format(e.EL_DEBUT,'%d-%m-%Y') DATE_DEBUT, date_format(e.EL_DEBUT,'%H:%i') HEURE_DEBUT, date_format(e.EL_FIN,'%d-%m') DATE_FIN, date_format(e.EL_FIN,'%H:%i') HEURE_FIN, e.EL_IMPORTANT,
    e.EL_TITLE, e.EL_ADDRESS,e.EL_COMMENTAIRE,e.EL_RESPONSABLE, p.P_NOM, p.P_PRENOM, evenement.E_LIBELLE,
    tel.TEL_DESCRIPTION, e.EL_ORIGINE, e.EL_DESTINATAIRE, TIMESTAMPDIFF(MINUTE,e.EL_DEBUT,e.EL_DATE_ADD) TIMEDIFF ,
    date_format(e.EL_DATE_ADD,'le %d-%m-%Y à %H:%i') DATE_ADD,
    date_format(e.EL_SLL,'%H:%i') HEURE_SLL,
    TIMESTAMPDIFF(MINUTE,e.EL_DATE_ADD,NOW()) NEW, date_format(e.EL_DEBUT,'%Y-%m-%d') EL_DEBUT
    FROM evenement_log e left join pompier p on p.P_ID = e.EL_RESPONSABLE,evenement, type_evenement_log tel
    WHERE tel.TEL_CODE = e.TEL_CODE
    AND e.E_CODE = evenement.E_CODE
    AND evenement.E_PARENT = $evenement
    AND e.EL_IMPRIMER=1
    AND e.EL_IMPORTANT = 1";
    $query .=" order by EL_DEBUT asc, HEURE_DEBUT asc";
    $result=mysqli_query($dbc,$query);
    if ( @mysqli_num_rows($result) > 0 ) {
        $prev_DATE_DEBUT="";
        while (custom_fetch_array($result) ) {
            $P_NOM=strtoupper($P_NOM);
            $P_PRENOM=my_ucfirst($P_PRENOM);
            if ( $TEL_CODE == 'M' ) $TEL_DESCRIPTION="";
            if ( $TEL_CODE =='I' ) {
                if ( $EL_IMPORTANT == 1 ) $img="intervention_important.png";
                else $img="intervention.png";
            }
            else if ( $EL_IMPORTANT == 1 ) $img="important.png";
            else $img="";
            if ( $HEURE_FIN == '00:00' ) $HEURE_FIN ='';
            if ( $EL_ORIGINE <> '' or $EL_DESTINATAIRE <> '' ) {
                    $fromto = $EL_ORIGINE;
                    if ( $EL_DESTINATAIRE <> '' ) $fromto .=" => ".$EL_DESTINATAIRE;
                    $fromto .=" : ";
            }
            else $fromto='';
            
            if ( $y > 240 ) {
                $pdf->AddPage();
                $y=$np;
            }
            if ( $DATE_DEBUT <> $prev_DATE_DEBUT and $te_code <> 'REU' and $te_code <> 'WEB' and $CEV_CODE <> 'C_STA') {
                $pdf->SetFont('Arial','B',14);
                $y=$y+7;
                $pdf->SetXY(15,$y);
                $pdf->MultiCell(180,6,"Le ".$DATE_DEBUT,"","L");
                $prev_DATE_DEBUT=$DATE_DEBUT;
            }
            $y=$y+6;
            if ( $img <> "" ) $pdf->Image("./images/".$img, 20, $y+1, 4);

            if ( $te_code == 'REU' or $te_code == 'WEB' or $CEV_CODE == 'C_STA') {
                $pdf->SetXY(22,$y);
                $pdf->SetFont('Arial','B',11);
                $pdf->MultiCell(180,6,$EL_TITLE,"","L");
            }
            else {
                $pdf->SetXY(26,$y);
                $pdf->SetFont('Arial','B',11);
                if($E_CODE != $evenement) $pdf->MultiCell(180,6,"Renfort ($E_LIBELLE) ".$HEURE_DEBUT.": ".$TEL_DESCRIPTION." ".$fromto." ".$EL_TITLE,"","L");
                else $pdf->MultiCell(180,6,$HEURE_DEBUT.": ".$TEL_DESCRIPTION." ".$fromto." ".$EL_TITLE,"","L");

                if ( strlen($fromto." ".$EL_TITLE) > 75 ) $y=$y+7;
                else $y=$y+2;
            }
            $detail="";
            // équipes engagées sur l'intervention
            $query5="select ee.EE_ID, ee.EE_NAME
            from evenement_equipe ee, intervention_equipe ie 
            where ie.E_CODE=ee.E_CODE 
            and ie.EE_ID = ee.EE_ID
            and ee.E_CODE=".$evenement." 
            and ie.EL_ID =  ".$EL_ID."
            order by ee.EE_ORDER ";
            $result5=mysqli_query($dbc,$query5);
            $nbequipes=mysqli_num_rows($result5);
            $eqnames="";
            while ($row5=@mysqli_fetch_array($result5)) {
                $eqnames .= " ".$row5["EE_NAME"].",";
            }
            if ( $nbequipes > 0 ) {
                if ( $nbequipes == 1 ) $detail .="Equipe engagée: ";
                else $detail .="Equipes engagées: ";
                $detail .=rtrim($eqnames,",").". ";
            }
            
            if ( $EL_RESPONSABLE <> "" ) $detail .="Responsable: ".$P_PRENOM." ".$P_NOM.". ";
            if ( $HEURE_SLL <> "" ) $detail .="Arrivée sur les lieux: ".$HEURE_SLL.". ";
            if ( $HEURE_FIN <> "" ) $detail .="Fin intervention: ".$HEURE_FIN.". ";
            if ( $detail <> "" ) {
                $pdf->SetFont('Arial','',10);
                $y = $y+6;
                $pdf->SetXY(28,$y);
                $pdf->MultiCell(170,5,$detail,"","J");
            }
            if ( strlen($detail) > 100 ) $y = $y+6;
            if ( $EL_ADDRESS <> '' ) {
                $pdf->SetFont('Arial','U',9);
                $y = $y+6;
                $pdf->SetXY(28,$y);
                $pdf->MultiCell(170,5,"Adresse intervention: ".$EL_ADDRESS,"","J");
            }
            if ( $EL_COMMENTAIRE <> "" ) $y =$y +6;
            $pdf->SetFont('Arial','',9);
            $y = $pdf->WriteBigTextBlock($EL_COMMENTAIRE,28,$y);
            
            // victimes
            if ( $TEL_CODE == 'I' ) {
                $pdf->SetTextColor(0,0,200);
                $pdf->SetFont('Arial','I',9);
                
                $query2="select VI_ID, EL_ID, VI_NOM, VI_PRENOM, VI_SEXE, VI_ADDRESS, date_format(VI_BIRTHDATE, '%d-%m-%Y') VI_BIRTHDATE, VI_SEXE,
                VI_DETRESSE_VITALE, VI_INFORMATION, VI_SOINS, VI_MEDICALISE, VI_TRANSPORT, VI_VETEMENT, VI_ALIMENTATION, VI_TRAUMATISME, VI_REPOS, VI_REPARTI, VI_DECEDE, VI_MALAISE, VI_PAYS,
                victime.D_CODE,VI_COMMENTAIRE, VI_REFUS, VI_IMPLIQUE, VI_NUMEROTATION, destination.D_NAME, transporteur.T_CODE, lower(transporteur.T_NAME) T_NAME, pays.NAME as NOM_PAYS, 
                date_format(HEURE_HOPITAL, '%H:%i') HEURE_HOPITAL,
                VI_AGE AS age
                from victime, destination , pays, transporteur 
                where EL_ID=".$EL_ID."
                and victime.D_CODE = destination.D_CODE
                and victime.T_CODE = transporteur.T_CODE
                and victime.VI_PAYS = pays.ID
                order by VI_NUMEROTATION asc";

                $result2=mysqli_query($dbc,$query2);
                while ( custom_fetch_array($result2)) {
                    $VI_NOM=strtoupper($VI_NOM);
                    $VI_PRENOM=my_ucfirst($VI_PRENOM);
                    $VI_COMMENTAIRE=remove_returns($VI_COMMENTAIRE);
                    if ( $VI_COMMENTAIRE <> "" ) $VI_COMMENTAIRE .=". ";
                    $VI_PAYS=intval($VI_PAYS);

                    $y = $y+6;
                    $pdf->SetXY(30,$y);
                
                    $txt="Personne prise en charge: ";
                    if ( $VI_NUMEROTATION <> "" ) $txt .= "V".$VI_NUMEROTATION.", ";
                    $txt .= $VI_PRENOM." ".$VI_NOM;
                    if ( $VI_SEXE == 'F' ) $txt .= ", féminin";
                    else $txt .= ", masculin";
                    if ($age <> "" ) $txt .= " ".$age." ans";
                    if ( $VI_PAYS > 0 ) $txt .= ", ".$NOM_PAYS;
                    
                    if ( $VI_BIRTHDATE <> '' ) {
                        if ( $VI_SEXE == 'M' ) $txt .= ", né le ";
                        else  $txt .= ", née le ";
                        $txt .= $VI_BIRTHDATE;
                    }
                    $pdf->MultiCell(170,5, $txt,"","J");
                    if ( $VI_ADDRESS <> "" ) {
                        $y = $y+5;
                        $pdf->SetXY(30,$y);
                        $pdf->MultiCell(170,5, "Adresse: ".$VI_ADDRESS,"","J");
                    }
                    $comments = write_victime_comments($VI_COMMENTAIRE);
                    $hauteur_commentaire=(strlen($comments) / $rl) * 4;
                    if ( $hauteur_commentaire + $y > 255 ) {
                        $pdf->AddPage();
                        $y=$np;
                    }
                    else {
                        $y = $y+6;
                    }
                    $pdf->SetXY(30,$y);
                    $pdf->MultiCell(170,5,$comments,"","J");
                    $y = $y + $hauteur_commentaire ;
                }
                $pdf->SetTextColor(0,0,0);
            }

        }

    }
    $pdf->SetXY(10,272);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->Output("Rapport_".$evenement.".pdf",'I');
}

//=============================
// note de frais
//=============================

else if ( $mode == 13 ) {

    $query="select date_format( nf.NF_CREATE_DATE , '%d-%m-%Y %H:%i') NF_CREATE_DATE,  
            date_format( nf.NF_STATUT_DATE , '%d-%m-%Y %H:%i') NF_STATUT_DATE,
            date_format( nf.NF_VALIDATED_DATE , '%d-%m-%Y %H:%i') NF_VALIDATED_DATE,
            date_format( nf.NF_VALIDATED2_DATE , '%d-%m-%Y %H:%i') NF_VALIDATED2_DATE,
            date_format( nf.NF_REMBOURSE_DATE , '%d-%m-%Y %H:%i') NF_REMBOURSE_DATE, 
            nf.NF_CREATE_BY, nf.NF_STATUT_BY, nf.NF_REMBOURSE_BY,  nf.NF_VALIDATED_BY, nf.NF_VALIDATED2_BY, nf.NF_FRAIS_DEP,
            p.P_NOM, p.P_PRENOM, nf.FS_CODE,p.P_ADDRESS, p.P_PHONE, p.P_CITY, p.P_ZIP_CODE,
            p1.P_NOM 'P_NOM1', p1.P_PRENOM 'P_PRENOM1',
            p2.P_NOM 'P_NOM2', p2.P_PRENOM 'P_PRENOM2',
            p3.P_NOM 'P_NOM3', p3.P_PRENOM 'P_PRENOM3',
            p4.P_NOM 'P_NOM4', p4.P_PRENOM 'P_PRENOM4',
            p5.P_NOM 'P_NOM5', p5.P_PRENOM 'P_PRENOM5',
            nf.TOTAL_AMOUNT 'SUM', nfts.FS_DESCRIPTION, nf.TM_CODE, nfts.FS_CLASS, nf.NF_NATIONAL, nf.NF_DEPARTEMENTAL, nf.NF_DON,
            nf.COMMENT, nf.NF_CODE1,nf.NF_CODE2,nf.NF_CODE3,
            YEAR(NF_CREATE_DATE) year, MONTH(NF_CREATE_DATE) month, tm.TM_DESCRIPTION, s.S_ID, s.S_CODE, s.S_DESCRIPTION
            from note_de_frais nf 
            join pompier p on p.P_ID = nf.P_ID
            left join pompier p1 on p1.P_ID = nf.NF_CREATE_BY
            left join pompier p2 on p2.P_ID = nf.NF_STATUT_BY
            left join pompier p3 on p3.P_ID = nf.NF_REMBOURSE_BY
            left join pompier p4 on p4.P_ID = nf.NF_VALIDATED_BY
            left join pompier p5 on p5.P_ID = nf.NF_VALIDATED2_BY,
            note_de_frais_type_statut nfts, note_de_frais_type_motif tm, section s
            where nfts.FS_CODE = nf.FS_CODE
            and tm.TM_CODE = nf.TM_CODE
            and nf.S_ID = s.S_ID
            and nf.NF_ID=".$note;
    $result=mysqli_query($dbc,$query);
    $row=custom_fetch_array($result);
    
    $logo=get_logo_specific($S_ID, $NF_NATIONAL);

    $pdf= new FPDF('L','mm','A4');
    $pdf->AliasNbPages();
    $pdf->SetCreator("$cisname");
    $pdf->SetAuthor("$cisname");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Note de frais");
    $pdf->SetAutoPageBreak(0);
    $pdf->AliasNbPages();
    $y=54;    
    $pdf->AddPage();
    $pdf->Image($logo,5,5,20);
    $pdf->SetTextColor(13,53,148);

    if ( $NF_FRAIS_DEP == 1 ) {
        //specific 
        if ( $cisname == 'FA/SPP-PATS' and $S_ID > 1 ) $BENEFICIAIRE  = "SA/SPP-PATS ".substr($S_CODE,0,2);
        else $BENEFICIAIRE = $S_CODE;
        if ( $S_DESCRIPTION <> "" ) $BENEFICIAIRE.=" -".$S_DESCRIPTION;
        $BENEFICIAIRE .= " (frais pour ".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM).")";
    }
    else $BENEFICIAIRE = my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)." (".$S_CODE." - ".$S_DESCRIPTION.")";
    $ADDRESS=$P_ADDRESS." - ".$P_ZIP_CODE." - ".$P_CITY;
    
    $created=" Le ".$NF_CREATE_DATE." par ".my_ucfirst($P_PRENOM1)." ".strtoupper($P_NOM1);
    $signature_file1=get_signature($NF_CREATE_BY);
    
    if ( $NF_VALIDATED_DATE <> "" ) {
        $valid=" Le ".$NF_VALIDATED_DATE." par ".my_ucfirst($P_PRENOM4)." ".strtoupper($P_NOM4);
        $signature_file2=get_signature($NF_VALIDATED_BY);
    }
    else $valid="";
    if ( $NF_VALIDATED2_DATE <> "" ) {
        $valid2=" Le ".$NF_VALIDATED2_DATE." par ".my_ucfirst($P_PRENOM5)." ".strtoupper($P_NOM5);
        $signature_file3=get_signature($NF_VALIDATED2_BY);
    }
    else $valid2="";
    if ( $FS_CODE == 'REMB' ) {
        $rembourse="le ".$NF_REMBOURSE_DATE." par ".my_ucfirst($P_PRENOM3)." ".strtoupper($P_NOM3);
        $signature_file4=get_signature($NF_REMBOURSE_BY);
    }
    else $rembourse="";

    $num_comptable = $NF_CODE1." / ".str_pad($NF_CODE2, 2, '0', STR_PAD_LEFT)." / ".str_pad($NF_CODE3,3, '0' , STR_PAD_LEFT);
    $pdf->SetFont('Arial','B',14);
    $pdf->Text(60,17,"Note de frais ".$note.". N° comptable  ".$num_comptable);
    $pdf->SetXY(0,10);
    
    if ( $NF_NATIONAL == 1 ) $TM_DESCRIPTION ="National, ".$TM_DESCRIPTION;
    else if ( $NF_DEPARTEMENTAL == 1 ) $TM_DESCRIPTION ="Départemental, ".$TM_DESCRIPTION;
    $pdf->SetFont('Arial','B',11);
    $pdf->MultiCell(0,10,"$TM_DESCRIPTION",0,"R",0);

    $y=35;
    if ( $evenement > 0 ) {
        $pdf->SetFont('Arial','B',11);
        $pdf->Text(12,$y,"Evenement concerné: ");
        $pdf->SetFont('Arial','',10);
        $pdf->Text(60,$y,$type_evenement." - ".$description." - ".$periode);
        $y += 6;
    }
    $pdf->SetFont('Arial','B',11);
    $pdf->Text(12,$y,"Bénéficiaire: ");
    $pdf->SetFont('Arial','',10);
    $pdf->Text(60,$y,$BENEFICIAIRE);
    $y += 6;
    if ( $syndicate == 0 ) {
        $pdf->SetFont('Arial','B',11);
        $pdf->Text(12,$y,"Adresse:");
        $pdf->SetFont('Arial','',10);
        $pdf->Text(60,$y,$ADDRESS);
        $pdf->SetFont('Arial','B',11);
        $pdf->Text(210,$y,"Téléphone:");
        $pdf->SetFont('Arial','',10);
        $pdf->Text(235,$y,$P_PHONE);
        $y += 6;
    }
    $pdf->SetFont('Arial','B',11);
    $pdf->Text(12,$y,"Commentaire:");
    $pdf->SetFont('Arial','',9);
    $pdf->Text(60,$y,$COMMENT);
    $y += 6;
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFillColor(200);
    $hauteur=7;
    $pdf->SetXY(10,$y);
    $pdf->SetFont('Arial','B',10);
    $pdf->MultiCell(25,$hauteur,"Date",1,"L",true);    
    $pdf->SetXY(35,$y);
    $pdf->MultiCell(35,$hauteur,"Lieu",1,"C",true);
    $pdf->SetXY(70,$y);
    $pdf->MultiCell(40,$hauteur,"Motif",1,"C",true);
    $pdf->SetXY(110,$y);
    $pdf->MultiCell(50,$hauteur,"Type",1,"C",true);
    $pdf->SetXY(160,$y);
    $pdf->MultiCell(80,$hauteur,"Commentaire",1,"C",true);
    $pdf->SetXY(240,$y);
    $pdf->MultiCell(20,$hauteur,"Quantité",1,"C",true);
    $pdf->SetXY(260,$y);
    $pdf->MultiCell(20,$hauteur,"Montant",1,"C",true);

    $query="select date_format( nf.NF_CREATE_DATE , '%d-%m-%Y') NF_CREATE_DATE, nf.FS_CODE,
            nfd.NFD_ID, nfd.AMOUNT, nfd.LIEU,
            date_format( nfd.NFD_DATE_FRAIS , '%d-%m-%Y') NFD_DATE_FRAIS, 
            nfd.TF_CODE, nfd.NFD_DESCRIPTION, nfd.QUANTITE,
            nftf.TF_DESCRIPTION, nftf.TF_CATEGORIE, nftf.TF_PRIX_UNITAIRE, nftf.TF_UNITE
            from note_de_frais nf, note_de_frais_type_frais nftf, note_de_frais_detail nfd 
            where nfd.NF_ID=nf.NF_ID
            and nf.NF_ID = ".$note."
            and nftf.TF_CODE = nfd.TF_CODE
            order by NFD_ORDER";
    $result=mysqli_query($dbc,$query);

    $pdf->SetFillColor(255,255,255); 
    $pdf->SetFont('Arial','',9);
    while (custom_fetch_array($result)) {
        if ( $AMOUNT > 0 ) $AMOUNT = my_number_format($AMOUNT)." ".$default_money_symbol;
        $LIEU=substr($LIEU,0,18); 
        $NFD_DESCRIPTION=substr($NFD_DESCRIPTION,0,60);
        if ( $TF_PRIX_UNITAIRE <> "" ) {
            $QUANTITE .= " ".$TF_UNITE;
        }
        
        $y = $y +$hauteur;
        
        $pdf->SetXY(10,$y);
        $pdf->MultiCell(25,$hauteur,$NFD_DATE_FRAIS,1,"L",true);    
        $pdf->SetXY(35,$y);
        if ( strlen($LIEU) > 15 ) $pdf->SetFont('Arial','',8);
        $pdf->MultiCell(35,$hauteur,$LIEU,1,"C",true);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(70,$y);
        $pdf->MultiCell(40,$hauteur,$TF_CATEGORIE,1,"C",true);
        $pdf->SetXY(110,$y);
        if ( strlen($TF_DESCRIPTION) > 20 ) $pdf->SetFont('Arial','',8);
        $pdf->MultiCell(50,$hauteur,$TF_DESCRIPTION,1,"C",true);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(160,$y);
        if ( strlen($NFD_DESCRIPTION) > 30 ) $pdf->SetFont('Arial','',8);
        $pdf->MultiCell(80,$hauteur,$NFD_DESCRIPTION,1,"C",true);
        $pdf->SetFont('Arial','',10);
        $pdf->SetXY(240,$y);
        $pdf->MultiCell(20,$hauteur,$QUANTITE,1,"C",true);
        $pdf->SetXY(260,$y);
        $pdf->MultiCell(20,$hauteur,$AMOUNT,1,"C",true);
    }
    $y = $y +  $hauteur;
    $pdf->SetFont('Arial','B',10);    
    $pdf->SetXY(240,$y);
    $pdf->MultiCell(20,$hauteur,"Total",1,"C",true);
    $pdf->SetXY(260,$y);
    $pdf->MultiCell(20,$hauteur,my_number_format($SUM)." ".$default_money_symbol,1,"C",true);
    
    if ( $syndicate == 1 ) {
        $label1="Validation trésorier";
        $label2="Validation président";
    } else {
        $label1="Validation";
        $label2="Validation 2";
    }
    
    $y = $y +  5 + $hauteur;
    $pdf->SetFillColor(200);
    $pdf->SetFont('Arial','B',10);
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(68,$hauteur,"Création",1,"C",true);    
    $pdf->SetXY(78,$y);
    $pdf->MultiCell(68,$hauteur,$label1,1,"C",true);
    $pdf->SetXY(146,$y);
    $pdf->MultiCell(67,$hauteur,$label2,1,"C",true);
    $pdf->SetXY(213,$y);
    $pdf->MultiCell(67,$hauteur,"Remboursement",1,"C",true);

    $y = $y +$hauteur;
    
    $pdf->SetFillColor(255,255,255); 
    $pdf->SetXY(10,$y);
    $pdf->SetFont('Arial','',7);
    $pdf->MultiCell(68,$hauteur,$created,1,"L",true);    
    $pdf->SetXY(78,$y);
    $pdf->MultiCell(68,$hauteur,$valid,1,"L",true);
    $pdf->SetXY(146,$y);
    $pdf->MultiCell(67,$hauteur,$valid2,1,"L",true);
    $pdf->SetXY(213,$y);
    $pdf->MultiCell(67,$hauteur,$rembourse,1,"L",true);
    
    $y = $y +$hauteur;
    $hauteur = $hauteur + 25;
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(68,$hauteur,"",1,"L",true);    
    if (  @is_file($signature_file1) ) $pdf->Image($signature_file1, 15, $y+1, 40);
    else $pdf->Text(15, $y+4,"Pas d'image pour la signature");
    $pdf->SetXY(78,$y);
    $pdf->MultiCell(68,$hauteur,"",1,"L",true);
    if ( $valid <> "" ) {
        if (  @is_file($signature_file2) ) $pdf->Image($signature_file2, 82, $y+1, 40);
        else $pdf->Text(82, $y+4,"Pas d'image pour la signature");
    }
    $pdf->SetXY(146,$y);
    $pdf->MultiCell(67,$hauteur,"",1,"L",true);
    if ( $valid2 <> "" ) {
        if (  @is_file($signature_file3) ) $pdf->Image($signature_file3, 150, $y+1, 40);
        else $pdf->Text(150, $y+4,"Pas d'image pour la signature");
    }
    $pdf->SetXY(213,$y);
    $pdf->MultiCell(67,$hauteur,"",1,"L",true);
    if ( $rembourse <> "" ) {
        if (  @is_file($signature_file4) ) $pdf->Image($signature_file4, 216, $y+1, 40);
        else $pdf->Text(216, $y+4,"Pas d'image pour la signature");
    }
    $pdf->SetXY(10,200);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");

    // imprimer les justificatifs
    $mypath=$filesdir."/files_note/".$note;
    $i=1;
    if (is_dir($mypath)) {
        $dir=opendir($mypath); 
        while ($file = readdir ($dir)) {
            $ext=strtoupper(file_extension($file));
            if ($ext == "JPG" or $ext == "PNG") {
                if ( $ext == "PNG" ) {
                    $f = fopen($mypath."/".$file,'rb');
                    $res = fread($f,1); // read 1 byte from stream
                    $bpc = ord($res);
                    // test 16-bit depth
                    if ( $bpc>8 ) continue;
                    fclose($f);
                }
                $pdf->AddPage();
                $pdf->SetFont('Arial','B',12);
                $pdf->Text(10,10,"Note de frais N°".$note." pour ".$BENEFICIAIRE.", justificatif n°".$i);
                $pdf->Image($mypath."/".$file,15,15,0,180);
                $pdf->SetXY(10,200);
                $pdf->SetFont('Arial','',6);
                $pdf->MultiCell(100,5,$printed_by,"","L");
                $i++;
            }
        }
    }

    $pdf->SetXY(10,272);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->SetDisplayMode('fullpage','single');
    if ( $tofile == 1 ) {
        $note_dir = $filesdir."/files_personnel/".$pid."/";
        @mkdir($filesdir."/files_personnel/",0755);
        @mkdir($note_dir,0755);
        $note_file = $note_dir."/Note_de_frais_".$note.".pdf";
        if ( @is_file($note_file)) unlink($note_file);
        $pdf->Output($note_file,'F');
    }
    else
        $pdf->Output("Note_de_frais_".$note.".pdf",'I');
}


//=============================
// fiche victime ou intervention
//=============================

else if ( $mode == 17 or $mode == 16 ) {
    $printPageNum=true;
    $pdf= new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator("$cisname");
    $pdf->SetAuthor("$cisname");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Fiche intervention ou victime");
    $pdf->SetAutoPageBreak(0);
    $pdf->AliasNbPages();
    $y=50;    
    $pdf->AddPage();
    $pdf->SetTextColor(13,53,148);
    $pdf->SetFont('Arial','B',14);
    $pdf->SetXY(0,10);

    $query="select date_format(el.EL_DEBUT, '%d-%m-%Y ') EL_DEBUT, 
            date_format(el.EL_DEBUT, '%H:%i') EL_HEURE_DEBUT,
            date_format(el.EL_FIN, '%d-%m-%Y ') EL_FIN, 
            date_format(el.EL_FIN, '%H:%i') EL_HEURE_FIN, date_format(el.EL_SLL,'%H:%i') EL_SLL,
            el.EL_TITLE, el.EL_COMMENTAIRE, p.P_NOM, p.P_PRENOM,
            el.EL_RESPONSABLE, el.EL_ADDRESS
            from evenement_log el
            left join pompier p on p.P_ID = el.EL_RESPONSABLE
            where el.EL_ID = ".$numinter."
            and el.E_CODE = ".$evenement;

    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $P_NOM=strtoupper($P_NOM);
    $P_PRENOM=my_ucfirst($P_PRENOM);
        
    $query5="select ee.EE_ID, ee.EE_NAME
    from evenement_equipe ee, intervention_equipe ie 
    where ie.E_CODE=ee.E_CODE 
    and ie.EE_ID = ee.EE_ID
    and ee.E_CODE=".$evenement." 
    and ie.EL_ID =  ".$numinter."
    order by ee.EE_ORDER ";
    $result5=mysqli_query($dbc,$query5);
    $nbequipes=mysqli_num_rows($result5);
    $eqnames="";
    while ($row5=@mysqli_fetch_array($result5)) {
        $eqnames .= " ".$row5["EE_NAME"].",";
    }
    $eqnames = rtrim($eqnames,",");

    if ( $mode == 17 ) {
        
        $query="select victime.CAV_ID, VI_ID, VI_NOM, VI_PRENOM, VI_SEXE, VI_ADDRESS, date_format(VI_BIRTHDATE, '%d-%m-%Y') VI_BIRTHDATE, VI_SEXE,
            VI_DETRESSE_VITALE, VI_INFORMATION, VI_SOINS, VI_MEDICALISE, VI_TRANSPORT, VI_VETEMENT, VI_ALIMENTATION, VI_TRAUMATISME, VI_REPOS, VI_REPARTI, VI_DECEDE, VI_MALAISE, VI_PAYS,
            victime.D_CODE,victime.T_CODE,VI_COMMENTAIRE, VI_REFUS, VI_IMPLIQUE, VI_NUMEROTATION, destination.D_NAME, transporteur.T_NAME, VI_AGE, pays.NAME,
            date_format(CAV_ENTREE, '%d-%m-%Y') DATE_ENTREE, date_format(CAV_ENTREE, '%H:%i') HEURE_ENTREE,
            date_format(CAV_SORTIE, '%d-%m-%Y') DATE_SORTIE, date_format(CAV_SORTIE, '%H:%i') HEURE_SORTIE,
            date_format(HEURE_HOPITAL, '%H:%i') HEURE_HOPITAL,
            CAV_REGULATED, CAV_NAME, IDENTIFICATION
            from victime left join destination on victime.D_CODE = destination.D_CODE
            left join transporteur on victime.T_CODE = transporteur.T_CODE
            left join centre_accueil_victime cav on victime.CAV_ID = cav.CAV_ID,
            pays
            where pays.ID=victime.VI_PAYS
            and VI_ID =".$victime."
            and EL_ID =".$numinter;

        $result=mysqli_query($dbc,$query);
        custom_fetch_array($result);
        $VI_NOM=strtoupper($VI_NOM);
        $VI_PRENOM=my_ucfirst($VI_PRENOM);
        if ( $VI_BIRTHDATE == '' ) $age=$VI_AGE." ans";
        else $age=$VI_BIRTHDATE;
        $numcav=intval($CAV_ID);
        if ( $IDENTIFICATION <> "" ) $IDENTIFICATION=" - identifiant: ".$IDENTIFICATION;
        
        $pdf->Text(60,$y,"Fiche victime n°".$VI_NUMEROTATION.$IDENTIFICATION);
        $y=$y+6;
        $pdf->SetFont('Arial','B',11 );
        $pdf->SetXY(10,$y);
        $pdf->MultiCell(180,5,"Evenement:      ".$description,"","L");
        $y=$y+5;
        $pdf->SetXY(10,$y);
        $pdf->MultiCell(180,5,"Date(s):             ".$periode,"","L");
        $y=$y+5;
        $pdf->SetXY(10,$y);
        $pdf->MultiCell(180,5,"Lieu:                  ".$lieu,"","L");
        $pdf->SetFont('Arial','',10 );
        
        if ( $EL_TITLE <> "" ) {
            $y=$y+7;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(180,5,"Intervention:                              ".$EL_TITLE,"","L");
        }
        if ( $EL_DEBUT <> "" ) {
            $y=$y+5;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(180,5,"Date Début intervention:           ".$EL_DEBUT,"","L");
            $y=$y+5;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(180,5,"Heure début:                             ".$EL_HEURE_DEBUT,"","L");
        }
        if ( $EL_SLL <> "" ) {
            $y=$y+5;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(180,5,"Heure sur les lieux:                   ".$EL_SLL,"","L");
        }
        if ( $EL_FIN <> '' ) {
            $y=$y+5;
            if ( $EL_FIN  <> $EL_DEBUT ) {
                $pdf->SetXY(10,$y);
                $pdf->MultiCell(180,5,"Fin intervention:                       ".$EL_FIN,"","L");
                $y=$y+5;
            }
            if ( $EL_HEURE_FIN <> '00:00' ) {
                $pdf->SetXY(10,$y);
                $pdf->MultiCell(180,5,"Heure fin:                                  ".$EL_HEURE_FIN,"","L");
            }
        }
        if ( $EL_RESPONSABLE <> "" ) {
            $y=$y+5;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(180,5,"Responsable:                            ".$P_PRENOM." ".$P_NOM,"","L");
        }
        if ( $nbequipes > 0 ) {
            $y=$y+5;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(180,5,"Equipes engagées:                  ".$eqnames,"","L");           
        }
        if ( $numcav > 0 ) {
            $y=$y+7;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(180,5,"Centre d'accueil des victimes:   ".$CAV_NAME,"","L");
            $y=$y+5;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(180,5,"Date entrée:                               ".$DATE_ENTREE,"","L");
            $y=$y+5;
            $pdf->SetXY(10,$y);
            $pdf->MultiCell(180,5,"Heure entrée:                             ".$HEURE_ENTREE,"","L");
            if ( $HEURE_SORTIE <> '' ) {
                $y=$y+5;
                if ( $DATE_SORTIE  <> $DATE_ENTREE ) {
                    $pdf->SetXY(10,$y);
                    $pdf->MultiCell(180,5,"Date sortie:                                ".$DATE_SORTIE,"","L");
                    $y=$y+5;
                }
                $pdf->SetXY(10,$y);
                $pdf->MultiCell(180,5,"Heure sortie:                              ".$HEURE_SORTIE,"","L");
            }
        }
        $y=$y+10;
        
        $pdf->SetXY(10,$y);
        $pdf->SetFont('Arial','B',11 );
        $pdf->MultiCell(180,5,"Informations victime:","","L");    
        $y=$y+5;
        $pdf->SetFont('Arial','',10 );
        $pdf->SetXY(20,$y);
        $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
        $pdf->MultiCell(180,5,"Identité victime: ".$VI_PRENOM." ".$VI_NOM,"","L");
        
        $y=$y+5;
        $pdf->SetXY(20,$y);
        $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
        $pdf->MultiCell(180,5,"Age ou date de naissance: ".$age,"","L");
        $y=$y+5;
        $pdf->SetXY(20,$y);
        $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
        $pdf->MultiCell(180,5,"Nationalité: ".$NAME,"","L");
        if ( $VI_ADDRESS <> '' ) {
            $y=$y+5;
            $pdf->SetXY(20,$y);
            $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
            $pdf->MultiCell(180,5,"Adresse: ".$VI_ADDRESS,"","L");
        }
        
        // bilan simple
        $y=$y+8;
        $pdf->SetFont('Arial','B',11 );
        $pdf->SetXY(10,$y);
        $pdf->MultiCell(180,5,"Bilan simple:","","L");    
        $pdf->SetFont('Arial','',9);
        $y=$y+5;
        $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
        $comments = remove_returns($VI_COMMENTAIRE);
        $comments = write_victime_comments($comments);
        $nb_lignes = substr_count($comments, "\n") + strlen($comments) / 120;
        $pdf->SetXY(20,$y);
        $pdf->MultiCell(170,5,$comments,"","J");
        $y = $y + $nb_lignes * 5;

        // bilan détaillé
        $y=$y+10;
        $pdf->SetXY(10,$y);
        $pdf->SetFont('Arial','B',11 );
        $pdf->MultiCell(180,5,"Bilan détaillé:","","L");    
        $pdf->SetFont('Arial','',9);
        $y=$y+5;
        
        $query2="select bvc.BVC_CODE,bvc.BVC_TITLE,bvc.BVC_ORDER,
                   bvp.BVP_ID,bvp.BVP_TITLE,bvp.BVP_TYPE, bvp.DOC_ONLY,
                   bv.BVP_VALUE, bvv.BVP_TEXT
            from victime v,
            bilan_victime_category bvc,
            bilan_victime_param bvp,
            bilan_victime bv
            left join bilan_victime_values bvv on (bvv.BVP_ID = bv.BVP_ID and bvv.BVP_INDEX = bv.BVP_VALUE )
            where bvc.BVC_CODE = bvp.BVC_CODE
            and bv.BVP_ID = bvp.BVP_ID 
            and bv.V_ID=".$victime."
            and v.VI_ID = bv.V_ID
            and v.EL_ID=".$numinter."
            order by bvc.BVC_PAGE, bvc.BVC_ORDER, bvp.BVP_ID";

        $result2=mysqli_query($dbc,$query2);
        $prevBVC_CODE="";
        
        while (custom_fetch_array($result2)) {
            $BVP_VALUE=str_replace(array("\n", "\t", "\r"), '',$BVP_VALUE);
            $BVP_TEXT=str_replace(array("\n", "\t", "\r"), '',$BVP_TEXT);
            
            // cas particuliers des traumatismes 2 et 3
            if ( $BVP_ID == 531 or $BVP_ID == 535 ) $bi = 500;
            else if ( $BVP_ID == 532 or $BVP_ID == 536 ) $bi = 510;
            else if ( $BVP_ID == 533 or $BVP_ID == 537 ) $bi = 520;
            else if ( $BVP_ID == 534 or $BVP_ID == 538 ) $bi = 530;
            else $bi = $BVP_ID;
            if ( $bi <> $BVP_ID ) {
                $query3="select BVP_TEXT from bilan_victime_values where BVP_ID = ".$bi." and BVP_INDEX = ".$BVP_VALUE;
                $result3=mysqli_query($dbc,$query3);
                $row3=@mysqli_fetch_array($result3);
                $BVP_TEXT = str_replace(array("\n", "\t", "\r"), '',$row3[0]);
            }
            
            if ( $BVP_TEXT <> "" ) $value = $BVP_TEXT;
            else $value = $BVP_VALUE;
            
            if ( $BVP_TYPE == 'checkbox' and $value == '1' ) {
                $value="";
            }
            else 
                $BVP_TITLE .=":   ";
            if ( $prevBVC_CODE <> $BVC_CODE ) {
                $pdf->SetXY(20,$y);
                $pdf->SetFont('Arial','U',10 );
                $pdf->MultiCell(180,5,$BVC_TITLE.":","","L");    
                $pdf->Image("./images/bullet_black_small.png", 15, $y, 4);
                $prevBVC_CODE=$BVC_CODE;
                $y=$y+5;
            }
            
            $pdf->SetXY(25,$y);
            $pdf->Image("./images/bullet_blue_small.png", 20, $y, 4);
            if ( substr($BVP_TITLE,0,11) == 'Traumatisme' ) $pdf->SetFont('Arial','B',9 );
            else $pdf->SetFont('Arial','',9 );
            $pdf->MultiCell(180,5,$BVP_TITLE.$value,"","L");
            if ( $y > 258 ) {
                $pdf->AddPage();
                $y=45;
            }
            else {
                $y = $y+ 5  + 5 * round (strlen($value) / 120 );
            }
        }
    }
    else { // fiche intervention

        $pdf->Text(60,$y,"Fiche intervention n°".$numinter);
        $y=$y+6;
        $pdf->SetFont('Arial','B',11 );
        $pdf->SetXY(10,$y);
        $pdf->MultiCell(180,5,"Evenement:      ".$description,"","L");
        $y=$y+5;
        $pdf->SetXY(10,$y);
        $pdf->MultiCell(180,5,"Date(s):             ".$periode,"","L");
        $y=$y+5;
        $pdf->SetXY(10,$y);
        $pdf->MultiCell(180,5,"Lieu:                  ".$lieu,"","L");
        $pdf->SetFont('Arial','',10 );
        
        $y=$y+10;
        $pdf->SetXY(15,$y);
        $pdf->SetFont('Arial','B',10 );
        $pdf->MultiCell(180,5,"Intervention:","","L");    
        $pdf->SetFont('Arial','',10 );
        $pdf->Image("./images/bullet_black_small.png", 10, $y, 4);
        
        if ( $EL_TITLE <> "" ) {
            $y=$y+7;
            $pdf->SetXY(15,$y);
            $pdf->MultiCell(180,5,"Type intervention:             ".$EL_TITLE,"","L");
        }
        if ( $EL_DEBUT <> "" ) {
            $y=$y+5;
            $pdf->MultiCell(180,5,"Date Début intervention:   ".$EL_DEBUT,"","L");
            $y=$y+5;
            $pdf->MultiCell(180,5,"Heure début:                     ".$EL_HEURE_DEBUT,"","L");
        }
        if ( $EL_SLL <> "" ) {
            $y=$y+5;
            $pdf->MultiCell(180,5,"Heure sur les lieux:           ".$EL_SLL,"","L");
        }
        if ( $EL_FIN <> '' ) {
            $y=$y+5;
            if ( $EL_FIN  <> $EL_DEBUT ) {
                $pdf->MultiCell(180,5,"Fin intervention:               ".$EL_FIN,"","L");
                $y=$y+5;
            }
            $pdf->MultiCell(180,5,"Heure fin:                          ".$EL_HEURE_FIN,"","L");
        }
        if ( $RESPONSABLE <> "" ) {
            $y=$y+5;
            $pdf->MultiCell(180,5,"Responsable:                    ".$P_PRENOM." ".$P_NOM,"","L");
        }
        if ( $nbequipes > 0 ) {
            $y=$y+5;
            $pdf->MultiCell(180,5,"Equipes engagées:          ".$eqnames,"","L");           
        }
        if ( $EL_ADDRESS <> "" ) {
            $y=$y+5;
            $pdf->MultiCell(180,5,"Adresse intervention:        ".$EL_ADDRESS,"","L");
        }
        if ( $EL_COMMENTAIRE <> "" ) {
            $y=$y+8;
            $pdf->SetXY(15,$y);
            $pdf->SetFont('Arial','B',10 );
            $pdf->MultiCell(180,5,"Commentaire:","","L");    
            $pdf->Image("./images/bullet_black_small.png", 10, $y, 4);
            $pdf->SetFont('Arial','',9 );
            $pdf->MultiCell(180,5,$EL_COMMENTAIRE,"","L");
            $y= $y + 5 + 4 * substr_count( $EL_COMMENTAIRE, "\n" ) ;

        }
        
        $query="select VI_ID, VI_NOM, VI_PRENOM, VI_SEXE, VI_ADDRESS, date_format(VI_BIRTHDATE, '%d-%m-%Y') VI_BIRTHDATE, VI_SEXE,
            VI_DETRESSE_VITALE, VI_INFORMATION, VI_SOINS, VI_MEDICALISE, VI_TRANSPORT, VI_VETEMENT, VI_ALIMENTATION, VI_TRAUMATISME, VI_REPOS, VI_REPARTI, VI_DECEDE, VI_MALAISE, VI_PAYS,
            victime.D_CODE,victime.T_CODE,VI_COMMENTAIRE, VI_REFUS, VI_IMPLIQUE, VI_NUMEROTATION, destination.D_NAME, transporteur.T_NAME, VI_AGE, pays.NAME, IDENTIFICATION,
            date_format(HEURE_HOPITAL, '%H:%i') HEURE_HOPITAL
            from victime left join destination on victime.D_CODE = destination.D_CODE
            left join transporteur on victime.T_CODE = transporteur.T_CODE,
            pays, evenement e, evenement_log el
            where el.EL_ID=".$numinter."
            and e.E_CODE = ".$evenement."
            and e.E_CODE = el.E_CODE
            and el.EL_ID = victime.EL_ID
            and victime.VI_PAYS = pays.ID
            order by VI_NUMEROTATION asc";

        $result=mysqli_query($dbc,$query);
        
        if ( mysqli_num_rows($result) > 0 ) {
            $y=$y+15;
            $pdf->SetXY(15,$y);
            $pdf->SetFont('Arial','B',10 );
            $pdf->MultiCell(180,5,"Victimes:","","L");    
            $pdf->Image("./images/bullet_black_small.png", 10, $y, 4);
            $y=$y+7;
        }
        
        while ( custom_fetch_array($result)) {
            $VI_NOM=strtoupper($VI_NOM);
            $VI_PRENOM=my_ucfirst($VI_PRENOM);
            if ( $VI_BIRTHDATE == '' ) $age=$VI_AGE." ans";
            else $age=$VI_BIRTHDATE;
            if ( $IDENTIFICATION <> "" ) $IDENTIFICATION = " - identifiant : ".$IDENTIFICATION;
            if ( $y > 258 ) {
                $pdf->AddPage();
                $y=48;
            }
            
            $pdf->SetXY(10,$y);
            $pdf->SetFont('Arial','U',9 );
            $pdf->SetXY(20,$y);
            $pdf->Image("./images/bullet_blue_small.png", 15, $y, 4);
            $pdf->MultiCell(180,5,"Victime V".$VI_NUMEROTATION.$IDENTIFICATION." : ".$VI_PRENOM." ".$VI_NOM,"","L");
            $pdf->SetFont('Arial','',9);
            $y=$y+5;
            $pdf->SetXY(20,$y);
            $pdf->MultiCell(180,5,"- Age ou date de naissance: ".$age,"","L");
            $y=$y+5;
            $pdf->SetXY(20,$y);
            $pdf->MultiCell(180,5,"- Nationalité: ".$NAME,"","L");
            if ( $VI_ADDRESS <> '' ) {
                $y=$y+5;
                $pdf->SetXY(20,$y);
                $pdf->MultiCell(180,5,"- Adresse: ".$VI_ADDRESS,"","L");
            }
        
            // bilan simple
            $y=$y+5;
            $pdf->SetXY(10,$y);
            $pdf->SetFont('Arial','',9);
            $comments = write_victime_comments($VI_COMMENTAIRE);
            $rl = 85; // nb max de caractères par ligne de commentaire
            $hauteur_commentaire=(strlen($comments) / $rl) * 4;
            $pdf->SetXY(20,$y);
            $pdf->MultiCell(170,5,"- ".$comments,"","J");
            $y = $y + $hauteur_commentaire + 6 ;
        }
        
        
    }
        
    $pdf->SetXY(10,272);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->Output("Rapport.pdf",'I');    

}



//===========================================================
// fiche Bilan PSSP - Centre Accueil des impliqués CADI
//===========================================================

else if ( $mode == 21 ) {
    $printPageNum=true;
    $pdf= new PDFEB();
    $pdf->AliasNbPages();
    $pdf->SetCreator("$cisname");
    $pdf->SetAuthor("$cisname");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->SetTitle("Fiche bilan PSSP");
    $pdf->SetAutoPageBreak(0);
    $y=55;    
    $pdf->AddPage();
    $pdf->SetTextColor(13,53,148);
    $pdf->SetFont('Arial','B',15);
    $pdf->SetXY(0,10);

    $pdf->SetXY(10,$y);
    $pdf->MultiCell(180,5,"FICHE BILAN PSSP - Centre Accueil Des Impliqués","","C");
    $y=$y+8;
    $pdf->SetFont('Arial','B',10 );
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(180,5,"Evenement:    ".$description." - ".$lieu,"","L");
    $pdf->SetFont('Arial','',10 );
    $y=$y+5;
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(180,5,"Date(s):           ".$periode,"","L");
    $y=$y+5;
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(180,5,"Fiche N°:         ......................","","L");
    $y=$y+7;

    $pdf->SetDrawColor(13,53,148);
    $pdf->SetLineWidth(1);
    $pdf->Line(10, $y, 200, $y);

    $y=$y+4;
    $pdf->SetFont('Arial','B',10);
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(180,5,"IDENTIFICATION");
    $y=$y+5;
    $pdf->SetXY(10,$y);
    $pdf->SetFont('Arial','',10 );
    $pdf->MultiCell(180,5,"Nom ........................................ Prénoms ........................................ Date Naissance ............................. (Age .......)","","L");
    $y=$y+5;
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(180,5,"Adresse .....................................................................................................................................................................","","L");
    $y=$y+5;
    $pdf->SetXY(10,$y);
    $pdf->MultiCell(180,5,"Tél .........................................Personne à prévenir....................................................................................................","","L");
    $y=$y+7;

    $pdf->SetDrawColor(13,53,148);
    $pdf->SetLineWidth(1);
    $pdf->Line(10, $y, 200, $y);

    $y=$y+4;
    $pdf->SetFont('Arial','B',10);
    $pdf->SetXY(15,$y);
    $pdf->MultiCell(180,5,"SIGNES REPERES (* : soins Médico Psychologiques)");

    $y=$y+7;
    $pdf->SetLineWidth(0.5);
    $pdf->SetFont('Arial','',9);
    $pdf->Rect(20, $y, 5, 5);
    $pdf->SetXY(26,$y);
    $pdf->MultiCell(25,5,"AGITATION");
    $pdf->Rect(60, $y, 5, 5);
    $pdf->SetXY(66,$y);
    $pdf->MultiCell(25,5,"PROSTRATION");
    $pdf->Rect(100, $y, 5, 5);
    $pdf->SetXY(106,$y);
    $pdf->MultiCell(25,5,"SIDERATION *");
    $pdf->Rect(140, $y, 5, 5);
    $pdf->SetXY(146,$y);
    $pdf->MultiCell(25,5,"AGRESSIVITE");
    $y=$y+7;
    $pdf->Rect(20, $y, 5, 5);
    $pdf->SetXY(26,$y);
    $pdf->MultiCell(25,5,"CONFUSION");
    $pdf->Rect(60, $y, 5, 5);
    $pdf->SetXY(66,$y);
    $pdf->MultiCell(30,5,"FUITE PANIQUE *");
    $pdf->Rect(100, $y, 5, 5);
    $pdf->SetXY(106,$y);
    $pdf->MultiCell(48,5,"GESTES AUTOMATIQUES *");
    $y=$y+7;
    $pdf->Rect(20, $y, 5, 5);
    $pdf->SetXY(26,$y);
    $pdf->MultiCell(25,5,"EUPHORIE");
    $pdf->Rect(60, $y, 5, 5);
    $pdf->SetXY(66,$y);
    $pdf->MultiCell(25,5,"COLERE");
    $pdf->Rect(100, $y, 5, 5);
    $pdf->SetXY(106,$y);
    $pdf->MultiCell(25,5,"TRISTESSE*");
    $pdf->Rect(140, $y, 5, 5);
    $pdf->SetXY(146,$y);
    $pdf->MultiCell(20,5,"ANGOISSE");
    $pdf->Rect(170, $y, 5, 5);
    $pdf->SetXY(176,$y);
    $pdf->MultiCell(25,5,"PLEURS");
    $y=$y+7;
    $pdf->SetXY(26,$y);
    $pdf->Rect(20, $y, 5, 5);
    $pdf->MultiCell(25,5,"MEFIANCE");
    $pdf->Rect(60, $y, 5, 5);
    $pdf->SetXY(66,$y);
    $pdf->MultiCell(25,5,"CULPABILITE");
    $pdf->Rect(100, $y, 5, 5);
    $pdf->SetXY(106,$y);
    $pdf->MultiCell(40,5,"DEREALISATION *");

    $y=$y+9;
    $pdf->SetFont('Arial','B',10);
    $pdf->SetXY(15,$y);
    $pdf->MultiCell(180,5,"CONTACT RELATIONNEL                          VERBALISATION                         RECIT DE L'EVENEMENT");
    $y=$y+7;
    $pdf->SetFont('Arial','',9);
    $pdf->Rect(15, $y, 5, 5);
    $pdf->SetXY(21,$y);
    $pdf->MultiCell(25,5,"SATISFAISANT");
    $pdf->Rect(80, $y, 5, 5);
    $pdf->SetXY(86,$y);
    $pdf->MultiCell(25,5,"SPONTANEE");
    $pdf->Rect(130, $y, 5, 5);
    $pdf->SetXY(136,$y);
    $pdf->MultiCell(35,5,"FACTUEL EXCLUSIF");
    $y=$y+7;
    $pdf->Rect(15, $y, 5, 5);
    $pdf->SetXY(21,$y);
    $pdf->MultiCell(35,5,"PEU SATISFAISANT");
    $pdf->Rect(80, $y, 5, 5);
    $pdf->SetXY(86,$y);
    $pdf->MultiCell(25,5,"PROVOQUEE");
    $pdf->Rect(130, $y, 5, 5);
    $pdf->SetXY(136,$y);
    $pdf->MultiCell(50,5,"EMOTIONNEL EXCLUSIF");
    $y=$y+7;
    $pdf->Rect(15, $y, 5, 5);
    $pdf->SetXY(21,$y);
    $pdf->MultiCell(35,5,"INSATISFAISANT");
    $pdf->Rect(80, $y, 5, 5);
    $pdf->SetXY(86,$y);
    $pdf->MultiCell(25,5,"ABSENTE");
    $pdf->Rect(130, $y, 5, 5);
    $pdf->SetXY(136,$y);
    $pdf->MultiCell(50,5,"FACTUEL ET EMOTIONNEL");
    $y=$y+7;
    $pdf->Rect(130, $y, 5, 5);
    $pdf->SetXY(136,$y);
    $pdf->MultiCell(50,5,"AMNESIE");

    $y=$y+7;
    $pdf->SetLineWidth(1);
    $pdf->Line(10, $y, 200, $y);
    $y=$y+4;

    $pdf->SetLineWidth(0.5);
    $pdf->SetFont('Arial','B',10);
    $pdf->SetXY(15,$y);
    $pdf->MultiCell(30,5,"EVOLUTION:");
    $pdf->SetFont('Arial','',9);
    $pdf->SetXY(50,$y);
    $pdf->MultiCell(40,5,"SANS CHANGEMENT");
    $pdf->Rect(85, $y, 7, 5);
    $pdf->SetXY(100,$y);
    $pdf->MultiCell(30,5,"AMELIORATION");
    $pdf->Rect(128, $y, 7, 5);
    $pdf->SetXY(140,$y);
    $pdf->MultiCell(30,5,"AGGRAVATION");
    $pdf->Rect(168, $y, 7, 5);
    $y=$y+7;
    $pdf->Rect(15, $y, 180, 20);
    $pdf->SetFont('Arial','',7);
    $pdf->SetXY(17,$y);
    $pdf->MultiCell(30,5,"Observation:");
    $y=$y+23;
    $pdf->SetFont('Arial','B',10);
    $pdf->SetXY(15,$y);
    $pdf->MultiCell(30,5,"SUITE DONNEE:");
    $pdf->SetFont('Arial','',9);
    $pdf->SetXY(60,$y);
    $pdf->MultiCell(40,5,"AVIS MEDICAL");
    $pdf->Rect(85, $y, 5, 5);
    $pdf->SetXY(105,$y);
    $pdf->MultiCell(30,5,"EVACUATION");
    $pdf->Rect(128, $y, 5, 5);
    $pdf->SetXY(140,$y);
    $pdf->MultiCell(40,5,"ORIENTATION:  Hopital");
    $pdf->Rect(178, $y, 5, 5);
    $y=$y+7;
    $pdf->SetXY(60,$y);
    $pdf->MultiCell(24,5,"et/ou CUMP","","R");
    $pdf->Rect(85, $y, 5, 5);
    $pdf->SetXY(105,$y);
    $pdf->MultiCell(28,5,"Heure: .................","","R");
    $pdf->SetXY(140,$y);
    $pdf->MultiCell(36,5,"domicile seul","","R");
    $pdf->Rect(178, $y, 5, 5);
    $y=$y+7;
    $pdf->SetXY(140,$y);
    $pdf->MultiCell(36,5,"accompagné","","R");
    $pdf->Rect(178, $y, 5, 5);

    $y=$y+7;
    $pdf->SetLineWidth(1);
    $pdf->Line(10, $y, 200, $y);
    $y=$y+3;

    $pdf->SetFont('Arial','B',11);
    $pdf->SetXY(25,$y);
    $pdf->MultiCell(30,5,"BILAN:");
    $y=$y+7;
    $pdf->SetXY(25,$y);
    $pdf->MultiCell(60,5,"Pouls++ ......................");

    $y=$y+7;
    $pdf->SetLineWidth(1);
    $pdf->Line(10, $y, 200, $y);
    $y=$y+2;

    $pdf->SetFont('Arial','B',10);
    $pdf->SetXY(25,$y);
    $pdf->MultiCell(175,5,"NOM DES INTERVENANTS                                                           SIGNATURES");

    $pdf->SetXY(10,272);
    $pdf->SetFont('Arial','',6);
    $pdf->MultiCell(100,5,$printed_by,"","L");
    $pdf->SetDisplayMode('fullpage','single');
    $pdf->Output("Fiche_Bilan.pdf",'I');

}
?>
