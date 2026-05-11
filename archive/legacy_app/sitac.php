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
// always disable error reporting, it causes javascript errors in map
error_reporting(0);
check_all(0);

$table_params = (!isset($_GET['table'])) ? 0 : $_GET['table'];

if (!$table_params) {
    writehead();
}

$id=$_SESSION['id'];

if (isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;

if ( ! is_operateur_pc($id,$evenement)) 
    check_all(76);

if (isset($_GET["showlist"])) $showlist=intval($_GET["showlist"]);
else $showlist=1;

if (isset($_GET["gps"])) $gps=intval($_GET["gps"]);
else $gps=1;

if (isset($_GET["autorefresh"])) $autorefresh=intval($_GET["autorefresh"]);
else $autorefresh=0;

if (isset($_GET["addmarker"])) $addmarker=intval($_GET["addmarker"]);
else $addmarker=0;

if (isset($_GET["addflag"])) $addflag=intval($_GET["addflag"]);
else $addflag=0;

if ( isset($_SESSION["addflag"]) ) {
    if ( $_SESSION['addflag'] == 0 ) {
        $addflag = 0;
        unset($_SESSION['addflag']);
    }
}
if ( isset($_SESSION["addmarker"]) ) {
    if ( $_SESSION['addmarker'] == 0 ) {
        $addmarker = 0;
        unset($_SESSION['addmarker']);
    }
}

if ( $addmarker > 0 or $addflag == 1) $autorefresh=0;
$evts=get_event_and_renforts($evenement,$exclude_canceled_r=true);

$query ="select LAT, LNG, ZOOMLEVEL, MAPTYPEID from geolocalisation where type='E' and CODE=".$evenement;
$result=mysqli_query($dbc,$query);
$nbrow=mysqli_num_rows($result);

$row=@mysqli_fetch_array($result);
$LAT=$row["LAT"];
$LNG=$row["LNG"];
$EVTLAT=$row["LAT"];
$EVTLNG=$row["LNG"];
if ( isset($_SESSION['centerMapFor'])) $centerMapFor = intval($_SESSION['centerMapFor']);
else $centerMapFor = 0;

if ( $centerMapFor == $evenement ) {
    if ( isset($_SESSION['centerlat'])) $LAT = $_SESSION['centerlat'];
    if ( isset($_SESSION['centerlng'])) $LNG = $_SESSION['centerlng'];
}

$ZOOMLEVEL=intval($row["ZOOMLEVEL"]);
if ( $ZOOMLEVEL == 0 ) $ZOOMLEVEL=15;

$MAPTYPEID=$row["MAPTYPEID"];
if ( $MAPTYPEID == '' ) $MAPTYPEID = 'ROADMAP';

$query ="select E_ADDRESS, S_ID from evenement where E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$E_ADDRESS=$row["E_ADDRESS"];
$S_ID=$row["S_ID"];

if (check_rights($id, 15, $S_ID)) $granted_event=true;
else if ( is_chef_evenement($id, $evenement) ) $granted_event=true;
else if ( is_operateur_pc($id, $evenement) ) $granted_event=true;
else $granted_event=false;


if ( $autorefresh > 0 ) echo "<meta http-equiv='Refresh' content='".$autorefresh."'>";

echo "
<style TYPE='text/css'>

.labelswhiteHuge {
   color: ".$mydarkcolor.";
   background-color: white;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: left;
   width: 160px;
   height: 130px;   
   border: 2px solid ".$mydarkcolor.";
 }

.labelswhiteBig {
   color: ".$mydarkcolor.";
   background-color: white;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: left;
   width: 120px;
   height: 40px;   
   border: 2px solid ".$mydarkcolor.";
 }

.labelswhite {
   color: ".$mydarkcolor.";
   background-color: white;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: center;
   width: 100px;     
   border: 2px solid ".$mydarkcolor.";
   white-space: nowrap;
 }

.labelsgreen {
   color: ".$mydarkcolor.";
   background-color: #00FF00;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: center;
   width: 100px;     
   border: 2px solid ".$mydarkcolor.";
   white-space: nowrap;
 }
 
.labelsred {
   color: ".$mydarkcolor.";
   background-color: red;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: center;
   width: 100px;     
   border: 2px solid ".$mydarkcolor.";
   white-space: nowrap;
 }

.labelsorange {
   color: ".$mydarkcolor.";
   background-color: orange;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: center;
   width: 100px;     
   border: 2px solid ".$mydarkcolor.";
   white-space: nowrap;
 }
 
.labelsyellow {
   color: ".$mydarkcolor.";
   background-color: yellow;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: center;
   width: 100px;     
   border: 2px solid ".$mydarkcolor.";
   white-space: nowrap;
 }

.labelsblue {
   color: white;
   background-color: blue;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: center;
   width: 100px;     
   border: 2px solid white;
   white-space: nowrap;
 } 
 
.labelsblack {
   color: white;
   background-color: black;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: center;
   width: 100px;     
   border: 2px solid white;
   white-space: nowrap;
 }
 
.labelsdarkgreen {
   color: white;
   background-color: darkgreen;
   font-family: 'Arial', sans-serif;
   font-size: 10px;
   font-weight: bold;
   text-align: center;
   width: 100px;     
   border: 2px solid white;
   white-space: nowrap;
 }
 
#equipesdiv{overflow:auto; height: 800px;margin-top:60px}

.red{color:red; font-weight: bold; font-size:10pt;}
.green{color:green; font-weight: bold; font-size:10pt;}
.darkgreen{color:darkgreen; font-weight: bold; font-size:10pt;}
.orange{color:#FF6600; font-weight: bold; font-size:10pt;}
.black {color:black; font-weight: bold; font-size:10pt;}
.yellow{color:#FFCC00; font-weight: bold; font-size:10pt;}
.blue{color:blue; font-weight: bold; font-size:10pt;}
</style>
<script type='text/javascript' src='".$google_maps_url."&libraries=drawing'></script>
<script type='text/javascript' src='js/markerwithlabel.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type='text/javascript'>
function redirect_to(cible, autorefresh) {
   self.location.href = cible+'&autorefresh='+autorefresh;
}

</script>
</head>";

echo "<body>";

echo "<div align=right class='table-responsive tab-buttons-container'>";
$url="sitac_options.php?evenement=".$evenement."&gps=".$gps."&autorefresh=".$autorefresh."&showlist=".$showlist;

echo "</div>";

if (!$table_params) {
    echo "<table class='noBorder'><tr>
    <td><span class='ebrigade-h4'>".get_info_evenement($evenement)." </span></td>";

    $url="sitac_options.php?evenement=".$evenement."&gps=".$gps."&autorefresh=".$autorefresh."&showlist=".$showlist;
    echo "<td><div class='noprint'>";
    print write_modal( $url, "options_".$evenement, "<i class='fa fa-cog fa-2x' title='Voir ou modifier les options de la carte'></i>");
    echo "</td>";

    $help="Des formes oranges peuvent être dessinées sur la carte (cercle, carré, polygone), par exemple pour montrer des zones particulières sur la carte. 
    Pour cela utiliser le petit menu blanc en haut de la carte.
    ATTENTION: Ces formes ne sont pas conservées lorsque la page est rafraîchie. Aussi il suffit de recharger la page pour supprimer les formes.
    Et en mode rafraîchissement automatique, le mode dessin de formes n'est pas activé.
    Cliquer sur la main pour quitter le mode dessin.";  

    $tiylehelp='Informations menu Dessiner des formes : ';
    echo "<td><div class='noprint'><a href='#'  title= \"".$tiylehelp.$help."\"><i class='fa fa-question-circle fa-2x' title='aide'></i></a></div></td>";
    echo "<td><div class='noprint'><a href='evenement_display.php?evenement=".$evenement."'  title='retour événement'>Retour</a></div></td>";
        
    $query ="select count(1) as NBE from evenement_equipe ee where E_CODE=".$evenement; 
    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    if ( $NBE == 0 )  $showlist=0;
        
    if ( $showlist == 1 ) $class="col-12 col-md-9";
    else $class="col";
    echo "</tr></table>";
}

echo "<div class='container-fluid table-responsive'>";
if ($nbrow == 0 ) 
    echo "<div align=center >
                Pour avoir accès à la carte, il faut saisir l'adresse exacte avec code postal sur l'activité.
                <br>De plus des équipes doivent être configurées sur l'activité, elles pourront alors être positionnées sur la carte.
          </div>";
else {
    echo "<div class='row'>
    <div id='canvas' class='".$class."' style='width:100%;height:100%'></div>";
    print write_modal( $url."' style='position:absolute;margin-left:76%;margin-top:1%'", "options_".$evenement, "<input type='button' class='btn btn-primary' value='Options' style='position:absolute'>");
}

if ( $showlist == 1 ) {
    echo "<div id='equipesdiv' align = right class='col' style='position: absolute;
    height: 600px;
    width: 315px;
    background-color: white;
    float: right;
    opacity: 0.8;
    margin-left:76%;
    magin-top:60px'>";

   echo" <table class='noBorder'>";

    $queryf="select IS_ID, IS_DESCRIPTION, IS_COLOR from intervention_status";
    $resultf=mysqli_query($dbc,$queryf);
    
    $query ="select ee.EE_ID, ee.EE_NAME, ee.EE_ICON, ee.EE_ADDRESS,
            ins.IS_ID, ins.IS_DESCRIPTION, ins.IS_COLOR, g.LAT 'ELAT', g.LNG 'ELNG'
            from intervention_status ins, evenement_equipe ee
            left join geolocalisation g on ( g.TYPE ='Q' and g.CODE=ee.E_CODE and ee.EE_ID = g.CODE2)
            where ee.E_CODE=".$evenement."
            and ins.IS_ID = ee.IS_ID
            order by ee.EE_ORDER";
    $result=mysqli_query($dbc,$query);
    echo "<tr height=70>";
    
    echo "<p><div class='panel-group' id='accordion' role='tablist' aria-multiselectable='true'>";
    
    while ( custom_fetch_array($result)) {
        if ( $EE_ICON == '' ) {
            if ( $assoc == 1 ) $EE_ICON="images/sitac/protectioncivile.png";
            else $EE_ICON="images/gardes/GAR.png";
        }
        if ( $ELAT == '' ) $ELAT = $LAT;
        if ( $ELNG == '' ) $ELNG = $LNG;
    
        $q2="select count(distinct P_ID) from evenement_participation
              where E_CODE in (".$evts.")
              and EE_ID=".$EE_ID;
        $r2=mysqli_query($dbc,$q2);
        $row2=mysqli_fetch_array($r2);
        $nb=$row2[0];
        if ( $nb > 0 ) $personnel=" ".$nb." <i class='fa fa-user' title='".$nb." personnes' ></i>";
        else $personnel='';
        
        $q2="select count(distinct V_ID) from evenement_vehicule
              where E_CODE in (".$evts.")
              and EE_ID=".$EE_ID;
        $r2=mysqli_query($dbc,$q2);
        $row2=mysqli_fetch_array($r2);
        $nb2=$row2[0];
        if ( $nb2 > 0 ) $vehicules=" ".$nb2." <i class='fa fa-car' title='".$nb2." véhicules' ></i>";
        else $vehicules='';
        
        $inside="<select name='ins".$EE_ID."' id='ins".$EE_ID."' class='noboxshadow selectpicker smalldropdown2'>";
        $resultf=mysqli_query($dbc,$queryf);
        while ($rowf=@mysqli_fetch_array($resultf)) {
            $color=$rowf["IS_COLOR"];
            if ( $rowf["IS_ID"] == $IS_ID) $selected='selected';
            else $selected='';
            $inside .= "<option value='".$rowf["IS_ID"]."' $selected class='".$color."'>".$rowf["IS_DESCRIPTION"]."</option>";
        }
        $inside .= "</select>";
        
        // véhicules affectés à l'équipe
        $query2="select distinct ev.E_CODE as EC,v.V_ID,v.V_IMMATRICULATION,v.TV_CODE, vp.VP_LIBELLE, v.V_MODELE, v.V_INDICATIF,
        vp.VP_ID, vp.VP_OPERATIONNEL, s.S_DESCRIPTION, s.S_ID, s.S_CODE,
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE,
        DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE,
        DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE,
        ee.EE_ID, ee.EE_NAME
        from vehicule v, vehicule_position vp, section s, evenement e, evenement_vehicule ev
        left join evenement_equipe ee on (ee.E_CODE=".$evenement." and ee.EE_ID=ev.EE_ID)
        where v.V_ID=ev.V_ID
        and e.E_CODE=ev.E_CODE
        and s.S_ID=v.S_ID
        and vp.VP_ID=v.VP_ID
        and ev.E_CODE in (".$evts.")
        and ev.EE_ID = ".$EE_ID."
        order by ev.E_CODE asc";
        $result2=mysqli_query($dbc,$query2);

        while (custom_fetch_array($result2)) {
            if ( $V_INDICATIF <> '' ) $V_IDENT = $V_INDICATIF;
            else $V_IDENT = $V_IMMATRICULATION;
            $inside .= "<br><a href=upd_vehicule.php?vid=".$V_ID." title='voir fiche véhicule' class=small2>".$TV_CODE." - ".$V_MODELE." - ".$V_IDENT."</a>";
        }
        
        // personnel engagé sur l'équipe
        // trouver tous les participants
        $query2="select distinct tp.TP_NUM, p.P_ID, p.P_NOM, p.P_PHONE, p.P_PRENOM, s.S_ID, 
        p.P_OLD_MEMBER, s.S_CODE, s2.S_CODE,
        EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),p.P_BIRTHDATE))))+0 AS age,
        tp.TP_LIBELLE
        from  pompier p, section s, evenement e, section s2, evenement_participation ep
        left join type_participation tp on tp.TP_ID = ep.TP_ID
        where ep.E_CODE in (".$evts.")
        and ep.EE_ID = ".$EE_ID."
        and e.E_CODE = ep.E_CODE
        and p.P_ID=ep.P_ID
        and ep.EP_ABSENT = 0
        and p.P_SECTION=s.S_ID
        and e.S_ID = s2.S_ID
        order by tp.TP_NUM, p.P_NOM";
        $result2=mysqli_query($dbc,$query2);
        while (custom_fetch_array($result2)) {
            $inside .= "<br><i class='fa fa-user'></i> <a href=upd_personnel.php?pompier=".$P_ID." 
                    title='voir fiche personnel' class=small2>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</a> <span class=small2>".$TP_LIBELLE."</span>";
        }
        $inside .= "<br><input type=text class='noboxshadow' style=\"border:0;color:".$mydarkcolor.";font-size: 10px;width:270px;\" id=\"address".$EE_ID."\" value=\"".$EE_ADDRESS."\" size=70 readonly>";
       
        echo "<div class='panel panel-default' style='width:300px;'>
                    <div class='panel-heading' role='tab' id='heading".$EE_ID."' style='text-align:left;'>
                    <span class='panel-title' >
                        <a href=\"javascript:selectEquipe('r".$EE_ID."','".$ELAT."','".$ELNG."');\">
                        <img src='".$EE_ICON."' class='img-max-40' title='cliquer pour centrer la carte sur cette équipe'></a>
                        <a role='button' data-toggle='collapse' data-parent='#accordion' href='#collapse".$EE_ID."' aria-expanded='false' aria-controls='collapse".$EE_ID."' >
                        <b>".$EE_NAME."</b></a>
                        <img id='color".$EE_ID."' src='images/f_".$IS_COLOR.".gif' title='".$IS_DESCRIPTION."' border=0 class='img-max-20'>
                        ".$vehicules."
                        ".$personnel."
                    </span>
                    </div>
                    <div id='collapse".$EE_ID."' class='panel-collapse collapse' role='tabpanel' aria-labelledby='heading".$EE_ID."'>
                        <div class='panel-body' align=left class='left10'>".$inside."</div>
                    </div>
            </div>";
    }
    echo "</div></table>";
}
echo "</div>
</div>";

