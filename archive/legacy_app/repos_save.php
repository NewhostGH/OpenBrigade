<?php

  # project: eBrigade
  # homepage: https://ebrigade.app
  # version: 5.3

  # Copyright (C) 2004, 2021 Nicolas MARCHE (eBrigade Technologies) , Michel GAUTIER
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
destroy_my_session_if_forbidden($id);
$SES_NOM=$_SESSION['SES_NOM'];
$SES_PRENOM=$_SESSION['SES_PRENOM'];
$SES_GRADE=$_SESSION['SES_GRADE'];
$nbjours=intval($_POST["nbjours"]);
$month=intval($_POST["month"]);
$year=intval($_POST["year"]);
$person=intval($_POST["person"]);
$type = 'RT';
if (get_matricule($person) == '' ) {
    param_error_msg();
    exit;
}

$hissection=get_section_of($person);

if ( $id <> $person ) {
 check_all(10);
 if (! check_rights($id, 10, $hissection)) check_all(24);
}

writehead();

//=====================================================================
// purger les disponibilités de la personne pour le mois en cours
//=====================================================================

$query="delete from indisponibilite
      where P_ID=".$person."
        and I_DEBUT>='".$year."-".$month."-01'
        and I_DEBUT<='".$year."-".$month."-".$nbjours."'
    and TI_CODE ='".$type."'";
$result=mysqli_query($dbc,$query);
//=====================================================================
// Purger les absences quand incrit de garde
//=====================================================================
 $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=0,EP_EXCUSE=0 WHERE P_ID = '".$person."'
           AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= '".$year."-".$month."-01' 
    and eh.EH_DATE_DEBUT<='".$year."-".$month."-".$nbjours."')";
  $result2 = mysqli_query($dbc,$query2);
//=====================================================================
// enregistrer les disponibilités saisies
//=====================================================================

$query="select P_NOM,P_PRENOM, P_EMAIL from pompier where P_ID=".$person;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$prenom=my_ucfirst($row["P_PRENOM"]);
$nom=strtoupper($row["P_NOM"]);
$email = $row["P_EMAIL"];

$i=1;

while ( $i <= $nbjours ) {
    for ( $z = 1; $z <= 4; $z++ ) {
        if ( isset($_POST[$z."_".$i])) {
            switch($z) { 
                case 2 : 
                    $query=
                      "insert into indisponibilite (P_ID,  TI_CODE,  I_STATUS,  I_DEBUT,  I_FIN, I_COMMENT, IH_DEBUT, IH_FIN, I_JOUR_COMPLET,I_TYPE_PERIODE)
                      values ( '".$person."','".$type."','VAL','".$year."-".$month."-".$i."','".$year."-".$month."-".$i."','Demi Garde de Jour','08:00:00','12:00:00',2,2)";
                    $result=mysqli_query($dbc,$query);
                    $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=1,EP_EXCUSE=1 WHERE P_ID = '".$person."'
                    AND EH_ID = '1'
                    AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= '".$year."-".$month."-".$i."' 
                    and eh.EH_DATE_DEBUT<='".$year."-".$month."-".$i."')";
                    $result2 = mysqli_query($dbc,$query2); 
                    break;
                case 4 :
                    $query=
                      "insert into indisponibilite (P_ID,  TI_CODE,  I_STATUS,  I_DEBUT,  I_FIN, I_COMMENT, IH_DEBUT, IH_FIN, I_JOUR_COMPLET,I_TYPE_PERIODE)
                      values ( '".$person."','".$type."','VAL','".$year."-".$month."-".$i."','".$year."-".$month."-".$i."','Demi Garde de Nuit','20:00:00','08:00:00','2','3')";
                    $result=mysqli_query($dbc,$query);
                    $query2=" UPDATE evenement_participation SET EP_REMINDER=0,EP_ABSENT=1,EP_EXCUSE=1 WHERE P_ID = '".$person."'
                    AND EH_ID = '2'
                    AND E_CODE IN ( SELECT E_CODE FROM evenement_horaire eh WHERE eh.EH_DATE_DEBUT >= '".$year."-".$month."-".$i."' 
                    and eh.EH_DATE_DEBUT<='".$year."-".$month."-".$i."')";
                    $result2 = mysqli_query($dbc,$query2);
                    break;
            }
        }
    }
    $i=$i+1;
}

$detail="Type absence:".$type;

$moislettres=moislettres($month);

if ( $id == $person ) {
   write_msgbox("OK", $star_pic, "Merci <b>".$prenom."</B> tes repos pour <b>".$moislettres."</b> ont été enregistrés <p align=center><a href=repos_saisie.php?person=$id&section=".$hissection."><input type='submit' class='btn btn-default' value='Retour accueil'></a>",30,0);
}
else {
   write_msgbox("OK", $star_pic, "Les repos de ".$prenom." ".$nom." pour <b>".$moislettres."</b> ont été enregistrés <p align=center><a href=repos_saisie.php?person=$person&section=".$hissection." ><input type='submit' class='btn btn-default' value='Retour'></a>",30,0);
}

insert_log('INSABS', $person, $moislettres." ".$year.": ".$detail);

writefoot();

?>
