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
include_once ("fonctions_infos.php");
check_all(0);
$id=$_SESSION['id'];
$SES_NOM=$_SESSION['SES_NOM'];
$SES_PRENOM=$_SESSION['SES_PRENOM'];
$SES_GRADE=$_SESSION['SES_GRADE'];

writehead();
include_once ("css/css.php");
$body="";

if ($geolocalize_enabled) {
    $body .= "
    <script type='text/javascript' src='".$google_maps_url."'></script> 
    <script type='text/javascript' src='js/gps.js'></script>";
}
?>
<script>
function redirect() {
    url="configuration.php";
    self.location.href=url;
}
function update_number_events(number) {
    url="index_d.php?number_events="+number;
    self.location.href=url;
}

</script>
</head>
<?php

if ( isset($_GET["number_events"])) {
    $number = intval($_GET["number_events"]);
    if ( in_array($number,array(10,20,30,40))) {
        $query="delete from personnel_preferences WHERE PP_ID=15 and P_ID=".intval($id);
        $result=mysqli_query($dbc,$query);
        $query="insert into personnel_preferences (PP_ID,P_ID,PP_VALUE,PP_DATE) values (15,".intval($id).",'".$number."',NOW())";
        $result=mysqli_query($dbc,$query);
    }
}

if ($already_configured == 0 and check_rights($id, 14)) {
    $body .= "<body onload=redirect();></body>";
    print $body;
    exit;
} 
else $body .= "<body class='lightgray'><div class='table-responsive'>";

$widgets = write_competence_warning($id);
$widgets .= write_pasword_expiry_alert();
$widgets .= write_boxes('default', $id);

$querystat="select WU_VISIBLE from widget_user where W_ID=29 and P_ID=".$id;
$result=mysqli_query($dbc,$querystat);
if ( mysqli_num_rows($result) == 0 ) $resultstat=1;
else $resultstat=mysqli_fetch_array($result)[0];
if ($resultstat==1) 
    $body .= show_stats().$widgets;
else
    $body .= $widgets;
print $body;
writefoot();

