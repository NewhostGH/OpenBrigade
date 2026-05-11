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

$possibleorders= array('TE_CODE','TP_NUM','TP_LIBELLE','INSTRUCTOR','DESCRIPTION','DESCRIPTION2');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TE_CODE';

writehead();

if (isset($_GET['ope'])) $ope = secure_input($dbc, $_GET['ope']);
else $ope = '';
if ($ope == 'edit') {
  include_once ("paramfn_edit.php");
  exit;
} 
?>
<script type="text/javascript" src="js/paramfn.js"></script>
<?php
echo "</head><body>";

$query="select tp.TE_CODE, tp.TP_ID, tp.TP_LIBELLE, tp.TP_NUM, tp.EQ_ID, tg.EQ_NOM, tg.EQ_ICON, te.TE_ICON,
        tp.PS_ID, tp.PS_ID2, tp.INSTRUCTOR, p.TYPE, p.DESCRIPTION, p2.TYPE TYPE2, p2.DESCRIPTION DESCRIPTION2, te.TE_LIBELLE
          from type_participation tp
        left join type_garde tg on tg.EQ_ID = tp.EQ_ID
          left join poste p on p.PS_ID=tp.PS_ID
        left join poste p2 on p2.PS_ID=tp.PS_ID2
        join type_evenement te on te.TE_CODE=tp.TE_CODE
        where 1=1 ";


if ( $type_evenement <> 'ALL' and $type_evenement <> 'ALLBUTGARDE' ) $query .= "\nand tp.TE_CODE='".$type_evenement."'";
if ( $gardes == 0 ) $query .= "\nand tp.EQ_ID=0";
if ( $order == 'TE_CODE' ) $query .= " order by tp.TE_CODE, tp.EQ_ID, tp.TP_NUM";
else $query .=" order by ". $order;
if ( $order == 'DESCRIPTION' or $order == 'DESCRIPTION2' or $order == 'INSTRUCTOR') 
$query .= " desc";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);
echo "<div align=center class='table-responsive'>";
echo "<form name=r>"; 

echo "<div class='div-decal-left' style='float:left'><select id='type_evenement' name='type_evenement' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
          onchange=\"orderfilter('".$order."',document.getElementById('type_evenement').value, '1')\">
      <option value='ALL' class='option-ebrigade'>Tous types d'activités</option>";

$query2="select distinct TE_CODE, TE_LIBELLE from type_evenement order by TE_LIBELLE asc";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
      $TE_CODE=$row["TE_CODE"];
      $TE_LIBELLE=$row["TE_LIBELLE"];
      if($TE_LIBELLE == '')
        continue;
      echo "<option value='".$TE_CODE."' class='option-ebrigade'";
      if ($TE_CODE == $type_evenement ) echo " selected ";
      echo ">".ucfirst($TE_LIBELLE)."</option>\n";
}
echo "</select></div>";

echo "<div class='dropdown-right' align=right><a class='btn btn-success' name='ajouter' 
       onclick=\"bouton_redirect('parametrage.php?tab=1&child=6&ope=edit&type=".$type_evenement."');\"><i class=\"fas fa-plus-circle\"></i><span class='hide_mobile'> Fonction</span></a></div>";
       

echo "</form>";

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
              <td width=20></th>
              <td style='min-width:180px;'>
                <a href=parametrage.php?tab=1&child=6&order=TE_CODE >Type</a></th>
                <td class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=6&order=TP_NUM >Ordre</a></th>
                <td style='min-width:120px;'>
                <a href=parametrage.php?tab=1&child=6&order=TP_LIBELLE >Fonction</a></th>
              <td class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=6&order=INSTRUCTOR >Instructeur</a></th>";
    if ( $competences == 1 ) {
        echo "<td class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=6&order=DESCRIPTION >Compétence requise</a></th>";
        echo "<td class='hide_mobile'>
                <a href=parametrage.php?tab=1&child=6&order=DESCRIPTION2 >Ou</a></th>";
    }
    echo "</tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    while (custom_fetch_array($result)) {
        $DESCRIPTION=strip_tags(@$row["DESCRIPTION"]);
        $DESCRIPTION2=strip_tags(@$row["DESCRIPTION2"]);
        if ( $EQ_ICON == '' ) 
            $type_cell="<td align=center><img src=images/evenements/".$TE_ICON."  title=\"".$TE_LIBELLE."\" class='img-max-20'></td>
                    <td align=left> ".$TE_LIBELLE."</td>";
        else 
            $type_cell="<td align=center><img src=".$EQ_ICON." class='img-max-20' title=\"".$EQ_NOM."\"></td>
            <td  align=left>".$EQ_NOM."</td>";
        
        if ( $INSTRUCTOR == 1 ) $ins="<i class='fa fa-check-square fa-lg' style='color:green;' title='Instructeur ou moniteur'></i>";
        else $ins="";
        echo "<tr class='newTable-tr' onclick=\"this.bgColor='#33FF00'; displaymanager('".$TP_ID."','".$type_evenement."')\" >
                ".$type_cell."
              <td class='widget-text hide_mobile'>$TP_NUM</td>
              <td class='widget-text'>$TP_LIBELLE</td>
              <td class='hide_mobile'>$ins</td>";
        if ( $competences == 1 ) {
            echo "<td class='widget-text hide_mobile'>".$TYPE." - ".$DESCRIPTION."</td>";
            echo "<td class='widget-text hide_mobile'>".$TYPE2." - ".$DESCRIPTION2."</td>";
        }
        echo "</tr>";
          
    }
    echo "</table>";
}
else  {
    echo "Aucune fonction trouvée.";
}
echo @$later;
writefoot();
?>
