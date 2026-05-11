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
include_once ("fonctions_sms.php");
check_all(43);

if(!empty($_GET))
{
    $_SESSION['sauvegarde'] = $_GET ;
    $fichierActuel = $_SERVER['PHP_SELF'] ;
    header('Location: ' . $fichierActuel);
    exit;
}

if(isset($_SESSION['sauvegarde']))
{
    $_GETSAUVE = $_SESSION['sauvegarde'] ;
    unset($_SESSION['sauvegarde']);
}

$id=intval($_SESSION['id']);
$mysection = $_SESSION['SES_SECTION'];
writehead();

if(!isset($_GETSAUVE) AND empty($_GET)) {
  write_msgbox("ERREUR", $error_pic, 
    "Vous venez de recharger la page. Votre message n'a pas été envoyé une 2e fois.
        <p align=center><a href='index.php'><input type='submit' class='btn btn-primary' value='Retour'></a>",30,0);
  writefoot();
  exit;
}

$dest=$_GETSAUVE["dest"];
$mode=$_GETSAUVE["mode"];

if ( isset ($_GETSAUVE["subject"])) $subject=clean_mail_data($_GETSAUVE["subject"]);
else $subject = "";

if ( $mode == 'sms' ) check_all(23);

$message=clean_mail_data($_GETSAUVE["message"]);
if ( $mode == 'sms' ) {
    $sent = send_sms ( "$id", "$dest", "$message", "$mysection", "mail_create.php?mode=sms" );
}
else {
    if ( $subject == "") $subject="message de ".ucfirst(get_prenom($id))." ".strtoupper(get_nom($id));
    $nb = mysendmail( "$dest" , $id  , $subject , "$message" );

    write_msgbox("OK", $star_pic, "Le message a été envoyé à:
    <br>".$nb." personnes<p><font face=arial size=2>Sujet:[".$cisname."] ".$subject."
    <p>".nl2br($message)."</font><p align=center><a href='mail_create.php'><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
}
writefoot();
?>
