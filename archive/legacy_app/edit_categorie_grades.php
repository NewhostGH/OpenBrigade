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
<script type='text/javascript' src='js/swal.js'></script>
<script type='text/javascript' src='js/upd_grades.js'></script>
<?php
echo "</head>";

if (isset($_GET['error'])) $error = secure_input($dbc, $_GET['error']);
else $error = '';
if (isset($_GET['insertCat'])) $insertCat = secure_input($dbc, $_GET['insertCat']);
else $insertCat = '';
if (isset($_GET['updCat'])) $updCat = secure_input($dbc, $_GET['updCat']);
else $updCat = '';
if (isset($_GET['operation'])) $operation = secure_input($dbc, $_GET['operation']);
else $operation = '';

echo "<div align = center class = 'table-responsive'>";

//=====================================================================
// Error
//=====================================================================
if ($error == 'fill_all' && $operation == "insert"){
    echo '<script>
                errorCat("insertCat");
            </script>';
    exit;
}
if ($error == 'fill_all' && $operation == "upd"){
    echo '<script>
                errorCat("updCat");
            </script>';
    exit;
}

if ($error == 'exist' && $operation == "insert"){
    echo '<script>
                errorCatExist("insertCat");
            </script>';
exit();
}
if ($error == 'exist' && $operation == "upd"){
    echo '<script>
                errorCatExist("updCat");
            </script>';
    exit();
}

//=====================================================================
// Vérification si catégorie utilisée
//=====================================================================
$query = "select count(1)from grade g
left join categorie_grade cg on g.G_CATEGORY = cg.CG_CODE
where g.G_CATEGORY = '".$catGrade."'";
$numrows = $dbc->query($query)->fetch_row()[0];


//=====================================================================
// Insertion ou modification de catégorie
//=====================================================================
if ($insertCat == 1){
    $titre =  "<div class='card-title'><strong> Créer une catégorie de grades </strong></div>";

    $lines = "<td title='Code limite de  5 caractères.'>Code de la catégorie $asterisk</td>
            <td  align = left><input type = 'text' class='form-control form-control-sm' name = 'code_cat' value = '' size = 30 maxlength='5'></td>
            <td align = right></tr>";
    $lines .= "<tr>
            <td title='Libéllé de la catégorie.'>Description  $asterisk</td>
            <td  align = left><input type = 'text' class='form-control form-control-sm' name = 'description_cat' value = '' size = 30></td>
            <td  align = right></tr>";

} elseif ($updCat == 1){
    $query=" select CG_CODE, CG_DESCRIPTION from categorie_grade WHERE CG_CODE = '".$catGrade."'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_row($result);
    $CG_CODE = $row[0];
    $CG_DESCRIPTION = $row[1];

    $titre = " <div class='card-title'><strong> Modification de la catégorie \"$CG_DESCRIPTION\"</strong></div>";

    $lines = " <td title='Code limite de  5 caractères.'>Code de la catégorie</td>
                <td  align = left><input type = 'text' class='form-control form-control-sm' name = 'code_cat' value = '".$catGrade."' size = 30 maxlength='5' disabled></td>
                <td align = right></tr>";
    $lines .= "<tr>
                <td title='Libéllé de la catégorie.'>Description  $asterisk</td>
                <td  align = left><input type = 'text' class='form-control form-control-sm' name = 'description_cat' value = '".htmlspecialchars($CG_DESCRIPTION, ENT_QUOTES)."' size = 30></td>
                <td  align = right></tr>";
}


//=====================================================================
// Affichage global du formulaire
//=====================================================================
echo "<div class='col-md-6 col-lg-5 '>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
        <div class='card-header graycard'>";
echo $titre;
echo "</div>
                <div class='card-body graycard'>";
echo "<table class='noBorder' cellspacing = 0 border = 0>";
echo "<form action = save_edit_categorie_grades.php? method = 'post'>";
echo $lines;
echo "<tr>";


//=====================================================================
// Submit
//=====================================================================
echo "</table></div></div>";
if ($updCat == 1){
    echo "<input type = 'submit' class = 'btn btn-success' name='operation' value = 'Modifier'></form> ";
    if ($numrows == 0)
    echo "<input type='button' class='btn btn-danger' value='Supprimer' onclick=\"suppressCat('".$catGrade."');\"> ";
    else echo "<input type='button' class='btn btn-danger' value='Supprimer' disabled title='Des grades sont associés à cette catégorie : Catégorie système'> ";
}else echo "<input type = 'submit' class = 'btn btn-success' name='operation' value = 'Sauvegarder'></form> ";

echo "<input type = 'submit' class = 'btn btn-secondary' value = 'Retour' onclick = redirect('parametrage.php?tab=5&child=14&catGrade=ALL');>";
writefoot();
echo "</body>";
?>
