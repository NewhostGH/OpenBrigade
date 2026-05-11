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
if ( $already_configured == 0 ) $nomenu=1;
writehead();

$new1=secure_input($dbc,$_POST["new1"]);
$new2=secure_input($dbc,$_POST["new2"]);
$section=get_section_of($id);
$matricule=get_matricule($id);
$url = "change_password.php?pid=".$id;

?>
<html>
<SCRIPT language=JavaScript>
function redirect(url) {
     self.location.href=url;
}
</SCRIPT>
<?php
verify_csrf('change_password');

if ($new1 =="" ) {
    write_msgbox("erreur mot de passe",$error_pic,"le nouveau mot de passe doit être renseigné <br><p align=center><input type='button' class='btn btn-secondary' value='Retour' onclick=\"redirect('".$url."');\">",30,30);
    exit;
}

//======================
// check current password
//======================

if ( isset($_POST["current"]) ) {
    $current=secure_input($dbc,$_POST["current"]);
    $query="select P_MDP from pompier where P_ID=".$id;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $valid =  my_validate_password($current, $row["P_MDP"]);

    if ( ! $valid ) {
        write_msgbox("erreur mot de passe",$error_pic,"le mot de passe actuel est faux <br><p align=center><input type='button' class='btn btn-secondary' value='Retour' 
        onclick=\"redirect('".$url."');\">",30,30);
        exit;
    }
}
//======================
// check duplicate
//======================

elseif ($new1 <> $new2) {
    write_msgbox("erreur mot de passe",$error_pic,"les 2 valeurs saisies pour le nouveau mot de passe sont différentes<br><p align=center><input type='button' class='btn btn-secondary' value='Retour' 
    onclick=\"redirect('".$url."');\">",30,30);
    exit;
}

//======================
// check quality
//======================
$pos = strpos($new1, $matricule);

if (($pos == true ) or ( substr($new1,0,2) == substr($matricule,0,2)))  { 
    write_msgbox("erreur mot de passe",$error_pic,"le mot de passe ne doit pas être basé sur votre identifiant.<br><p align=center><input type='button' class='btn btn-secondary' value='Retour' 
    onclick=\"redirect('".$url."');\">",30,30);
    exit;
}

if ( $password_quality > 0 ){
  if (! preg_match("/.*[0-9].*/","$new1" )){
        write_msgbox("erreur mot de passe",$error_pic,"le mot de passe doit aussi contenir des chiffres.<br><p align=center><input type='button' class='btn btn-secondary' value='Retour' 
        onclick=\"redirect('".$url."');\">",30,30);
        exit;
  }
  if (! preg_match("/.*[a-zA-Z].*/","$new1" )){
        write_msgbox("erreur mot de passe",$error_pic,"le mot de passe doit aussi contenir des lettres.<br><p align=center><input type='button' class='btn btn-secondary' value='Retour' 
        onclick=\"redirect('".$url."');\">",30,30);
        exit;
  }
  if ($password_quality > 1 and ! preg_match("/\W/","$new1" )){
        write_msgbox("erreur mot de passe",$error_pic,"le mot de passe doit aussi contenir au moins un caractère spécial, <br>parmi ceux-ci par
        exemple:<p><b>!,@,#,$,%,^,&,*,?,_,~,£,µ,§,=,<br>é,è,ç,à,ù,>,<,€,\.,\;,\,+,-,¤,|</b><br><p align=center>
        <input type='button' class='btn btn-secondary' value='Retour' 
        onclick=\"redirect('".$url."');\">",30,30);
        exit;
  }
}

if ( preg_match("/\"|\'/","$new1" )){
    write_msgbox("erreur mot de passe",$error_pic,"le mot de passe ne doit pas contenir d'apostrophes ou guillemets.<br><p align=center><input type='button' class='btn btn-secondary' value='Retour' 
    onclick=\"redirect('".$url."');\">",30,30);
    exit;
}

//======================
// check length
//======================

if ( $password_length > 0 ){
    if (strlen("$new1") < $password_length ) {
        write_msgbox("erreur mot de passe",$error_pic,"le mot de passe est trop court. Il doit avoir au moins $password_length caractères.<br><p align=center><input type='button' class='btn btn-secondary' 
        value='Retour' onclick=\"redirect('".$url."');\">",30,30);
        exit;
    }
}

$hash = my_create_hash($new1);
$query="update pompier set P_MDP=\"".$hash."\", P_PASSWORD_FAILURE=null, P_MDP_EXPIRY=null where P_ID=".$id;
$result=mysqli_query($dbc,$query);

if ( $password_expiry_days > 0 ) {
    $current = date('Y-m-d');
    $next_expiry = date('Y-m-d', strtotime($current. " + ".intval($password_expiry_days)." days"));
    $query="update pompier set P_MDP_EXPIRY='".$next_expiry."' where P_ID=".$id;
    $result=mysqli_query($dbc,$query);
}

insert_log('UPDMDP', $id);
echo "<p>";

// lorsque admin vient de choisir son mot de passe la première fois, ouvrir la page configuration
if ( $already_configured == 0 and check_rights($id,14) and $id == 1 )
    write_msgbox("changement réussi",$star_pic,"le mot de passe a été configuré avec succès<br><p align=center><input type='button' class='btn btn-primary' value='Continuer' 
            onclick=\"redirect('wizard.php');\">",30,30);
else {
    $target = "upd_personnel.php?pompier=".$id;
    if ( $charte_active ) {
        $query="select P_ACCEPT_DATE from pompier where P_ID=".$id;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        if ( $row["P_ACCEPT_DATE"] == '' ) $target="charte.php";
    }
    write_msgbox("changement réussi",$star_pic,"le mot de passe a été modifié avec succès<br><p align=center><input type='button' class='btn btn-secondary' value='Retour' 
            onclick=\"redirect('".$target."');\">",30,30);
}
writefoot();
?>
