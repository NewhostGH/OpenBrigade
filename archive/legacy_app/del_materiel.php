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
check_all(70);

writehead();
forceReloadJS('js/upd_materiel.js');
echo "</head>";

$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];
$mysectionparent=get_section_parent($mysection);
$MA_ID=intval($_GET["MA_ID"]);
$from=$_GET["from"];

// verifier les permissions de suppression: on a que le droit sur la section pere et ses descendants
$query="select AFFECTED_TO, DATE_FORMAT(MA_ADDED,'%d-%m-%Y') as MA_ADDED, TIMEDIFF (NOW(),MA_ADDED) / 3600 as MA_HOURS,S_ID
        from materiel where MA_ID=".$MA_ID;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$S_ID=$row["S_ID"];
$AFFECTED_TO=$row["AFFECTED_TO"];
$MA_HOURS=$row["MA_HOURS"];
$MA_ADDED=$row["MA_ADDED"];

if ( $MA_ADDED == '' or $MA_HOURS > 24 )
    check_all(19);

if (! is_children($S_ID,$mysectionparent)) check_all(24);

//=====================================================================
// suppression fiche
//=====================================================================
if ( $MA_ID > 0 ) {
    $query="delete from materiel where MA_ID=".$MA_ID ;
    $result=mysqli_query($dbc,$query);

    $query="update materiel set MA_PARENT = null where MA_ID=".$MA_ID ;
    $result=mysqli_query($dbc,$query);

    $query="delete from evenement_materiel where MA_ID=".$MA_ID ;
    $result=mysqli_query($dbc,$query);

    if ( $from == 'personnel' and $AFFECTED_TO <> "") $url="upd_personnel.php?from=tenues&pompier=".$AFFECTED_TO;
    else $url="materiel.php?order=TM_USAGE";

    $query="delete from document where M_ID=".$MA_ID ;
    $result=mysqli_query($dbc,$query);

    $mypath=$filesdir."/files_materiel/".$MA_ID;
    if(is_dir($mypath)) {
        full_rmdir($mypath);
    }
}
echo "<body onload=redirect3('".$url."')>";
writefoot();
?>
