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
get_session_parameters();
?>
<script type="text/javascript" src="js/paramfn.js"></script>
<?php

include_once ("config.php");

$TP_ID=intval($_GET["TP_ID"]);
//if (isset($_GET["operation"])) $operation=secure_input($dbc,$_GET["operation"]);
//else $operation="update";
if(isset($_GET["operation"]) and $_GET["operation"] == "Ajouter")
	$operation = "insert";
elseif(isset($_GET["operation"]) and $_GET["operation"] == "Sauvegarder")
	$operation = "update";
elseif(isset($_GET["operation"]) and $_GET["operation"] == "Supprimer" or isset($_GET["confirmed"]))
	$operation = "delete";

	
if ( $operation <> 'delete' ) {
    $TP_NUM=intval($_GET["TP_NUM"]);
    $PS_ID=intval($_GET["PS_ID"]);
    $PS_ID2=intval($_GET["PS_ID2"]);
    if ( isset($_GET["INSTRUCTOR"])) $INSTRUCTOR=intval($_GET["INSTRUCTOR"]);
    else $INSTRUCTOR=0;
    $EQ_ID=intval($_GET["EQ_ID"]);
    if ( $gardes == 0 ) $EQ_ID=0; 
    $TE_CODE=secure_input($dbc,$_GET["TE_CODE"]);
    $TP_LIBELLE=stripslashes(secure_input($dbc,str_replace("\"","",$_GET["TP_LIBELLE"])));
}
else if (isset($_GET["confirmed"])) $confirmed=true;
else $confirmed=false;
//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
    $query="update type_participation set
           TP_NUM=".$TP_NUM.",
           PS_ID=".$PS_ID.",
           PS_ID2=".$PS_ID2.",
           EQ_ID=".$EQ_ID.",
           INSTRUCTOR=".$INSTRUCTOR.",
           TP_LIBELLE=\"".$TP_LIBELLE."\",
           TE_CODE=\"".$TE_CODE."\"
          where TP_ID=".$TP_ID;
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
    $query="insert into type_participation
    (TE_CODE, TP_NUM, TP_LIBELLE, PS_ID, PS_ID2, INSTRUCTOR, EQ_ID)
    values
    (\"$TE_CODE\", $TP_NUM, \"$TP_LIBELLE\", $PS_ID, $PS_ID2, $INSTRUCTOR, $EQ_ID)";
    $result=mysqli_query($dbc,$query);
}

if ($operation == 'delete' ) {
    if ( $confirmed) {
        $query="delete from type_participation where TP_ID=".$TP_ID;
        $result=mysqli_query($dbc,$query);
        $query="update evenement_participation set TP_ID=0 where TP_ID=".$TP_ID;
		$result=mysqli_query($dbc,$query);
		echo "<body onload=redirect('".$type_evenement."')>";
		//echo "done";
   }
   else
       echo "<body onload=suppress('$TP_ID','$type_evenement')>";
}
else 
    echo "<body onload=redirect('".$type_evenement."')>";

?>