if ( $showlist == 0 ) $min = "50";
else $min = "420";

//============================================================
//   create map.
//============================================================

// configuration
// MapTypeId : HYBRID, ROADMAP, SATELLITE, TERRAIN

?>

<script type='text/javascript'>
var myZoom = <?php echo $ZOOMLEVEL; ?>;
var myCoordsLenght = 6;
var defaultLat = <?php echo $LAT; ?>;
var defaultLng = <?php echo $LNG; ?>;
var evtLat = <?php echo $EVTLAT; ?>;
var evtLng = <?php echo $EVTLNG; ?>;
var myMapTypeId = google.maps.MapTypeId.<?php echo $MAPTYPEID; ?>;
var flags = [];
var geocoder = new google.maps.Geocoder();
var icon0 = new google.maps.MarkerImage('images/sitac/flag-red.png');
    icon0.size = new google.maps.Size(35, 35);
    icon0.anchor = new google.maps.Point(0, 35);
var iconInter = new google.maps.MarkerImage('images/gyrophare.gif');
var iconFlag = new google.maps.MarkerImage('images/marker.png');
var iconFlag2 = new google.maps.MarkerImage('images/marker2.png');



function selectEquipe(equipe, lat1, lng1) {
    changeCenter(lat1, lng1);
}

function changeCenter(lat1, lng1) {
    map.setCenter(new google.maps.LatLng(lat1, lng1));
}

