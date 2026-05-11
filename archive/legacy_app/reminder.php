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

// script to be used by cronjob in command line. Or for test purpose in a browser  
if(! defined('STDIN')) {
  // for test purpose only
  check_all(14);
}

// competences arrivant a expiration
if ( $nbsections == 0 ) {
    notify_before_expiration();
    // statistiques manquantes 3 jours après la fin du DPS ou de la garde
    $query = "select distinct
    e.E_CODE,
    e.TE_CODE,
    e.E_LIEU,
    e.E_LIBELLE,
    s.S_CODE,
    s.S_DESCRIPTION
    from evenement e, section s, evenement_horaire eh
    where e.s_id = s.s_id
    and e.E_CODE = eh.E_CODE
    and e.E_PARENT is null
    and e.E_CANCELED = 0
    and eh.EH_ID = 1
    and not exists (select 1 from bilan_evenement be where be.E_CODE=e.E_CODE)
    and e.TE_CODE in ( 'DPS', 'GAR' )
    and TO_DAYS(NOW()) - TO_DAYS(eh.EH_DATE_FIN) = 3";
    $result=mysqli_query($dbc,$query);

    while ( custom_fetch_array($result)) {
        if ( $TE_CODE == 'GAR' ) $t = 'Garde';
        else $t = $TE_CODE;
        $subject="Statistiques ".$t." manquantes: ".$E_LIBELLE;

        $url=get_plain_url($cisurl);
        $siteurl = "http://".$url."/index.php?evenement=".$E_CODE;
        $message_desc  = "<a href=".$siteurl." title='cliquer pour voir le détail'>".$t." : ".$E_LIBELLE."</a>.\n\n";
        $message_desc .= "organisé par..........: ".$S_CODE." - ".$S_DESCRIPTION."\n";
        $message_desc .= "lieu..................: ".$E_LIEU.".\n";
        $message_desc .= "Merci de compléter les statistiques.\n";
        
        $chefs = get_chefs_evenement($evenement);
        if ( count($chefs) > 0 ) {
            for ( $c = 0; $c < count($chefs); $c++ ) {
                $P_ID = $chefs[$c] ;
                $message = "Bonjour,\n
Vous n'avez pas renseigné les statistiques de l'événement suivant, dont vous êtes le responsable désigné:\n\n";
                $message .= $message_desc;
                mysendmail("$P_ID","$P_ID","$subject","$message");
            }
        }
    }
}

// inscriptions aux evenements
$query="select e.E_CODE, eh.EH_ID, e.S_ID, e.TE_CODE, te.TE_LIBELLE, e.E_LIEU, eh.EH_DATE_DEBUT, eh.EH_DATE_FIN,
        p.P_ID, p.P_PRENOM, p.P_NOM, p.P_EMAIL, s.S_CODE, s.S_DESCRIPTION, e.E_COMMENT, e.E_COMMENT2, ep.EP_COMMENT, e.E_LIBELLE,
        TIME_FORMAT(eh.EH_DEBUT, '%k:%i ') as EH_DEBUT,
        TIME_FORMAT(eh.EH_FIN, '%k:%i ') as EH_FIN
        from evenement e, evenement_horaire eh, evenement_participation ep, pompier p, type_evenement te, section s
        where p.P_ID = ep.P_ID
        and s.S_ID = e.S_ID
        and e.TE_CODE = te.TE_CODE
        and e.E_CODE = ep.E_CODE
        and eh.E_CODE = ep.E_CODE
        and eh.EH_ID = ep.EH_ID
        and ep.EP_REMINDER = 1
        and e.E_CANCELED = 0
        and p.P_OLD_MEMBER = 0
        and p.P_EMAIL is not null
        and TO_DAYS(eh.EH_DATE_DEBUT) - TO_DAYS(NOW()) = 1";
$result=mysqli_query($dbc,$query);

while ( custom_fetch_array($result)) {
    $tmp=explode ( "-",$EH_DATE_DEBUT); $year1=$tmp[0]; $month1=$tmp[1]; $day1=$tmp[2];
    $date1=mktime(0,0,0,$month1,$day1,$year1);
    if (( $EH_DATE_FIN <> '' ) and ( $EH_DATE_FIN <> $EH_DATE_DEBUT )) {
        $tmp=explode ( "-",$EH_DATE_FIN); $year2=$tmp[0]; $month2=$tmp[1]; $day2=$tmp[2];
        $date2=mktime(0,0,0,$month2,$day2,$year2);
        $infos_dates = "dates.................: du ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1." à ".$EH_DEBUT;
        $infos_dates .= " au ".date_fran($month2, $day2 ,$year2)." ".moislettres($month2)." ".$year2." à ".$EH_FIN;
    }
    else {
        $infos_dates = "date..................: le ".date_fran($month1, $day1 ,$year1)." ".moislettres($month1)." ".$year1." de ".$EH_DEBUT." à ".$EH_FIN;
    }

    $subject=$TE_LIBELLE.":  ".$E_LIBELLE;

    $message_desc  = $TE_LIBELLE." : ".$E_LIBELLE.".\n";
    $message_desc .= "organisé par..........: ".$S_CODE." - ".$S_DESCRIPTION."\n";
    $message_desc .= $infos_dates."\n";

    $message_desc .= "lieu..................: ".$E_LIEU.".\n";
    if ( $E_COMMENT <> "" ) 
        $message_desc .= "commentaire...........: ".$E_COMMENT."\n";
    if ( $E_COMMENT2 <> "" ) 
        $message_desc .= "commentaire externe ..: ".$E_COMMENT2."\n";
    if ( $EP_COMMENT <> "" ) 
        $message_desc .= "commentaire personnel .: ".$EP_COMMENT."\n";
    $subject="Rappel - ".$subject;
    $user=fixcharset(my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM));
    $message = "Bonjour ".$user.",\n
Vous êtes inscrit(e) pour participer demain à l'événement suivant:\n\n";
    $message .= $message_desc;
    mysendmail("$P_ID","$P_ID","$subject","$message");
    //echo "<pre>".$user." ".$message."<pre>";
}
?>
