<?php

  # written by: Nicolas MARCHE <nico.marche@free.fr>
  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 2.7
  # Copyright (C) 2004, 2012 Nicolas MARCHE
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
  
include_once ("../../config.php");
check_all(14);
ini_set ('max_execution_time', 0);

$query="delete from compte_bancaire";
$result=mysqli_query($dbc,$query);

$query="delete from compte_unknown";
$result=mysqli_query($dbc,$query);

$query="select nom,banque,etablissement,guichet,numerocompte
        from prelevement";
$result=mysqli_query($dbc,$query);

$i=0;$j=0;$k=0;
while ( $row=mysqli_fetch_array($result)) {
     $i++;
     //if ( $i > 100 ) exit;
    $nom=$row['nom'];                        // 50
    $banque=$row['banque'];                    // 30
    $etablissement=$row['etablissement'];    //5
    $guichet=$row['guichet'];                // 5
    $numerocompte=$row['numerocompte'];     //11
    
    
    // si un seul nom correspond 
    $query2="select P_ID from pompier where P_NOM=\"".$nom."\"
             and not exists (select 1 from compte_bancaire where compte_bancaire.CB_TYPE='P' and compte_bancaire.CB_ID=pompier.P_ID)";
    $result2=mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    $nb=mysqli_num_rows($result2);
    if ( $nb == 1 ) {
        $P_ID=intval($row2[0]);
         $query3="insert into compte_bancaire (CB_TYPE, CB_ID, ETABLISSEMENT, GUICHET, COMPTE,  CODE_BANQUE)
                 values ('P', ".$P_ID.",'".$etablissement."','".$guichet."','".$numerocompte."',\"".$banque."\")";
         $result3=mysqli_query($dbc,$query3);
        echo $nom." ... insert done<br>";
        $j++;
    }
    else {
        $query3="insert into compte_unknown (NOM, NUM, ETABLISSEMENT, GUICHET, COMPTE, CODE_BANQUE)
                 values (\"".$nom."\",".intval($nb).",'".$etablissement."','".$guichet."','".$numerocompte."',\"".$banque."\")";
        $result3=mysqli_query($dbc,$query3);
        echo $nom." ".$nb."<br>";
        $k++;
    }
}
echo $i." records processed. ".$j." inserts compte_bancaire, ".$k." inserts compte_unknown";
?>