function saveflag(id,txtfield) {
    var txt = document.getElementById(txtfield).value;
    txt = txt.replace(/(?:\r\n|\r|\n)/g, '<br>');
    url='sitac_save.php?evenement=<?php echo $evenement;?>&flag='+id+'&flagtxt='+txt;
    self.location.href=url;
}

function removeFlag(flag) {
    var i = getFlagIndex(flag);
    flag.setMap(null);
    flags.splice(i, 1);
}

window.onresize = function(event) {
    resizeMap();
}

function resizeMap() {
    var height = $(window).height();
    var width = $(window).width();
    if ( width < 300 ) width = 300;
    $('#canvas').height(height);
    $('#equipesdiv').height(height);
    $('#canvas').width(width);
    showPolylines();
}

$(document).ready(function() {
    resizeMap();
}
);

// creates the map
var map = new google.maps.Map(document.getElementById('canvas'), {
    zoom: myZoom,
    center: new google.maps.LatLng(defaultLat, defaultLng),
    mapTypeId: myMapTypeId
});

// put a flag at requested address
var eventMarker = new google.maps.Marker({
    position: new google.maps.LatLng(evtLat, evtLng),
    icon: icon0,
    title: "<?php echo $E_ADDRESS; ?>",
    draggable: false
});

function getFlagIndex(flag) {
    for (var i = 0; i < flags.length; i++) {
        if (flag == flags[i]) {
            return i;
        }
    }
    return -1;
}

