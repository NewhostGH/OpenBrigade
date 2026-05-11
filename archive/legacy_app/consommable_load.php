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
        //Minimum
        if ( $C_MINIMUM == 0 ) $C_MINIMUM ="";

        //Stock
        if ( $C_NOMBRE < $C_MINIMUM ) {
            $style1=$widget_all_red;
            $title1='Stock trop faible, il faut recommender';
        }
        else {
            $style1=$widget_all_green;
            $title1='Stock suffisant';
        }

        //Conditionnement
        if ( $TC_QUANTITE_PAR_UNITE > 1 ) $TUM_DESCRIPTION .='s';
        if ( $TCO_CODE == 'PE' ) $conditionnement =  $TUM_DESCRIPTION."s";
        else if ( $TUM_CODE <> 'un' or  $TC_QUANTITE_PAR_UNITE <> 1 ) $conditionnement = $TCO_DESCRIPTION." ".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION;
        else $conditionnement = "";

        //Date limite
        if ( $C_DATE_PEREMPTION <> '' ) {
            if ( my_date_diff(getnow(),$C_DATE_PEREMPTION) < 0 ) {
                $style2=$widget_all_red;
                $title2='Attention produit périmé';
            }
            else if ( my_date_diff(getnow(),$C_DATE_PEREMPTION) <= 30 ) {
                $style2=$widget_all_orange;
                $title2='Attention produit bientôt périmé, dans moins de 30 jours';
            }
            else {
                $style2=$widget_all_green;
                $title2="La date limite d&apos;utilisation est dans plus de 30 jours";
            }
        }
        else {
            $title2="";
            $style2="";
        }
        
        if ( $C_DATE_PEREMPTION <> '' ) $date_peremption = "<span class='badge' style='".$style2."' title='".$title2."'>$C_DATE_PEREMPTION</span>";
        else $date_peremption = '';

        $C_DESCRIPTION=str_replace(',','',$C_DESCRIPTION);

        $out .= "{
        \"id\":\"".$C_ID."\",
        \"cat\":\"".$CC_NAME."\",
        \"type\":\"".ucfirst($TC_DESCRIPTION)."\",
        \"stock\":\"<span class='badge' style='".$style1."' title='".$title1."'>".$C_NOMBRE."</span>\",
        \"min\":\"".$C_MINIMUM."\",
        \"conditionnement\":\"".removeTabsReturns($conditionnement)."\",
        \"section\":\"".removeTabsReturns($S_CODE)."\",
        \"desc\":\"".removeTabsReturns($C_DESCRIPTION)."\",
        \"date\":\"".$date_peremption."\",
        \"stockage\":\"".removeTabsReturns($C_LIEU_STOCKAGE)."\"
        },";
    }
    $out = rtrim($out , ",");
    $out .= "]}";

    $out = str_replace("\\", "", $out);

    echo $out;
    exit;
}