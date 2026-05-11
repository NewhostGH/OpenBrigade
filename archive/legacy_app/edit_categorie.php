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
    self.location.href="parametrage.php?tab=3&child=3&ope=edit_cat&usage=" + usage;
    return true
}
function redirect() {
    self.location.href="parametrage.php?tab=3&child=3";
    return true
}

function Get_logo(value) {
    if (value.length == 0) {
        document.getElementById("show_logo").innerHTML="";
        return;
    } else
        document.getElementById("show_logo").innerHTML='<i class="fa fa-'+ value +' fa-2x"></i>';
    return;
}
</script>
<?php
echo "</head>";
if (isset($_GET['usage'])) $usage=secure_input($dbc, $_GET['usage']);
else $usage='';
if (isset($_GET['error'])) $error=secure_input($dbc, $_GET['error']);
else $error='';
$TM_USAGE='';
$CM_DESCRIPTION='';
$description='';
if ($usage <> 'Nouvelle catégorie')
    $query="select CM_DESCRIPTION, PICTURE from categorie_materiel where TM_USAGE='".$usage."'";
else
    $query="select CM_DESCRIPTION, PICTURE from categorie_materiel";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$picture=@$row["PICTURE"];
echo "<body onload='Get_logo(\"".$picture."\")'>";

echo "<div align=center class='table-responsive'>";

//=====================================================================
// Error
//=====================================================================

if ($error == 'fill_all')
  echo "<div align=center class='table-responsive'><table class='noBorder'>
  <tr>
    <div class='alert alert-warning' role='alert' align='center'><i class ='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i>
    Veuillez remplir tous les champs avec un $asterisk.</div>
  </tr><br>";
  
echo "<div class='table-responsive'>";
echo "<div class='col-sm-4'>
        <div class='card hide card-default graycarddefault' style='margin-bottom:5px'>
            <div class='card-header graycard'>
                <div class='card-title'><strong> Informations catégorie de matériel </strong></div>
            </div>
            <div class='card-body graycard'>";

echo "<table class='noBorder' cellspacing=0 border=0>";

//=====================================================================
// Inside tab Option part
//=====================================================================

$query="select TM_USAGE, CM_DESCRIPTION from categorie_materiel
         where TM_USAGE<>'ALL' order by TM_USAGE asc";
$result=mysqli_query($dbc,$query);

echo "<tr>
<td><b>Categorie</b></td>
<td align=left>
<select class='form-control select-control' id='TM_USAGE' name='TM_USAGE' onchange=\"displaymanager(document.getElementById('TM_USAGE').value)\">";
if ($usage <> '') {
    $query2="select CM_DESCRIPTION from categorie_materiel where TM_USAGE='".$usage."'";
    if ($usage <> 'Nouvelle catégorie') {
        $result2=mysqli_query($dbc,$query2);
        $row=@mysqli_fetch_array($result2);
        $description=$row["CM_DESCRIPTION"];
        echo "<option>".$usage.' - '.$description."</option>";
    } 
    else
        echo "<option>".$usage."</option>";
} 
else
    echo "<option>Choisissez une catégorie</option>";
if ($usage <> 'Nouvelle catégorie')
    echo "<option value='Nouvelle catégorie' style='color:#e02bdd'>Nouvelle catégorie</option>";
while ($row=@mysqli_fetch_array($result)) {
    $TM_USAGE=$row["TM_USAGE"];
    $CM_DESCRIPTION=$row["CM_DESCRIPTION"];
    if ($TM_USAGE != $usage)
        echo "<option value=\"".$row["TM_USAGE"]."\">".$row["TM_USAGE"]." - ".$row["CM_DESCRIPTION"]."</option>";
}
echo "<td align=right></td></tr>";

//=====================================================================
// Edit categorie name, description and logo apres selection
//=====================================================================

if ($usage <> '') {
    echo "<form action=save_edit_categorie.php method='post'>";
    $query="select TM_USAGE, CM_DESCRIPTION, PICTURE from categorie_materiel where TM_USAGE='".$usage."'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $PICTURE=$row["PICTURE"];
    $TM_USAGE=$row["TM_USAGE"];
    $CM_DESCRIPTION=$row["CM_DESCRIPTION"];
    echo "<input type='hidden' name='TM_USAGE_PREV' value=\"".$usage."\">";
    echo "<tr>
    <td><b>Nom de la catégorie </b> $asterisk</td>
    <td align=left><input type='text' class='form-control form-control-sm' name='TM_USAGE' value=\"".$TM_USAGE."\" size=30></td>
    <td align=right></tr>";
    echo "<tr>
    <td><b>Description </b> $asterisk</td>
    <td align=left><input type='text' class='form-control form-control-sm' name='CM_DESCRIPTION' value=\"".$CM_DESCRIPTION."\" size=30></td>
    <td align=right></tr>";

    echo "<tr>
    <td><b>Icône</b></td>";
    if ($PICTURE <> '')
        echo "<td align=left><input type='text' class='form-control form-control-sm flex' name='logo' value=\"".$PICTURE."\" onkeyup='Get_logo(this.value)' size=30 style='width: 80%;'>";
    else
        echo "<td align=left><input type='text' class='form-control form-control-sm flex' name='logo' value='cog' onkeyup='Get_logo(this.value)' size=30 style='width: 80%;'>";
    echo " <a href='https://fontawesome.com/icons?d=gallery&m=free' target='_blank' class='fa fa-question-circle fa-lg' title=\"Choisissez une icone font-awesome puis copier-coller le texte sous l'icone choisie dans le champ ci-contre.\"></a>
    <a id='show_logo'></a></td>
    <td align=right></td>
    </tr>";
}

//=====================================================================
// Submit
//=====================================================================
echo "</table></div></div>";
if ($usage <> '') {
    echo "<input type='submit' class='btn btn-danger' name='Delete' value='Supprimer'>";
    echo "<input type='submit' class='btn btn-success' value='Sauvegarder'></form>";
}
echo "<input type='submit' class='btn btn-secondary' value='Annuler' onclick=redirect();>";
writefoot();
?>