// adds the marker on the map
eventMarker.setMap(map);

var drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: google.maps.drawing.OverlayType.null,
        drawingControl: true,
        drawingControlOptions: {
        position: google.maps.ControlPosition.TOP_CENTER,
        //drawingModes: ['circle', 'polygon', 'polyline', 'rectangle']
        drawingModes: ['circle', 'polygon', 'rectangle']
    },
    markerOptions: {icon: 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png'},
    circleOptions: {
        fillColor: '#FF8040',
        strokeColor: '#FF8040',
        fillOpacity: 0.4,
        strokeWeight: 3,
        clickable: false,
        editable: true,
        zIndex: 1
    },
    polygonOptions: {
        fillColor: '#FF8040',
        strokeColor: '#FF8040',
        strokeWeight: 4,
        clickable: false,
        editable: true,
    },
    rectangleOptions: {
        fillColor: '#FF8040',
        strokeColor: '#FF8040',
        draggable: true,
        fillOpacity: 0.4,
        strokeWeight: 3,
        clickable: false,
        editable: true,
        zIndex: 1
    },
});


<?php
if ( $autorefresh == 0 ) 
echo "drawingManager.setMap(map);";

if ( $granted_event ) {
echo "
google.maps.event.addListener(map, 'zoom_changed', function() {
    var newZoomLevel = map.getZoom();
    $.post(
        'sitac_save.php',
        {evenement: '".$evenement."', zoomlevel: newZoomLevel, },
            function(responseText){
                $('#result').html(responseText);
        },
        'html'
    );
 });
google.maps.event.addListener(map, 'maptypeid_changed', function() {
    var newMapTypeId = map.getMapTypeId();
    $.post(
        'sitac_save.php',
        {evenement: '".$evenement."', maptypeid: newMapTypeId},
            function(responseText){
                $('#result').html(responseText);
        },
        'html'
    );
 });
google.maps.event.addListener(map, 'center_changed', function() {
    var newCenter = map.getCenter();
    var newLat = newCenter.lat();
    var newLng = newCenter.lng();
    $.post(
        'sitac_save.php',
        {evenement: '".$evenement."', centerlat: newLat, centerlng: newLng },
        function(responseText){
            $('#result').html(responseText);
        },
        'html'
    );
  });
";
}



//============================================================
//   ajouter équipes
//============================================================

// http://mapicons.nicolasmollet.com/category/markers

$query ="select g.LAT 'ELAT', g.LNG 'ELNG', ee.EE_ID, ee.EE_NAME, ee.EE_ORDER, ee.EE_DESCRIPTION , ee.EE_ADDRESS, ee.EE_ICON, ins.IS_COLOR, ins.IS_DESCRIPTION
        from intervention_status ins, evenement_equipe ee 
        left join geolocalisation g on ( g.TYPE ='Q' and g.CODE=ee.E_CODE and ee.EE_ID = g.CODE2)
        where ee.E_CODE=".$evenement." 
        and ee.IS_ID = ins.IS_ID
        order by EE_ID";
