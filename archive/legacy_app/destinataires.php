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
$mysection=$_SESSION['SES_SECTION'];

$nomenu=1;
writehead();
write_modal_header("Destinataires");

$poste=secure_input($dbc,$_GET["poste"]);
$section=secure_input($dbc,$_GET["section"]);
$dispo=secure_input($dbc,$_GET["dispo"]);

if ($dispo == '0')
 	$type='YN';
else {
	$type='O';
	$P=explode("-",$dispo);
	$udate=mktime(0,0,0,$P[1],$P[2],$P[0]);
	$day= date('j',$udate);
	$month= date('n',$udate);
	$year= date('Y',$udate);
}

echo "<body class='top15'><div align=center>";

if ( $type == 'O' )
	$tablerows=personnel_dispo($year, $month, $day, $type, $poste, $section);
else
	$tablerows=personnel_dispo_ou_non($poste, $section);

if ( $tablerows <> "" ) {
    echo "<div style='margin-bottom:100px'>
      <table class='newTableAll' border=0 cellspacing=0 cellpadding=0>
      <tr>";
    echo "<td width=300>liste des destinataires</td>";
    echo "<td width=50>Mail</td>";
    echo "<td width=50>Téléphone</td>";
    echo "</tr>";
    echo $tablerows;
    echo "</table></div></div>";
}
else 
    echo "Aucun destinataire.<p>";

writefoot();
?>
