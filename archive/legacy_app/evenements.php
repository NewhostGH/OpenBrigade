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
  

/*
Consultation de l'agenda depuis Google Agenda
Authentification selon une clé md5 propre a chaque utilisateur
Possibilité de voir l'agenda de sa section filtré ou non sur un type d'événement, ou seulement l'agenda perso et ses favoris.

key = md5($perso."-".$nom."-".md5($mdp));
exemple : md5("2-kuntz-".md5(motdepasse));

exemple d'URL a passer dans Google Agenda

Calendrier de la section d'appartenance
http://<url>/evenements.php?cid=f0c40f6478abe127dbbf21fdb57bbeb0

Calendrier de la section filtré sur un type d'événement
http://<url>/evenements.php?type_evenement=FOR&cid=f0c40f6478abe127dbbf21fdb57bbeb0

Calendrier perso et des favoris filtré sur un type d'événement
http://<url>/evenements.php?perso=1&cid=f0c40f6478abe127dbbf21fdb57bbeb0

*/
include_once ("config.php");
require_once("iCalcreator.class.php");
require_once("fonctions.php");

$out="";

// récupération des variables
$key = (isset($_GET['cid']) ? secure_input($dbc, $_GET['cid'],$strict=true) : "");
$subsections = (isset($_GET['niv'])?1:0);
$perso = (isset($_GET['perso'])?intval($_GET['perso']):"");
$persocal = (isset($_GET['fav'])?secure_input($dbc,$_GET['fav'],$strict=true):"");
$type_evenement=(isset($_GET['type_evenement'])?secure_input($dbc,$_GET['type_evenement'],$strict=true):"ALL");

if ($key == "" ){
    $nomenu=1;
    writehead();
    write_msgbox("Erreur", $error_pic, "Paramètre de connexion inconnu!",30,0);
    exit;
}

/* ne pas modifier en dessous de ce point */
$evenement="";
$ical_perso="";

//  DEB - Identification via une clé spéciale passée en GET
$sqlp="select p.p_id, p.p_nom, p.p_prenom, p.p_code, p.p_mdp ,p.p_calendar, p.p_section section, s.s_code,
md5(concat(p.p_id,'-',p.p_nom,'-',p.p_mdp)) keyp
from pompier p , section s
where p.p_fin is null
and p.p_section = s.s_id
and md5(concat(p.p_id,'-',p.p_nom,'-',p.p_mdp)) = '$key'
";
$p_id=0;
$resp = mysqli_query($dbc,$sqlp);
while($rowp= mysqli_fetch_array($resp)){
    $expectedkey=md5($rowp['p_id']."-".$rowp['p_nom']."-".$rowp['p_mdp']);
    $p_id = intval($rowp['p_id']);
    if ($perso<>"") {
        $ical_perso=$p_id;
    }
    $section = $rowp['section'];
    if ( $section > 0 ){
        $code_section = fixcharset($cisname." - ".$rowp['s_code']);
    }else{ 
        $code_section = fixcharset($cisname);
    }
}
//  FIN - Identification via une clé spéciale passée en GET

