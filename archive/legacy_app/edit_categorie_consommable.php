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
?>
<script>
function displaymanager(usage) {
    self.location.href = "parametrage.php?tab=3&child=4&ope=edit_cat&usage=" + usage;
    return true
}
function redirect() {
    self.location.href = "parametrage.php?tab=3&child=4&id=0";
    return true
}

function Get_logo(value) {
    if (value.length == 0) {
        document.getElementById("show_logo").innerHTML="";
        return;
    } else
        document.getElementById("show_logo").innerHTML = '<i class="fa fa-'+ value +' fa-2x"></i>';
    return;
}

</script>
<?php
echo "</head>";

if (isset($_GET['usage'])) $usage = secure_input($dbc, $_GET['usage']);
else $usage = '';
if (isset($_GET['error'])) $error = secure_input($dbc, $_GET['error']);
else $error = '';
$CC_CODE = '';
$CC_NAME = '';
$description = '';

if ($usage <> 'Nouvelle catégorie')
  $query = "select CC_IMAGE from categorie_consommable where CC_CODE = '".$usage."'";
else
  $query = "select CC_IMAGE from categorie_consommable";
$result = mysqli_query($dbc, $query);
$row = @mysqli_fetch_array($result);
$CC_IMAGE = @$row["CC_IMAGE"];
echo "<body onload = 'Get_logo(\"".$CC_IMAGE."\")'>";
echo "<div align = center class = 'table-responsive'>";

//=====================================================================
// Error
//=====================================================================

if ($error == 'fill_all')
  echo "<div align = center class = 'table-responsive'><table class = 'noBorder'>
  <tr>
    <div class='alert alert-warning' role='alert' align='center'><i class ='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i>
    Veuillez remplir tous les champs avec un $asterisk.</div>
  </tr><br>";

//=====================================================================
// Tab line 1 / Header
//=====================================================================

echo "<div class='col-sm-6'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Editer une catégorie de consommable </strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing = 0 border = 0>";

//=====================================================================
// Inside tab Option part
//=====================================================================

$query="select CC_CODE, CC_NAME from categorie_consommable
         where CC_CODE<>'ALL' order by CC_ORDER asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
<td>Categorie</td>
<td  align = left>
<select class='form-control select-control' id = 'CC_CODE' name = 'CC_CODE' onchange = \"displaymanager(document.getElementById('CC_CODE').value)\">";
if ($usage <> '') {
    $query2 = "select CC_CODE, CC_NAME from categorie_consommable where CC_CODE = '".$usage."'";
    if ($usage <> 'Nouvelle catégorie') {
        $result2 = mysqli_query($dbc,$query2);
        $row = @mysqli_fetch_array($result2);
        $name = $row["CC_NAME"];
        echo "<option>".$usage.' - '.$name."</option>";
    } else
        echo "<option>".$usage."</option>";
} 
else
    echo "<option>Choisissez une catégorie</option>";
if ($usage <> 'Nouvelle catégorie')
    echo "<option value = 'Nouvelle catégorie' style = 'color:#e02bdd'>Nouvelle catégorie</option>";
while ($row = @mysqli_fetch_array($result)) {
    $CC_CODE = $row["CC_CODE"];
    $CC_NAME = $row["CC_NAME"];
    if ($CC_CODE != $usage)
        echo "<option value=\"".$row["CC_CODE"]."\">".$row["CC_CODE"]." - ".$row["CC_NAME"]."</option>";
}
echo "<td  align = right>";
echo "</td>
    </tr>";

//=====================================================================
// Edit categorie name and description apres selection
//=====================================================================

if ($usage <> '') {
    echo "<form action = save_edit_categorie_consommable.php method = 'post'>";
    $query = "select CC_CODE, CC_NAME, CC_DESCRIPTION, CC_IMAGE from categorie_consommable where CC_CODE = '".$usage."'";
    $result = mysqli_query($dbc,$query);
    $row = @mysqli_fetch_array($result);
    $CC_CODE = @$row["CC_CODE"];
    $CC_NAME = @$row["CC_NAME"];
    $CC_IMAGE = @$row["CC_IMAGE"];
    $CC_DESCRIPTION = @$row["CC_DESCRIPTION"];
    echo "<input type = 'hidden' class='form-control form-control-sm' name = 'CC_CODE_PREV' value = \"".$usage."\">";
    echo "<tr>
    <td>Code de la catégorie $asterisk</td>
    <td  align = left><input type = 'text' class='form-control form-control-sm' name = 'CC_CODE' value = \"".$CC_CODE."\" size = 30></td>
    <td align = right></tr>";
    echo "<tr>
    <td>Nom de la catégorie $asterisk</td>
    <td  align = left><input type = 'text' class='form-control form-control-sm' name = 'CC_NAME' value = \"".$CC_NAME."\" size = 30></td>
    <td  align = right></tr>";
    echo "<tr>
    <td>Déscription consommable</td>
    <td align = left><input type = 'text' class='form-control form-control-sm' name = 'CC_DESCRIPTION' value = \"".$CC_DESCRIPTION."\" size = 30></td>
    <td align = right></tr>";

    echo "<tr>
    <td>Icône</td>";
    if ($CC_IMAGE <> '')
        echo "<td  align = left><input type = 'text' class='form-control form-control-sm' name = 'logo' value = \"".$CC_IMAGE."\" onkeyup = 'Get_logo(this.value)' size = 30>";
    else
        echo "<td  align = left><input type = 'text' class='form-control form-control-sm' name = 'logo' value = 'utensils' onkeyup = 'Get_logo(this.value)' size = 30>";
    echo " <a href='https://fontawesome.com/icons?d=gallery&m=free' target='_blank'  title=\"Choisissez une icone font-awesome puis copier coller le texte sous l'icone choisie dans le champ ci-contre.\"><i class='fa fa-question-circle fa-lg'></i></a>
    <a id = 'show_logo'></a></td>
    <td align = right></td>
    </tr>";
}

//=====================================================================
// Submit
//=====================================================================
echo "</table></div></div>";
if ($usage <> '') {
    echo "<input type = 'submit' class = 'btn btn-danger' name = 'Delete' value = 'Supprimer'>
            <input type = 'submit' class = 'btn btn-success' value = 'Sauvegarder'></form> ";
}
echo "<input type = 'submit' class = 'btn btn-secondary' value = 'Retour' onclick = redirect();>";
writefoot();
echo "</body>";
?>