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

$query="update section set S_ID_RADIO = null";
$result=mysqli_query($dbc,$query);

$query0="select s.S_ID, s.S_CODE, s.S_ID_RADIO, sf.NIV, s.S_PARENT
        from section s, section_flat sf
        where sf.S_ID = s.S_ID
        and sf.NIV in (3,4)
        order by s.S_CODE";
$result0=mysqli_query($dbc,$query0);

$i=0;$j=0;
while ( $row0=mysqli_fetch_array($result0)) {
     $i++;
    echo "<p>".$row0["S_CODE"]." ".$row0["S_ID"].":";
    $idradio=generate_id_radio($row0["S_ID"]);
    if (intval($idradio) > 0 ) {
        echo " generated ".$idradio;
        $query1="update section set S_ID_RADIO='".$idradio."' where S_ID=".$row0["S_ID"];
        $result1=mysqli_query($dbc,$query1);
        $j++;
    }
}
echo "<p>".$i." sections processed. ".$j." updated";
?>