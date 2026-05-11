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
if (! check_rights($id,2) and ! check_rights($id,26) and ! check_rights($id,43)) check_all(2);

header('Content-Disposition: attachment; filename=contacts.csv');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

if (isset ($_POST['SelectionMail'])) $pids = explode(",",($_POST['SelectionMail']));
else if (isset ($_GET['destid'])) $pids = explode(",",($_GET['destid']));
$query="select P_NOM, P_PRENOM, P_EMAIL from pompier where P_ID in (".implode(",",$pids).")";
$res = mysqli_query($dbc,$query);

echo "Prénom,Nom,Adresse de messagerie\r\n";
while ( custom_fetch_array($res)) {
   if ( $P_EMAIL <> "" ) echo ucfirst(fixcharset($P_PRENOM)).",".fixcharset(strtoupper($P_NOM)).",".$P_EMAIL."\r\n";
}
?>