$result=mysqli_query($dbc,$query);
while ( custom_fetch_array($result)) {
    if ( $ELAT == '' ) $ELAT = $LAT;
    if ( $ELNG == '' ) $ELNG = $LNG;
    if ( $EE_ICON == '' ) {
        if ( $assoc == 1 ) $EE_ICON="images/sitac/protectioncivile.png";
        else $EE_ICON="images/gardes/GAR.png";
    }

if ( substr($EE_ICON,0,16) == "images/vehicules" ) $w=60;
else $w=45;
    
echo "
var icon".$EE_ID." = {
    url: '".$EE_ICON."',
    size: null, /* size is determined at runtime */
    origin: null, /* origin is 0,0 */
    anchor: null, /* anchor is bottom center of the scaled image */
    scaledSize: new google.maps.Size(".$w.", ".$w.")
};

//creates a draggable marker for team ".$EE_ID."
var myMarker".$EE_ID." = new MarkerWithLabel({
    position: new google.maps.LatLng(".$ELAT.", ".$ELNG."),
    icon: icon".$EE_ID.",
    title: \"".$EE_NAME." - ".$IS_DESCRIPTION."\",";

if ( $granted_event    ) echo "draggable: true,";
else echo "draggable: false,";
    
echo "labelContent: \"".$EE_NAME."\",
    labelAnchor: new google.maps.Point(50, 0),
    labelClass: 'labels".$IS_COLOR."', // the CSS class for the label
    labelStyle: {opacity: 0.75}
});

// change status
$('#ins".$EE_ID."').change(function() {
  var newstatus = $(this).val();
  var newimage='images/f_green.gif';
  var newlabelclass='labelsgreen';
  var newtitle='Disponible';
  if (newstatus == 2) {
    newimage='images/f_black.gif';
    newlabelclass='labelsblack';
    newtitle='Indisponible';
  }
  if (newstatus == 3) {
    newimage='images/f_red.gif';
    newlabelclass='labelsred';
    newtitle='Engagé en intervention';
  }
  if (newstatus == 4) {
    newimage='images/f_orange.gif';
    newlabelclass='labelsorange';
    newtitle='Retour disponible';
  }
  if (newstatus == 5) {
    newimage='images/f_yellow.gif';
    newlabelclass='labelsyellow';
    newtitle='Sur les lieux';
  }
  if (newstatus == 6) {
    newimage='images/f_blue.gif';
    newlabelclass='labelsblue';
    newtitle='Transport en cours';
  }
  if (newstatus == 7) {
    newimage='images/f_darkgreen.gif';
    newlabelclass='labelsdarkgreen';
    newtitle='En Patrouille';
  }
  $('#color".$EE_ID."').attr('src',newimage);
  $('#color".$EE_ID."').attr('title',newtitle);
  myMarker".$EE_ID.".set('labelClass', newlabelclass);
  $('#r".$EE_ID."').hide();
  $.post(
    'sitac_save.php',
    {evenement: '".$evenement."', equipe: '".$EE_ID."', status: newstatus},
        function(responseText){
            $('#result').html(responseText);
        },
        'html'
    );
});

// adds the marker on the map for team ".$EE_ID."
 myMarker".$EE_ID.".setMap(map); 

// on click icon show details
google.maps.event.addListener(myMarker".$EE_ID.", 'click', function() {
    var row = document.getElementById('row".$EE_ID."');
    // nothing for now
});
 
// save new coordinates after move
google.maps.event.addListener(myMarker".$EE_ID.", 'dragend', function(evt){
    var lat = evt.latLng.lat().toFixed(myCoordsLenght);
    var lng = evt.latLng.lng().toFixed(myCoordsLenght);
    var address = document.getElementById('address".$EE_ID."');
    var current_address;
    
    geocoder.geocode({'latLng': evt.latLng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                current_address =  results[0].formatted_address;
                $.post(
                    'sitac_save.php',
                    {evenement: '".$evenement."', equipe: '".$EE_ID."', latitude: lat, longitude: lng, address: current_address},
                    function(responseText){
                        $('#result').html(responseText);
                    },
                    'html'
                );
                
                address.value = current_address;
                address.style.color = 'green';
            }
            else {
                swalAlert(\"Erreur de géolocalisation de l'équipe\");
            }
        }
    });
    
});

";
}

//============================================================
//   ajouter les interventions
//============================================================
$query ="select el.EL_ID, el.EL_TITLE, date_format(el.EL_DEBUT, '%d-%m-%Y %h:%i') EL_DEBUT, g.LAT, g.LNG, el.EL_ADDRESS
        from evenement_log el 
        left join geolocalisation g on ( g.TYPE ='I' and g.CODE=el.E_CODE and el.EL_ID = g.CODE2)
        where el.E_CODE=".$evenement."
        and el.EL_DEBUT < NOW()
        and ( el.EL_FIN is null or el.EL_FIN > NOW() or TIME(el.EL_FIN) = '00:00:00')
        and el.TEL_CODE='I'
        order by el.EL_ID";
$result=mysqli_query($dbc,$query);
while ( $row=@mysqli_fetch_array($result)) {
    $ELAT=$row["LAT"];
    $ELNG=$row["LNG"];
    if ( $ELAT == '' ) $ELAT = $LAT;
    if ( $ELNG == '' ) $ELNG = $LNG;
    $EL_ID=$row["EL_ID"];
    $EL_TITLE=$row["EL_TITLE"];
    $EL_DEBUT=$row["EL_DEBUT"];
    $EL_ADDRESS=$row["EL_ADDRESS"];
    if ( $EL_ADDRESS == '' ) $EL_ADDRESS="Placez l'icône sur la carte";

    $query2="select VI_ID, VI_NUMEROTATION, VI_SEXE, VI_AGE
             from victime where EL_ID=".$EL_ID." order by VI_NUMEROTATION" ;
    $result2=mysqli_query($dbc,$query2);
    $victimes="";
    while ($row2=@mysqli_fetch_array($result2) ) {
        $VI_ID=$row2["VI_ID"];
        $VI_NUMEROTATION=$row2["VI_NUMEROTATION"];
        if (intval($VI_NUMEROTATION) == 0 ) $VI_NUMEROTATION='?';
        $victimes .= "V".$VI_NUMEROTATION." ";
    }
    if ( $victimes <> "" ) $victimes .= "- ";
    
    echo "
// put a flag at requested address
var interventionMarker".$EL_ID." = new google.maps.Marker({
    position: new google.maps.LatLng(".$ELAT.", ".$ELNG."),
    icon: iconInter,
    title: \"".$victimes.$EL_TITLE." - ".$EL_DEBUT." - ".$EL_ADDRESS." - cliquer pour éditer\" ,";
if ( $granted_event    ) echo "draggable: true";
else echo "draggable: false";
echo "
});

// adds the marker on the map for intervention $EL_ID
 interventionMarker".$EL_ID.".setMap(map);
 
// save new coordinates after move
google.maps.event.addListener(interventionMarker".$EL_ID.", 'dragend', function(evt){
    var lat = evt.latLng.lat().toFixed(myCoordsLenght);
    var lng = evt.latLng.lng().toFixed(myCoordsLenght);
    var current_address;
    
    geocoder.geocode({'latLng': evt.latLng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                current_address =  results[0].formatted_address;
                interventionMarker".$EL_ID.".setTitle(\"".$EL_TITLE." - ".$EL_DEBUT." - \" + current_address);
                $.post(
                    'sitac_save.php',
                    {evenement: '".$evenement."', intervention: '".$EL_ID."', latitude: lat, longitude: lng, address: current_address},
                    function(responseText){
                        $('#result').html(responseText);
                    },
                    'html'
                );
            }
        }
    });
    
});

