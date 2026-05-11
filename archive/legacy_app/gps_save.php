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
if ( isset ($_POST['pid'])) {
    $pid = intval($_POST['pid']);
    $dbc=connect();
}
else {
    check_all(0);
    $id=$_SESSION['id'];
    $pid = $id;
}
// localisation d'une personne
if ( isset ($_POST['lat'])) {
    $lat = (float)($_POST['lat']);
    $lng = (float)($_POST['lng']);

    if ( $pid > 0 ) {
        $address="";
        if ( isset ($_POST['findAddress'])) {
            if ( $_POST['findAddress'] == 1 ) $address=fixcharset(getaddress($lat,$lng));
        }
        if ( isset ($_POST['GPSAddress'])) {
            $address=fixcharset(secure_input($dbc,$_POST['GPSAddress']));
        }
        // modification geolocalisation GPS
        $query="update gps set LAT='".$lat."', LNG='".$lng."', DATE_LOC=NOW(), ADDRESS=\"".$address."\" where P_ID=".$pid;
        $result=mysqli_query($dbc,$query);
        
        if ( mysqli_affected_rows($dbc) == 0 ) {
            $query="insert into gps(P_ID,DATE_LOC,LAT,LNG,ADDRESS) values (".$pid.",NOW(),'".$lat."','".$lng."',\"".$address."\")";
            $result=mysqli_query($dbc,$query);
        }
    }
}
disconnect();

?>
