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
check_all(22);
check_all(55);
check_all(2);
?>

<html>
<SCRIPT language=JavaScript>

function redirect(section) {
     url="upd_section.php?S_ID="+section;
     self.location.href=url;
}

</SCRIPT>

<?php
$id=$_SESSION['id'];
$S_ID=intval($_GET["S_ID"]);

if (! check_rights($_SESSION['id'], 55, $S_ID)) check_all(24);
if (! check_rights($_SESSION['id'], 2, $S_ID)) check_all(24);

//=====================================================================
// section inactive
//=====================================================================

set_time_limit($mytimelimit);

$query="update section set S_INACTIVE=1 where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

//=====================================================================
// mise à jour personnel
//=====================================================================

$query="select P_ID from pompier where P_SECTION =".$S_ID." and P_OLD_MEMBER=0 and P_STATUT <> 'EXT'" ;
$result=mysqli_query($dbc,$query);
while ($row=@mysqli_fetch_array($result)) {
    $P_ID=$row["P_ID"];
    $query2="update pompier set P_OLD_MEMBER = 4, GP_ID=-1, GP_ID2=-1, P_FIN=NOW() where P_ID=".$P_ID;
    $result2=mysqli_query($dbc,$query2);
    if ($log_actions == 1 )
        insert_log('UPDSTP', $P_ID, "Radiation de toute la section");
}

echo "<body onload=redirect('".$S_ID."')>";

?>
