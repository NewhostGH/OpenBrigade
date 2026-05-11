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
$id=$_SESSION['id'];
$evenement=intval($_GET["evenement"]);
$showlist=intval($_GET["showlist"]);
$gps=intval($_GET["gps"]);
$autorefresh=intval($_GET["autorefresh"]);

$modal=true;
$nomenu=1;
writehead();
write_modal_header("<i class='fa fa-cog fa-lg'></i> Options carte");

$S_ID=get_section_organisatrice($evenement);
if (check_rights($id, 15, $S_ID)) $granted_event=true;
else if ( is_chef_evenement($id, $evenement) ) $granted_event=true;
else if ( is_operateur_pc($id, $evenement) ) $granted_event=true;
else $granted_event=false;

$query ="select count(1) as NBE from evenement_equipe ee where E_CODE=".$evenement; 
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
if ( $NBE == 0 )  $showlist=0;

$out="<div align=center><table class='noBorder'>";
if (  $NBE > 0 ) {
    $out .= "<tr><td>";
    if ( $showlist == 1 )
        $out .= "<i class='fa fa-minus-square' style='color:red;'  title='Masquer la liste des équipes' ></i></td>
        <td><a href='sitac.php?evenement=".$evenement."&showlist=0&gps=".$gps."&autorefresh=".$autorefresh."'>Masquer liste des équipes</a>";
    else
        $out .= "<i class='fa fa-plus-square' style='color:green;'  title='Afficher la liste des équipes à droite' ></i></td>
        <td><a href='sitac.php?evenement=".$evenement."&showlist=1&gps=".$gps."&autorefresh=".$autorefresh."'>Afficher liste des équipes</a>";
    $out .= "</td></tr>";
}
if ( $gps == 1 ) 
    $out .= "<tr><td><i class='fa fa-minus-square' style='color:red;'  title='Masquer le personnel géolocalisé par GPS' ></i></td>
    <td><a href='sitac.php?evenement=".$evenement."&showlist=".$showlist."&gps=0&autorefresh=".$autorefresh."'>Masquer personnel géolocalisé GPS</a></td></tr>";
else
    $out .= "<tr><td><i class='fa fa-plus-square' style='color:green;' title='Afficher le personnel géolocalisé par GPS'></i></td>
    <td><a href='sitac.php?evenement=".$evenement."&showlist=".$showlist."&gps=1&autorefresh=".$autorefresh."'>Afficher personnel géolocalisé GPS</a></td></tr>";

$out .= "<tr><td><i class='fa fa-redo fa-lg' title='Rafraîchissement automatique.'></i></td>
     <td><select name='autorefresh_select' onchange=\"redirect_to('sitac.php?evenement=".$evenement."&showlist=".$showlist."&gps=".$gps."',this.value);\" title='Paramètres du Rafraîchissement automatique.'>";
if ( $autorefresh == 0 ) $selected='selected'; else $selected='';
$out .= "\n<option value='0' $selected>Pas de rafraîchissement automatique</option>";
if ( $autorefresh == 10 ) $selected='selected'; else $selected='';
$out .= "\n<option value='10' $selected>Toutes les 10 secondes</option>";
if ( $autorefresh == 20 ) $selected='selected'; else $selected='';
$out .= "\n<option value='20' $selected>Toutes les 20 secondes</option>";
if ( $autorefresh == 30 ) $selected='selected'; else $selected='';
$out .= "\n<option value='30' $selected>Toutes les 30 secondes</option>";
if ( $autorefresh == 45 ) $selected='selected'; else $selected='';
$out .= "\n<option value='45' $selected>Toutes les 45 secondes</option>";
if ( $autorefresh == 60 ) $selected='selected'; else $selected='';
$out .= "\n<option value='60' $selected>Toutes les 60 secondes</option>";
if ( $autorefresh == 120 ) $selected='selected'; else $selected='';
$out .= "\n<option value='120' $selected>Toutes les 2 minutes</option>";
if ( $autorefresh == 180 ) $selected='selected'; else $selected='';
$out .= "\n<option value='180' $selected>Toutes les 3 minutes</option>";
if ( $autorefresh == 300 ) $selected='selected'; else $selected='';
$out .= "\n<option value='300' $selected>Toutes les 5 minutes</option>";
if ( $autorefresh == 600 ) $selected='selected'; else $selected='';
$out .= "\n<option value='600' $selected>Toutes les 10 minutes</option>";
$out .= "\n</select></td></tr>";

if ( $granted_event  and $autorefresh == 0 )
    $out .= "<tr><td><img src='images/red.png' border=0></a></td>
        <td><a href='sitac.php?evenement=".$evenement."&showlist=".$showlist."&gps=".$gps."&addmarker=1' title='ajouter un point de tracé' >
        Ajouter un point sur le tracé rouge</a></td></tr>
        <tr><td><img src='images/blue.png' border=0></a></td>
        <td><a href='sitac.php?evenement=".$evenement."&showlist=".$showlist."&gps=".$gps."&addmarker=2' title='ajouter un point de tracé' >
        Ajouter un point sur le tracé bleu</a></td></tr>
        <tr><td><img src='images/green.png' border=0></a></td>
        <td><a href='sitac.php?evenement=".$evenement."&showlist=".$showlist."&gps=".$gps."&addmarker=3' title='ajouter un point de tracé' >
        Ajouter un point sur le tracé vert</a></td></tr>
        <tr><td><img src='images/yellow.png' border=0></a></td>
        <td><a href='sitac.php?evenement=".$evenement."&showlist=".$showlist."&gps=".$gps."&addmarker=4' title='ajouter un point de tracé' >
        Ajouter un point sur le tracé jaune</a></td></tr>
        <tr><td><img src='images/marker.png' class='img-max-20' border=0></a></td>
        <td><a href='sitac.php?evenement=".$evenement."&showlist=".$showlist."&gps=".$gps."&addflag=1' title='ajouter un point particulier' >
        Ajouter un point particulier</a></td></tr>";
$out .= "</table></div><p>"; 

print $out;
writefoot();
?>