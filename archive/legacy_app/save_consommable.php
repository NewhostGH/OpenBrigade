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
writehead();
check_all(71);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
$section=$_SESSION['SES_SECTION'];
echo "<script type='text/javascript' src='js/consommable.js'></script>";

if (isset ($_POST["C_ID"])) $C_ID = intval($_POST["C_ID"]);
else $C_ID=0;
if (isset ($_POST["TC_ID"])) $TC_ID = intval($_POST["TC_ID"]);
else $TC_ID=0;
if (isset ($_POST["S_ID"])) $S_ID = intval($_POST["S_ID"]);
else $S_ID=0;
if (isset ($_POST["quantity"])) $C_NOMBRE = intval($_POST["quantity"]);
else $C_NOMBRE=0;
if (isset ($_POST["minimum"])) $C_MINIMUM = intval($_POST["minimum"]);
else $C_MINIMUM=0;
if (isset ($_POST["C_DATE_ACHAT"])) $C_DATE_ACHAT = secure_input($dbc,$_POST["C_DATE_ACHAT"]);
else $C_DATE_ACHAT='';
if (isset ($_POST["C_DATE_PEREMPTION"])) $C_DATE_PEREMPTION  = secure_input($dbc,$_POST["C_DATE_PEREMPTION"]);
else $C_DATE_PEREMPTION ='';
if (isset ($_POST["C_DESCRIPTION"])) $C_DESCRIPTION = secure_input($dbc,$_POST["C_DESCRIPTION"]);
else $C_DESCRIPTION='';
$C_DESCRIPTION=str_replace("\"","",$C_DESCRIPTION);
if (isset ($_POST["C_LIEU_STOCKAGE"])) $C_LIEU_STOCKAGE = secure_input($dbc,$_POST["C_LIEU_STOCKAGE"]);
else $C_LIEU_STOCKAGE='';
$C_LIEU_STOCKAGE=str_replace("\"","",$C_LIEU_STOCKAGE);
if (isset ($_POST["numlot"])) $numlot = intval($_POST["numlot"]);
else $numlot=0;

if (isset ($_POST["operation"])) $operation=$_POST["operation"];
else $operation='insert';

// verifier les permissions de modification
if (! check_rights($id, 71,"$S_ID")) {
 check_all(24);
}

if ( $C_DATE_ACHAT <> '') {
    $tmp=explode ("-",$C_DATE_ACHAT); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $C_DATE_ACHAT = "\"".$year1."-".$month1."-".$day1."\"";
}
else  $C_DATE_ACHAT = 'null';

if ( $C_DATE_PEREMPTION <> '') {
    $tmp=explode ("-",$C_DATE_PEREMPTION); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
    $C_DATE_PEREMPTION = "\"".$year1."-".$month1."-".$day1."\"";
}
else  $C_DATE_PEREMPTION = 'null';

$query="select TC_PEREMPTION from type_consommable where TC_ID=".$TC_ID;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ($row[0] == '0') $C_DATE_PEREMPTION = 'null';

//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
    $query="update consommable set
           TC_ID=\"".$TC_ID."\",
           C_DESCRIPTION=\"".$C_DESCRIPTION."\",
           C_NOMBRE=\"".$C_NOMBRE."\",
           C_MINIMUM=\"".$C_MINIMUM."\",
           C_DATE_ACHAT=".$C_DATE_ACHAT.",
           C_DATE_PEREMPTION=".$C_DATE_PEREMPTION.",
           S_ID=\"".$S_ID."\",
           C_LIEU_STOCKAGE=\"".$C_LIEU_STOCKAGE."\",
           MA_PARENT=".$numlot."
           where C_ID =".$C_ID;
    $result=mysqli_query($dbc,$query);
    
    $query="update evenement_consommable set TC_ID=\"".$TC_ID."\"  where C_ID =".$C_ID;
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="insert into consommable 
   (S_ID, TC_ID, C_DESCRIPTION, C_NOMBRE, C_MINIMUM,C_DATE_ACHAT, C_DATE_PEREMPTION, C_LIEU_STOCKAGE, MA_PARENT)
   values
   (\"$S_ID\",\"$TC_ID\",\"$C_DESCRIPTION\",$C_NOMBRE, $C_MINIMUM, $C_DATE_ACHAT,$C_DATE_PEREMPTION,\"$C_LIEU_STOCKAGE\",".$numlot.")";
   $result=mysqli_query($dbc,$query);
   $_SESSION['filter'] = $S_ID;
}

if ($operation == 'delete' ) {
   echo "<body onload=suppress('".$C_ID."')>";
}
else if ($numlot > 0 ) {
   echo "<body onload=redirect('upd_materiel.php?tab=3&mid=".$numlot."')>";
}
else {
   echo "<body onload=redirect('consommable.php')>";
}
?>
