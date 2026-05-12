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
check_all(17);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
$section=$_SESSION['SES_SECTION'];
echo "<script type='text/javascript' src='js/element_facturable.js'></script>";

if (isset ($_GET["EF_ID"])) $EF_ID = intval($_GET["EF_ID"]);
else $EF_ID=0;
if (isset ($_GET["S_ID"])) $S_ID = intval($_GET["S_ID"]);
else $S_ID=0;
if (isset ($_GET["EF_NAME"])) $EF_NAME = secure_input($dbc,str_replace("\"","",$_GET["EF_NAME"]));
else $EF_NAME="";
if (isset ($_GET["EF_PRICE"])) $EF_PRICE = (float) $_GET["EF_PRICE"];
else $EF_PRICE=0;
if (isset ($_GET["TEF_CODE"])) $TEF_CODE = $_GET["TEF_CODE"];
else $TEF_CODE="";

if($_GET["operation"] == "Ajouter")
	$operation = "insert";
elseif($_GET["operation"] == "Sauvegarder")
	$operation = "update";
elseif($_GET["operation"] == "Supprimer")
	$operation = "delete";
// verifier les permissions de modification
if (! check_rights($id, 29,"$S_ID")) {
 check_all(24);
}

if (isset ($_GET["from"])) $from=$_GET["from"];
else $from=0;

//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
    $query="update element_facturable set
	       EF_NAME=\"".$EF_NAME."\",
	       EF_PRICE=\"".$EF_PRICE."\",
		   TEF_CODE=\"".$TEF_CODE."\",
		   S_ID=\"".$S_ID."\"
		   where EF_ID =".$EF_ID;
    $result=mysqli_query($dbc,$query);

}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="insert into element_facturable  (S_ID, EF_NAME, EF_PRICE, TEF_CODE)
   values (".$S_ID.",\"".$EF_NAME."\",".$EF_PRICE.", \"".$TEF_CODE."\")";
   $result=mysqli_query($dbc,$query);
   $_SESSION['filter'] = $S_ID;
}

if ($operation == 'delete' ) {
   echo "<body onload=suppress('".$EF_ID."'); >";
   
}
else {
   echo "<body onload=redirect('parametrage.php?tab=5&child=12')>";
}
?>
