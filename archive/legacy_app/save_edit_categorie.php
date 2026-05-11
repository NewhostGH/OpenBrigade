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
        self.location.href="del_edit_categorie.php?cat_old="+cat_old;
    else
        self.location.href="parametrage.php?tab=3&child=3";
}
</script>
<?php
if (isset($_POST['TM_USAGE_PREV'])) $cat_old = secure_input($dbc,$_POST["TM_USAGE_PREV"]);
else $cat_old = '';
if (isset($_POST['TM_USAGE'])) $cat_value = secure_input($dbc, $_POST['TM_USAGE']);
else $cat_value = '';
if (isset($_POST['CM_DESCRIPTION'])) $cat_description = secure_input($dbc, $_POST['CM_DESCRIPTION']);
else $cat_description = '';
if (isset($_POST['logo'])) $logo = secure_input($dbc, $_POST['logo']);
else $logo = '';
if (isset($_POST['Delete'])) $del = secure_input($dbc, $_POST['Delete']);
else $del = '';

$cat_old = STR_replace("\"","",$cat_old);
$cat_value = STR_replace("\"","",$cat_value);
$cat_description = STR_replace("\"","",$cat_description);
$logo = STR_replace("\"","",$logo);

//=====================================================================
// New categorie
//=====================================================================

if ($cat_description == '' || $cat_value == '') {
    header("Location: edit_categorie.php?error=fill_all");
    exit();
}

if ($cat_old === 'Nouvelle catégorie') {
    $query = "INSERT INTO categorie_materiel(TM_USAGE, CM_DESCRIPTION, PICTURE) VALUES (\"".$cat_value."\", \"".$cat_description."\", \"".$logo."\")";
    $result = mysqli_query($dbc, $query);
}

//=====================================================================
// Rename categorie
//=====================================================================

if ($cat_old <> '' && $cat_value <> '') {
    if ($cat_old != $cat_value) {
        $query = "UPDATE type_materiel SET TM_USAGE = \"".$cat_value."\" WHERE type_materiel.TM_USAGE = \"".$cat_old."\""; 
        $result = mysqli_query($dbc, $query);
        $query = "UPDATE categorie_materiel SET TM_USAGE = \"".$cat_value."\" WHERE categorie_materiel.TM_USAGE = \"".$cat_old."\""; 
        $result = mysqli_query($dbc, $query);
    }
}

//=====================================================================
// Rename description
//=====================================================================

if ($cat_old <> '' && $cat_value <> '') {
    if ($cat_old != $cat_value) {
        $cat_old = $cat_value;
    }
    $query = "UPDATE categorie_materiel SET CM_DESCRIPTION = \"".$cat_description."\" WHERE categorie_materiel.TM_USAGE = \"".$cat_old."\""; 
    $result = mysqli_query($dbc, $query);
}

//=====================================================================
// Rename description
//=====================================================================

if ($logo <> 'cog') {
    $query = "UPDATE categorie_materiel SET PICTURE = \"".$logo."\" WHERE type_materiel.TM_USAGE = \"".$cat_old."\"";
    $result = mysqli_query($dbc, $query);
    echo "test";
}

//=====================================================================
// Redirect
//=====================================================================

if ($del === 'Supprimer catégorie')
    echo "<body onload=suppress('".$cat_old."')>";
else
    header("Location: parametrage.php?tab=3&child=3&ope=edit&catmateriel=$cat_value");
exit();
?>