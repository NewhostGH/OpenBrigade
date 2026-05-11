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
check_all(0);
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
  msgbox("ERREUR", $error_pic, 
    "Vous venez de recharger la page. Votre message n'a pas été enoyé une 2e fois.
        <p align=center><a href='index.php'><input type='submit' class='btn btn-default' value='Retour'></a>",30,0);
  writefoot();
  exit;
}


$SMS_CONFIG=get_sms_config($mysection);

$poste=intval($_GETSAUVE["poste"]);
$section=intval($_GETSAUVE["section"]);
$mode=$_GETSAUVE["mode"];
$dispo=secure_input($dbc,$_GETSAUVE["dispo"]);
if ( $mode == 'sms' ) check_all(23);

if ( isset ($_GETSAUVE["subject"])) $subject=clean_mail_data($_GETSAUVE["subject"]);
else $subject = "";

if ( $dispo == '0' ) {
    if ( $poste <> 0 ) { 
    $query="select distinct a.P_ID from pompier a, poste b, qualification c
        where a.P_ID=c.P_ID
        and b.PS_ID=c.PS_ID
        and a.P_OLD_MEMBER=0
        and a.P_STATUT <> 'EXT'
        and b.PS_ID = $poste 
        and c.Q_VAL > 0
        and (a.P_SECTION in (".get_family("$section").")
             or a.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )";
    }
    else {
     $query="select distinct P_ID from pompier
         where P_OLD_MEMBER=0
         and P_STATUT <> 'EXT'
        and (P_SECTION in (".get_family("$section").")
             or P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )";
    }
}
else {
     if ( $poste <> 0 ) { 
    $query="select distinct a.P_ID from pompier a, poste b, qualification c, disponibilite d
        where a.P_ID=c.P_ID
        and d.P_ID = a.P_ID
        and b.PS_ID=c.PS_ID
        and a.P_OLD_MEMBER=0
        and a.P_STATUT <> 'EXT'
        and b.PS_ID = $poste 
        and d.D_DATE = '".$dispo."'
        and c.Q_VAL > 0
        and (a.P_SECTION in (".get_family("$section").")
             or a.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )";
    }
    else {
     $query="select distinct p.P_ID from pompier p, disponibilite d
         where d.P_ID =p.P_ID
         and p.P_OLD_MEMBER=0
         and p.P_STATUT <> 'EXT'
         and d.D_DATE = '".$dispo."'
        and (P_SECTION in (".get_family("$section").")
              or p.P_ID in (select P_ID from section_role where S_ID in (".get_family("$section")."))
            )";
    }
}

$dest=''; $nb1=0;
$result=mysqli_query($dbc,$query);
while ($row=@mysqli_fetch_array($result) ) {
     $dest .= $row["P_ID"].",";
     $nb1++;
}

$message=clean_mail_data($_GETSAUVE["message"]);

if ( $mode == 'sms' ) {
    $sent = send_sms ( "$id", "$dest", "$message", "$mysection", "alerte_create.php" );
}
else {
    if ( $subject == "") $subject="message de ".ucfirst(get_prenom($id))." ".strtoupper(get_nom($id));
    $nb = mysendmail( "$dest" , $id  , $subject , "$message" );

    write_msgbox("OK", $star_pic, "Le message (de ".get_email($id).") a été envoyé à:
    <br>".$nb." personnes sur ".$nb1."<p><font face=courrier-new size=2>Sujet:[".$cisname."] ".$subject."
    <p>".nl2br($message)."</font><p align=center><a href='index.php'><input type='submit' class='btn btn-default' value='Retour'></a>",30,0);
}

?>
