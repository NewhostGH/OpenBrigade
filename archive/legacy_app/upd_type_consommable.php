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
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/type_consommable.js'></script>";
echo "</head>";
?>
<script>
function redirect_edit() {
    self.location.href = "parametrage.php?tab=3&child=4&ope=edit_cat";
    return true
}
</script>
<?php
echo "<body>";

$TC_ID=$_GET["id"];
if (isset ($_GET["from"])) $from=$_GET["from"];
else $from=0;

//=====================================================================
// affiche la fiche type de consommable
//=====================================================================
$query="select tc.TC_ID, tc.CC_CODE, tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE,
        cc.CC_NAME, cc.CC_DESCRIPTION, cc.CC_IMAGE, cc.CC_ORDER,
        tco.TCO_CODE, tco.TCO_DESCRIPTION, 
        tc.TC_QUANTITE_PAR_UNITE, tc.TC_UNITE_MESURE,
        tum.TUM_CODE, tum.TUM_DESCRIPTION, tc.TC_PEREMPTION
        from type_consommable tc, categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum
        where tc.CC_CODE=cc.CC_CODE
        and tum.TUM_CODE = tc.TC_UNITE_MESURE
        and tco.TCO_CODE = tc.TC_CONDITIONNEMENT
        and tc.TC_ID='".$TC_ID."'";
        
$result=mysqli_query($dbc,$query);
$row = mysqli_fetch_array($result);
$TC_DESCRIPTION=ucfirst(@$row["TC_DESCRIPTION"]);
$TCO_CODE=@$row["TCO_CODE"];
$TC_QUANTITE_PAR_UNITE=@$row["TC_QUANTITE_PAR_UNITE"];
$TC_PEREMPTION=@$row["TC_PEREMPTION"];
$TUM_CODE=@$row["TUM_CODE"];
$CC_CODE =@$row["CC_CODE"];

echo "<div class='table-responsive' align=center>
<form name='consommable' action='save_type_consommable.php' method='POST'>";

// update
if ( $TC_ID > 0 ) {
    echo "<input type='hidden' name='TC_ID' value='$TC_ID'>";

    $query="select sum(C_NOMBRE) from consommable where TC_ID='".$TC_ID."'";
    $result=mysqli_query($dbc,$query);
    $row1=mysqli_fetch_array($result);
    $nombre=intval($row1[0]);
    $title= 'Modifier type de consommable';
}
// insert
else {

    echo "<input type='hidden' name='TC_ID' value='0'>";

    $query="select CC_CODE, CC_NAME , CC_IMAGE from categorie_consommable";
    if ( $catconso <> 'ALL' ) 
        $query .= " where CC_CODE='".$catconso."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $CC_CODE=$row["CC_CODE"];
    $CC_NAME=$row["CC_NAME"];
    $CC_IMAGE=$row["CC_IMAGE"];
    $title="Nouveau type de consommable";
}

echo "<div class='table-responsive'>";
echo "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong> $title </strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// ligne catégorie
//=====================================================================

$query="select CC_CODE, CC_NAME from categorie_consommable
         where CC_CODE<>'ALL' order by CC_ORDER asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td >Catégorie $asterisk</td>
            <td align=left>
          <select class='form-control-sm' name='CC_CODE' >";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["CC_CODE"] == $CC_CODE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["CC_CODE"]."\" $selected>".$row["CC_CODE"]." - ".$row["CC_NAME"]."</option>";
              }
 echo "</select>";
 echo " <i class='fa fa-pencil-alt fa-lg' onClick=\"redirect_edit()\"/ title=\"Editer une catégorie de matériel\"></i></td>";
 echo "</td>
      </tr>";

//=====================================================================
// ligne code
//=====================================================================

echo "<tr>
            <td >Type $asterisk</td>
            <td align=left><input class='form-control form-control-sm' type='text' name='TC_DESCRIPTION' size='40' value=\"$TC_DESCRIPTION\">";
echo " </td>
      </tr>";
      
//=====================================================================
// ligne conditionnement
//=====================================================================

$query="select TCO_CODE, TCO_DESCRIPTION from type_conditionnement
        order by TCO_ORDER asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td >Conditionnement $asterisk</td>
            <td align=left>
          <select class='form-control form-control-sm' name='TCO_CODE'>";
               while ($row=mysqli_fetch_array($result)) {
                  if ( $row["TCO_CODE"] == $TCO_CODE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["TCO_CODE"]."\" $selected>".$row["TCO_DESCRIPTION"]."</option>";
              }
echo "</select>";
echo "</td>
      </tr>";
     
//=====================================================================
// ligne quantité par conditionnement et unité de mesure
//=====================================================================

$query="select TUM_CODE, TUM_DESCRIPTION from type_unite_mesure
        order by TUM_ORDER asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td >Contenance  $asterisk</td>
            <td>
          <input style='width: 49%' class='form-control-sm' type='text' size='3' maxlength='4' name='TC_QUANTITE_PAR_UNITE' 
            value='".$TC_QUANTITE_PAR_UNITE."' title='Précisez la quantité ou le nombre pour une unité de conditionnement'
            onchange=\"checkFloat(this,'".$TC_QUANTITE_PAR_UNITE."')\";>
          <select style='width: 50%' class='form-control-sm' name='TUM_CODE' title='précisez l'unité de mesure'>";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["TUM_CODE"] == $TUM_CODE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["TUM_CODE"]."\" $selected>".$row["TUM_DESCRIPTION"]."s</option>";
              }
echo "</select>";
echo "</td>
      </tr>";
  
//=====================================================================
// lot de matériel
//=====================================================================
if ( $TC_PEREMPTION == 1 ) $checked='checked';
else $checked='';

echo "<tr>
            <td>Périssable</td>
            <td align=left>
            <input type='checkbox' name='TC_PEREMPTION' value='1' $checked
            title=\"Cochez la case si ce type de consommable est périssable\">
            <font size=1><i>denrée périssable, avec une date limite<i></font>";
echo " </td>
      </tr>";

echo "</table></div></div>";

if ( $TC_ID <> 0 ){
	echo "<input type='submit' class='btn btn-danger' name='operation' value='Supprimer'> ";
	echo "<input type='submit' class='btn btn-success' name='operation' value='Sauvegarder'> ";
}
else
	echo "<button class='btn btn-success' name='operation' value='Ajouter' onclick='submit()'>Sauvegarder</button> ";
echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\"> ";

echo "</form>";
writefoot();
?>
