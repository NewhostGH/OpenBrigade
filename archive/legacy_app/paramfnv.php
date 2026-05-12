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

if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if ($ope == 'edit') {
  //  paramfnv_edit
  include_once ("paramfnv_edit.php");
  exit;
}

$possibleorders= array('TFV_ORDER','TFV_NAME','TFV_DESCRIPTION');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TFV_ORDER';
writehead();
?>
<script type="text/javascript" src="js/paramfn.js"></script>
<?php
echo "<body>";

$query="select TFV_ID,TFV_ORDER,TFV_NAME,TFV_DESCRIPTION from type_fonction_vehicule";
$query .=" order by ". $order;


$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);

echo "<div class='dropdown-right' align=right><a class='btn btn-success' value='Ajouter' name='ajouter' 
       onclick=\"bouton_redirect('parametrage.php?tab=3&child=2&ope=edit');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Fonction</span></a></div>";

// ====================================
// pagination
// ====================================
$string = "tab=".$tab."&child=".$child;

$later=1;
execute_paginator($number, $string);

echo "<div class='col-sm-4'>";
echo "<table class='newTableAll'>";

// ===============================================
// premiere ligne du tableau
// ===============================================

echo "<tr>
            <td width=200 align=center>
            <a href=parametrage.php?tab=3&child=2&order=TFV_NAME >Nom</a></td>
          <td width=10 align=center>
            <a href=parametrage.php?tab=3&child=2&order=TFV_ORDER >Ordre</a></td>
            <td width=200 align=center>
            <a href=parametrage.php?tab=3&child=2&order=TFV_DESCRIPTION >Description</a></td>";
echo "</tr>";

// ===============================================
// le corps du tableau
// ===============================================
$i=0;
while ($row=@mysqli_fetch_array($result)) {
    $TFV_ID=$row["TFV_ID"];
    $TFV_NAME=$row["TFV_NAME"];
    $TFV_ORDER=$row["TFV_ORDER"];
    $TFV_DESCRIPTION=$row["TFV_DESCRIPTION"];
    $i=$i+1;
    if ( $i%2 == 0 ) {
        $mycolor=$mylightcolor;
    }
    else {
          $mycolor="#FFFFFF";
    }
    echo "<tr onclick=\"this.bgColor='#33FF00'; displaymanager2('".$TFV_ID."')\" >
            <td align=left>".$TFV_NAME."</td>
          <td align=center>".$TFV_ORDER."</td>
          <td align=center>".$TFV_DESCRIPTION."</td>";
    echo "</tr>";
      
}

echo "</table></div>";
echo @$later;
writefoot();
?>
