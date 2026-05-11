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
?>
<script>
function suppress(cat_old) {
    if (confirm("Voulez vous vraiment supprimer cette catégorie?\nTous les éléments de cette catégorie seront supprimés."))
        self.location.href="del_edit_categorie_consommable.php?cat_old="+cat_old;
    else
        self.location.href="parametrage.php?tab=3&child=4&ope=edit&id=0";
}
</script>
<?php
if (isset($_POST['CC_CODE_PREV'])) $cat_old = secure_input($dbc, $_POST['CC_CODE_PREV']);
else $cat_old = '';
if (isset($_POST['CC_CODE'])) $cat_code = secure_input($dbc, $_POST['CC_CODE']);
else $cat_code = '';
if (isset($_POST['CC_NAME'])) $cat_name = secure_input($dbc, $_POST['CC_NAME']);
else $cat_name = '';
if (isset($_POST['logo'])) $logo = secure_input($dbc, $_POST['logo']);
else $cat_name = '';
if (isset($_POST['CC_DESCRIPTION'])) $description = secure_input($dbc, $_POST['CC_DESCRIPTION']);
else $description = '';
if (isset($_POST['Delete'])) $del = secure_input($dbc, $_POST['Delete']);
else $del = '';

$cat_old = STR_replace("\"","",$cat_old);
$cat_code = STR_replace("\"","",$cat_code);
$cat_name = STR_replace("\"","",$cat_name);
$description = STR_replace("\"","",$description);
$logo = STR_replace("\"","",$logo);

//=====================================================================
// Error
//=====================================================================

if ($cat_name == '' || $cat_code == '') {
    header("Location: edit_categorie_sonsommable.php?error=fill_all");
    exit();
}
//=====================================================================
// New categorie
//=====================================================================

if ($cat_old === 'Nouvelle catégorie') {
    $query = "INSERT INTO categorie_consommable(CC_CODE, CC_NAME, CC_IMAGE, CC_DESCRIPTION) VALUES (\"".$cat_code."\", \"".$cat_name."\", \"".$logo."\", \"".$description."\")";
    $result = mysqli_query($dbc, $query);
}

//=====================================================================
// Rename categorie
//=====================================================================

if ($cat_old <> '' && $cat_code <> '') {
    if ($cat_old != $cat_code) {
        $query = "UPDATE type_materiel SET CC_CODE = \"".$cat_code."\" WHERE type_materiel.CC_CODE = \"".$cat_old."\""; 
        $result = mysqli_query($dbc, $query);
        $query = "UPDATE categorie_consommable SET CC_CODE = \"".$cat_code."\" WHERE categorie_consommable.CC_CODE = \"".$cat_old."\""; 
        $result = mysqli_query($dbc,$query);
    }
}

//=====================================================================
// Rename name
//=====================================================================

if ($cat_old <> '' && $cat_code <> '') {
    if ($cat_old != $cat_code) {
        $cat_old = $cat_code;
    }
    $query = "UPDATE categorie_consommable SET CC_NAME = \"".$cat_name."\" WHERE categorie_consommable.CC_CODE = \"".$cat_old."\"";
    $result = mysqli_query($dbc,$query);
}

//=====================================================================
// Rename description
//=====================================================================

if ($logo <> 'utensils') {
    $query = "UPDATE categorie_consommable SET CC_IMAGE = \"".$logo."\" WHERE categorie_consommable.CC_CODE = \"".$cat_old."\"";
    $result = mysqli_query($dbc, $query);
}

//=====================================================================
// Redirect
//=====================================================================

if ($del === 'Supprimer catégorie')
    echo "<body onload=suppress('".$cat_old."')>";
else
    header("Location: parametrage.php?tab=3&child=4&id=0");
exit();
?>