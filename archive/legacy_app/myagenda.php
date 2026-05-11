<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade
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

header('Content-Type: text/html; charset=ISO-8859-1');
include_once ("config.php");
check_all(41);
$id=$_SESSION['id'];

$myagenda = (isset($_GET['myagenda'])) ? $_GET['myagenda'] : 0;

if (!$myagenda) {
    writehead();
}

$sqlk = "select md5(concat(p.p_id,'-', p.p_nom,'-', p.p_mdp)) pkey , p.p_calendar
    from pompier p
    where p.p_id = ".$id;
$resk = mysqli_query($dbc,$sqlk);
$calk = "evenements.php";
$row=@mysqli_fetch_array($resk);
$pkey = $row[0];
$p_calendar = $row[1];

if ( isset($_GET['type_evenement'])) {
    $type_evenement = secure_input($dbc,$_GET['type_evenement']);
}
else $type_evenement = '';

if ( isset($_GET['chxCal'])) {
    $chxCal  = secure_input($dbc,$_GET['chxCal']);
}
else $chxCal  = array();

if ( isset($_GET['subsections '])) {
    $subsections  = intval($_GET['subsections']);
}
else $subsections  = '0';

// DEB - Type evenement
$sqlte="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE, te.TE_LIBELLE
        from type_evenement te, categorie_evenement ce
        where te.CEV_CODE=ce.CEV_CODE
        and te.TE_CODE <> 'MC'
        order by te.CEV_CODE desc, te.TE_LIBELLE asc";
$resultte=mysqli_query($dbc,$sqlte);
$prevCat='';
$cb_typeevt="";
while (custom_fetch_array($resultte)) {
    if ( $prevCat <> $CEV_CODE ){ 
           if ($CEV_CODE == $type_evenement ) $cb_typeevt .= " checked ";
        $cb_typeevt .= "<h4>".$CEV_DESCRIPTION."</h4>\n";
    }
    $prevCat=$CEV_CODE;
    $cb_typeevt .= "
                        <label for='$TE_CODE'>$TE_LIBELLE</label>
                        <label class='switch'>
                            <input type=\"checkbox\" class='typeEvt' id='$TE_CODE' value='".$TE_CODE."' title=\"".$TE_LIBELLE." (".$TE_CODE.")"."\"'";
    if ($TE_CODE == $type_evenement ) $cb_typeevt .= " checked ";
    $cb_typeevt .= ">
                            <span class='slider round'></span>
                        </label>\n<br />";
}

// FIN - Type evenement

$ChxCalendar = (isset($_GET['btGo'])?(isset($_GET['chxCal'])?$_GET['chxCal']:array()):$chxCal);// utilise les données du formulaire ou de la session
if (count($ChxCalendar)==0){ $_SESSION['chxCal']=array(); }
if ($p_calendar == '') $_SESSION['chxCal']=array();

$html = "<div class='container-fluid d-flex justify-content-center ' >
    <div class='col-12'>
        <div class='card hide card-default graycarddefault '>
            <div class='card-header graycard '>
                <div class='card-title text-center '><strong class='h2' '>Mon Agenda</strong></div>
            </div>
            <div class='card-body graycard'>
                <div class='text-center mb-3'>Activités où je suis inscrit</div><div class='text-center '><a  href='$calk?&cid=$pkey&perso=1' ><i class='far fa-calendar-alt fa-2x ' title='$calk?cid=$pkey&perso=1'/></i></a></div>
                <div align='right'>Clé de sécurité : </td><td><input type='text' value='$pkey' size=30></div>
            </div>
       </div></div></div>";


$html .= "<div align=center class='table-responsive'><div class='container-fluid'><div class='row'>
        <div class='col-md-12 col-lg-5'>
        <div class='card hide card-default graycarddefault'>
            <div class='card-header graycard'>
                <div class='card-title'><strong>Exemples de calendriers</strong></div>
            </div>
            <div class='card-body graycard'>";

//$html.= "<table class='noBorder'><tr><td>Clé de sécurité : </td><td><input type='text' value='$pkey' size=30></td></tr>";
$html.= "<table class='noBorder'>";
$html.= "<tr><td>De la section</td><td><a class='btn btn-default btn-action' href='$calk?cid=$pkey'><i class='far fa-calendar-alt fa-lg' title='$calk?cid=$pkey'/></i></a></td></tr>";

if( $subsections == 1){
    $html.= "<tr><td>De la section et des sous sections</td><td><a class='btn btn-default btn-action' href='$calk?cid=$pkey'><i class='far fa-calendar-alt fa-lg' title='$calk?cid=$pkey&niv=1'/></i></a></td></tr>";
}
if($p_calendar!=""){
    $html.= "<tr><td>De la section et des sous sections favorites</td><td><a class='btn btn-default btn-action' target='_blank' href='$calk?cid=$pkey'><i class='far fa-calendar-alt fa-lg'  title='$calk?cid=$pkey&fav=$p_calendar'/></i></a></td></tr>";
}

