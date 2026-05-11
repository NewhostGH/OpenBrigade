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
?>
<script type="text/javascript" src="js/paramfn.js"></script>
<?php

$title="Fonction";
if (isset($_GET["TFV_ID"])) $TFV_ID=intval($_GET["TFV_ID"]);
else $TFV_ID=0;


echo "<form name='paramfnv' action='paramfnv_save.php'>";
//=====================================================================
// affiche la fiche
//=====================================================================

if ( $TFV_ID > 0 ) {
    $query="select TFV_ID,TFV_ORDER,TFV_NAME,TFV_DESCRIPTION from type_fonction_vehicule
            where TFV_ID=".$TFV_ID;    
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $TFV_ID=$row["TFV_ID"];
    $TFV_ORDER=$row["TFV_ORDER"];
    $TFV_NAME=$row["TFV_NAME"];
    $TFV_DESCRIPTION=$row["TFV_DESCRIPTION"];

    $title = 'Modifer fonction de véhicule';
    echo "<input type='hidden' name='TFV_ID' value='".$TFV_ID."'>";
}
else {
    $TFV_NAME='';
    $TFV_ORDER='';
    $TFV_DESCRIPTION='';
    $title = 'Nouvelle fonction de véhicule';
    echo "<input type='hidden' name='TFV_ID' value='0'>";
}

echo "<div class='table-responsive'>";
echo "<div class='col-sm-3'>
        <div class='card hide card-default graycarddefault cardtab' style='margin-bottom:5px'>
            <div class='card-header graycard cardtab'>
                <div class='card-title'><strong> $title</strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// ligne nom
//=====================================================================

echo "<tr>
            <td>
            Nom $asterisk</td>
            <td align=left>
            <input type='text' class='form-control form-control-sm' name='TFV_NAME' value=\"".$TFV_NAME."\"
            ";//title=\"Choisir la description de la fonction, maximum 40 caractères\" >";
echo "</tr>";


//=====================================================================
// ligne ordre
//=====================================================================

echo "<tr>
            <td>Ordre dans la liste$asterisk</td>
            <td align=left>
          <select name='TFV_ORDER' class='form-control form-control-sm'
          title=\"Choisir l'ordre de la fonction dans la liste déroulante listant les fonctions applicables pour les véhicules\">";
          for ($i=1 ; $i<=20 ; $i++) {
            if ($TFV_ORDER == $i) $selected="selected";
            else $selected="";
            echo "<option value='$i' $selected>$i</option>";
          }
           echo "</select>";
echo "</tr>";


//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td>
            Description </font></td>
            <td align=left>
            <input type='text' class='form-control form-control-sm' name='TFV_DESCRIPTION' value=\"".$TFV_DESCRIPTION."\"
            title=\"Choisir la description de la fonction, maximum 200 caractères\" >";        
echo "</tr>";

//=====================================================================
// bas de tableau
//=====================================================================

echo "</table></div></div></div>";

if($TFV_ID >0 ){
	echo "<input type='submit' class='btn btn-danger' name='operation' value='Supprimer'> ";
	echo "<input type='submit' class='btn btn-success' id='sauver' name='operation' value='Sauvegarder'> ";
}
else
	echo "<button class='btn btn-success' name='operation' value='Ajouter' onclick='submit()'>Sauvegarder</button> ";
echo "<input type=button class='btn btn-secondary' value=Retour name=annuler onclick=\"redirect2();\"> ";
echo "</form>";
writefoot();
?>