// onclick open intervention
google.maps.event.addListener(interventionMarker".$EL_ID.", 'click', function() {
    window.location.href = 'intervention_edit.php?evenement=".$evenement."&numinter=".$EL_ID."&action=update&type=I&from=map';
});
 
 
";
}

//============================================================
//   ajouter les centres accueil victimes
//============================================================
$query ="select c.CAV_ID, c.CAV_NAME, c.CAV_OUVERT, g.LAT, g.LNG, c.CAV_ADDRESS
        from centre_accueil_victime c 
        left join victime v on v.CAV_ID = c.CAV_ID
        left join geolocalisation g on ( g.TYPE ='C' and g.CODE=c.E_CODE and c.CAV_ID = g.CODE2)
        where c.E_CODE=".$evenement."
        group by c.CAV_ID
        order by c.CAV_ID";
$result=mysqli_query($dbc,$query);
while ( $row=@mysqli_fetch_array($result)) {
    $ELAT=$row["LAT"];
    $ELNG=$row["LNG"];
    $NBV=$row["NBV"];
    if ( $ELAT == '' ) $ELAT = $LAT;
    if ( $ELNG == '' ) $ELNG = $LNG;
    $CAV_ID=$row["CAV_ID"];
    $CAV_NAME=$row["CAV_NAME"];
    $CAV_ADDRESS=$row["CAV_ADDRESS"];
    $CAV_OUVERT=$row["CAV_OUVERT"];
    
    $query2="select count(1) as NB from victime where CAV_ID=".$CAV_ID;
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $nbvt=$row2["NB"];
    
    if ( $nbvt > 0 ) {
        $query2="select count(1) as NB from victime where CAV_ID=".$CAV_ID." and CAV_SORTIE is null";
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        $nbve=$row2["NB"];
    }
    else $nbve=0;
    
    if ( $CAV_OUVERT == 1 ) {
        $icon='images/cav_small.png';
        if ( $nbve == 0 ) $label='labelswhite';
        else if ( $nbve < 3 ) $label='labelsyellow';
        else if ( $nbve < 7 ) $label='labelsorange';
        else $label='labelsred';
    }
    else {
        $icon='images/cav_small_closed.png';
        $label='labelsblack';
    }
    if ( $CAV_ADDRESS == '' ) $CAV_ADDRESS="Placez l'icône sur la carte";
    
echo "

var cavicon".$CAV_ID." = new google.maps.MarkerImage(
    '".$icon."',
    null, /* size is determined at runtime */
    null, /* origin is 0,0 */
    null, /* anchor is bottom center of the scaled image */
    new google.maps.Size(33, 33)
);

//creates a draggable marker for CAV ".$CAV_ID."
var cavMarker".$CAV_ID." = new MarkerWithLabel({
    position: new google.maps.LatLng(".$ELAT.", ".$ELNG."),
    icon: cavicon".$CAV_ID.",
    title: \"".$CAV_NAME." - ".$nbvt." victime(s) traitées, ".$nbve." présentes en ce moment - ".$CAV_ADDRESS." - cliquer pour éditer\" ,";

if ( $granted_event    ) echo "draggable: true,";
else echo "draggable: false,";
    
echo "labelContent: \"".$CAV_NAME."\",
    labelAnchor: new google.maps.Point(50, 0),
    labelClass: '".$label."', // the CSS class for the label
    labelStyle: {opacity: 0.75}
});

// adds the marker on the map for team ".$CAV_ID."
 cavMarker".$CAV_ID.".setMap(map);
 
// save new coordinates after move
google.maps.event.addListener(cavMarker".$CAV_ID.", 'dragend', function(evt){
    var lat = evt.latLng.lat().toFixed(myCoordsLenght);
    var lng = evt.latLng.lng().toFixed(myCoordsLenght);
    var current_address;
    
    geocoder.geocode({'latLng': evt.latLng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                current_address =  results[0].formatted_address;
                cavMarker".$CAV_ID.".setTitle(\"".$CAV_NAME." - ".$nbv." victime(s) - \" + current_address);
                $.post(
                    'sitac_save.php',
                    {evenement: '".$evenement."', cav: '".$CAV_ID."', latitude: lat, longitude: lng, address: current_address},
                    function(responseText){
                        $('#result').html(responseText);
                    },
                    'html'
                );
            }
        }
    });
    
});

// onclick open cav
google.maps.event.addListener(cavMarker".$CAV_ID.", 'click', function() {
    window.location.href = 'liste_victimes.php?evenement=".$evenement."&type_victime=".$CAV_ID."';
});
";

}


//============================================================
//   custom markers and polylines - 4 colors
//============================================================

echo "
  var iconCustom1 = new google.maps.MarkerImage('images/red.png');
  var iconCustom2 = new google.maps.MarkerImage('images/blue.png');
  var iconCustom3 = new google.maps.MarkerImage('images/green.png');
  var iconCustom4 = new google.maps.MarkerImage('images/yellow.png');

  iconCustom1.anchor = new google.maps.Point(8,8);
  iconCustom2.anchor = new google.maps.Point(8,8);
  iconCustom3.anchor = new google.maps.Point(8,8);
  iconCustom4.anchor = new google.maps.Point(8,8);