$html.= "<tr><td>Exemple de filtre sur un type d'activité (exemple: FOR)</td><td><a class='btn btn-default btn-action' href='$calk?cid=$pkey&type_evenement=FOR' ><i class='far fa-calendar-alt fa-lg' title='$calk?cid=$pkey&type_evenement=FOR'/></i></a></td></tr>";
$html.= "<tr><td>Exemple de filtre sur un type d'activité (exemple: DPS)</td><td><a class='btn btn-default btn-action' href='$calk?cid=$pkey&type_evenement=DPS' ><i class='far fa-calendar-alt fa-lg'  title='$calk?cid=$pkey&type_evenement=DPS'/></i></a></td></tr>";

$html.= "<tr><td>Alerte des bénévoles $cisname</td><td><a class='btn btn-default btn-action' href='$calk?cid=$pkey&type_evenement=ALERT_NAT' ><i class='far fa-calendar-alt fa-lg'  title='$calk?cid=$pkey&type_evenement=ALERT_NAT'/></i></a></td></tr>";


//DEB -  Configurateur
$html.="<tr><td colspan=\"2\">";
$html.="
<script type=\"text/javascript\">
function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}


$(document).ready(function(){
    $(':checkbox').click(function(){
        // If checked
        if ($('#subsections').is(':checked')){
            var SubSection = '&niv=1';
        }else{
            var SubSection = '';
        }
        // Liste des calendriers des sections
        var ListeCal=''
        var CBCalendriers = ( $(\".CalendriersFavoris\").map(function(){
            if ($(this).is(':checked')){
                return $(this).val();
            }
        }).get().join(\",\") );    
        if (CBCalendriers!='') {
            var ListeCal = '&fav='+CBCalendriers;
        }
        // Liste des événements
        var ListeEvt=''
        var CBEvt = ( $(\".typeEvt\").map(function(){
            if ($(this).is(':checked')){
                return $(this).val();
                }
        }).get().join(\",\") );    
        if (CBEvt!='') {
            var ListeEvt = '&type_evenement='+CBEvt;
        }        
        //$(\"#urlIcal\").val($('#urlbase').val()+SubSection+ListeCal+ListeEvt);
        $(\"#urlIcal\").val(SubSection+ListeCal+ListeEvt);
        $(\"#icalConfig\").attr(\"href\",$('#urlbase').val()+SubSection+ListeCal+ListeEvt);
        $(\"#icalConfigIco\").attr(\"title\",$('#urlbase').val()+SubSection+ListeCal+ListeEvt);
    });
});
</script>";

$html.="</TABLE></div></div>";

$html.= "<div class='alert alert-warning ml-0 mr-0' style='text-align: left;'><p class='m-0 p-0' >Placez-vous sur l'icône <i class='far fa-calendar-alt fa-lg' ></i> , cliquez avec le bouton droit et choisissez \"Copier l'adresse du lien\". </p><p class='m-0'> Collez ce lien dans votre gestionnaire de calendrier ical: </p><small>Exemple: Google Agenda > Ajouter un Calendrier > Par URL</small></div>";

$html.="</div>";

$html .= "<div class='col-md-12 col-lg-7'>
        <div class='card hide card-default graycarddefault'>
            <div class='card-header graycard'>
                <div class='card-title'><strong>Configurateur de calendrier</strong></div>
            </div>
            <div class='card-body graycard'>";

$html.="<form name=\"FrmICalConfig\">";
$html.="<input type=\"hidden\" id=\"urlbase\" name=\"urlbase\" value=\"".$calk."?cid=$pkey\" />";
$html.="Copiez ce lien : 
<a href=\"".$calk."?cid=$pkey\" title=\"\" id=\"icalConfig\"><i class='far fa-calendar-alt fa-lg'  id=\"icalConfigIco\" ></i></a>
<input type=\"text\" name=\"urlIcal\" id=\"urlIcal\" style=\"width:320px;\" value=\"\" readonly />";
$html.="<p><input type=\"checkbox\" name=\"subsections\" id=\"subsections\" value=\"&niv=1\" /> Inclure les sous sections</p>";
if ($p_calendar!=""){
    $pcalendar = explode(",",$p_calendar);    
    $cbcalendar='';
    foreach ($pcalendar as $pcal){
        if ( $pcal <> "" ) {
            if ( in_array($pcal,$ChxCalendar)) $checked="checked";
            else $checked="";
            $cbcalendar .=" <input class=\"CalendriersFavoris\" type=\"checkbox\" name=\"chxCal[]\"  value=\"$pcal\" $checked.> ".get_section_code("$pcal");
        }
    }
    $html.="<div style='max-width:800px'>Inclure les sections favorites <br />$cbcalendar</div>";
}
$html.= "<div style=\"border:1px solid grey;padding:1em;\"><h3>Types d'activités </h3>".$cb_typeevt."</div>";
$html.="</form>";
$html.="</td></tr>";
$html.="</TABLE></div></div></div>";
//FIN -  Configurateur

if (!$myagenda)
    $html.= "<input type='button' class='btn btn-default' value='Retour' onclick='javascript:history.back(1);'> ";

$html.= "</body></html>";

echo $html;

if (!$myagenda)
    writefoot();
?>
