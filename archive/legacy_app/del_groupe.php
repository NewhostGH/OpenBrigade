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
check_all(9);

?>

<html>
<SCRIPT language=JavaScript>

function redirect() {
    url="habilitations.php";
    self.location.href=url;
}

</SCRIPT>

<?php
$id=$_SESSION['id'];
$GP_ID=intval($_GET["GP_ID"]);

//=====================================================================
// suppression groupe
//=====================================================================

$query="delete from groupe where GP_ID=".$GP_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from habilitation where GP_ID=".$GP_ID;
$result=mysqli_query($dbc,$query);

$query="update pompier set GP_ID=0 where GP_ID=".$GP_ID ;
$result=mysqli_query($dbc,$query);

$query="update pompier set GP_ID2=0 where GP_ID2=".$GP_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from section_role where GP_ID=".$GP_ID;
$result=mysqli_query($dbc,$query);

echo "<body onload=redirect()>";

?>
