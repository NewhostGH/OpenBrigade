<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade
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
check_all(53);
$id=$_SESSION['id'];

if (isset($_GET["person"])) $person=intval($_GET["person"]);
else if (isset($_POST["person"])) $person=intval($_POST["person"]);
else $person=0;

$query="select P_NOM, P_PRENOM, OBSERVATION, P_SECTION from pompier where P_ID=".$person;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
if ( ! check_rights($id,53, "$P_SECTION")) check_all(24);

// -------------------------------------------
// Update
// -------------------------------------------
if (isset($_POST["person"])) {
    if (isset ($_POST["observation"])) $observation=STR_replace("\"","",$_POST["observation"]);
    else $observation="";
    $observation=secure_input($dbc,$observation);
    
    $query="update pompier set OBSERVATION=\"".$observation."\" where P_ID=".$person;
    $result=mysqli_query($dbc,$query);

    echo "<body onload=\"javascript:self.location.href='upd_personnel.php?pompier=".$person."&tab=8'\";>";
    exit;
}

// -------------------------------------------
// Display
// -------------------------------------------
$modal=true;
$nomenu=1;
writehead();

$NAME=ucfirst($P_PRENOM)." ".strtoupper($P_NOM);
write_modal_header($NAME);

$html=  "</head><body><div align=center><form name='notifications' action='observations_modal.php' method='POST'>
<input type='hidden' name='person' value='".$person."'>";

$html.= "<p><small>Observations concernant les cotisations de ".$NAME."</small>
        <textarea name='observation' cols='45' rows='8' maxlength='200'
            style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
            value=\"$OBSERVATION\" >".$OBSERVATION."</textarea>";

$html.= "<br><input type='submit' class='btn btn-success' value='Sauvegarder'></form>";
$html.= "</div></body></html>";

print $html;
writefoot($loadjs=false);
?>
