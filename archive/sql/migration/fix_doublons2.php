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

$nomenu=1;
writehead();

echo "============STEP 1 =================================";
$query="ALTER TABLE pompier DROP INDEX ID_API";
mysqli_query($dbc,$query);

$query0="select P_ID, P_NOM, REPLACE(REPLACE(P_PRENOM,'è','e'),'é','e') P_PRENOM, P_BIRTHDATE, P_SECTION, P_LICENCE, P_LICENCE_DATE, ID_API from pompier
         where P_OLD_MEMBER > 0 and P_CREATE_DATE='2020-09-30' and P_STATUT='BEN' and P_NB_CONNECT=0 and ID_API > 0";
$result0=mysqli_query($dbc,$query0);

$i=0;$j=0;$k=0;$l=0;$m=0;


while ( custom_fetch_array($result0)) {
    $i++;
    echo "<p>".$P_NOM." ".$P_PRENOM.":";
    $query2="select P_ID from pompier where ID_API is null and P_ID <> ".$P_ID."
             and P_NOM=\"".$P_NOM."\" and REPLACE(REPLACE(P_PRENOM,'è','e'),'é','e')=\"".$P_PRENOM."\" and P_BIRTHDATE='".$P_BIRTHDATE."'";
    $result2=mysqli_query($dbc,$query2);
    $nbactif= mysqli_num_rows($result2);
    if (  $nbactif == 1 ) {
        $row2=mysqli_fetch_array($result2);
        $CURRENT=$row2['P_ID'];
        $query3="update pompier set P_LICENCE=\"".$P_LICENCE."\", P_LICENCE_DATE=\"".$P_LICENCE_DATE."\", ID_API=".$ID_API." where P_ID=".$CURRENT;
        mysqli_query($dbc,$query3);
        delete_personnel($P_ID);
        echo " fusion des fiches (YES)";
        $j++;
    }
    else echo " mapping impossible (plusieurs fiches) ...";
}
echo "<p>".$i." users processed. ".$j." actifs, ".$k." anciens, et ".$l." externes, et ".$m." sans date de naissance";

$query="ALTER TABLE pompier ADD UNIQUE (ID_API)";
mysqli_query($dbc,$query);


?>