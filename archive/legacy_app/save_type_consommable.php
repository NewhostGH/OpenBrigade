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

writehead();
echo "<script type='text/javascript' src='js/type_consommable.js'></script>";
echo "</head>";
echo "<body>";

$TC_ID=intval($_POST["TC_ID"]);

if($_POST["operation"] == "Ajouter")
	$operation = "insert";
elseif($_POST["operation"] == "Sauvegarder")
	$operation = "update";
elseif($_POST["operation"] == "Supprimer")
	$operation = "delete";

if ( $operation == 'delete' ) {
   echo "<body onload=\"suppress('".$TC_ID."');\">";
   exit;
}

$CC_CODE=secure_input($dbc,$_POST["CC_CODE"]);
$TC_DESCRIPTION=secure_input($dbc,$_POST["TC_DESCRIPTION"]);
$TCO_CODE=secure_input($dbc,$_POST["TCO_CODE"]);
$TUM_CODE=secure_input($dbc,$_POST["TUM_CODE"]);
$TC_QUANTITE_PAR_UNITE=secure_input($dbc,$_POST["TC_QUANTITE_PAR_UNITE"]);
if (isset($_POST["TC_PEREMPTION"])) $TC_PEREMPTION=intval($_POST["TC_PEREMPTION"]);
else $TC_PEREMPTION=0;
$TC_DESCRIPTION=STR_replace("\"","",$TC_DESCRIPTION);


if (isset ($_POST["from"])) $from=$_POST["from"];
else $from=0;

//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
    $query="update type_consommable set
           CC_CODE=\"".$CC_CODE."\",
           TC_DESCRIPTION=\"".$TC_DESCRIPTION."\",
           TC_CONDITIONNEMENT=\"".$TCO_CODE."\",
           TC_UNITE_MESURE=\"".$TUM_CODE."\",
           TC_QUANTITE_PAR_UNITE=\"".$TC_QUANTITE_PAR_UNITE."\",
           TC_PEREMPTION=".$TC_PEREMPTION."
           where TC_ID =".$TC_ID;
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// insertion nouveau type
//=====================================================================

if ( $operation == 'insert' ) {  
   $query="insert into type_consommable
   (CC_CODE, TC_DESCRIPTION, TC_CONDITIONNEMENT,TC_UNITE_MESURE,TC_QUANTITE_PAR_UNITE,TC_PEREMPTION)
   values
   (\"$CC_CODE\",\"$TC_DESCRIPTION\",\"$TCO_CODE\",\"$TUM_CODE\",\"$TC_QUANTITE_PAR_UNITE\",$TC_PEREMPTION)";
   $result=mysqli_query($dbc,$query);
   $_SESSION['catmateriel'] = $CC_CODE;
}

echo "<body onload=redirect('parametrage.php?tab=3&child=4&catconso=".$CC_CODE."')>";
writefoot();
?>