";

for ( $c=1; $c < 5; $c++ ) {

  switch ($c) {
    case 1:  $color='#FF0000'; break;
    case 2:  $color='#0000FF'; break;
    case 3:  $color='#009933'; break;
    case 4:  $color='#FFFF00'; break;
  }
  echo "
  var markers".$c." = [];
  var polyline".$c.";

  function showPolyline".$c."() {
    if (polyline".$c.") {
        polyline".$c.".setMap(null);
    }
    var path = [];
    for (var i = 0; i < markers".$c.".length; i++) {
        var latlng = markers".$c."[i].getPosition();
        path.push(latlng);
    }
    polyline".$c." = new google.maps.Polyline({
        map: map,
        path: path,
        strokeColor: '".$color."',
        strokeOpacity: 1.0,
        strokeWeight: 4
    });
  }    
  function removeMarker".$c."(M) {
    var i = getMarkerIndex".$c."(M);
    M.setMap(null);
    markers".$c.".splice(i, 1);
    showPolyline".$c."();
  }
  
  function getMarkerIndex".$c."(M) {
    for (var i = 0; i < markers".$c.".length; i++) {
        if (M == markers".$c."[i]) {
            return i;
        }
    }
    return -1;
  }";
  
}

echo "
 function showPolylines() {
    showPolyline1();
    showPolyline2();
    showPolyline3();
    showPolyline4();
 }
 
 function removeMarker(type,M) {
    if ( type == 1 ) removeMarker1(M);
    if ( type == 2 ) removeMarker2(M);
    if ( type == 3 ) removeMarker3(M);
    if ( type == 4 ) removeMarker4(M);
 }
";


if ( $addmarker > 0 and $granted_event) {
    $query="select max(CODE2) from geolocalisation where TYPE in ('1','2','3','4') and CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]) +1;
    
    $query2="select LAT,LNG from geolocalisation where TYPE=".$addmarker." and  CODE=".$evenement." and CODE2=( select max(CODE2) from geolocalisation where TYPE =".$addmarker." and CODE=".$evenement.")";
     $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);   
    if ( $row2["LAT"] <> '' ) {
        $newlat=$row2["LAT"] + 5 / 6000;
        $newlng=$row2["LNG"];       
    }
    else {
        $newlat=$LAT + 5 / 6000;
        $newlng=$LNG;
    }
    $query="insert into geolocalisation (TYPE,CODE,CODE2,LAT,LNG) 
            values ('".$addmarker."',".$evenement.",".$nb.",".$newlat.",".$newlng.")";
    $result=mysqli_query($dbc,$query);
}

$query ="select TYPE,CODE2,LAT,LNG from geolocalisation where TYPE in ('1','2','3','4') and CODE=".$evenement." order by CODE2 asc";
$result=mysqli_query($dbc,$query);
$prevpointnumber=0;
while ( $row=@mysqli_fetch_array($result)) {
    $CLAT=$row["LAT"];
    $CLNG=$row["LNG"];
    $TYPE=$row["TYPE"];
    $pointnumber=$row["CODE2"];

    echo "
  var customMarker".$pointnumber." = new google.maps.Marker({
    position: new google.maps.LatLng(".$CLAT.", ".$CLNG."),
    icon: iconCustom".$TYPE.",
    title: \"Point $pointnumber, click droit pour supprimer\",";
if ( $granted_event    ) echo "draggable: true";
else echo "draggable: false";
echo "
});

markers".$TYPE.".push(customMarker".$pointnumber.");

// adds the custom marker #$pointnumber on the map
customMarker".$pointnumber.".setMap(map);";

if ( $granted_event    ) echo "
// save new coordinates after move
google.maps.event.addListener(customMarker".$pointnumber.", 'dragend', function(evt){
    var lat = evt.latLng.lat().toFixed(myCoordsLenght);
    var lng = evt.latLng.lng().toFixed(myCoordsLenght);
    showPolylines();
    geocoder.geocode({'latLng': evt.latLng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                $.post(
                    'sitac_save.php',
                    {evenement: '".$evenement."', custom: '".$pointnumber."', latitude: lat, longitude: lng},
                    function(responseText){
                        $('#result').html(responseText);
                    },
                    'html'
                );
            }
        }
    });
});

google.maps.event.addListener(customMarker".$pointnumber.", 'rightclick', function(evt) {
    removeMarker('".$TYPE."',customMarker".$pointnumber.");
    showPolylines();
    $.post(
        'sitac_save.php',
            {evenement: '".$evenement."', custom: '".$pointnumber."', latitude: 0, longitude: 0},
                function(responseText){
                    $('#result').html(responseText);
                },
                'html'
    );
});
";
}

//============================================================
//   custom flags
//============================================================
if ( $addflag == 1 and $granted_event) {
    $query="select max(CODE2) from geolocalisation where TYPE='F' and CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $nb=intval($row[0]) +1;

    $query="insert into geolocalisation (TYPE,CODE,CODE2,LAT,LNG) 
            values ('F',".$evenement.",".$nb.",".$LAT.",".$LNG.")";
    $result=mysqli_query($dbc,$query);
}

