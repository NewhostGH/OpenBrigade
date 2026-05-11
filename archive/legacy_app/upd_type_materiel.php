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

writehead();
echo "
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/type_materiel.js'></script>
";
echo "</head>";
echo "<body>";

$TM_ID=$_GET["id"];
if (isset ($_GET["from"])) $from=$_GET["from"];
else $from=0;

//=====================================================================
// affiche la fiche type de matériel
//=====================================================================

$query="select tm.TM_CODE,tm.TM_DESCRIPTION,tm.TM_USAGE, cm.CM_DESCRIPTION,cm.PICTURE, tm.TM_LOT, tt.TT_CODE, tt.TT_NAME
        from type_materiel tm left join type_taille tt on tt.TT_CODE=tm.TT_CODE, 
        categorie_materiel cm
        where tm.TM_ID='".$TM_ID."'
        and cm.TM_USAGE=tm.TM_USAGE
        order by TM_USAGE asc";
        
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$TM_CODE=$row["TM_CODE"];
$TM_DESCRIPTION=$row["TM_DESCRIPTION"];
$TM_USAGE=$row["TM_USAGE"];
$TM_LOT=$row["TM_LOT"];
$CM_DESCRIPTION=$row["CM_DESCRIPTION"];
$PICTURE=$row["PICTURE"];
$TT_CODE=$row["TT_CODE"];
$TT_NAME=$row["TT_NAME"];

$query="select count(1) from materiel where TM_ID='".$TM_ID."'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$nombre=$row[0];

echo "<div align=center class='table-responsive'>";

echo "<form name='materiel' action='save_type_materiel.php'>";
echo "<input type='hidden' name='TM_ID' value='$TM_ID'>";
echo "<input type='hidden' name='TM_CODE' value=\"$TM_CODE\">";
echo "<input type='hidden' name='TM_USAGE' value='$TM_USAGE'>";
echo "<input type='hidden' name='TM_LOT' value='0'>";
echo "<input type='hidden' name='operation' value='update'>";


echo "<div class='table-responsive'>";
echo "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong> Modifier type de matériel</span><span class='badge' style='float:right'>$nombre article(s)</span></strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// ligne catégorie
//=====================================================================

$query="select TM_USAGE, CM_DESCRIPTION from categorie_materiel
         where TM_USAGE<>'ALL' order by TM_USAGE asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
            <td>Catégorie $asterisk</td>
            <td align=left>
          <select class='form-control select-sm' name='TM_USAGE' id='TM_USAGE' onchange='change_type();'>";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["TM_USAGE"] == $TM_USAGE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["TM_USAGE"]."\" $selected>".$row["TM_USAGE"]." - ".$row["CM_DESCRIPTION"]."</option>";
              }
 echo "</select>";
 echo "</td>
      </tr>";

//=====================================================================
// ligne code
//=====================================================================

echo "<tr>
            <td>Type $asterisk</td>
            <td align=left><input type='text' class='form-control form-control-sm' name='TM_CODE' size='20' value=\"$TM_CODE\">";        
echo " </td>
      </tr>";
      
//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td>Description</td>
            <td align=left><input type='text' class='form-control form-control-sm' name='TM_DESCRIPTION' size='40' value=\"$TM_DESCRIPTION\">";        
echo " </td>
      </tr>";
      
//=====================================================================
// vetement taille
//=====================================================================
      
$query="select TT_CODE, TT_NAME, TT_DESCRIPTION from type_taille order by TT_ORDER";
$result=mysqli_query($dbc,$query);

if ( $TM_USAGE == 'Habillement' ) $style="";
else $style="style='display:none'";

echo "<tr id=row_tt $style>
            <td>Mesure Taille $asterisk</td>
            <td align=left>
          <select name='TT_CODE' title='choisir le type de mesure pour les tailles de ce vetement'>";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["TT_CODE"] == $TT_CODE ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["TT_CODE"]."\" title=\"".$row["TT_DESCRIPTION"]."\" $selected>".$row["TT_NAME"].": ".$row["TT_DESCRIPTION"]."</option>";
              }
 echo "</select>";
      
      
//=====================================================================
// lot de matériel
//=====================================================================
if ( $TM_LOT == 1 ) $checked='checked';
else $checked='';

echo "<tr>
            <td>Lot de matériel</td>
            <td align=left>
            <label class='switch'><input type='checkbox' name='TM_LOT' value='1' $checked
            title=\"Cochez la case si ce type définit un lot de matériel\"><span class='slider round'></span></label>
            <font size=1><i>des pièces de matériel peuvent être intégrées dans un lot<i></font>";        
echo " </td>
      </tr>";    

echo "</table></div></div></div>";
echo "<input type='submit' class='btn btn-danger' name='operation' value='Supprimer'> ";
echo "<input type='submit' class='btn btn-success' name='operation' value='Sauvegarder'> ";
echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\"> ";
echo "</form>";

writefoot();
?>
