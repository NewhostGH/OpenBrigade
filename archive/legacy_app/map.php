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
check_all(76);

if (isset ($_GET['code'])) $code = intval($_GET['code']);
else $code=0;
if (isset ($_GET['type'])) $type = secure_input($dbc,$_GET['type']);
else $type='E';

if ( $type == 'E' ) 
    $query="select e.E_ADDRESS ADDRESS, g.LAT, g.LNG from geolocalisation g, evenement e
       where g.TYPE='".$type."' 
       and e.E_CODE = g.CODE
       and g.CODE=".$code;
else if  ( $type == 'P' ) 
    $query="select concat(p.P_ADDRESS, ' - ', p.P_CITY) ADDRESS, g.LAT, g.LNG from geolocalisation g, pompier p
       where g.TYPE='".$type."' 
       and p.P_ID = g.CODE
       and g.CODE=".$code;  
else if  ( $type == 'S' ) 
    $query="select concat(s.S_ADDRESS, ' - ', s.S_CITY) ADDRESS, g.LAT, g.LNG from geolocalisation g, section s
       where g.TYPE='".$type."' 
       and s.S_ID = g.CODE
       and g.CODE=".$code;
else if  ( $type == 'G' ) 
    $query="select tg.EQ_ADDRESS ADDRESS, g.LAT, g.LNG from geolocalisation g, type_garde tg
       where g.TYPE='".$type."' 
       and tg.EQ_ID = g.CODE
       and g.CODE=".$code;
 
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$lat=$row["LAT"];
$lng=$row["LNG"];
$address=preg_replace('/\r\n/', ' ', trim($row["ADDRESS"]));


if ( $lat == '' or $lng == '') {
    write_msgbox("erreur géolocalisation",$error_pic, "Pas d'adresse enregistrée ou données de géolocalisation incorrectes",0,0);
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
  html { height: 100% }
  body { height: 100%; margin: 0px; padding: 0px }
  #map_canvas { height: 100% }
</style>
<script type='text/javascript' src='<?php echo $google_maps_url; ?>'></script>
<script type="text/javascript">
  function initialize() {
    var latlng = new google.maps.LatLng(<?php echo $lat; ?>,<?php echo $lng; ?>);
    var myOptions = {
      zoom: 16,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.HYBRID 
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
    var marker = new google.maps.Marker({map: map, title: <?php echo "\"$address\""; ?>, position: map.getCenter()});
  }

</script>
</head>
<body onload="initialize()">
  <div id="map_canvas" style="width:100%; height:100%"></div>
</body>
</html>
