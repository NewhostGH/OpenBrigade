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
test_permission_level(18);
?>
<script type='text/javascript' src='js/upd_grades.js'></script>
<script type='text/javascript' src='js/swal.js'></script>
<?php

if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if (isset($_GET['upd'])) $upd = secure_input($dbc, $_GET['upd']);
else $upd = '';
if (isset($_GET['activ'])) $activ = secure_input($dbc, $_GET['activ']);
else $activ = 2;
if (isset($_GET['error'])) $error = secure_input($dbc, $_GET['error']);
else $error = '';
if (isset($_GET['operation'])) $operation = secure_input($dbc, $_GET['operation']);
else $operation = '';
if (isset($_GET['old'])) $old = secure_input($dbc, $_GET['old']);
else $old = '';
if ($ope == 'edit_cat') {
    include_once ("edit_categorie_grades.php");
    exit;
}
if ($upd) {
    include_once ("upd_grades.php");
    exit;
}


//=====================================================================
// Error
//=====================================================================
if ($error == 'fill_all' && $operation == "insert"){
    echo '<script>
                errorGrade("insert");
            </script>';
    exit;
}elseif ($error == 'fill_all' && $operation == "update"){
    echo '<script>
                errorGrade("update", "'.$old.'");
            </script>';
    exit;
}
if ($error == 'exist' && $operation == "insert"){
    echo '<script>
                errorGradeExist("insert");
            </script>';
    exit();
}elseif ($error == 'exist' && $operation == "update"){
    echo '<script>
                errorGradeExist("update", "'.$old.'");
            </script>';
    exit;
}

echo "<div class='div-decal-left' align=left>";
//=====================================================================
//filtre catégorie de grades
//=====================================================================
$order = 'CG_CODE';
echo "<div class='div-decal-left' style='float:left'><select id='usage' name='usage' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
    onchange=\"orderfilter(document.getElementById('usage').value)\">";
$query2=" select CG_CODE, CG_DESCRIPTION from categorie_grade";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
    $CG_CODE = $row["CG_CODE"];
    $CG_DESCRIPTION = $row["CG_DESCRIPTION"];
    $selected = $CG_CODE == $catGrade ? 'selected' : '';

    if($CG_CODE == 'ALL')
        echo "<option value='" . $CG_CODE . "' class='option-ebrigade' $selected>Toutes les catégories</option>\n";
    else
        echo "<option value='" . $CG_CODE . "' class='option-ebrigade' $selected>" . $CG_DESCRIPTION . "</option>\n";
}
echo "</select></div>";


//=====================================================================
//filtre catégories avec grades actifs
//=====================================================================
echo "<div class='div-decal-left' style='float:left'><select id='activ' name='activ' class='selectpicker'  data-style='btn-default' data-container='body'
    onchange=\"orderfilterActiv(document.getElementById('activ').value)\">";
$optionActiv = [2 => "Tous" , 1 => "Actifs", 0 => "Inactifs"];
foreach ($optionActiv as $option => $value){
    $selected =  $option == $activ ? "selected" : "";
    echo "<option value='" . $option . "' class='option-ebrigade'$selected >$value</option>\n";
}
echo "</select></div>";

if ( check_rights($_SESSION['id'], 18)) {
    //=====================================================================
    // Boutons
    //=====================================================================
    if ($catGrade == "ALL") echo "<div class='dropdown-right' style='float:right' align=right > <a class='btn btn-secondary disabled' value='Modifier' name='modifierCategorie' ><i class=\"fas fa-edit \"></i> Catégorie</a></div>";
    else echo "<div class='dropdown-right' style='float:right' align=right> <a class='btn btn-primary' value='Modifier' name='modifierCategorie' onclick=\"bouton_redirect('parametrage.php?tab=5&child=14&ope=edit_cat&updCat=1');\"><i class=\"fas fa-edit \"></i> Catégorie</a></div>";
    echo "<div class='dropdown-right' style='float:right' align=right> <a class='btn btn-success' value='Ajouter' name='ajouterCategorie' onclick=\"bouton_redirect('parametrage.php?tab=5&child=14&ope=edit_cat&insertCat=1');\"><i class=\"fas fa-plus-circle\"></i> Catégorie</a></div>";
    echo "<div class='dropdown-right ' style='float:right' align=right> <a class='btn btn-success' value='Ajouter' name='ajouterGrade' title='' onclick=\"bouton_redirect('parametrage.php?tab=5&child=14&operation=insert&upd=1');\"><i class=\"fas fa-plus-circle\" style='color:white'></i> Grade</a></div>";

}

$query=" select g.G_CATEGORY, g.G_ICON, g.G_DESCRIPTION, g.G_GRADE, g.G_LEVEL, g.G_FLAG, COUNT(p.P_GRADE) AS NB_grade_actif from grade g
left join categorie_grade cg on g.G_CATEGORY = cg.CG_CODE
left join pompier p on (p.P_GRADE = g.G_GRADE and p.P_STATUT <> 'EXT')
where g.G_CATEGORY = cg.CG_CODE";
$queryAdd = "";
if ($catGrade != 'ALL') $query .= "\nand cg.CG_CODE='".$catGrade."'";
if($activ == 0 || $activ ==  1) $queryAdd .= "\nand g.G_FLAG='".$activ."'";
$queryAdd .= "\nGROUP BY g.G_CATEGORY, g.G_ICON, g.G_DESCRIPTION, g.G_GRADE, g.G_LEVEL, g.G_FLAG ";
$queryAdd .= "\norder by g.G_LEVEL DESC";
$query .= $queryAdd;

echo "<div align=center class='container-fluid pl-0 pt-5 pt-sm-0 '>";


$_SESSION['query'] = $query;

    echo "<table class='table-sm table-hover new-table'
 id='table'
 data-ajax='ajaxRequest'
 data-locale='fr-FR'
 data-toggle='table'
 data-sort-class='table-active'
 data-search='true'
 data-page-size='25'
 data-minimum-count-columns='4'  
 data-show-columns='true'
 data-pagination='true'
 data-sortable='true'
 data-search-align='right'
 style='border-collapse: collapse !important;'>";

// ===============================================
// premiere ligne du tableau
// ===============================================
    echo "<thead>";
    echo "<tr class='widget-title'>";
    echo "<th data-field='grade' data-sortable='true'>Grade</th>";
    echo "<th  data-field='categorie' data-sortable='true'>Catégorie</th>";
    echo "<th  data-field='description' data-sortable='true'>Description</th>";
    echo "<th  data-field='code' data-sortable='true' class='hide_mobile'>Code</th>";
    echo "<th data-field='niveau' data-sortable='true' class='hide_mobile'>Niveau</th>";
    echo "<th  data-field='actif'  class='hide_mobile'>Actif</th>";
   // echo "<th data-field='action'>Action</th>";
    echo "</tr>";
    echo "</thead>";
    echo "</table>";
?>
<script>
  function ajaxRequest(params) {
      var url = 'grades_load.php?data=1'
    $.get(url + '?' + $.param(params.data)).then(function (res) {
        params.success(res)
    })
  }

  $("#table").on("click-cell.bs.table", function (field, value, row, $element) {
      if ( value != 'actif' ) {
          url = "parametrage.php?tab=5&child=14&operation=update&upd=1&old=" + $element.id;
          self.location.href = url;
      }
  });

</script>

<?php
echo "</div>";
writefoot();
?>

