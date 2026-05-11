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
check_all(27);
writehead(); 

$dbx = mysqli_query($dbc,$sqlLignes) or die ("Erreur : ".mysqli_error($dbc));

$out = "<table border=0 cellspacing=0 width=700>";
$out .= "<thead>";
$out .= "<tr class=TabHeader align=left>";
$out .= "<td>&nbsp;</td>";
// Ligne d'entête
mysqli_data_seek($dbCols, 0);
    $col_num = 0;
    $lig_num = 0;
    while($rowx = mysqli_fetch_object($dbCols)){
        $out .= "<th>";
        $out .= "$rowx->Code";
        $out .= "</th>";
        $col_num++;
    }    
$out .= "<th>Total par section</th>";
$out .= "</tr>";
$out .= "</thead>";
$out .= "<tbody>";

$SommeCol = array();
while($dbrow = mysqli_fetch_array($dbx)){
    $lig_num++;
    $level=$dbrow[1]; // 2ème valeur de la requete (niveau)
    $mycolor=get_color_level($level);
    $out .= "<tr bgcolor=$mycolor >";
    $col_num = 0;
    foreach($dbrow as $key=>$value){
        switch($col_num){
            case 0: // libellé section
                $out .= "<td nowrap align=left>";
                $out .= $dbrow[$col_num];
                $out .= "</td>";
            case 1: // niveau 
            break;
            default:
            if( isset($dbrow[$col_num])) {
                if ($dbrow[$col_num] > 0){
                    $out .= "<td style='text-align:left;' ".(($affichage=="xls")?" x:num":"").">"; // 20080728 - Aligne les chiffres
                    $out .= $dbrow[$col_num];
                    $SommeCol[$col_num]=(isset($SommeCol[$col_num])?$SommeCol[$col_num]+$dbrow[$col_num]:$dbrow[$col_num]);
                    $out .= "</td>";
                } else {
                    $out .= "<td>&nbsp;</td>";
                }
            }
        }
        $col_num++;
    }
    $out .= "</tr>\n";
}
//total des colonnes (formule excel)
$out .= "<tr>";
$out .= "<th class=TabTotal>Total par Activité</th>";
    $alpha = 'b';
    $numeric = 2;
    $rows = mysqli_num_rows($dbx)+1;
    for($i=2; $i < mysqli_num_fields($dbx); $i++){
        //$out .= "<th>=sum($alpha$numeric:$alpha$rows)</th>"; // formule Excel
        $out .= "<th class=TabTotal align=left".(($affichage=="xls")?" x:num":"").">".(isset($SommeCol[$i])?floatval($SommeCol[$i]):0)."</th>";// affichage somme
        $alpha++;
    }
$out .= "</tr>";
$out .= "</tbody>";
$out .= "</table>";

if ($affichage=="xls"){
    header("Content-type: application/vnd.ms-excel; name='excel'");
    header('Content-Disposition: attachment; filename="' . $export_name . '.xls"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: no-cache');
    header("Expires: 0");
    echo "<html>
    ";
    echo "<head>
    <title>$export_name</title>
    <style type=\"text/css\">
    ";
    echo "</style>
    </head>
    <body>
    $export_name
    <div id=\"Classeur1_16681\" align=center x:publishsource=\"Excel\">";
    echo $out;
    echo "</div>
    </body>
    </html>";
}
else {
    echo $out;
}

writefoot();
?>
