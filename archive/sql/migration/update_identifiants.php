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

function fixcharset2($string) {
    $string= strtr($string, 
          'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜİàáâãäåçèéêëìíîïğòóôõöùúûüıÿ', 
          'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
    $string= str_replace(" ","", $string);
    $string= str_replace("-","", $string);
    return substr($string,0,18);
}

$query="select P_NOM, P_PRENOM, P_ID from pompier where P_CODE <> 'admin' and P_CODE <> 'secretaire'";
$result=mysqli_query($dbc,$query);

$k=0;
while ( $row=mysqli_fetch_array($result) ) {
    $P_ID=$row['P_ID'];
    $P_NOM=ucfirst(str_replace("'","",$row['P_NOM']));
    $P_PRENOM=ucfirst(str_replace("'","",$row['P_PRENOM']));
    $i=1;
    $NEW=fixcharset2(substr($P_PRENOM,0,$i).'_'.$P_NOM);
    echo $NEW."<br>";
    $found=1;
    while ( $found > 0 ) {
        $query2="select count(1) from pompier where P_CODE='".$NEW."' and P_ID <> ".$P_ID;
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $found=$row2[0];
        if ( $found > 0 ) {
            $i++;
            $NEW=fixcharset2(substr($P_PRENOM,0,$i).'_'.$P_NOM);
            echo $NEW."<br>";
        }
        else {
            $query2="update pompier set P_CODE='".$NEW."' where P_ID=".$P_ID;
            $result2=mysqli_query($dbc,$query2);
            $k++;
        }
    }
}
echo $k." records processed.";
?>