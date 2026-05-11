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
include_once ("fonctions_dps.php");
check_all(15);
$nomenu=1;
writehead();

$evenement=$_POST['evenement'];
if(isset($_POST['P1'])){
    $P1 = (isset($_POST['P1'])?$_POST['P1']:0);
    $P2 = (isset($_POST['P2'])?$_POST['P2']:0.25);
    $E1 = (isset($_POST['E1'])?$_POST['E1']:0.25);
    $E2 = (isset($_POST['E2'])?$_POST['E2']:0.25);
    $nbisacteurs=(isset($_POST['dimNbISActeurs'])?$_POST['dimNbISActeurs']:0);
    $nbisacteurscom=(isset($_POST['dimNbISActeursCom'])?$_POST['dimNbISActeursCom']:"");
    EvenementSave($_POST);

    if( check_rights($_SESSION['id'], 15) ){
        echo "
        <p>Vous pouvez imprimer la grille:<br><ul>
        <li><a href=\"pdf.php?pdf=DPS&id=$evenement\" title=\"imprimer\" target=\"_blank\">grille complète</a> </li> 
        <li><a href=\"pdf.php?page=1&pdf=DPS&id=$evenement\" title=\"imprimer\" target=\"_blank\">page 1 seulement</a> <span class=small2>à joindre à la convention</span></li>
        </ul></p>
        ";
    }
}
else{
    echo "<p>Aucune donnée envoyée...</p>";
}

writefoot();
?>
