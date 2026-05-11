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
ini_set('memory_limit', '512M');
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
        //Couleurs et indication
        if ( $VP_OPERATIONNEL == -1 ) {
            $fgcolor="white";
            $bgcolor="black";
        }
        else if ( $VP_ID == "PRE" ) {
            $fgcolor=$widget_fgblue;
            $bgcolor=$widget_bgblue;
        }
        else if ( $VP_OPERATIONNEL == 1) {
            $fgcolor=$widget_fgred;
            $bgcolor=$widget_bgred;
        }
        else if ( my_date_diff(getnow(), $MA_REV_DATE1) < 0 ) {
            $fgcolor=$widget_fgorange;
            $bgcolor=$widget_bgorange;
            $VP_LIBELLE = "date dépassée";
        }
        else if ( $VP_OPERATIONNEL == 2) {
            $fgcolor=$widget_fgorange;
            $bgcolor=$widget_bgorange;
        }
        else {
            $fgcolor=$widget_fggreen;
            $bgcolor=$widget_bggreen;
        }

        //Icones
        if ( $TM_LOT == 1 ) $img1="<i class='fa fa-check' title='Lot de matériel'></i>";
        else $img1='';

        if ( $MA_EXTERNE == 1 ) $img2="<i class='fa fa-check' title='matériel mis à disposition par $cisname'></i>";
        else $img2='';

        //Type de materiel
        if ( is_numeric($type_materiel)) {
            $query2="select TM_USAGE from type_materiel where TM_ID='".$type_materiel."'";
            $result2=mysqli_query($dbc,$query2);
            $row=@mysqli_fetch_array($result2);
            $usage=$row["TM_USAGE"];
        }
        else {
            $usage=$type_materiel;
        }

        if ( $type_materiel == 'Habillement' or $usage == 'Habillement') $habillement=true;
        else $habillement=false;

        if ( $habillement ) $param="&tab=10";
        else $param="&tab=5";

        //Affectation
        if ( $AFFECTED_TO <> '' ) {
            $owner=strtoupper(substr($P_PRENOM, 0, 1).".".$P_NOM);
            if ( $P_OLD_MEMBER > 0 ) $owner="<span style='color:black;' title='ancien membre'><b>".$owner."</b><span>";
        }
        else $owner='';

        //Numéro série
        if ( $habillement ) $t=$TV_NAME;
        else $t = $MA_NUMERO_SERIE;
        
        //Dans Véhicule/lot
        if ( intval($V_ID) > 0 ) $contenu_dans = "<a href=upd_vehicule.php?vid=".$V_ID." title='$TV_CODE $V_MODELE $V_INDICATIF $V_IMMATRICULATION'>".$V_MODELE." ".$V_INDICATIF."</a>";
        else if ( $MA_PARENT <> '' ) {
            $queryp="select m.MA_ID, m.MA_MODELE, tm.TM_CODE, m.MA_NUMERO_SERIE
                        from materiel m, type_materiel tm
                        where m.TM_ID = tm.TM_ID
                        and m.MA_ID=".$MA_PARENT;
            $resultp=mysqli_query($dbc, $queryp);
            $rowp=@mysqli_fetch_array($resultp);
            $_MA_ID=@$rowp["MA_ID"];
            $_MA_MODELE=removeTabsReturns(@$rowp["MA_MODELE"]);
            $_TM_CODE=@$rowp["TM_CODE"];
            $contenu_dans = "<a href=upd_materiel.php?mid=".$_MA_ID." title='Dans lot de matériel'>".$_TM_CODE." ".$_MA_MODELE."</a>";
        }
        else $contenu_dans="";

        $out .= "{
        \"id\":\"".$MA_ID."\",
        \"cat\":\"".$TM_USAGE."\" ,
        \"type\":\"".$TM_CODE."\",
        \"lot\":\"".$img1."\",
        \"nb\":\"".removeTabsReturns($MA_NB)."\",
        \"section\":\"".$S_CODE."\",
        \"modele\":\"".removeTabsReturns($MA_MODELE)."\",
        \"serie\":\"".removeTabsReturns($t)."\",
        \"statut\":\"<b><span class='badge' style='background-color:$bgcolor; color:$fgcolor'>".ucfirst($VP_LIBELLE)."</span></b>\",
        \"date\":\"".$MA_REV_DATE1."\",
        \"nbinventaire\":\"".removeTabsReturns($MA_INVENTAIRE)."\",
        \"stockage\":\"".removeTabsReturns($MA_LIEU_STOCKAGE)."\",
        \"affectation\":\"<a href=upd_personnel.php?pompier=".$AFFECTED_TO.$param.">".$owner."</a>\",
        \"vehicule\":\"".$contenu_dans."\",
        \"annee\":\"".$MA_ANNEE."\",
        \"mad\":\"".$img2."\"
        },";
    }
    $out = rtrim($out , ",");
    $out .= "]}";

    $out = str_replace("\\", "", $out);

    echo $out;
    exit;
}
