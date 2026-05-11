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
check_all(0);
$id=$_SESSION['id'];
$code=intval($_GET["code"]);
$action=$_GET["action"];

$query="select p.P_ID, DATE_FORMAT(i.I_DEBUT, '%d-%m-%Y') as I_DEBUT, 
        DATE_FORMAT(i.I_FIN, '%d-%m-%Y') as I_FIN, i.TI_CODE, p.P_STATUT,
        ti.TI_LIBELLE, ti.TI_FLAG, i.I_COMMENT, ist.I_STATUS_LIBELLE, i.I_STATUS,
        date_format(i.IH_DEBUT,'%H:%i') IH_DEBUT, date_format(i.IH_FIN,'%H:%i') IH_FIN, i.I_JOUR_COMPLET, I_TYPE_PERIODE
        from pompier p, indisponibilite i, type_indisponibilite ti, indisponibilite_status ist
        where i.I_CODE = ".$code."
        and p.P_ID=i.P_ID
        and i.TI_CODE=ti.TI_CODE
        and i.I_STATUS=ist.I_STATUS";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$person=$row["P_ID"];
$section=get_section_of($person);
$debut=$row["I_DEBUT"];
$fin=$row["I_FIN"];
$IH_DEBUT=$row["IH_DEBUT"];
$IH_FIN=$row["IH_FIN"];
$I_JOUR_COMPLET=$row["I_JOUR_COMPLET"];
$I_TYPE_PERIODE=$row["I_TYPE_PERIODE"];
$type=$row["TI_CODE"];
$TI_FLAG=$row["TI_FLAG"];
$status=$row["I_STATUS"];
$tmp=explode ( "-",$debut); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
$tmp=explode ( "-",$fin); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
// cas particulier, je peux supprimer ma demande de Congés en attente de validation
if ( $action == "supprimer" and  $TI_FLAG == 1 and $person == $id and $status == 'ATT' )
    check_all(0);
else {
    check_all(13);
    if (! check_rights($id, 13 , $section)) check_all(24);
}

if ($I_JOUR_COMPLET == 1  and  $debut == $fin )
    $periode="du ".$debut;
else if ($I_JOUR_COMPLET == 1 )
    $periode="du ".$debut." au ".$fin;
else if ( $debut == $fin )
    $periode="du ".$debut." de ".$IH_DEBUT." à ".$IH_FIN;
else
    $periode="du ".$debut." (".$IH_DEBUT.") au ".$fin." (".$IH_FIN.")";

if ( $action == "valider" or $action == "refuser" ) {
    if ($id == $person and ! check_rights($id, 14)) {
         write_msgbox("error", $error_pic, "Vous ne pouvez pas accepter ou refuser vos propres demandes
         <p align=center><a class='btn btn-secondary' href=\"javascript:history.back(1)\">Retour</a>", 30, 30);
         exit;
    }
}
    
if ( $action == "valider" )  {
    //valider la demande
    $query="update indisponibilite
       set I_STATUS='VAL',
       I_ACCEPT=NOW(),
       I_STATUS_BY=".$id."
       where I_CODE=".$code;
    $result=mysqli_query($dbc,$query);
    $st="acceptée";
    //Quand demi absence
    if ($I_TYPE_PERIODE == 2){
        $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=1,EP_EXCUSE=1 WHERE P_ID = '".$person."'
        AND EH_ID = '1'
        AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= '".$year1."-".$month1."-".$day1."' 
        and eh.EH_DATE_DEBUT<='".$year2."-".$month2."-".$day2."')";
        $result2 = mysqli_query($dbc,$query2);
    }
    // Quand demi absence  
    elseif ($I_TYPE_PERIODE == 3){
        $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=1,EP_EXCUSE=1 WHERE P_ID = '".$person."'
        AND EH_ID = '2'
        AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= '".$year1."-".$month1."-".$day1."' 
        and eh.EH_DATE_DEBUT<='".$year2."-".$month2."-".$day2."')";
        $result2 = mysqli_query($dbc,$query2);
    }
    else {
        //autres cas
        $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=1,EP_EXCUSE=1 WHERE P_ID = '".$person."'
        AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= '".$year1."-".$month1."-".$day1."' 
        and eh.EH_DATE_DEBUT<='".$year2."-".$month2."-".$day2."')";
        $result2 = mysqli_query($dbc,$query2);
    }
    if ($log_actions == 1)
     insert_log('VALABS', $person, $type." ".$periode." ".$st);
}
if ( $action == "refuser" )  {
    //refuser la demande
    $query="update indisponibilite
       set I_STATUS='REF',
       I_CANCEL=NOW(),
       I_STATUS_BY=".$id."
       where I_CODE=".$code;
    $result=mysqli_query($dbc,$query);
    $st="refusée";
    if ($log_actions == 1)
        insert_log('REFABS', $person, $type." ".$periode." ".$st);
}
if ( $action == "supprimer" )  {
    //supprimer la demande
    $query="delete from  indisponibilite
           where I_CODE=".$code;
    $result=mysqli_query($dbc,$query);
    $st="supprimée";
    $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=0,EP_EXCUSE=0 WHERE P_ID = '".$person."'
    AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= '".$year1."-".$month1."-".$day1."' 
    and eh.EH_DATE_DEBUT<='".$year2."-".$month2."-".$day2."')";
    $result2 = mysqli_query($dbc,$query2);
    if ($log_actions == 1)
        insert_log('DELABS', $person, $type." ".$periode." ".$st);
}

// envoi email de notification
if ( $type == 'CP' ||  $type == 'RTT' ) {
    $destid=get_granted(13,"$section",'parent','yes').','.$person;
    // notifier auss les responsables d'autres sections selon les rôles de l'organigramme de la personne
    $query="select S_ID from section_role where S_ID <> ".$section ."
            and P_ID = ".$person;
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
         $destid .= ",".get_granted(13,$row["S_ID"],'local','yes');
    }
    $destid = str_replace(",,,",",",$destid);
    $destid = str_replace(",,",",",$destid);
 
    $subject="demande de ".$type." ".$st." pour ".ucfirst(get_prenom($person))." ".strtoupper(get_nom($person));
    $message="Bonjour,\n
La demande de ".$type." de ".ucfirst(get_prenom($person))." ".strtoupper(get_nom($person))."\n";
    $message .= $periode;
    $message .="\na été ".$st." par ".ucfirst(get_prenom($id))." ".strtoupper(get_nom($id)).".\n";
    $nb = mysendmail("$destid" , $id , $subject , "$message" );
}

echo "<body onload='javascript:self.location.href=\"indispo_choice.php?tab=2\";'>";
?>

