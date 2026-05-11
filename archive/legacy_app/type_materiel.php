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

$possibleorders= array('TM_USAGE','TM_CODE','TM_DESCRIPTION','TM_LOT', 'TT_NAME');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TM_USAGE';
writehead();
?>

<script language="JavaScript">
function orderfilter(p1,p2){
     self.location.href="parametrage.php?tab=3&child=3&order="+p1+"&catmateriel="+p2;
     return true
}
function displaymanager(p1){
     self.location.href="parametrage.php?tab=3&child=3&ope=upd_type_materiel&id="+p1;
     return true
}

function bouton_redirect(cible) {
     self.location.href = cible;
}

</script>
<?php

if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if ($ope == 'edit') {
  include_once ("ins_type_materiel.php");
  exit;
}
if ($ope == 'edit_cat') {
    include_once ("edit_categorie.php");
    exit;
}
if ($ope == 'upd_type_materiel') {
    include_once ("upd_type_materiel.php");
    exit;
}

$query1="select CM_DESCRIPTION,PICTURE from categorie_materiel
        where TM_USAGE='".$catmateriel."'";
$result1=mysqli_query($dbc,$query1);
$row=@mysqli_fetch_array($result1);
$cmt=$row["CM_DESCRIPTION"];
$picture=$row["PICTURE"];

$query="select tm.TM_ID,tm.TM_CODE,tm.TM_DESCRIPTION,tm.TM_USAGE,tm.TM_LOT,cm.PICTURE, tt.TT_CODE, tt.TT_NAME
        from type_materiel tm left join type_taille tt on tt.TT_CODE = tm.TT_CODE,
        categorie_materiel cm
        where cm.TM_USAGE=tm.TM_USAGE ";

if ( $catmateriel <> 'ALL' ) $query .= "\nand tm.TM_USAGE='".$catmateriel."'";
if ( $order == 'TT_NAME' ) $query .="\norder by tt.". $order;
else $query .="\norder by tm.". $order;

if ( $order == 'TM_LOT' ) $query .=" desc";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);
echo "<div align=center class='table-responsive'>";


//filtre type
echo "<div class='div-decal-left' style='float:left'><select id='usage' name='usage' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
    onchange=\"orderfilter('".$order."',document.getElementById('usage').value)\">";
$query2="select TM_USAGE,CM_DESCRIPTION from categorie_materiel order by TM_USAGE asc";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
    $TM_USAGE=$row["TM_USAGE"];
    $CM_DESCRIPTION=$row["CM_DESCRIPTION"];
    $selected = $TM_USAGE == $catmateriel ? 'selected' : '';
    if($TM_USAGE == 'ALL')
        echo "<option value='$TM_USAGE' class='option-ebrigade' $selected>Toutes les catégories</option>\n";
    else
        echo "<option value='$TM_USAGE' class='option-ebrigade' $selected>$TM_USAGE - $CM_DESCRIPTION</option>\n";
}
echo "</select></div>";

echo "<div class='dropdown-right' align=right><a class='btn btn-success' class='btn btn-default' value='Ajouter' name='ajouter' 
    onclick=\"bouton_redirect('parametrage.php?tab=3&child=3&ope=edit&catmateriel=$catmateriel');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Matériel ou Tenue</span></a></div>";


// ====================================
// pagination
// ====================================

$string = "tab=".$tab."&child=".$child;
$later=1;
execute_paginator($number, $string);

if ( $number > 0 ) {
    echo "<div class='col-sm-12'>";
    echo "<table class='newTableAll'>";

    // ===============================================
    // premiere ligne du tableau
    // ===============================================

    echo "<tr>
        <td width=120 ><a href=parametrage.php?tab=3&child=3&order=TM_USAGE > Catégorie</a></td>
        <td width=80 align=center><a href=parametrage.php?tab=3&child=3&order=TM_LOT >Lot</a></td>
        <td width=200 align=left><a href=parametrage.php?tab=3&child=3&order=TM_CODE >Code</a></td>
        <td width=300 align=left><a href=parametrage.php?tab=3&child=3&order=TM_DESCRIPTION >Description</a></td>";
        
    if ($catmateriel == 'Habillement')
        echo "<td width=150 align=center><a href=parametrage.php?tab=3&child=3&order=TT_NAME >Mesures</a></td>";
        
    echo "</tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while (custom_fetch_array($result)) {
        $i=$i+1;
        if ( $i%2 == 0 ) {
              $mycolor=$mylightcolor;
        }
        else {
              $mycolor="#FFFFFF";
        }
        if ( $TM_LOT == 1 ) $img1="<i class='fa fa-check fa-lg' title='Lot de matériel'></i>";
        else $img1='';
          
        echo "<tr onclick=\"this.bgColor='#33FF00'; displaymanager('$TM_ID')\" >
                <td align=left> <B>".$TM_USAGE."</B></td>
              <td align=center>".$img1."</td>
                <td align=left>".$TM_CODE."</td>
              <td align=left>".$TM_DESCRIPTION."</td>";
        
        if ($catmateriel == 'Habillement')
            echo "<td align=center class=small2>".$TT_NAME."</td>";
              
        echo " </tr>";
          
    }
    echo "</table>";
}
echo @$later;
writefoot();
?>
