<?php

  # project: eBrigade
  # homepage: https://ebrigade.app
  # version: 5.3

  # Copyright (C) 2004, 2021 Nicolas MARCHE
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
$mysection=$_SESSION['SES_SECTION'];
get_session_parameters();

//validate permissions
if ( $category == 'EXT' ) {
    if ( intval($_SESSION['SES_COMPANY']) == 0 )
        check_all(37);
    if ( check_rights($id, 37))
        test_permission_level(37);
    else {
        check_all(45);
    }
}
else {
    test_permission_level(56);
}

$query = $_SESSION['query'];

$result=mysqli_query($dbc, $query);
$totalNotFiltered=mysqli_num_rows($result);

$out="";

if (isset($_GET["data"])) {
    header('Content-Type: application/json; charset=ISO-8859-1');
    $out = "{
      \"total\": ".$totalNotFiltered.",
      \"totalNotFiltered\": ".$totalNotFiltered.",
      \"rows\": [";
    while (custom_fetch_array($result)) {
        //Image
        if ( $TV_ICON == "" ) $vimg="";
        else $vimg="<img src='".$TV_ICON."' class='img-max-35'>";

        //Icones
        if ( $V_FLAG1 == 1 ) $img1="<i class='fa fa-check' title='ce véhicule est équipé pour rouler sur la neige'></i>";
        else $img1='';
        if ( $V_FLAG2 == 1 ) $img2="<i class='fa fa-check' title='ce véhicule est climatisé'></i>";
        else $img2='';
        if ( $V_FLAG3 == 1 ) $img3="<i class='fa fa-check' title='ce véhicule est équipé public alert'></i>";
        else $img3='';
        if ( $V_FLAG4 == 1 ) $img4="<i class='fa fa-check' title='ce véhicule est équipé d&apos;un attelage pour tracter une remorque'></i>";
        else $img4='';
        if ( $V_EXTERNE == 1 ) $img5="<i class='fa fa-check' title='véhicule mis à disposition par $cisname'></i>";
        else $img5='';

        //Affectation
        if ( $AFFECTED_TO <> '' ) {
            $queryp="select P_NOM, P_PRENOM, P_OLD_MEMBER from pompier where P_ID=".$AFFECTED_TO;
            $resultp=mysqli_query($dbc, $queryp);
            $rowp=@mysqli_fetch_array($resultp);
            $P_NOM=$rowp["P_NOM"];
            $P_PRENOM=$rowp["P_PRENOM"];
            $P_OLD_MEMBER=$rowp["P_OLD_MEMBER"];
            $owner=strtoupper(substr($P_PRENOM,0,1).".".$P_NOM);
            if ( $P_OLD_MEMBER == 1 ) $owner="<font color=black title='ancien membre'><b>".$owner."</b><font>";
        }
        else $owner='';

        //Couleurs et indications
        if ( $VP_OPERATIONNEL == -1 ) {
            $fgcolor="white";
            $bgcolor="black";
        }
        else if ( $VP_OPERATIONNEL == 1) {
            $fgcolor=$widget_fgred;
            $bgcolor=$widget_bgred;
        }
        else if ( $VP_OPERATIONNEL == 2) {
            $fgcolor=$widget_fgorange;
            $bgcolor=$widget_bgorange;
        }
        else if ( $VP_ID == "PRE" ) {
            $fgcolor=$widget_fgblue;
            $bgcolor=$widget_bgblue;
        }
        else if ( my_date_diff(getnow(),$V_ASS_DATE1) < 0 ) {
            $fgcolor=$widget_fgred;
            $bgcolor=$widget_bgred;
            $VP_LIBELLE = "Assurance périmée";
        }
        else if ( my_date_diff(getnow(),$V_TITRE_DATE1) < 0 ) {
            $fgcolor=$widget_fgred;
            $bgcolor=$widget_bgred;
            $VP_LIBELLE = "Titre d'accès périmé";
        }
        else if ( my_date_diff(getnow(),$V_CT_DATE1) < 0 ) {
            $fgcolor=$widget_fgred;
            $bgcolor=$widget_bgred;
            $VP_LIBELLE = "Contrôle technique périmé";
        }
        else if (( my_date_diff(getnow(),$V_REV_DATE1) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
            $fgcolor=$widget_fgorange;
            $bgcolor=$widget_bgorange;
            $VP_LIBELLE = "Révision à faire";
        }  
        else {
            $fgcolor=$widget_fggreen;
            $bgcolor=$widget_bggreen;
        }

        // Matériel embarqué
        $query2="select count(*) as NB from materiel where V_ID=".$V_ID;
        $result2=mysqli_query($dbc, $query2);
        $row2=@mysqli_fetch_array($result2);
        if ( $row2["NB"] > 0 ) $mat="<span title='$row2[NB] élément(s) embarqué'>$row2[NB]</span>";
        else $mat="";

        //Assurance
        if ( my_date_diff(getnow(), $V_ASS_DATE1) < 0 ) $assurance=$widget_fgred;
        else if ( my_date_diff(getnow(),$V_ASS_DATE1) < 30 ) $assurance=$widget_fgorange;
        else $assurance=$mydarkcolor;

        //Controle
        if ( my_date_diff(getnow(), $V_CT_DATE1) < 0 ) $controle=$widget_fgred;
        else if ( my_date_diff(getnow(), $V_CT_DATE1) < 30 ) $controle=$widget_fgorange;
        else $controle=$mydarkcolor;

        //Révision
        if ( my_date_diff(getnow(), $V_REV_DATE1) < 0 ) $rev=$widget_fgorange;
        else $rev=$mydarkcolor;

        //Accès
        if ( my_date_diff(getnow(), $V_TITRE_DATE1) < 0 ) $titre=$widget_fgred;
        else if ( my_date_diff(getnow(), $V_TITRE_DATE1) < 30 ) $titre=$widget_fgorange;
        else $titre=$mydarkcolor;

        $out .= "{
        \"id\":\"".$V_ID."\",
        \"type\":\"".$vimg."<span style='color:$fgcolor;'><B> $TV_CODE</B></span>\",
        \"immatriculation\":\"".removeTabsReturns($V_IMMATRICULATION)."\",
        \"indicatif\":\"".removeTabsReturns($V_INDICATIF)."\",
        \"section\":\"".$S_CODE. "\",
        \"modele\":\"".removeTabsReturns($V_MODELE). "\",
        \"statut\":\" <span class='badge' style='color:$fgcolor; background-color:$bgcolor' ><b>".ucfirst($VP_LIBELLE)."</b></span>\",
        \"annee\":\"".$V_ANNEE."\",
        \"finassurance\":\"<span style='color:$assurance;'><b>$V_ASS_DATE1</b></span>\",
        \"ct\":\"<span style='color:$controle;'><b>$V_CT_DATE1</b></span>\",
        \"revision\":\"<span style='color:$rev;'><b>$V_REV_DATE1</b></span>\",
        \"acces\":\"<span style='color:$titre;'><b>$V_TITRE_DATE1</b></span>\",
        \"kmrevision\":\"<span align=center>$V_KM/$V_KM_REVISION</span>\",
        \"neige\":\"<span align=center>".$img1."</span>\",
        \"clim\":\"<span align=center>".$img2."</span>\",
        \"pa\":\"<span align=center>".$img3."</span>\",
        \"att\":\"<span align=center>".$img4."</span>\",
        \"affecte\":\"<a href=upd_personnel.php?from=vehicules&pompier=".$AFFECTED_TO.">".removeTabsReturns($owner)."</a>\",
        \"mat\":\"".$mat."\",
        \"mad\":\"<span align=center>".$img5."</span>\"  
        },";
    }
    $out = rtrim($out , ",");
    $out .= "]}";

    $out = str_replace("\\", "", $out);

    echo $out;
    exit;
}