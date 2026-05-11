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

if (isset($_GET['upd'])) $upd = secure_input($dbc, $_GET['upd']);
else $upd = '';

if ($upd) {
    include_once ("upd_type_vehicule.php");
    exit;
}

$possibleorders= array('TV_CODE','TV_LIBELLE','TV_NB','TV_USAGE','NB', 'TV_ICON');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TV_USAGE';
writehead();
?>
<script language="JavaScript">
function displaymanager(p1){
    self.location.href="parametrage.php?tab=3&child=1&upd=1&TV_CODE="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

</script>
<?php

$query="select tv.TV_ICON, tv.TV_CODE,tv.TV_LIBELLE,tv.TV_NB,tv.TV_USAGE, count(*) as NB
        from type_vehicule tv left join vehicule v on v.TV_CODE = tv.TV_CODE
        group by tv.TV_CODE";
$query .="\n order by ". $order;
if ( $order == 'TV_NB' or $order == 'NB') $query .=" desc";
$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);
echo "<div class='dropdown-right' align=right><a class='btn btn-success' value='Ajouter' name='ajouter' 
        onclick=\"bouton_redirect('parametrage.php?tab=3&child=1&operation=insert&upd=1');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Véhicule</span></a></div>";
echo "<div align=center class='table-responsive'>";

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
        <td class='hide_mobile'><a href=parametrage.php?tab=3&child=1&order=TV_ICON >Icône</a></td>
        <td><a href=parametrage.php?tab=3&child=1&order=TV_CODE >Code</a></td>
        <td><a href=parametrage.php?tab=3&child=1&order=TV_LIBELLE >Nom</a></td>
        <td class='hide_mobile'><a href=parametrage.php?tab=3&child=1&order=TV_USAGE >Catégorie</a></td>
        <td class='hide_mobile'><a href=parametrage.php?tab=3&child=1&order=TV_NB  title='Nombre de personnels dans le véhicule'>Equipage</a></td>
        <td><a href=parametrage.php?tab=3&child=1&order=NB  title='Nombre véhicules dans la base (y compris réformés)'>Nombre</a></td>
        </tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while ($row=@mysqli_fetch_array($result)) {
         $TV_USAGE=$row["TV_USAGE"];
        $TV_CODE=$row["TV_CODE"];
        $TV_LIBELLE=$row["TV_LIBELLE"];
        $TV_NB=$row["TV_NB"];
        $TV_ICON=$row["TV_ICON"];
        if ( $TV_ICON == '' ) $img="";
        else $img="<img src=".$TV_ICON." class='img-max-22'>";
        $NB=$row["NB"];
        
        if ( $NB == 1 ) {
            $query2="select count(1) from vehicule where TV_CODE=\"".$TV_CODE."\"";
            $result2=mysqli_query($dbc,$query2);
            $row2=mysqli_fetch_array($result2);
            $NB=$row2[0];
        }
        $i=$i+1;
        if ( $i%2 == 0 ) {
            $mycolor=$mylightcolor;
        }
        else {
            $mycolor="#FFFFFF";
        }
          
        echo "<tr onclick=\"this.bgColor='#33FF00'; displaymanager('$TV_CODE')\" >
              <td class='hide_mobile'>".$img."</td>
              <td>$TV_CODE</td>
              <td>$TV_LIBELLE</td>
              <td class='hide_mobile'>$TV_USAGE</td>
              <td class='hide_mobile'>$TV_NB</td>
              <td>$NB</td>
          </tr>";
          
    }
    echo "</table>";
}
echo @$later;
writefoot();

?>
