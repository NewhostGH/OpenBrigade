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

<html>
<head>
    <script type='text/javascript' src='js/upd_grades.js'></script>
    <script type='text/javascript' src='js/swal.js'></script>
</head>
<?php

$OLD_G_GRADE=secure_input($dbc,$_POST["OLD_G_GRADE"]);
$G_GRADE=secure_input($dbc,$_POST["G_GRADE"]);
$G_DESCRIPTION=secure_input($dbc,$_POST["G_DESCRIPTION"]);
if (isset($_POST["categorie"])) $new_categorie=secure_input($dbc,$_POST["categorie"]);
else $new_categorie = "";
$G_TYPE=secure_input($dbc,$_POST["G_TYPE"]);
$G_LEVEL=secure_input($dbc,$_POST["G_LEVEL"]);
if (isset($_POST["icon"])) $G_ICON=secure_input($dbc,$_POST["icon"]);
else  $G_ICON = "images/user-specific/DEFAULT.png";
if (isset($_POST["usage"])) $codeCat=secure_input($dbc,$_POST["usage"]);
else $codeCat = "";
if (isset($_POST["oldCat"])) $oldCat=secure_input($dbc,$_POST["oldCat"]);
else $oldCat = "";
if (isset($_GET["codeGrade"])) $codeGrade=secure_input($dbc,$_GET["codeGrade"]);
else $codeGrade = "";


if($_POST["operation"] == "Ajouter")
    $operation = "insert";
else
    $operation = "update";

//=====================================================================
// Vérification si grade utilisé
//=====================================================================
$query = "select count(1)from pompier p
left join grade g on g.G_GRADE = p.P_GRADE
where p.P_GRADE = '".$G_GRADE."' OR p.P_GRADE = '".$OLD_G_GRADE."'" ;
$numrows = $dbc->query($query)->fetch_row()[0];

//=====================================================================
// activation d'un grade
//=====================================================================
if (isset($codeGrade) && $codeGrade != ""){
    $query = "SELECT G_FLAG from grade WHERE G_GRADE = '".$codeGrade."'";
    $result=mysqli_query($dbc,$query);
    $oldFlag = mysqli_fetch_array($result);

    if ($oldFlag[0] == 1){
        $query="UPDATE grade SET G_FLAG = 0 WHERE G_GRADE = '".$codeGrade."'";
    }
    else {
        $query="UPDATE grade SET G_FLAG = 1 WHERE G_GRADE = '".$codeGrade."' ";
    }

    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// update d'un grade
//=====================================================================
if ( $operation == 'update' ) {
    if ($G_GRADE == '' || $G_DESCRIPTION == '' || $G_LEVEL == '' ) {
        header("Location: edit_grades.php?error=fill_all&operation=update&old=".$OLD_G_GRADE);
        exit();
    }
    if ( $G_GRADE <> $OLD_G_GRADE ) {
        $grade_exist = count_entities("grade", "G_GRADE='" . $G_GRADE . "'");
        if ($grade_exist) {
            header("Location: edit_grades.php?error=exist&operation=update&old=" . $OLD_G_GRADE);
            exit();
        }
    }
    if (($new_categorie != "")){
        $G_CATEGORY = $new_categorie;
    }

    $query="update grade set
           G_GRADE=\"".$G_GRADE."\",
           G_DESCRIPTION=\"".$G_DESCRIPTION."\",
           G_CATEGORY=\"".$G_CATEGORY."\",
           G_LEVEL=\"".$G_LEVEL."\",
            G_ICON=\"".$G_ICON."\",
           G_TYPE=\"".$G_TYPE."\"
           where G_GRADE ='".$OLD_G_GRADE."'";
    $result=mysqli_query($dbc,$query);

    if ($numrows){
        $query="update pompier set
           P_GRADE=\"".$G_GRADE."\"
           where P_GRADE ='".$OLD_G_GRADE."'";
        $result=mysqli_query($dbc,$query);
    }
  $codeCat = $new_categorie;
}

//=====================================================================
// insertion grade
//=====================================================================
if ( $operation == 'insert' ) {
    if ($G_GRADE == '' || $G_DESCRIPTION == '' || $G_LEVEL == '' ) {
        header("Location: edit_grades.php?error=fill_all&operation=insert");
        exit();
    }
    $grade_exist = count_entities("grade", "G_GRADE='" . $G_GRADE . "'");
    if ($grade_exist) {
        header("Location: edit_grades.php?error=exist&operation=insert");
        exit();
    }
    $query="insert into grade (G_GRADE, G_DESCRIPTION, G_TYPE, G_CATEGORY, G_FLAG, G_LEVEL, G_ICON)
            values (\"$G_GRADE\",\"$G_DESCRIPTION\",\"$G_TYPE\",\"$codeCat\", 1, \"$G_LEVEL\", \"$G_ICON\")";
    $result=mysqli_query($dbc,$query);
}
echo "<body onload=redirect('parametrage.php?tab=5&child=14&catGrade=".$codeCat."') />";
echo "<html>";
?>

