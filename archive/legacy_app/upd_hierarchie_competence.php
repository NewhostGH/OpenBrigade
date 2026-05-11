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
if (isset($_GET["hierarchie"])) $hierarchie=secure_input($dbc,$_GET["hierarchie"]);
else $hierarchie="";
?>
<script type='text/javascript' src='js/competence.js'></script>
<?php

writehead();

echo "
</head>
<body>";

//=====================================================================
// affiche la hierarchie
//=====================================================================
if ( $hierarchie <> "" ) {
    $query="select PH_CODE, PH_NAME, PH_HIDE_LOWER, PH_UPDATE_LOWER_EXPIRY, PH_UPDATE_MANDATORY from poste_hierarchie
         where PH_CODE='".$hierarchie."'";
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $title="Hiérarchie de Compétence - $PH_CODE";
    $operation='update';
}
else {
    $PH_CODE="";
    $PH_NAME="";
    $PH_HIDE_LOWER=1;
    $PH_UPDATE_LOWER_EXPIRY=1;
    $PH_UPDATE_MANDATORY=1;
    $title="Ajout nouvelle Hiérarchie de Compétence";
    $operation='insert';
}

echo "<div align=center class='table-responsive'>";
echo "<form name='hierarchie' action='save_hierarchie_competence.php'>";

echo "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> $title </strong></div>
            </div>
            <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing=0 border=0>";
echo "<input type='hidden' name='OLD_PH_CODE' value='$PH_CODE'>";
echo "<input type='hidden' name='operation' value='$operation'>";

//=====================================================================
// ligne description
//=====================================================================

echo "<tr>
            <td><b>Code</b>$asterisk</td>
            <td align=left>
            <input type='text' name='PH_CODE' class='form-control form-control-sm' size='15' maxlength='15' value=\"$PH_CODE\"  onchange=\"isValid2(form.PH_CODE,'$PH_CODE');\">
            </td>";
echo "</tr>";

echo "<tr>
            <td><b>Description</b> $asterisk</td>
            <td align=left>
            <input type='text' name='PH_NAME' class='form-control form-control-sm' size='30' maxlength='30' value=\"$PH_NAME\" onchange=\"isValid4(form.PH_NAME, '');\">";
echo "</tr>";


$t1="\"Montrer seulement la compétence la plus haute de la hiérarchie pour une personne sur les événements, masquer les autres\"";
$t2="\"En cas de mise à jour de la date d'expiration sur une compétence de la hiérarchie, la mise à jour automatique des dates des compétences inférieures est possible.\"";
$t3="\"Rendre obligatoire la validation des compétences inférieures, si non cochée elle reste facultative sur les événements formations.\"";

if ( $PH_HIDE_LOWER == 1 ) $checked='checked';
else $checked='';
echo "<tr>
            <td><b>Masquer les compétences inférieures</b></td>
            <td align=left>
            <input type='checkbox' name='PH_HIDE_LOWER' value='1' $checked title=".$t1.">
            <span class=small2> sur les activités</span>";
echo "</tr>";

if ( $PH_UPDATE_LOWER_EXPIRY == 1 ) $checked='checked';
else $checked='';
echo "<tr>
            <td><b>Prolonger les compétences inférieures</b></td>
            <td align=left>
            <input type='checkbox' name='PH_UPDATE_LOWER_EXPIRY' id='PH_UPDATE_LOWER_EXPIRY' value='1' $checked title=".$t2." onchange='checkProlonge();'>
            <span class=small2>les compétences inférieures peuvent être prolongées</span>";
echo "</tr>";

if ( $PH_UPDATE_LOWER_EXPIRY == 1 ) $disabled='';
else $disabled='disabled';
if ( $PH_UPDATE_MANDATORY == 1 ) $checked='checked';
else $checked='';
echo "<tr>
            <td align=right ><i>Obligatoire</i></td>
            <td align=left>
            <input type='checkbox' name='PH_UPDATE_MANDATORY' id='PH_UPDATE_MANDATORY' value='1' $disabled $checked title=".$t3.">
            <span class=small2>les compétences inférieures sont obligatoirement prolongées</span>";
echo "</tr>";
 
// afficher les compétences de cette hiérarchie
$queryp="select PS_ID, TYPE, DESCRIPTION, PH_LEVEL
        from  poste p
        where PH_CODE='".$PH_CODE."'
        order by PH_LEVEL asc";
$resultp=mysqli_query($dbc,$queryp);

if ( @mysqli_num_rows($resultp) > 0 ) {
    echo "<tr>
            <td colspan=2><strong>
            Compétences faisant partie de cette hiérarchie</strong></td>
        </tr>";
        
    while (custom_fetch_array($resultp)) {
        $DESCRIPTION=strip_tags($DESCRIPTION);
        echo "<tr>
            <td><b>Niveau ".$PH_LEVEL." - $TYPE</b></td>
            <td align=left><a href=parametrage.php?tab=1&child=7&ope=edit&&pid=$PS_ID>$DESCRIPTION</a>";
        echo "</tr>";
    }
}

//=====================================================================
// bas de tableau
//=====================================================================
echo "</table></div></div>";
if ( $hierarchie <> "" ) {
    echo "<input type='hidden' name='OLD_PH_CODE' value='$PH_CODE'>";
    //echo "<input type='hidden' name='PH_CODE' value='$PH_CODE'>";
    //echo "<input type='hidden' name='PH_NAME' value='$PH_NAME'>";
    echo "<input type='submit' class='btn btn-danger' name='operation' value='Supprimer'> ";
    echo "<input type='submit' class='btn btn-success' name='operation' value='Sauvegarder'> ";
}
else
    echo "<input type='submit' class='btn btn-success' name='operation' value='Ajouter'> ";
echo "<input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"redirect();\"> ";
echo "</form>";


echo "</div>";
?>
