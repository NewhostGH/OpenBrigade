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
?>

<?php
if (isset($_POST['code_cat'])) $cat_code = secure_input($dbc, $_POST['code_cat']);
else $cat_code = '';
if (isset($_POST['description_cat'])) $cat_description = secure_input($dbc, $_POST['description_cat']);
else $cat_description = '';

if(isset($_POST["operation"]) and $_POST["operation"] == "Modifier")
    $operation = "update";
elseif(isset($_POST["operation"]) and $_POST["operation"] == "Sauvegarder")
    $operation = "insert";
$cat_code = strtoupper($cat_code);
$cat_description = ucfirst($cat_description) ;
$cat_exist = count_entities("categorie_grade", "CG_CODE='" . $cat_code . "'");

//=====================================================================
// New categorie
//=====================================================================
if ($operation == "insert") {
    if ($cat_description == '' || $cat_code == '') {
        header("Location: edit_categorie_grades.php?error=fill_all&operation=insert");
        exit();
    }
if ($cat_exist) {
    header("Location: edit_categorie_grades.php?error=exist&operation=insert");
    exit();
}
    $query = "INSERT INTO categorie_grade(CG_CODE, CG_DESCRIPTION) VALUES (\"" . $cat_code . "\", \"" . $cat_description . "\")";
    $result = mysqli_query($dbc, $query);
    header("Location: parametrage.php?tab=5&child=14&catGrade=" . $cat_code . "");
    exit;
}

//=====================================================================
// Update categorie
//=====================================================================
if ($operation == "update"){
    if ($cat_description == '' || $cat_code == '') {
        header("Location: edit_categorie_grades.php?error=fill_all&operation=upd");
        exit();
    }
if ($cat_exist) {
    header("Location: edit_categorie_grades.php?error=exist&operation=upd");
    exit();
}
        $query = "UPDATE categorie_grade SET CG_CODE = \"".$cat_code."\", CG_DESCRIPTION = \"".$cat_description."\" WHERE CG_CODE = \"".$catGrade."\"";
        $result = mysqli_query($dbc, $query);
        $query2 = "UPDATE grade SET G_CATEGORY = \"".$cat_code."\" WHERE G_CATEGORY = \"".$catGrade."\"";
        $result2 = mysqli_query($dbc, $query2);
        $catGrade = $cat_code;
        header("Location: parametrage.php?tab=5&child=14&catGrade=".$catGrade."");
    }

?>