$query ="select CODE2,LAT,LNG,COMMENT from geolocalisation where TYPE='F' and CODE=".$evenement." order by CODE2 asc";
$result=mysqli_query($dbc,$query);
$prevpointnumber=0;
while ( $row=@mysqli_fetch_array($result)) {
    $CLAT=$row["LAT"];
    $CLNG=$row["LNG"];
    $pointnumber=$row["CODE2"];
    $COMMENT= str_replace('<br>','\n',$row["COMMENT"]);
    if ( $COMMENT == '' ) $COMMENT = '???';

if ( strlen($COMMENT) > 25 ) {
    if ( strlen($COMMENT) > 100 ) $class = 'labelswhiteHuge';
    else $class = 'labelswhiteBig';
    $iconflag='iconFlag2';
}
else {
    $class = 'labelswhite';
    $iconflag='iconFlag';
}
    
    
echo "
//creates a draggable marker for flag ".$pointnumber."
var customFlag".$pointnumber." = new MarkerWithLabel({
    position: new google.maps.LatLng(".$CLAT.", ".$CLNG."),
    icon: ".$iconflag.",";
if ( $granted_event    )
    echo "title: \"click gauche pour renommer, click droit pour supprimer\",
          draggable: true,";
else 
    echo " draggable: false,";


echo "labelContent: \"".str_replace('\n','<br>',$COMMENT)."\",
    labelAnchor: new google.maps.Point(30, 0),
    labelClass: \"".$class."\", // the CSS class for the label
    labelStyle: {opacity: 0.75}
});
    
    

flags.push(customFlag".$pointnumber.");

// adds the custom flag #$pointnumber on the map
customFlag".$pointnumber.".setMap(map);";

if ( $granted_event    ) echo "
// save new coordinates after move
google.maps.event.addListener(customFlag".$pointnumber.", 'dragend', function(evt){
    var lat = evt.latLng.lat().toFixed(myCoordsLenght);
    var lng = evt.latLng.lng().toFixed(myCoordsLenght);
    geocoder.geocode({'latLng': evt.latLng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                $.post(
                    'sitac_save.php',
                    {evenement: '".$evenement."', flag: '".$pointnumber."', latitude: lat, longitude: lng},
                    function(responseText){
                        $('#result').html(responseText);
                    },
                    'html'
                );
            }
        }
    });
});

google.maps.event.addListener(customFlag".$pointnumber.", 'rightclick', function(evt) {
    removeFlag(customFlag".$pointnumber.");
    $.post(
        'sitac_save.php',
            {evenement: '".$evenement."', flag: '".$pointnumber."', latitude: 0, longitude: 0},
                function(responseText){
                    $('#result').html(responseText);
                },
                'html'
    );
});";

echo "
var contentString".$pointnumber."= '<div id=\"content\">'+";
if ( $granted_event    ) 
    echo "'<textarea id=textforflag".$pointnumber." cols=35 rows=4 >".str_replace("'","\'",$COMMENT)."</textarea><br><input type=button class=\"btn btn-default\" value=sauver onclick=\"saveflag( \'".$pointnumber."\' ,\'textforflag".$pointnumber."\');\">'+";
else 
    echo "'".str_replace("'","\'",$COMMENT)."'+";
echo "'</div>';

var infowindow".$pointnumber." = new google.maps.InfoWindow({
    content: contentString".$pointnumber."
});

google.maps.event.addListener(customFlag".$pointnumber.", 'click', function() {
    infowindow".$pointnumber.".open(map,customFlag".$pointnumber.");
  });

";
}

// ================================================================
// Personnel géolocalisé
// ================================================================
if ( $gps == 1 ) {
    $time = intval($gps_persistence);

    $query1="select distinct p.P_ID, p.P_NOM , p.P_PRENOM, p.P_SEXE,
         p.P_SECTION, s.S_CODE, s.S_ID, p.P_EMAIL, p.P_PHOTO, g.LAT, g.LNG,
         date_format(g.DATE_LOC, '%d-%m à %H:%i') DATE_LOC";    
    $queryadd =" from pompier p, section s, gps g, evenement_participation ep
         where g.P_ID= p.P_ID
         and ep.P_ID = p.P_ID
         and ep.E_CODE in (".$evts.")
         and TIMESTAMPDIFF(MINUTE,g.DATE_LOC,NOW()) < ".$time."
         and p.P_SECTION=s.S_ID";

    $query1 .= $queryadd;
    $result1=mysqli_query($dbc,$query1);
    $map_data = "";

    // personnes
    while ($row=@mysqli_fetch_array($result1)) {
    $P_ID=$row["P_ID"];
    $P_PRENOM=$row["P_PRENOM"];
    $P_SEXE=$row["P_SEXE"];
    $P_NOM=$row["P_NOM"];
    $L_LAT=$row["LAT"];
    $L_LNG=$row["LNG"];
    $P_PHOTO=$row["P_PHOTO"];
    $DATE_LOC=$row["DATE_LOC"];    
    $name=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
        if( strlen($L_LAT) > 1 ){
            if ( $P_PHOTO <> '' and  is_file($trombidir.'/'.$P_PHOTO))
                $ICON = $trombidir.'/'.$P_PHOTO;
            else if ( $P_SEXE == 'M' )
                $ICON ='images/sitac/boy.png';
            else
                $ICON = 'images/sitac/girl.png';

            $map_data .= "
var Picon".$P_ID." = new google.maps.MarkerImage(
    '".$ICON."',
    null, /* size is determined at runtime */
    null, /* origin is 0,0 */
    null, /* anchor is bottom center of the scaled image */
    new google.maps.Size(40, 45)
);";
            
            
            $map_data .= "
var pers".$P_ID." = new google.maps.Marker({
    position: new google.maps.LatLng(".$L_LAT.",".$L_LNG."),
    title:\"".$name.", localisation GPS: ".$DATE_LOC."\",
    url: 'upd_personnel.php?pompier=".$P_ID."',
    icon:Picon".$P_ID.",
    map: map
});
        
google.maps.event.addListener(pers".$P_ID.", 'click', function() {
    window.location.href = pers".$P_ID.".url;
});
        ";
        }
    }
    echo $map_data;
}

     
echo "</script>";
writefoot();

?>
