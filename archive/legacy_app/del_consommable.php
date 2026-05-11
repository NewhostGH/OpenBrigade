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
check_all(71);
echo "<script type='text/javascript' src='js/consommable.js'></script>";
$id=$_SESSION['id'];
$C_ID=intval($_GET["C_ID"]);

$query="select S_ID from consommable where C_ID=".$C_ID;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$S_ID=$row["S_ID"];

if (! check_rights($id, 71,"$S_ID"))
	check_all(24);

//=====================================================================
// suppression fiche
//=====================================================================

$query="delete from consommable where C_ID=".$C_ID ;
$result=mysqli_query($dbc,$query);

$query="update evenement_consommable set C_ID=0 where C_ID=".$C_ID ;
$result=mysqli_query($dbc,$query);

echo "<body onload=redirect('consommable.php')>";

?>
