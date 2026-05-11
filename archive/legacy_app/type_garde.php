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
check_all(0);
$id=$_SESSION['id'];
get_session_parameters();
writehead();

check_all(5);

?>
<script type='text/javascript' src='js/competence.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<?php
if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if ($ope == 'edit') {
  include_once ("upd_type_garde.php");
  exit;
}
check_all(5);

$possibleorders= array('EQ_ID','S_CODE','EQ_NOM','NB_POSTES','EQ_JOUR','EQ_NUIT','EQ_VEHICULES','EQ_SPP', 'EQ_PERSONNEL1','EQ_PERSONNEL2','EQ_ORDER');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='EQ_ORDER';

echo "<body>";

$query1="select e.EQ_ID, e.EQ_NOM, e.EQ_JOUR, e.EQ_NUIT, e.EQ_ORDER,
        e.EQ_PERSONNEL1, e.EQ_PERSONNEL2, e.EQ_VEHICULES, e.EQ_DEFAULT, e.EQ_SPP, e.EQ_ICON, s.S_ID, s.S_CODE
        from type_garde e, section s
        where s.S_ID = e.S_ID";

if ( $nbsections == 0 and $filter > 0 ) 
        $query1 .= " and e.S_ID in (".get_family("$filter").")";
        
$query1 .= " group by e.EQ_ID";
$query1 .= " order by ". $order;
if ( $order == 'EQ_NOM' || $order == 'EQ_ID' || $order == 'EQ_ORDER' ) $query1 .=" asc";
else $query1 .=" desc";

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);

if ( check_rights($id, 5 ) ) {
    if ( $nbsections == 0 ) {
        //filtre section
        echo "<div class='div-decal-left' style='float:left'><select id='filter' name='filter' class='selectpicker' ".datalive_search()." data-style='btn-default' data-container='body'
        onchange=\"orderfiltergarde('".$order."',document.getElementById('filter').value)\">";
        if ( $pompiers ) $maxL = $nbmaxlevels -1 ;
        else $maxL = $nbmaxlevels;
        display_children2(-1, 0, $filter, $maxL, $sectionorder);
        echo "</select></div>";
    }
}

if ( check_rights($id, 5, $filter))
    echo "<div class='dropdown-right' align=right><a class='btn btn-success' value='Ajouter' name='ajouter' title='' 
        onclick=\"bouton_redirect('parametrage.php?tab=5&child=10&ope=edit&eqid=0');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Garde</span></a></div>";

echo "<div align=center class='table-responsive'>";
if ( $number == 0 ) 
    echo "<p>Aucun élément paramétré";
else {

    // ===============================================
    // tableau
    // ===============================================

    echo "<div class='col-sm-12'>";
    echo "<table class='newTableAll'>";
    echo "<tr>";
    echo "<td width=50 align=center ></td>";

    echo "<td width=180 align=left >Description</td>";
    if ( $nbsections == 0 ) {
        echo "<td  width=120 align=center >Centre</td>";
    }
    echo "<td  width=40 align=center >Actif jour</td>
          <td  width=40 align=center >Actif nuit</td>";
    echo  "<td width=40 align=center ><span title='Nombre de personnes requises le jour'>Personnel jour</span></td>";
    echo  "<td width=40 align=center ><span title='Nombre de personnes requises la nuit'>Personnel nuit</span></td>";
    if ( $vehicules) 
      echo "<td  width=80 align=center >Véhicules</td>";
    if ( $pompiers )
        echo "<td  width=40 align=center ><span title='Les sapeurs pompiers professionnels sont normalement engagés sur ce type de garde'>Pro</span></td>";
    echo "<td width=40 align=center ><span title='Ordre affichage'>Ordre</span></td>";
    echo "</tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================

    while (custom_fetch_array($result1)) {
        echo "<tr onclick=\"this.bgColor='#33FF00'; bouton_redirect('parametrage.php?tab=5&child=10&ope=edit&eqid=".$EQ_ID."')\" >";
        echo "<td align=center><img src='".$EQ_ICON."' height=25></td>";
        echo "<td align=left>$EQ_NOM";
        if ($EQ_DEFAULT == 1) echo "<i class='fa fa-star ml-2' title='Type de garde par défaut'></i>";
        echo "</td>";
        if ( $EQ_JOUR == 1) $jour="<i class='fa fa-circle fa-lg' style='color:green;' title='actif'></i>";
        else $jour="<i class='fa fa-circle fa-lg' style='color:red;' title='pas actif'></i>";
        if ( $EQ_NUIT == 1) $nuit="<i class='fa fa-circle fa-lg' style='color:green;' title='actif'></i>";
        else $nuit="<i class='fa fa-circle fa-lg' style='color:red;' title='pas actif'></i>";

        if ( $nbsections == 0 ) 
            echo "<td align=center>".$S_CODE."</td>";
        
        echo "
          <td align=center><B>$jour</B></td>
          <td align=center><B>$nuit</B></td>";
        echo "
            <td align=center><span class='badge' style='color:yellow;'>$EQ_PERSONNEL1</span></td>
            <td align=center><span class='badge' style='color:lightblue;'>$EQ_PERSONNEL2</span></td>";
            
        if ( $EQ_VEHICULES == 1 ) 
            $showv="<i class='fa fa-check fa-lg' title =\"Les véhicules sont par défaut automatiquement affichés\"></i>";
        else 
            $showv="";
            
            
        if ( $vehicules)
            echo "<td  align=center>".$showv."</td>";
            
        if ( $pompiers ) {
            if ( $EQ_SPP == 1 )
                $showspp="<i class='fa fa-check fa-lg'  title = \"Les sapeurs pompiers professionnels sont par défaut automatiquement engagés sur ce type de garde\"></i>";
            else 
                $showspp="";
            echo "<td align=center>".$showspp."</td> ";
        }
        echo "<td width=40 align=center>".$EQ_ORDER."</td>";
        echo "</tr>";
    }

    echo "</table>"; 
}

writefoot();

?>
