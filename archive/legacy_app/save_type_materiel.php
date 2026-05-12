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
$section=$_SESSION['SES_SECTION'];
?>

<html>
<SCRIPT language=JavaScript>

function redirect(url) {
     self.location.href=url;
}

function suppress(id) {
	if ( confirm("Voulez vous vraiment supprimer ce type de materiel?\n tous les articles de ce type seront supprimes")) {
		url="del_type_materiel.php?TM_ID="+id;
		self.location.href=url;
	}
	else{
       	url="upd_type_materiel.php?id="+id;
		self.location.href=url;
	}
}
</SCRIPT>

<?php

include_once ("config.php");

$TM_ID=intval($_GET["TM_ID"]);
$TM_LOT=intval($_GET["TM_LOT"]);
$TM_USAGE=secure_input($dbc,$_GET["TM_USAGE"]);
$TM_CODE=secure_input($dbc,$_GET["TM_CODE"]);
$TM_DESCRIPTION=secure_input($dbc,$_GET["TM_DESCRIPTION"]);
if ( isset ($_GET["TT_CODE"])) $TT_CODE=secure_input($dbc,$_GET["TT_CODE"]);
else $TT_CODE="";

if($_GET["operation"] == "Ajouter")
	$operation = "insert";
elseif($_GET["operation"] == "Sauvegarder")
	$operation = "update";
elseif($_GET["operation"] == "Supprimer")
	$operation = "delete";

$TM_CODE=STR_replace("\"","",$TM_CODE);
$TM_DESCRIPTION=STR_replace("\"","",$TM_DESCRIPTION);

if (isset ($_GET["from"])) $from=$_GET["from"];
else $from=0;

//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {

    $query="update type_materiel set
	       TM_CODE=\"".$TM_CODE."\",
	       TM_USAGE=\"".$TM_USAGE."\",
	       TM_DESCRIPTION=\"".$TM_DESCRIPTION."\",
		   TT_CODE=\"".$TT_CODE."\",
		   TM_LOT=".$TM_LOT."
		   where TM_ID =".$TM_ID;

    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="insert into type_materiel 
   (TM_CODE, TM_DESCRIPTION, TM_USAGE,TT_CODE, TM_LOT)
   values
   (\"$TM_CODE\",\"$TM_DESCRIPTION\",\"$TM_USAGE\",\"$TT_CODE\",$TM_LOT)";
   $result=mysqli_query($dbc,$query);
   $_SESSION['catmateriel'] = $TM_USAGE;
}

$query="update type_materiel set TT_CODE=null where TM_CODE=\"".$TM_CODE."\" and (TT_CODE='' or TM_USAGE <> 'Habillement')";
$result=mysqli_query($dbc,$query);

if ($operation == 'delete' ) {
   echo "<body onload=suppress('".$TM_ID."')>";
}
else {
   echo "<body onload=redirect('parametrage.php?tab=3&child=3&order=TM_USAGE&usage=$TM_USAGE')>";
}
?>