if ($p_id > 0 and check_rights($p_id, 41)) {

// DEB idem evenement_ical.php, sauf mise en commentaire signalée is_formateur()
$section_parent = get_section_parent("$section");
if ( $type_evenement == 'ALERT_NAT' ) $calendarname="Alertes des benevoles ".$cisname;
else if ($ical_perso!="") $calendarname="Mon Calendrier $code_section";
else if ( $type_evenement <> 'ALL' ) $calendarname=((count(explode(',',$type_evenement))>2)?"Evenements de ":"$type_evenement")." $code_section";// limiter les types d'événements à l'affichage
else $calendarname="Activites de $code_section";

$v = new vcalendar();

$v->setConfig( 'format', 'ical' );
$v->setConfig( 'allowEmpty', TRUE );
//$v->setConfig( 'language', utf8_encode('fr-FR') );
  // create a new calendar instance
$v->setConfig( 'unique_id', utf8_encode($cisurl) );
  // set Your unique id
$v->setConfig( 'directory', 'ical' );
$v->setConfig( "filename", "ebrigade".date('Ymd').".ics" );
if($ical_perso!=""){  
    $v->setConfig( "filename", "ebrigade_p".$ical_perso.".ics" );
}
if($evenement !=""){
    $v->setConfig( "filename", "ebrigade_e".$evenement.".ics" ); 
}


$v->setProperty( 'method', utf8_encode('PUBLISH') );
  // required of some calendar software
$v->setProperty( "x-wr-calname", utf8_encode($calendarname) );
  // required of some calendar software
$v->setProperty( "X-WR-CALDESC", utf8_encode($calendarname) );
  // required of some calendar software
$v->setProperty( "X-WR-TIMEZONE", utf8_encode("Europe/Paris") );
  // required of some calendar software

$sql = "select e.e_code, eh.eh_id,
eh.eh_date_debut, eh.eh_debut, eh.eh_description,
eh.eh_date_fin, eh.eh_fin, 
e.e_lieu, e.e_address, e.e_comment, 
e.te_code, e.e_libelle,
e.s_id, e.e_chef,
s.s_code
from evenement e, section s, evenement_horaire eh
where  eh.eh_date_fin >= CURDATE()
and e.te_code <> 'MC'
and e.e_code = eh.e_code
and s.s_id=e.s_id";
if ($evenement!="")
    $sql .= "\n and e.e_code = $evenement ";
else {
    if (  $type_evenement == 'ALERT_NAT' ) {
        $sql .= "\n and e.te_code ='ALERT'";
    }
    else if ( $type_evenement <> 'ALL' ) {
        $sql .= "\n and e.te_code in ('".str_replace("," , "','" , $type_evenement)."')";
    }
    if ( $type_evenement <> 'ALERT_NAT') {
         if ( $subsections == 1 )
             $sql .= "\n and e.s_id in (".get_family("$section").(($persocal!="")?",".$persocal:"").")";
         else 
             $sql .= "\n and e.s_id in (".$section.(($persocal!="")?",".$persocal:"").")";
    }
    $sql .= "\n and e.e_canceled = 0";
}
$sql .= " order by eh_date_debut asc";

if($ical_perso != "") { 
   $sql = "select e.e_code,  eh.eh_id,
        eh.eh_date_debut, eh.eh_debut, eh.eh_description,
        eh.eh_date_fin, eh.eh_fin, 
        e.e_lieu, e.e_address, e.e_comment, 
        e.te_code, e.e_libelle,
        e.s_id, e.e_chef, s.s_code
        from evenement e, evenement_participation ep, section s, evenement_horaire eh
        where e.e_code = ep.e_code
        AND eh.e_code = ep.e_code
        AND eh.eh_id = ep.eh_id
        AND e.s_id = s.s_id
        AND  ep.p_id = '$ical_perso'
        AND e.e_canceled = 0
        AND e.te_code <> 'MC'
        and eh.eh_date_fin >= CURDATE()
        order by eh_date_debut asc";

}
$res = mysqli_query($dbc,$sql);
$numrows = mysqli_num_rows($res);
while($row=mysqli_fetch_array($res)){
    $UID = $row['e_code'].$row['eh_id'];
    $dtdeb=array();
    $dtdeb=preg_split('/-/',$row['eh_date_debut']);
    $yeard=$dtdeb[0];
    $monthd=$dtdeb[1];
    $dayd=$dtdeb[2];
    $hrdeb = array();
    $hrdeb = preg_split('/:/',$row['eh_debut']);
    $hourd=$hrdeb[0];
    $mind=$hrdeb[1];

    $dtfin=array();
    $dtfin=preg_split('/-/',$row['eh_date_fin']);
    $yearf=$dtfin[0];
    $monthf=$dtfin[1];
    $dayf=$dtfin[2];
    $hrfin = array();
    if ( $row['eh_fin'] == '24:00:00' ) $myfin='23:59:00';
    else $myfin=$row['eh_fin'];
    $hrfin = preg_split('/:/',$myfin);
    $hourf=$hrfin[0];
    $minf=$hrfin[1];
 
    $n=get_nb_sessions($row['e_code']);
    $eh_description =$row['eh_description'];
    
    if ( $n > 1 ) {
        if ( $eh_description <> '' ) $dp = " - ".$eh_description;
        else $dp='';
        $summary = fixcharset(substr($row['e_libelle']." partie ".$row['eh_id']."/".$n.$dp,0,255));
    }
    else $summary = fixcharset(substr($row['e_libelle'],0,255));
    
    if ( $row['e_address'] <> '' )  $location = fixcharset($row['e_address']);
    else $location = fixcharset($row['e_lieu']);
    $s_code = fixcharset($row['s_code']);
    $comment = fixcharset($row['te_code']);
    $description=fixcharset($row['e_comment']);
    $contact_orga=fixcharset(strtoupper(get_nom($row['e_chef']))." ".get_prenom($row['e_chef']));
    $section_orga=fixcharset(get_section_code($row['s_id'])." ".get_section_name($row['s_id']));

    $vevent = new vevent();
    // create an event calendar component
    $start = array( 'year'=>$yeard, 'month'=>$monthd, 'day'=>$dayd, 'hour'=>$hourd, 'min'=>$mind, 'sec'=>0 );
    $vevent->setProperty( 'dtstart', $start );
    $end = array( 'year'=>$yearf, 'month'=>$monthf, 'day'=>$dayf, 'hour'=>$hourf, 'min'=>$minf, 'sec'=>0 );
    $vevent->setProperty( 'dtend', $end );
    $vevent->setProperty( 'LOCATION', $location );

    // property name - case independent
    $vevent->setProperty( 'summary', "[".utf8_encode($comment)." ".utf8_encode($s_code)."] ".utf8_encode($summary) );
    $vevent->setProperty( 'description', utf8_encode($description) );
    $vevent->setProperty( 'comment', utf8_encode($comment) );
    if ( $location == 'caserne' )
        $vevent->setProperty( 'url', "$cisurl");
    else
        $vevent->setProperty( 'url', "$cisurl/index.php?evenement=".$row['e_code']);
    $vevent->setProperty( 'UID', utf8_encode("evt".$UID."@$cisurl"));

    $vevent->setProperty( 'ORGANIZER', utf8_encode($section_orga));
    $vevent->setProperty( 'CONTACT', utf8_encode($contact_orga));
    $v->setComponent ( $vevent );
}
// FIN idem evenement_ical.php
$v->returnCalendar();
}
else {
    $nomenu=1;
    writehead();
    write_msgbox("Erreur", $error_pic, "Utilisateur non identifié ou permissions insuffisantes!",30,0);
}
?>
