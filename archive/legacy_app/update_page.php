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
check_all(6);
$id=$_SESSION['id'];
$comps=$_SESSION['comps'];
$personnel=$_SESSION['personnel'];
$list=$_SESSION['list'];

if (isset($_GET["evenement"])) $evenement = intval($_GET["evenement"]);
if (isset($_GET["vehicule"])) $vehicule= intval($_GET["vehicule"]);

$query="select t.EQ_JOUR, t.EQ_NUIT from evenement e, type_garde t where t.EQ_ID = e.E_EQUIPE and e.E_CODE=".$evenement;

$result=mysqli_query($dbc,$query);
$row = mysqli_fetch_array($result);

echo display_postes ($evenement, $vehicule, $showjour=$row["EQ_JOUR"], $shownuit=$row["EQ_NUIT"], $print_mode=false)//mise à jour d'un piquet suite à une modification

?>