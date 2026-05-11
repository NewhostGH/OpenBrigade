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
check_all(40);
$q=$_GET["q"];//lookup all links from the xml file if length of q>0

if (strlen($q) > 0)
{
// permission de voir les externes?
if ( check_rights($_SESSION['id'], 37)) $externe=true;
else  $externe=false;	 
$badletters  = array("é","è","ê","ë","à","ç","ï","ü");
$goodletters = array("e","e","e","e","a","c","i","u"); 

$query="select distinct P_ID, P_NOM , P_PRENOM, P_OLD_MEMBER, S_CODE, P_STATUT
        from pompier, section
	 	where P_NOM like '".$_GET["q"]."%'
	 	and P_SECTION=S_ID";
if ( !$externe ) $query .= " and P_STATUT <> 'EXT' ";	 	
$query .=" order by P_NOM, P_PRENOM asc";
$hint="";
$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result);
if ( $number > 0 ) {
 	$hint="<div align=left>";
 	while ($row=@mysqli_fetch_array($result)) {
		$P_PRENOM=str_replace($badletters, $goodletters, $row["P_PRENOM"]);
		$P_NOM=str_replace($badletters, $goodletters, $row["P_NOM"]);
		$P_ID=$row["P_ID"];
		$P_OLD_MEMBER=$row["P_OLD_MEMBER"];
		if ( $P_OLD_MEMBER > 0 ) $font="<font color=#1E1E1E>";
		else $font="<font color=$mydarkcolor>"; 
		$S_CODE=str_replace($badletters, $goodletters, $row["S_CODE"]);
		$hint = $hint ."<a href=upd_personnel.php?id=".$P_ID." title='".$S_CODE."'>".$font.strtoupper($P_NOM)." ".ucfirst($P_PRENOM)."</font></a><br>";
	}
	$response=$hint;
}
else $response="Pas de suggestion";

echo $response;
}
?>
