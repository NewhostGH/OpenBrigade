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
include_once ("fonctions_map.php");
$urlExec = $_SERVER['PHP_SELF'];
check_all(27);
get_session_parameters();
writehead();
writeBreadCrumb();
if ( isset($_GET["param"])) $param=$_GET["param"];
else $param=0;
if ( isset($_GET["map_mode"])) $map_mode=$_GET["map_mode"];
else $map_mode=6;

$_SESSION["map_mode"]=$map_mode;
$_SESSION["param"]=$param;

$maps = array(
    0 => "<optgroup label='Affichage du Personnel'>",
    1 => "Opérations de secours - participants",
    2 => "Autres Opérations - participants",
    3 => "Formations - participants",
    4 => "Veille opérationnelle",
    5 => "Personnel disponible",
    6 => "Personnel par département (adresse)",
    7 => "Personnel par département (affectation)",
    
    8 => "<optgroup label='Affichage des Véhicules et du Matériel'>",
    9 => "Matériel ".$cisname,
    10 => "Véhicules",
    11 => "Matériel de pompage",
    12 => "Matériel hébergement urgence",
    13=> "<optgroup label='Affichage des Compétences du personnel'>",
    14 => "Compétences",
    15 => "<optgroup label='Affichage des activités en cours'>",
    16 => "Activités",
    17 => "<optgroup label='Personnel externe'>",
    18 => "Personnel externe"
);


?>
<link rel="stylesheet" href="css/jquery-jvectormap-2.0.5.css" type="text/css" media="screen"/>
<script type='text/javascript' src='js/checkForm.js'></script>
<script src="js/jquery-jvectormap-2.0.5.min.js"></script>
<script src="js/jquery-jvectormap-fr-merc.js"></script>
<script language="JavaScript">
function orderfilter(report, param){
    self.location.href="jvectormap.php?map_mode="+report+"&param="+param;
    return true;
}
function orderfilter2(report, param, deb, fin){
    self.location.href="jvectormap.php?map_mode="+report+"&param="+param+"&dtdb="+deb+"&dtfn="+fin;
    return true;
}
$('#map').vectorMap({map: 'fr_merc'});
</script>
</head>
<?php

echo "<body>
    <div align=center class='table-responsive' style='margin-top: -5px;'>
    <div style='position:absolute; z-index:1; left:45px; top: 53px'>
    <select id='report' name='report' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
        onchange=\"orderfilter(document.getElementById('report').value, '0')\">";
        
foreach ($maps as $i => $value) {
    if ( $externes == 0 and ($i == 17 or $i == 18)) echo "";
    else if ( strpos($value,"optgroup")) 
        echo $value;
    else {
        if ($map_mode  == $i ) $selected='selected';
        else $selected ='';
        echo "<option value='$i' $selected>".$value."</option>";
    }
}
echo "</select>";

if ( $map_mode == 14 ) {
    echo "<select id='param' name='param' class='selectpicker' data-live-search='true' data-style='btn-default'  data-container='body'
        onchange=\"orderfilter('".$map_mode."', document.getElementById('param').value)\">";
    $query2="select p.PS_ID, p.DESCRIPTION, e.EQ_NOM, e.EQ_ID from poste p, equipe e 
           where p.EQ_ID=e.EQ_ID
           order by p.EQ_ID, p.PS_ID";
    $result2=mysqli_query($dbc,$query2);
    $prevEQ_ID=0;
    echo "<option value=0";
    if ($param == 0 ) echo " selected ";
    echo ">Choisir une compétence.....</option>";
    while (custom_fetch_array($result2)) {
        if ( $prevEQ_ID <> $EQ_ID ) echo "<OPTGROUP LABEL='".$EQ_NOM."'>";
        $prevEQ_ID=$EQ_ID;
        if ( is_iphone()) $DESCRIPTION = substr($DESCRIPTION,0,40);
        echo "<option value='".$PS_ID."' class='option-ebrigade'";
        if ($PS_ID == $param ) echo " selected ";
        echo ">".$DESCRIPTION."</option>\n";
    }
    echo "</select>";
}

if ( $map_mode == 16 ) {
    echo "<br><select id='param' name='param' class='selectpicker' data-live-search='true' data-style='btn-default' data-container='body'
        onchange=\"orderfilter2('".$map_mode."', document.getElementById('param').value,'".$dtdb."','".$dtfn."')\">";
    $query2="select c.CEV_DESCRIPTION, c.CEV_CODE, t.TE_CODE, t.TE_LIBELLE
            from type_evenement t , categorie_evenement c
            where t.CEV_CODE = c.CEV_CODE
            order by c.CEV_DESCRIPTION, t.TE_LIBELLE asc";
    $result2=mysqli_query($dbc,$query2);
    $prevCAT="";
    echo "<option value=0";
    if ($param == 0 ) echo " selected ";
    echo ">Choisir un type d'activité .....</option>";
    
    echo "<option value=ALL";
    if ($param == 'ALL' ) echo " selected ";
    echo ">Toutes les activités</option>";
    while (custom_fetch_array($result2)) {
        if ( $prevCAT <> $CEV_CODE ) echo "<OPTGROUP LABEL='".$CEV_DESCRIPTION."'>";
        $prevCAT=$CEV_CODE;
        echo "<option value='".$TE_CODE."' class='option-ebrigade'";
        if ($TE_CODE == $param ) echo " selected ";
        echo ">".$TE_LIBELLE."</option>\n";
    }
    echo "</select>";
    
    // Choix Dates
    echo "<br>Du
            <input name='dtdb' id='dtdb' placeholder='JJ-MM-AAAA' autocomplete='off' size='10' value='".$dtdb."' class='datepicker datepicker2' data-provide='datepicker' onchange='checkDate2(this);'>";
    echo " au
        <input name='dtfn' id='dtfn' placeholder='JJ-MM-AAAA' autocomplete='off' size='10' value='".$dtfn."' class='datepicker datepicker2' data-provide='datepicker' onchange='checkDate2(this);'>
        <button class='btn btn-secondary' name='btGo' style='margin-top: -1px;'
            onclick=\"orderfilter2('".$map_mode."', '".$param."',document.getElementById('dtdb').value,document.getElementById('dtfn').value);\"><i class='fas fa-search'></i></button>";
}
echo "</div>";
if ( $param > 0 or $map_mode <> 15 ){
    if (is_iphone() ) {
        $w='360px';
        $h='680px';
    }
    else {
        $w='100%';
        $h='86vh';
    }
    echo "<div id='vector-map' style='width:$w; height:$h; background-color:$mydarkcolor;'></div>";
?>
<script>
<?php print get_map_data($map_mode, $param, $dtdb, $dtfn);?>
$(function(){
    $('#vector-map').vectorMap({
        map: 'fr_merc',
        backgroundColor: '#A6E0FF',
        series: {
            regions: [{
                values: Data,
                scale: ['#FFF9C4', '#FFEB3B', '#FDD835', '#FBC02D', '#F9A825', '#F57F17'],
                normalizeFunction: 'linear',
                legend: {
                    vertical: false,
                    title: <?php echo "'".$maps[$map_mode]."'"; ?>
                }
            }]
        },
        onRegionTipShow: function(e, el, code){
            var nb = 0;
            var suffix='';
            if (code in Data) {
                nb = Data[code];
                if ( nb > 1 ) suffix = 's';
            }
            el.html(el.html()+': '+ nb + ' ' + <?php echo "'".$name."'"; ?> + suffix );
        }
    });
});
</script>
<?php

}
writefoot();
?>
