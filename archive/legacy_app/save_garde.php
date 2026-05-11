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
check_all(6);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
writehead();
get_session_parameters();
$evenement=intval($_POST["evenement"]);
//=====================================================================
// enregistrer le personnel sur la garde
//=====================================================================
/* if ( $debug ) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} */
$query="select E.E_CODE, EH.EH_ID, E.S_ID,E.TE_CODE, TE.TE_LIBELLE, E.E_LIEU, EH.EH_DATE_DEBUT,EH.EH_DATE_FIN,
        TIME_FORMAT(EH.EH_DEBUT, '%k:%i') as EH_DEBUT, S.S_CODE, E_PARENT, E_EXTERIEUR, E_VISIBLE_INSIDE,
        TIME_FORMAT(EH.EH_FIN, '%k:%i') as EH_FIN, S.S_EMAIL, S.S_EMAIL2, S.S_EMAIL3, E.TAV_ID,
        E.E_NB, E.E_COMMENT, E.E_LIBELLE, S.S_DESCRIPTION, E.E_CLOSED, E.E_CANCELED, E.E_CANCEL_DETAIL, 
        E.E_HEURE_RDV, E.E_LIEU_RDV
        from evenement E, type_evenement TE, section S, evenement_horaire EH
    where E.TE_CODE=TE.TE_CODE
    and E.E_CODE = EH.E_CODE
    and S.S_ID=E.S_ID
    and E.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);

$EH_ID= array();
$EH_DEBUT= array();
$EH_DATE_DEBUT= array();
$EH_DATE_FIN= array();
$EH_FIN= array();
$date1=array();
$month1=array();
$day1=array();
$year1=array();
$i=1;

while ( $row=mysqli_fetch_array($result)) {
    if ( $i == 1 ) {
        $E_CODE=$row["E_CODE"];
        $S_ID=$row["S_ID"];
        if (! check_rights($id, 6, $S_ID) and $nbsections == 0) check_all(24);
        $TE_CODE=$row["TE_CODE"];
        $E_LIBELLE=$row["E_LIBELLE"];
        $E_VISIBLE_INSIDE=$row["E_VISIBLE_INSIDE"];
    }
    $EH_DEBUT[$i]=$row["EH_DEBUT"];
    $EH_DATE_DEBUT[$i]=$row["EH_DATE_DEBUT"];
    $EH_DATE_FIN[$i]=$row["EH_DATE_FIN"];
    $EH_FIN[$i]=$row["EH_FIN"];
    $EH_ID[$i]=$row["EH_ID"];
    $tmp=explode ( "-",$EH_DATE_DEBUT[$i]); $year1[$i]=$tmp[0]; $month1[$i]=$tmp[1]; $day1[$i]=$tmp[2];
    $date1[$i]=mktime(0,0,0,$month1[$i],$day1[$i],$year1[$i]);
    $i++;
}
$url=get_plain_url($cisurl);
$siteurl = "http://".$url."/index.php?evenement=".$evenement;
$message_desc = "date..................: le ".date_fran($month1[1], $day1[1] ,$year1[1])." ".moislettres($month1[1])." ".$year1[1]."\n";
$message_desc  .= "<a href=".$siteurl." title='cliquer pour voir le détail'>".$E_LIBELLE."</a>.\n\n";
$admins_garde = get_granted(60,"$S_ID",'local','yes');
$chefs=get_chefs_evenement($evenement);

// qui est déjà inscrit?
$old_inscrits=array();
$old_inscrits[0]=explode(",",get_inscrits_garde($evenement));
$old_inscrits[1]=explode(",",get_inscrits_garde($evenement,1));
$old_inscrits[2]=explode(",",get_inscrits_garde($evenement,2));
$nbinscrits=count($old_inscrits[0]);
$already_sent = array();

// parties de la garde
$queryG="select EH_ID, EH_DUREE from evenement_horaire where E_CODE=".$evenement." order by EH_ID asc";
$resultG=mysqli_query($dbc,$queryG);
$parties=array();
while ($rowG=@mysqli_fetch_array($resultG)) {
    array_push($parties,array($rowG["EH_ID"],$rowG["EH_DUREE"]));
}

// ceux qui sont maintenant cochés
$new_inscrits[1]=array();
$new_inscrits[2]=array();
foreach ($_POST as $key => $value) {
    if ( substr($key,0,5) == 'check' ) {
        $T = explode("_", $key);
        $pid=$T[1];
        $statut=$T[2];
        if ( substr($key,0,6) == 'check1' ) $ehid=1;
        else $ehid=2;
        if ( $statut == 'SPP' ) $flag1=1;
        else $flag1=0;

        if ( intval($value) == 1 ) {
            // tableau des nouveaux inscrits
            array_push($new_inscrits[$ehid],$pid);
        
            // si déjà inscrit, passer au suivant
            if (in_array($pid, $old_inscrits[$ehid]))
            continue;
        
            $EP_REMINDER = get_reminder($pid, 72);
            
            foreach($parties as $p) {
                $part=$p[0];
                $duree=$p[1];
                if ( $ehid == $part ) {
                    $query="insert into evenement_participation (E_CODE, EH_ID, P_ID, EP_DATE, EP_BY, EP_FLAG1, EP_DUREE, EP_REMINDER)
                        values ( ".$evenement.", ".$part.", ".$pid.", now() ,".$id.",".$flag1.",".$duree.",".$EP_REMINDER.")";
                    $result=mysqli_query($dbc,$query);
                    if ( count($parties) == 1 ) $comment="";
                    else $comment = "partie ".$part;
                    if ( mysqli_affected_rows($dbc) > 0 )
                        insert_log('INSCP', $pid, $comment, $evenement);
                }
            }
            // nouvel inscrit après publication tableau de garde, envoyer notification ici
            // on notifie le SP nouvellement inscrit et le chef de garde + ceux qui peuvent modifier le tableau
            // dans le cas association, pas de notification car le tableau est fait au fil de l'eau
            if ( $E_VISIBLE_INSIDE == 1 and ! in_array($pid, $already_sent) and $pompiers ) {
                $subject="inscription - ".$E_LIBELLE;
                $prenom=my_ucfirst(get_prenom($pid));
                $nom=strtoupper(get_nom($pid));
                $message = "Bonjour,\n
Pour information, ".$prenom." ".$nom."\nvient d'être enregistré pour participer à la garde suivante:\n";
                $message .= $message_desc;
                $message .= "pour plus d'information sur les horaires consulter votre calendrier.\n";
                $destid = $admins_garde.",".$pid;
                if ( count($chefs) > 0 ) $destid .= ",".implode(',',$chefs);
                if ( $destid <> '' ) $nb = mysendmail("$destid" , $id , "$subject" , "$message" );
                array_push($already_sent, $pid);
            }
        }
    }
}

//les désinscriptions Jour
desinscrire_garde($evenement, $old_inscrits[1], $new_inscrits[1], 1, $year1[1],$month1[1], $day1[1]);
//les désinscriptions Nuit
desinscrire_garde($evenement, $old_inscrits[2], $new_inscrits[2], 2, $year1[1],$month1[1], $day1[1]);

echo "<body onload=\"javascript:self.location.href='evenement_display.php?evenement=".$evenement."&tab=2';\">";

writefoot();

?>
