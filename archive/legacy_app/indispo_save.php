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
check_all(11);
$id = $_SESSION['id'];
$person=intval($_GET["person"]);
$section=get_section_of($person);
$statut= get_statut($person);
$nom=get_nom($person);
$prenom=get_prenom($person);

writehead();

if ( $id <> $person ) {
    check_all(12);
    if (! check_rights($id, 12 , $section)) check_all(24);
}

$type=secure_input($dbc,$_GET["type"]);
$comment=secure_input($dbc,$_GET["comment"]);
$dc1=secure_input($dbc,$_GET["dc1"]);
$dc2=secure_input($dbc,$_GET["dc2"]);
$debut=secure_input($dbc,$_GET["debut"]);
$fin=secure_input($dbc,$_GET["fin"]);
$type_periode = 1 ;

if (isset($_GET["morning"])) $morning=intval($_GET["morning"]);
else $morning=0;
if (isset($_GET["afternoon"])) $afternoon=intval($_GET["afternoon"]);
else $afternoon=0;
if (isset($_GET["full_day"])) $full_day=1;
else if ($morning > 0 or $afternoon > 0 ) $full_day=2;
else $full_day=0;

// full day = 1 (que des journées complètes), 2 (une demi_journée), 0 (des heures)

if ($full_day == 1) $type_periode = 1 ;
else if ( $morning == 1 ) {
    $debut='08:00';
    $fin='12:00';
    $type_periode = 2 ;
}
else if ( $afternoon == 1 ) {
    $debut='14:00';
    $fin='18:00';
    $type_periode = 3 ;
}

$query = "select TI_FLAG from type_indisponibilite where TI_CODE ='".$type."'";
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
$TI_FLAG = intval($TI_FLAG);

