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
check_all(29);
get_session_parameters();
writehead();
test_permission_level(29);
if (isset($_GET["from"])) $from=$_GET["from"];
else $from="default";
$possibleorders= array('TEF_NAME','EF_NAME','EF_PRICE','S_CODE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='e.TEF_CODE, e.EF_NAME';
if (isset($_GET["EF_ID"])) $EF_ID=$_GET["EF_ID"];
else $EF_ID = '';
if (isset($_GET["write"])) $write=$_GET["write"];
else $write = '';
if ($EF_ID) {
    include_once ("upd_element_facturable.php");
    exit;
}
if ($write) {
    include_once ("upd_element_facturable.php");
    exit;
}

?>
<script type='text/javascript' src='js/element_facturable.js'></script>
<?php

$querycnt="select count(*) as NB";
$query=" select e.EF_ID, e.TEF_CODE, t.TEF_NAME, e.S_ID, e.EF_NAME, e.EF_PRICE, s.S_CODE";
$queryadd=" from element_facturable e, type_element_facturable t, section s
            where s.S_ID=e.S_ID
            and e.TEF_CODE = t.TEF_CODE";
if ( $type_element <> 'ALL' ) $queryadd .= "\n and e.TEF_CODE='".$type_element."'";
$queryadd .= "\nand e.S_ID =".$filter;


$querycnt .= $queryadd;
if ( $order =='EF_PRICE' ) $query .= $queryadd." order by EF_PRICE desc";
else $query .= $queryadd." order by ". $order;

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

if ( check_rights($_SESSION['id'], 17)) {
    echo "<div class='dropdown-right' style='float:right' align=right> <a class='btn btn-success' value='Ajouter' name='ajouter' 
        onclick=\"bouton_redirect('parametrage.php?tab=5&child=13&action=insert&type_element=ALL');\"><i class=\"fas fa-plus-circle\" style='color:white'></i><span class='hide_mobile'> Élément facturable</span></a>";
}

echo "</div>";

echo "<div class='div-decal-left' align=left>";
//filtre section
echo "<select id='filter' name='filter' name='filter' class='selectpicker' ".datalive_search()."  data-style='btn-default' data-container='body'
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'ALL')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";

//filtre categorie_facturable
echo "<select id='type_element' name='type_element' class='selectpicker' data-style='btn-default' data-container='body'
onchange=\"orderfilter('".$order."','".$filter."',document.getElementById('type_element').value)\">";

$query2="select TEF_CODE, TEF_NAME from type_element_facturable order by TEF_NAME asc";
$result2=mysqli_query($dbc,$query2);
if ( $type_element == 'ALL' ) $selected="selected ";
else $selected ="";
echo "<option value='ALL' $selected>Tous les types d'éléments facturables</option>\n";
while ($row=@mysqli_fetch_array($result2)) {
    $TEF_CODE=$row["TEF_CODE"];
    $TEF_NAME=$row["TEF_NAME"]; 
    if ($TEF_CODE == $type_element ) $selected="selected ";
    else $selected ="";
    echo "<option value='".$TEF_CODE."' $selected>".$TEF_NAME."</option>\n";
}
echo "</select>";
echo "</div>";

echo "<div align=center class='table-responsive'>";

$string = "tab=".@$tab."&child=".@$child;

$later=1;
execute_paginator($number, $string);
$numberrows=mysqli_num_rows($result);

if ( $number > 0 ) {
    echo "<div class='col-sm-8'>";
    echo "<table class='newTableAll'>";

    echo "<tr>";
    echo "<td>
            <a href=parametrage.php?tab=5&child=12&order=S_CODE >Section</a></td>";
    echo "<td><a href=parametrage.php?tab=4&child=12&order=TEF_NAME >Type</a></td>";
    echo "<td>
        <a href=parametrage.php?tab=5&child=12&order=EF_NAME >Nom</a></td>";
    echo "<td>
        <a href=parametrage.php?tab=5&child=12&order=EF_PRICE >Prix unitaire ".$default_money_symbol."</a></td>";
    echo "</tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while ($row=@mysqli_fetch_array($result)) {
        $EF_ID = $row["EF_ID"];
        $TEF_CODE = $row["TEF_CODE"];
        $TEF_NAME = $row["TEF_NAME"];
        $EF_NAME = $row["EF_NAME"];
        $EF_PRICE = $row["EF_PRICE"];
        $S_CODE = $row["S_CODE"];
        $revision=$mydarkcolor;

        echo "<tr onclick=\"this.bgColor='#33FF00';javascript:bouton_redirect('parametrage.php?EF_ID=".$EF_ID."&tab=5&child=13')\" >";
        echo "<td>$S_CODE</td>";
        echo "<td>$TEF_NAME</td>";
        echo "<td>$EF_NAME</td>
                <td>$EF_PRICE</td>
        </tr>";
    }
    echo "</table>";
    echo $later;
}
else {
    echo "Pas d'éléments facturables configurés.";
}
if ( $from == 'top' );

else {
    if ( $evenement_facture > 0 ) { 
        $url="evenement_facturation_detail.php?evenement=".$evenement_facture;
        echo "<p><input type='button' class='btn btn-secondary' value='Retour' name='annuler' onclick=\"history.go(-1);\">";
    }
}
writefoot();
?>
