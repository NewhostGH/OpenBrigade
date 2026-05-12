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
$id = $_SESSION['id'];
//Initialisation de la liste
$list1 = array();

if (isset($_POST["maxRows"])) $maxRows=intval($_POST["maxRows"]);
else $maxRows=300;

if (isset($_POST["ZipCode"])) $ZipCode=secure_input($dbc,$_POST["ZipCode"]);
else if (isset($_GET["ZipCode"])) $ZipCode=secure_input($dbc,$_GET["ZipCode"]);
else $ZipCode = "0";

if (isset($_POST["City"])) $City=strtoupper(secure_input($dbc,$_POST["City"]));
else if (isset($_GET["City"])) $City=strtoupper(secure_input($dbc,$_GET["City"]));
else $City="";

$query = "SELECT CODE, CITY, DEP from zipcode ";
if (intval($ZipCode) > 0) {
    $query .= " where CODE LIKE '".intval($ZipCode)."%' ";
    if ($ZipCode[0] == 0 ) $query .= " and CODE < 10000 ";
    else $query .= " and CODE > 10000 ";
}
else {
    $query .= " where CODE < 10000";
}
if ($City <> "") $query .= " where CITY LIKE '".$City ."%'";
$query .= " LIMIT 0, ".$maxRows;

$list=array();
$result=mysqli_query($dbc,$query);

if ( mysqli_num_rows($result ) > 0 ) echo "<table class='noBorder'><tr><td colspan=3><b>Choisissez une commune dans la liste:</b></td></tr>";
else echo "<i class='fa fa-exclamation-triangle fa-lg' style='color:orange;'></i><b> Aucune commune avec ce code postal ".$ZipCode."</b><p>";

while ($row = mysqli_fetch_array($result)){
    $CODE = $row['CODE'];
    if ( $ZipCode == 0 ) $CODE = "0".$row['CODE'];
    else if ($ZipCode > 0 ) {
        if ($ZipCode[0] == 0 ) $CODE = "0".$row['CODE'];
    }
    echo "<tr><td width=50>".$CODE."</td><td width=230>
        <a onclick='javascript:select_city(\"".$row['CITY']."\",\"".$CODE."\");' title='choisir cette ville' href='#'>".$row['CITY']."</a></td>
        <td>".$row['DEP']."</td></tr>";
}
if ( mysqli_num_rows($result ) > 0 ) echo "</table>";

echo "<div align=center><input type='button' class='btn btn-secondary' value='fermer' onclick=\"javascript:document.getElementById('divzipcode').style.display = 'none';\" class='noprint'></div>";

?>