if ( $type == "") {
    write_msgbox("Erreur type", $error_pic, 
    " Le type d'absence doit être renseigné.<p align=center>
    <a href='indispo_choice.php?tab=1&person=$person'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
}
else if ( $dc1 == "") {
    write_msgbox("Erreur date", $error_pic, 
    " La date de début doit être renseignée.<p align=center>
    <a href='javascript:history.back()'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
}
else if ( $dc2 == "" and $full_day <> 2 ) {
    write_msgbox("Erreur date", $error_pic, 
    " La date de fin doit être renseignée.<p align=center>
    <a href='indispo_choice.php?tab=1&person=$person'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
}
else if (( $statut == 'SPV'  or  $statut == 'BEN' or  $statut == 'ADH' or  $statut == 'JSP')
            and  $TI_FLAG == 1 ) {
     write_msgbox("Erreur type indisponibilité", $error_pic, 
    " Les absences de type 'Congés avec circuit de validation' ne sont pas possibles pour le personnel de cette catégorie.<p align=center>
        <a href='indispo_choice.php?tab=1&person=$person'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
}
else {
    if ( $dc2 == "" ) $dc2 = $dc1;
    $tmp=explode ( "-",$dc1); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $date1=mktime(0,0,0,$month1,$day1,$year1);
    $tmp=explode ( "-",$dc2); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
    $date2=mktime(0,0,0,$month2,$day2,$year2);

    if ( $TI_FLAG == 1 ) $INDISPO_STATUT='ATT';
    else $INDISPO_STATUT='VAL';

    //insert indisponibilite
    $query="insert into indisponibilite (P_ID,  TI_CODE,  I_STATUS,  I_DEBUT,  I_FIN, I_COMMENT, IH_DEBUT, IH_FIN, I_JOUR_COMPLET,I_TYPE_PERIODE)
        values (".$person.",'".$type."','".$INDISPO_STATUT."','".$year1."-".$month1."-".$day1."','".$year2."-".$month2."-".$day2."',\"".$comment."\",'".$debut."','".$fin."',".$full_day.",".$type_periode.")";
    $result=mysqli_query($dbc,$query);

    $query="select max(I_CODE) from indisponibilite where P_ID=".$person;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $absence=$row[0];

    // suppression du tableau de garde et des disponibilités
    if (  $full_day == 1 ) {
        if ($INDISPO_STATUT=='VAL'){
            $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=1,EP_EXCUSE=1 WHERE P_ID = '".$person."'
            AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= NOW()
            and eh.EH_DATE_DEBUT >= '".$year1."-".$month1."-".$day1."' 
            and eh.EH_DATE_DEBUT<='".$year2."-".$month2."-".$day2."')";
            $result2 = mysqli_query($dbc,$query2);
        }
    }

    if ($dc1 == $dc2 or $full_day == 2) {
        $period = "du ".$day1."-".$month1."-".$year1;
        if ( $morning == 1 )  $period .= " le matin";
        else if ( $afternoon == 1 )  $period .= " l'après-midi";
        else if ( $full_day == 0 ) $period .= " de ".$debut." à ".$fin;
        //Quand demi absence
       if ($INDISPO_STATUT =='VAL'){
            $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=1,EP_EXCUSE=1 WHERE P_ID = '".$person."'
            AND EH_ID = '1'
            AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= NOW()
            and eh.EH_DATE_DEBUT >= '".$year1."-".$month1."-".$day1."' 
            and eh.EH_DATE_DEBUT<='".$year2."-".$month2."-".$day2."')";
            $result2 = mysqli_query($dbc,$query2);
        }
    }
    else  {
        $period = "du ".$day1."-".$month1."-".$year1;
        if ( $full_day == 0 and $morning == 0 and $afternoon == 0) $period .=" ($debut)";
        $period .= " au ".$day2."-".$month2."-".$year2;
        if ( $full_day == 0 and $morning == 0 and $afternoon == 0) $period .=" ($fin)";
        if ($INDISPO_STATUT =='VAL') {
            $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=1,EP_EXCUSE=1 WHERE P_ID = '".$person."'
            AND EH_ID = '2'
            AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= NOW()
            and eh.EH_DATE_DEBUT >= '".$year1."-".$month1."-".$day1."' 
            and eh.EH_DATE_DEBUT<='".$year2."-".$month2."-".$day2."')";
            $result2 = mysqli_query($dbc,$query2);
        }
    }
    
    if ( $type == 'MAL' and ($statut == 'SAL' or $statut == 'FONC' )) {
        $dates_id= array();
        $query="select H_ID, H_DATE from horaires where H_DATE >= '".$year1."-".$month1."-".$day1."' and H_DATE <= '".$year2."-".$month2."-".$day2."' and P_ID=".$person;
        $result = mysqli_query($dbc,$query);
        while ($row=mysqli_fetch_array($result)) {
            $dates_id[$row["H_DATE"]] = $row["H_ID"];
        }
        $current = $year1."-".$month1."-".$day1;
        $i=0;
        while ( $i < 365 ) {
            if ( $current == $year2."-".$month2."-".$day2 ) $i=365;
            $i++;
            
            $tmp=explode ( "-",$current); $month=$tmp[1]; $day=$tmp[2]; $year=$tmp[0];
            $_dt= mktime(0,0,0,$month,$day,$year);
            $date = new DateTime($current);
            $week = $date->format("W");
            if (! dateCheckFree($_dt)) {
                if ( isset($dates_id[$current]) ) {
                    $query="update horaire set H_DUREE_MINUTES=420, H_DUREE_MINUTES2 = 420 where H_ID = ".$dates_id[$current]." and H_DATE = '".$current."'
                            and P_ID=".$person." and (H_DUREE_MINUTES is null or H_DUREE_MINUTES=0)";
                    $result = mysqli_query($dbc,$query);
                }
                else {
                    $query="insert into horaires (P_ID, H_DATE, H_DEBUT1, H_FIN1, H_DEBUT2, H_FIN2, H_DUREE_MINUTES, H_DUREE_MINUTES2)
                            values ( ".$person.", '".$current."', null,null,null,null, 420, 420 )";
                    $result = mysqli_query($dbc,$query);
                    
                    $query="insert into horaires_validation(P_ID, ANNEE, SEMAINE, HS_CODE, CREATED_BY, CREATED_DATE)
                        select * from (select ".$person." as 'P_ID', '".$year."' as 'ANNEE', '".$week."' as 'SEMAINE', 'SEC' as 'HS_CODE', ".$id." as 'CREATED_BY', NOW() as 'CREATED_DATE') as TMP
                        where not exists (select 1 from horaires_validation h1
                                            where h1.P_ID = ".$person."
                                            and h1.ANNEE = '".$year."'
                                            and h1.SEMAINE = '".$week."')";
                    $result=mysqli_query($dbc,$query);
                }
            }
            $interval = new DateInterval('P1D');
            $date->add($interval);
            $current = $date->format("Y-m-d");
        }
    }

    $url=get_plain_url($cisurl);
    $siteurl = "http://".$url."/index.php?absence=".$absence;

    if ($log_actions == 1)
        insert_log('INSABS', $person, $type." ".$period);

    // envoi email de notification
    if ($type== 'CP' or $type== 'RTT' ) {
         $destid=$person.",".get_granted(57,"$section",'parent','yes').",".get_granted(13,"$section",'parent','yes');
        // notifier auss les responsables d'autres sections selon les rôles de l'organigramme de la personne
        $query="select S_ID from section_role where S_ID <> ".$section ."
                and P_ID = ".$person;
        $result=mysqli_query($dbc,$query);
        while ($row=mysqli_fetch_array($result)) {
             $destid .= ",".get_granted(13,$row["S_ID"],'local','yes');
        }
        $destid = str_replace(",,,",",",$destid);
        $destid = str_replace(",,",",",$destid);
         
        $subject="demande de ".$type." pour ".ucfirst($prenom)." ".strtoupper($nom);
        $message="Merci de valider la demande de ".$type." de ".ucfirst($prenom)." ".strtoupper($nom)."\n
         ".$period."\n
    Lien: ".$siteurl;
        $info="<p>Un email a été envoyé aux personnes suivantes, pour information ou validation:<br><span>".show_names_dest($destid)."</span>";
        $nb = mysendmail("$destid" , $id , $subject , "$message" );
    }
    else {
        $info="";
    }

    write_msgbox("demande enregistrée", $star_pic, 
    " L'absence (".$type.") de ".strtoupper($nom)." 
    ".ucfirst($prenom)." ".$period.
    " a été enregistrée.".$info."<p align=center>
    <a href='upd_personnel.php?pompier=".$person."&tab=18&table=1'><input type='submit' class='btn btn-primary' value=\"Fiche de ".ucfirst($prenom)." ".strtoupper($nom)."\"></a>
    <p align=center>
    <a href='indispo_choice.php?tab=2&statut=ALL&type=ALL&person=ALL&validation=ALL'><input type='submit' class='btn btn-secondary' value='Tableau absences'></a>
    <p align=center>
    <a href='indispo_choice.php?ajouter=true'><input type='submit' class='btn btn-default' value='Autre saisie'></a> ",10,0);
}
writefoot();
?>
