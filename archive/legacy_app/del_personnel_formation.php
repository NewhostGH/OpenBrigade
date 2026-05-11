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
check_all(4);

$P_ID=intval($_GET["P_ID"]);
$PS_ID=intval($_GET["PS_ID"]);
$PF_ID=intval($_GET["PF_ID"]);
if (isset($_GET["from"])) $from=$_GET["from"];
else $from='qualif';

$mysection=$_SESSION['SES_SECTION'];
if (! check_rights($_SESSION['id'], 4, get_section_of("$P_ID"))) check_all(24);

?>
<html>
<SCRIPT language=JavaScript>
function redirect1(P_ID,PS_ID) {
     url="personnel_formation.php?P_ID="+P_ID+"&PS_ID="+PS_ID;
     self.location.href=url;
}
function redirect2(P_ID) {
     url="upd_personnel.php?pompier="+P_ID+"&tab=2&child=2&from=formations";
     self.location.href=url;
}
</SCRIPT>

<?php

$query="delete from personnel_formation where P_ID=$P_ID and PS_ID=$PS_ID and PF_ID=$PF_ID";
$result=mysqli_query($dbc,$query);

if ( $from='formations' ) 
	echo "<body onload=redirect2('".$P_ID."');>";
else
	echo "<body onload=redirect('".$P_ID."','".$PS_ID."');>";
?>
