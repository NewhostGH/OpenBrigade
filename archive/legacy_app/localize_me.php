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
$nomenu=1;
writehead();
$pid=intval($_GET["pid"]);
$key=secure_input($dbc,$_GET["key"]);

if ( isset ($_GET["accept"])) $accept=intval($_GET["accept"]);
else $accept=0;

$query="select D_SECRET, D_BY from demande where P_ID=".$pid." and D_TYPE='gps'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["D_SECRET"] == "" or $key <> $row["D_SECRET"] ) {
    write_msgbox("Erreur", $error_pic, "Aucune demande de géolocalisation trouvée avec les paramètres fournis.",30,0);
    exit;
}
$D_BY=intval($row["D_BY"]);

?>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<?php print import_jquery(); ?>
<?php print import_bootstrap_js_bundle(); ?>
<script type='text/javascript' src='<?php echo $google_maps_url; ?>'></script> 
</head>
<body>
<?php

$class='col-sm-6 col-md-4 col-md-offset-4';

if ( $accept == 0 ) $message=$cisname." Essaye de vous localiser.<br>
<small><i>Pour votre localisation suivez les instructions suivantes:
<br><span class='badge'>1</span> Activez la fonction gps de votre téléphone. 
<br><span class='badge'>2</span> Pour les iphones aller dans réglages > confidentialité > service de localisation et activez safari
<br><span class='badge'>3</span> Cliquez sur le bouton accepter ci dessous
<br><span class='badge'>4</span> Vous devrez acceptez la geolocalisation depuis votre smartphone
<br><span class='badge'>5</span> Patientez, un message contenant votre position va etre envoyé aux secouristes </i></small>
<p align=center>
<input type='button' class='btn btn-success' value='Accepter' onclick='javascript:self.location.href=\"localize_me.php?accept=1&pid=".$pid."&key=".$key."\";'>
";
else {
    $N=count_entities("pompier", $where_clause="P_ID=".$pid);
    if ( $N == 1 )
        $message="<p align=center><input type='button' class='btn btn-primary' value='Se Connecter' onclick='javascript:self.location.href=\"http://".get_plain_url($cisurl)."\";'>";
    else 
        $message="";
}
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>
	<div class='container' align='center' style='position: relative; top: 10px;' width='".$width."'>
	 <div class='row'>
      <div class='".$class."'>
        <div class='panel panel-default'>
          <div class='panel-heading' >
            <strong>Gélocalisation</strong>
          </div>
          <div class='panel-body' style='background-color:".$mylightcolor.";'>
                <div class='row'>
                  <div class='col-sm-2 col-md-2 '><i class='fa fa-globe fa-3x' style='color:green;'></i></div>
                  <div class='col-sm-5 col-md-10 ' align='left' id='out'>".$message."</div>
                </div>
          </div>
        </div>
      </div>
	 </div>
	</div>";

if ( $accept == 1 ) {
?>
    <script type='text/javascript'>
    var x = document.getElementById("out");

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCallback,showError);
    } 
    else { 
        x.innerHTML = "Votre navigateur ne prend pas en compte la géolocalisation HTML5";
    }
        
    function successCallback(position){
        var newLat = position.coords.latitude;
        var newLng = position.coords.longitude;
        $.post( "gps_save.php", { lat: newLat, lng: newLng, pid: '<?php echo $pid; ?>', findAddress: 1 } );
        if ( Math.abs(newLat) == 0 ) {
            x.innerHTML = "Erreur de géolocalisation, pas de coordonnées GPS trouvées" + x.innerHTML;
        }
        else {
            x.innerHTML = "Vous avez été géolocalisés, votre position est maintenant visible sur <?php echo $cisname; ?>" + x.innerHTML;
        }
    };

    function showError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                x.innerHTML = "User denied the request for Geolocation."
                break;
            case error.POSITION_UNAVAILABLE:
                x.innerHTML = "Location information is unavailable."
                break;
            case error.TIMEOUT:
                x.innerHTML = "The request to get user location timed out."
                break;
            case error.UNKNOWN_ERROR:
                x.innerHTML = "An unknown error occurred."
                break;
        }
    }
    </script>
<?php
    insert_log('GPS', "$pid", "Localisation demandee et realisee", $D_BY);
}
writefoot();
?>