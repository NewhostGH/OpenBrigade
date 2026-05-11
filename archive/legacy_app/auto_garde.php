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
include_once ("fonctions_gardes.php");
include_once ("fonctions_gardes_auto.php");
check_all(6);
$id=$_SESSION['id'];
if (isset($_GET["equipe"])) $equipe=intval($_GET["equipe"]);
$nomenu=1;
get_session_parameters();
writehead();

//---------------------------------------------------------------------
// MAIN
//---------------------------------------------------------------------
//$QUALIFICATIONS=prepare_qualif(0);
//my_print_r("Qualifications du personnel",$QUALIFICATIONS);

remplir_tableau_avec_disponibles ($filter,$month,$year,$equipe);

writefoot();


?>
