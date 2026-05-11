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

?>
<head>
<script type='text/javascript' src='js/equipe.js'></script>
</head>
<?php

$EQ_ID=intval($_GET["EQ_ID"]);

//=====================================================================
// suppression fiche
//=====================================================================

$query="update evenement set E_EQUIPE=0 where E_EQUIPE=".$EQ_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from poste where EQ_ID=".$EQ_ID;
$result=mysqli_query($dbc,$query);

$query="delete from qualification where PS_ID not in (select PS_ID from poste)";
$result=mysqli_query($dbc,$query);

$query="delete from equipe where EQ_ID=".$EQ_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from categorie_evenement_affichage where EQ_ID=".$EQ_ID ;
$result=mysqli_query($dbc,$query);

echo "<body onload=redirect('COMPETENCE')>";

?>
