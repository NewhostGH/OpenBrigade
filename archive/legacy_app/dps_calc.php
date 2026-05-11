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
check_all(0);
$id=$_SESSION['id'];
$nomenu=1;
writehead();

if(isset($_POST['P1'])){
    $P1 = (isset($_POST['P1'])?$_POST['P1']:0);
    $P2 = (isset($_POST['P2'])?$_POST['P2']:0.25);
    $E1 = (isset($_POST['E1'])?$_POST['E1']:0.25);
    $E2 = (isset($_POST['E2'])?$_POST['E2']:0.25);
    $nbisacteurs=(isset($_POST['dimNbISActeurs'])?$_POST['dimNbISActeurs']:0);
    $nbisacteurscom=(isset($_POST['dimNbISActeursCom'])?$_POST['dimNbISActeursCom']:"");
    CalcRIS($P1,$P2,$E1,$E2,$nbisacteurs,$nbisacteurscom);
}

if(isset($_GET['evenement'])){
    $evenement=$_GET['evenement'];
    $organisation=get_section_organisatrice($evenement);
}
if(isset($_POST['evenement'])){
    $evenement=$_POST['evenement'];
    $organisation=get_section_organisatrice($evenement);
}
else $organisation=0;

$changeallowed=true;
$ended=get_number_days_after_block($evenement);
if ( $ended > 0 ) {
    if (! check_rights($id, 19, $organisation))
        $changeallowed=false;
}
$changeallowed=true;

if( check_rights($id, 15, "$organisation") or is_chef_evenement($id,$evenement)){
    if ( $changeallowed ) 
        echo "<input type=\"submit\"  class='btn btn-default' name=\"action\" id=\"btGrille\" value=\"Enregistrer\" >";

    $actionPrint = (isset($_POST['actionPrint'])?$_POST['actionPrint']:"");
    if($actionPrint=="Modifier"){
        echo "
        <p>Vous pouvez imprimer la grille:<br>
        - <a href=\"pdf.php?pdf=DPS&id=$evenement\" title=\"imprimer\" target=\"_blank\">grille complète</a> <br> 
        - <a href=\"pdf.php?page=1&pdf=DPS&id=$evenement\" title=\"imprimer\" target=\"_blank\">page 1 seulement</a> <span class=small2>à joindre à la convention</span>
        </p>
        ";
   }
}

writefoot();
?>
