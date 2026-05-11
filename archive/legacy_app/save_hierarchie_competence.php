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
check_all(18);
$id=$_SESSION['id'];
?>
<script type='text/javascript' src='js/competence.js'></script>
<?php
writehead();
destroy_my_session_if_forbidden($id);

if(isset($_GET["operation"]) and $_GET["operation"] == "Ajouter")
	$operation = "insert";
elseif(isset($_GET["operation"]) and $_GET["operation"] == "Sauvegarder")
	$operation = "update";
elseif(isset($_GET["operation"]) and $_GET["operation"] == "Supprimer")
	$operation = "delete";
elseif(isset($_GET["operation"])){
	$operation = secure_input($dbc,$_GET["operation"]);
}


$PH_CODE=secure_input($dbc,$_GET["PH_CODE"]);

if ( $operation == 'update' or $operation == 'insert') {
    $PH_NAME=secure_input($dbc,$_GET["PH_NAME"]);
    $OLD_PH_CODE=secure_input($dbc,$_GET["OLD_PH_CODE"]);
    if (isset($_GET["PH_HIDE_LOWER"])) $PH_HIDE_LOWER=intval($_GET["PH_HIDE_LOWER"]);
    else $PH_HIDE_LOWER=0;
    if (isset($_GET["PH_UPDATE_LOWER_EXPIRY"])) $PH_UPDATE_LOWER_EXPIRY=intval($_GET["PH_UPDATE_LOWER_EXPIRY"]);
    else $PH_UPDATE_LOWER_EXPIRY=0;
    if (isset($_GET["PH_UPDATE_MANDATORY"])) $PH_UPDATE_MANDATORY=intval($_GET["PH_UPDATE_MANDATORY"]);
    else $PH_UPDATE_MANDATORY=0;
}

//=====================================================================
// update la fiche
//=====================================================================

if ( $PH_CODE == "" ) {
    write_msgbox("erreur", $error_pic, "Le code hiérarchie doit être renseigné<p align=center><input type=submit class='btn btn-default' value='Retour' onclick='redirect();'> ",10,0);
    exit;
}
if ( $operation == 'update' ) {
    $query="update poste_hierarchie set
           PH_CODE=\"".$PH_CODE."\",
           PH_NAME=\"".$PH_NAME."\",
           PH_HIDE_LOWER=".$PH_HIDE_LOWER.",
           PH_UPDATE_LOWER_EXPIRY=".$PH_UPDATE_LOWER_EXPIRY.",
           PH_UPDATE_MANDATORY=".$PH_UPDATE_MANDATORY."
           where PH_CODE=\"".$OLD_PH_CODE."\"" ;
    $result=mysqli_query($dbc,$query);
   
    if ( $PH_CODE <> $OLD_PH_CODE ) {
        $query="update poste set PH_CODE=\"".$PH_CODE."\" where PH_CODE=\"".$OLD_PH_CODE."\"";
        $result=mysqli_query($dbc,$query);
    }
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
    $query="insert into poste_hierarchie(PH_CODE,PH_NAME,PH_HIDE_LOWER,PH_UPDATE_LOWER_EXPIRY,PH_UPDATE_MANDATORY)
    values (\"".$PH_CODE."\",\"".$PH_NAME."\",".$PH_HIDE_LOWER.",".$PH_UPDATE_LOWER_EXPIRY.",".$PH_UPDATE_MANDATORY.")";
    $result=mysqli_query($dbc,$query);
}

if ($operation == 'delete_confirmed' ) {
    $query="delete from poste_hierarchie where PH_CODE=\"".$PH_CODE."\"" ;
    $result=mysqli_query($dbc,$query);
    
    $query="update poste set PH_CODE=null, PH_LEVEL=null where PH_CODE=\"".$PH_CODE."\"";
    $result=mysqli_query($dbc,$query);
}
if ($operation == 'delete' ) {
   echo "<body onload=suppress_hierarchie('".$PH_CODE."')>";
}
else {
  echo "<body onload=\"redirect();\">";
}
writefoot();
?>
