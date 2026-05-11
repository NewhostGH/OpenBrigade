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
destroy_my_session_if_forbidden($id);

if (isset($_POST["evenement"])) $evenement=intval($_POST["evenement"]);
else $evenement=0;
if (isset($_POST["renfort"])) $renfort=intval($_POST["renfort"]);
else $renfort=0;
if (isset($_POST["confirmed"])) $confirmed=intval($_POST["confirmed"]);
else $confirmed=0;

?>
<SCRIPT>
function redirect(evenement) {
    url="evenement_display.php?from=choice&evenement="+evenement;
    self.location.href=url;
}
</SCRIPT>
<?php

$evts=get_event_and_renforts($evenement,false);

$chefs=get_chefs_evenement($evenement);

// sanity checks
$query = "select E_CODE, E_PARENT, E_CANCELED, E_COLONNE_RENFORT from evenement where E_CODE=".$renfort;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$E_PARENT=intval($row["E_PARENT"]);
$E_CANCELED=$row["E_CANCELED"];
$E_COLONNE_RENFORT=$row["E_COLONNE_RENFORT"];
$E_CODE=intval($row["E_CODE"]);
$errcode=0;

if ( $renfort == 0 ){
    $msg="Le numéro saisi pour l'activité à rattacher est incorrect, saisir une valeur numérique. Veuillez choisir un autre numéro.";
    $errcode=1;
}
else if ( $evenement == $renfort) {
    $msg="Un événement ne peut pas être rattaché à lui même en tant que ".$renfort_label.". Veuillez choisir un autre numéro.";
    $errcode=1;
}
else if ( $E_CODE == 0 ) {
    $msg="L'événement n°".$renfort." n'a pas été trouvé dans la base. Veuillez choisir un autre numéro.";
    $errcode=1;
}
else if ( $E_PARENT > 0 ) {
    $msg="L'événement n°".$renfort." est déjà rattaché en tant que ".$renfort_label." sur un autre événement principal. Veuillez choisir un autre numéro.";
    $errcode=1;    
}
else if ( $E_CANCELED > 0 ) {
    $msg="L'événement n°".$renfort." est annulé et ne peut pas être rattaché à la colonne. Veuillez choisir un autre numéro.";
    $errcode=1;
}
else if ( $E_COLONNE_RENFORT > 0 ) {
    $msg="L'événement n°".$renfort." est aussi une colonne de ".$renfort_label."s et ne peut pas être enregistré comme ".$renfort_label.". Veuillez choisir un autre numéro.";
    $errcode=1;
}
else if ( get_nb_sessions($evenement) > 1 ) {
    $msg="Une colonne de renfort ne doit pas avoir plusieurs parties. Veuillez changer les horaires avant de rattacher des ".$renfort_label."s.";
    $errcode=1;
}
//else if ( get_nb_sessions($renfort) > 1 ) {
//    $msg="L'événement renfort n°".$renfort." a plusieurs parties. Veuillez changer les horaires avant de rattacher des renforts, il ne doit en avoir que une pour pouvoir être rattaché.";
//    $errcode=1;
//}
else if (! evenements_overlap( $evenement, $renfort )) {
    $msg="L'événement renfort n°".$renfort." a des dates qui ne correspondent pas à celles de l'événement principal. Il ne peut donc pas être rattaché.";
    $errcode=1;
}

if ($errcode > 0 ) {
     write_msgbox("ERREUR", $error_pic, $msg."<p align=center><a href='javascript:history.back(1);'><input type='submit' class='btn btn-secondary' value='Retour'></a> ",10,0);
     exit;    
}
else if ( $confirmed == 0 ) {
    $query="select te.TE_LIBELLE, te.TE_ICON from type_evenement te, evenement e where e.TE_CODE= te.TE_CODE and e.E_CODE=".$renfort;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $icon=$row["TE_ICON"];
    $libelle=$row["TE_LIBELLE"];
    $query ="select count(distinct P_ID) NB from evenement_participation where E_CODE=".$renfort." and EP_ABSENT=0";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $NB=$row["NB"];
    $msg = "Vous aller rattacher cet événement en tant que renfort: <p>";
    $msg .= " <img src='images/evenements/".$icon."' height=16 title=\"".$libelle."\"> <b>".get_info_evenement($renfort)."</b>";
    $msg .= " <b>".$NB."</b> inscrits sur cet événement.";
    $msg .= "<br><form method=POST action=evenement_add_renfort.php><input type='hidden' name='evenement' value='".$evenement."'><input type='hidden' name='renfort' value='".$renfort."'><input type='hidden' name='confirmed' value='1'>";
    write_msgbox("CONFIRMATION", $question_pic, $msg."<p align=center> <input type='submit' class='btn btn-primary' value='confirmer'> <input type='button' class='btn btn-default' value='annuler' onclick='javascript:history.back(1);'>",10,0);
     exit;
}

if ( ! in_array($id, $chefs)) {
    check_all(15);
    $organisateur_colonne=get_section_organisatrice($evenement);
    $organisateur_renfort=get_section_organisatrice($renfort);
    if ( ! check_rights($id, 15, $organisateur_colonne)) 
        check_all(24);
    if ( ! check_rights($id, 15, $organisateur_renfort)) 
        check_all(24);    
}
$query="update evenement set E_PARENT=".$evenement." where E_CODE=".$renfort;    
$result=mysqli_query($dbc,$query);
$query="update evenement_participation set EE_ID = null where E_CODE = $renfort and EE_ID in (select EE_ID from evenement_equipe where E_CODE = $renfort )";
$result=mysqli_query($dbc,$query);
$query="delete from evenement_equipe where E_CODE = $renfort";
$result=mysqli_query($dbc,$query);

echo "<body onload=\"redirect('".$evenement."');\">";
?>
