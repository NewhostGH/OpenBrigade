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
check_all(18);
get_session_parameters();
writehead();
echo "<script type='text/javascript' src='js/type_consommable.js'></script>";

if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if ($ope == 'edit') {
  include_once ("upd_type_consommable.php");
  exit;
}
if ($ope == 'edit_cat') {
    include_once ("edit_categorie_consommable.php");
    exit;
}


$possibleorders= array('CC_CODE','TCO_DESCRIPTION','TC_DESCRIPTION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='CC_CODE';
echo "</head>";
echo "<body>";

if ( $catconso == 'ALL' ) {
    $picture = "<i class='fa fa-coffee fa-lg fa-2x' style='color:saddlebrown;'></i>";
    $image='Toutes';
    $code = 'tous';
}
else {
    $query1="select CC_CODE, CC_NAME, CC_DESCRIPTION, CC_IMAGE, CC_ORDER  
        from categorie_consommable
        where CC_CODE='".$catconso."'";
    $result1=mysqli_query($dbc,$query1);
    $row=@mysqli_fetch_array($result1);
    $code=$row["CC_CODE"];
    $picture="<i class='fa fa-".$row["CC_IMAGE"]." fa-2x' style='color:saddlebrown;'></i>";
}

$query="select tc.TC_ID, tc.CC_CODE, tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE,
        cc.CC_NAME, cc.CC_DESCRIPTION, cc.CC_IMAGE, cc.CC_ORDER,
        tco.TCO_CODE, tco.TCO_DESCRIPTION, 
        tc.TC_QUANTITE_PAR_UNITE, tc.TC_UNITE_MESURE,
        tum.TUM_DESCRIPTION
        from type_consommable tc, categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum
        where tc.CC_CODE=cc.CC_CODE
        and tum.TUM_CODE = tc.TC_UNITE_MESURE
        and tco.TCO_CODE = tc.TC_CONDITIONNEMENT";
if ( $catconso <> 'ALL' ) $query .= "\nand cc.CC_CODE='".$catconso."'";
if ( $order == 'TCO_DESCRIPTION' ) $query .="\norder by tco.". $order;
else $query .="\norder by tc.". $order;

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);


echo "<div align=center class='table-responsive'>";

//filtre type
echo "<div class='div-decal-left' style='float:left'><select id='usage' name='usage'  class='selectpicker' data-style='btn-default' data-container='body'
    onchange=\"orderfilter('".$order."',document.getElementById('usage').value)\">";

$query2="select CC_CODE, CC_NAME from categorie_consommable order by CC_CODE asc";
$result2=mysqli_query($dbc,$query2);
if ( $catconso == 'ALL' ) $selected="selected ";
else $selected ="";
echo "<option value='ALL' $selected>Toutes les catégories</option>\n";
while ($row=@mysqli_fetch_array($result2)) {
    $CC_CODE=$row["CC_CODE"];
    $CC_NAME=$row["CC_NAME"];

    $selected = $CC_CODE == $catconso ? 'selected' : '';
    echo "<option value='$CC_CODE' class='option-ebrigade' $selected>$CC_CODE - $CC_NAME</option>\n";
}
echo "</select></div>";

echo "<div class='dropdown-right' align=right><a class='btn btn-success' value = 'Ajouter type' name='ajouter' 
    onclick=\"bouton_redirect('parametrage.php?tab=3&child=4&ope=edit&id=0');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Consommable</span></a></div>";

// ====================================
// pagination
// ====================================

$string = "tab=".$tab."&child=".$child;

$later=1;
execute_paginator($number, $string);

if ( $number > 0 ) {
    echo "<div class='col-sm-12'>";
    echo "<table class='newTableAll'>";
    // ===============================================
    // premiere ligne du tableau
    // ===============================================

    echo "<tr>
        <td width=130 ><a href=parametrage.php?tab=3&child=4&order=CC_CODE >Catégorie</a></td>
        <td width=300 align=center><a href=parametrage.php?tab=3&child=4&order=TC_DESCRIPTION >Description</a></td>
        <td width=200 align=center><a href=parametrage.php?tab=3&child=4&order=TCO_DESCRIPTION >Conditionnement</a></td>
        <td width=200 align=center><a href=parametrage.php?tab=3&child=4&order=TC_QUANTITE_PAR_UNITE >Contenance</a></td>
    </tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while ($row=@mysqli_fetch_array($result)) {
         $TC_ID=$row["TC_ID"];
        $CC_CODE=$row["CC_CODE"];
        $TC_DESCRIPTION=ucfirst($row["TC_DESCRIPTION"]);
        $TC_CONDITIONNEMENT=$row["TC_CONDITIONNEMENT"];
        $TC_UNITE_MESURE=$row["TC_UNITE_MESURE"];
        $CC_NAME=$row["CC_NAME"];
        $CC_DESCRIPTION=$row["CC_DESCRIPTION"];
        $CC_IMAGE=$row["CC_IMAGE"];
        $TCO_CODE=$row["TCO_CODE"];
        $TCO_DESCRIPTION=$row["TCO_DESCRIPTION"];
        $TUM_DESCRIPTION=$row["TUM_DESCRIPTION"];
        $TC_QUANTITE_PAR_UNITE=$row["TC_QUANTITE_PAR_UNITE"];
        if ( $TC_QUANTITE_PAR_UNITE > 1 ) $TUM_DESCRIPTION .="s";
        $i=$i+1;
        if ( $i%2 == 0 ) {
              $mycolor=$mylightcolor;
        }
        else {
            $mycolor="#FFFFFF";
        }
          
        echo "<tr onclick=\"this.bgColor='#33FF00'; displaymanager('$TC_ID')\" >
                <td align=left> $CC_CODE</td>
                <td align=center>".$TC_DESCRIPTION."</td>
              <td align=center>".$TCO_DESCRIPTION."</td>
              <td align=center>".$TC_QUANTITE_PAR_UNITE." ".$TUM_DESCRIPTION."</td>
          </tr>";
          
    }
    echo "</table>";
}
echo @$later;
writefoot();
?>
