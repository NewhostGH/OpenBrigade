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
check_all(5);
$id=$_SESSION['id'];
?>
<head>
<script type='text/javascript' src='js/equipe.js'></script>
</head>
<?php
$EQ_ID=intval($_GET["EQ_ID"]);
$S_ID = get_section_organisatrice_garde($EQ_ID);

check_all(5);
if ( ! check_rights($id,5, $S_ID)) check_all(24);

//=====================================================================
// suppression fiche
//=====================================================================

$query="update evenement set E_EQUIPE=0 where E_EQUIPE=".$EQ_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from type_garde where EQ_ID=".$EQ_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from planning_garde_status where EQ_ID=".$EQ_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from garde_competences where EQ_ID=".$EQ_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from geolocalisation where TYPE='G' and CODE=".$EQ_ID ;
$result=mysqli_query($dbc,$query);

echo "<body onload=redirect('GARDE')>";

?>
