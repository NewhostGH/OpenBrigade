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
<script type='text/javascript' src='js/type_materiel.js'></script>
</script>
";
?>
<script>
function redirect_edit() {
    self.location.href = "parametrage.php?tab=3&child=3&ope=edit_cat";
    return true
}
</script>
<?php
echo "</head>";

echo "<body>";

//=====================================================================
// affiche la fiche type de matériel
//=====================================================================

$query="select CM_DESCRIPTION,PICTURE from categorie_materiel
        where TM_USAGE='ALL'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$cmt=$row["CM_DESCRIPTION"];
$picture=$row["PICTURE"];

echo "<form name='materiel' action='save_type_materiel.php'>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='TM_ID' value='0'>";
echo "<input type='hidden' name='TM_LOT' value='0'>";
    
echo "<div class='table-responsive'>";
echo "<div class='col-sm-5'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong> Nouveau type de matériel </strong></div>
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
          <select class='form-control form-control-sm' name='TM_USAGE'  id='TM_USAGE' onchange = 'change_type();'>";
               while ($row=@mysqli_fetch_array($result)) {
                  if ( $row["TM_USAGE"] == $catmateriel ) $selected='selected';
                  else $selected='';
                  echo "<option value=\"".$row["TM_USAGE"]."\" $selected>".$row["TM_USAGE"]." - ".$row["CM_DESCRIPTION"]."</option>";
            }
echo "<td align = right>
      <i class='fa fa-pencil-alt fa-lg' onClick=\"redirect_edit()\"/ title=\"Editer une catégorie de matériel\"></i></td>";
echo "</select>";
echo "</td>
      </tr>";

//=====================================================================
// ligne code
//=====================================================================

echo "<tr>
            <td>Type $asterisk</td>
            <td align=left><input class='form-control form-control-sm' type='text' name='TM_CODE' size='20' value=''>
            <td align = right>";
echo " </td>
      </tr>";
  
      
//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td>Description</td>
            <td align=left><input class='form-control form-control-sm' type='text' name='TM_DESCRIPTION' size='40' value=''>
            <td align = right>";
echo " </td>
      </tr>";


//=====================================================================
// vetement taille
//=====================================================================
      
$query="select TT_CODE, TT_NAME, TT_DESCRIPTION from type_taille order by TT_ORDER";
$result=mysqli_query($dbc,$query);

if ( $catmateriel == 'Habillement' ) $style="";
else $style="style='display:none'";

echo "<tr id=row_tt $style>
            <td>Mesure Taille $asterisk</td>
            <td align=left>
          <select class='form-control form-control-sm' name='TT_CODE' title='choisir le type de mesure pour les tailles de ce vetement'>";
               while ($row=@mysqli_fetch_array($result)) {
                  echo "<option value=\"".$row["TT_CODE"]."\" title=\"".$row["TT_DESCRIPTION"]."\" >".$row["TT_NAME"].": ".$row["TT_DESCRIPTION"]."</option>";
              }
 echo "</select>";
            
      
//=====================================================================
// lot de matériel
//=====================================================================
echo "<tr>
            <td>Lot de matériel</td>
            <td align=left>
            <input type='checkbox' name='TM_LOT' value='1'
            title=\"Cochez la case si ce type définit un lot de matériel\">
            <font size=1><i>des pièces de matériel peuvent être intégrées dans un lot<i></font>
            <td align = right>";
echo " </td>
      </tr>";    

echo "</table></div></div>";
echo "<button class='btn btn-success' name='operation' value='Ajouter' onclick='submit()'>Sauvegarder</button> ";

echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\"> ";
echo "</form>";
writefoot();
?>
