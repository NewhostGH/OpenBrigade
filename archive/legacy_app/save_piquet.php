<?php
  # project: eBrigade
  # homepage: https://ebrigade.app
  # version: 5.3

  # Copyright (C) 2004, 2021 Nicolas MARCHE (eBrigade Technologies)
  # Copyright (C) 2018 Michel GAUTIER
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
header('Content-Type: text/html; charset=windows-1252');//une ligne à ajouter pour résoudre le problème d'encodage avec la BD
include_once ("config.php");
check_all(6);
$id=$_SESSION['id'];
global $dbc;
if (isset($_GET["evenement"])) $evenement = intval($_GET["evenement"]);
if (isset($_GET["periode"])) $periode = intval($_GET["periode"]) ;
if (isset($_GET["vehicule"])) $vehicule= intval($_GET["vehicule"]);
if (isset($_GET["piquet"])) $piquet = intval($_GET["piquet"]);
if (isset($_GET["pid"])) $pid=intval($_GET["pid"]);
else $pid=0;

if ( $evenement > 0 and $periode > 0 and $vehicule > 0 and $piquet > 0 ) {
    $section=get_section_organisatrice($evenement);
    if ( check_rights($id, 6, $section)) {
        if ( $pid == 0 ) {
            $query = "delete from evenement_piquets_feu where 
                    E_CODE =".$evenement." and V_ID = ".$vehicule." and ROLE_ID = ".$piquet." and EH_ID = ".$periode;
            $result = mysqli_query($dbc,$query);
        }
        else {
            $query ="update evenement_piquets_feu SET P_ID =".$pid."
                     where E_CODE =".$evenement." and V_ID = ".$vehicule." and ROLE_ID = ".$piquet." and EH_ID = ".$periode ;
            $result = mysqli_query($dbc,$query);
            if ( mysqli_affected_rows($dbc) == 0 ) {
                $query ="insert into evenement_piquets_feu (E_CODE, EH_ID, V_ID, ROLE_ID, P_ID)
                        values (".$evenement.",".$periode.",".$vehicule.",".$piquet.",".$pid.")";
                $result = mysqli_query($dbc,$query);
            }
            // supprimer la personne des autres piquets
            $query = "delete from evenement_piquets_feu where 
                        E_CODE =".$evenement." and V_ID = ".$vehicule." and ROLE_ID <> ".$piquet." and EH_ID = ".$periode." and P_ID =".$pid;
            $result = mysqli_query($dbc,$query);
        }
    }
}
exit;

?>