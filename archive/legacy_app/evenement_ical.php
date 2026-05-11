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
  
require_once("config.php");
check_all(0);
$id=$_SESSION['id'];
get_session_parameters();

$section=$filter;
require_once("iCalcreator.class.php");

$fixed_company = false;
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
    if (! check_rights($_SESSION['id'], 41)) {
        check_all(45);
        $company=$_SESSION['SES_COMPANY'];
        $_SESSION['company'] = $company;
        $fixed_company = true;
    }
}
else check_all(41);

if ($company <= 0 ) check_all(41);

$evenement=(isset($_GET['evenement'])?intval($_GET['evenement']):""); // Exporter un seul événement
$ical_perso=(isset($_GET['pid'])?intval($_GET['pid']):""); // Exporter le calendrier perso
if (isset($_GET['pid']) and ! check_rights($id, 56 )) $ical_perso = $id;

$list=$section;

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
$v->setProperty( "x-wr-calname", utf8_encode("Calendrier $cisname") );
  // required of some calendar software
$v->setProperty( "X-WR-CALDESC", utf8_encode("$cisname - Gestion des activités") );
  // required of some calendar software
$v->setProperty( "X-WR-TIMEZONE", utf8_encode("Europe/Paris") );
  // required of some calendar software

$sql = "select e.e_code, eh.eh_id,
eh.eh_date_debut, eh.eh_debut, 
eh.eh_date_fin, eh.eh_fin, 
e.e_lieu, e.e_address, e.e_comment, 
e.te_code, e.e_libelle,
e.s_id, e.e_chef,
s.s_code
from evenement e, section s, evenement_horaire eh
where  eh.eh_date_debut >= curdate()-365
and e.e_code = eh.e_code
and s.s_id=e.s_id
and e.te_code <> 'MC'
and e.e_visible_inside=1";
if ($evenement !="")
    $sql .= "\n and e.e_code = $evenement ";
else {
    if ( $type_evenement <> 'ALL' ) {
        $sql .= "\n and ( e.te_code = '".$type_evenement."'
                          or e.te_code in (select te_code from type_evenement where cev_code='".$type_evenement."')
                         )";
    }
    if (( is_formateur($id) == 0 ) 
        and (! check_rights($_SESSION['id'], 15))) 
        $sql .= "\n and e.te_code <> 'INS'";

     if ( $subsections == 1 )
         $sql .= "\n and e.s_id in (".get_family("$section").")";
     else 
         $sql .= "\n and e.s_id =".$section;
    if ( $canceled == 0 )
        $sql .= "\n and e.e_canceled = 0";
        
    if ( $company <> '-1' )
        $sql .= "\n and e.c_id =".$company;

    $tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2]; 
    $tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];

    $sql .="\n and eh.eh_date_debut <= '$year2-$month2-$day2' 
             and eh.eh_date_fin   >= '$year1-$month1-$day1'"; 
}
$sql .= " order by eh_date_debut asc";


if($ical_perso != ""){
   $sql = "select e.e_code,  eh.eh_id,
        eh.eh_date_debut, eh.eh_debut, 
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
        and eh.eh_date_debut >= now()
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
    if ( $n > 1 ) $summary = fixcharset(substr($row['e_libelle']." partie ".$row['eh_id']."/".$n,0,255));
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
    $vevent->setProperty( 'LOCATION', utf8_encode($location) );

    // property name - case independent
    $vevent->setProperty( 'summary', utf8_encode("[".$comment." ".$s_code."] ".$summary) );
    $vevent->setProperty( 'description', utf8_encode($description) );
    $vevent->setProperty( 'comment', utf8_encode($comment) );
    if ( $location == 'caserne' )
       $vevent->setProperty( 'url', utf8_encode("$cisurl"));
    else
       $vevent->setProperty( 'url', utf8_encode("$cisurl/index.php?evenement=".$row['e_code']));
    $vevent->setProperty( 'UID', utf8_encode("evt".$UID."@$cisurl"));

    $vevent->setProperty( 'ORGANIZER', utf8_encode($section_orga));
    $vevent->setProperty( 'CONTACT', utf8_encode($contact_orga));
    $v->setComponent ( $vevent );
}
$v->returnCalendar();

?>
