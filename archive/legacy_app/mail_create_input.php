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
check_all(43);

// la veille opérationnelle doit pouvoir alerter tout le personnel sous sa responsabilité
$s=get_highest_section_where_granted($_SESSION['id'], 43);
if ( $s <> '' ) $mysection=$s;
 
$query="select concat(upper(p.p_nom),' ',upper(substr(p.p_prenom,1,1)) , substr(p.p_prenom,2,100)) name, p.P_ID id
   	from pompier p, section s
	where p.P_SECTION=s.S_ID
	and p.P_OLD_MEMBER = 0
	and p.P_STATUT <> 'EXT'
	and ( p.P_EMAIL <> \"\"  or p.P_PHONE <> \"\" )";	
if (! check_rights($_SESSION['id'], 24) ) {
    $query .= " and p.P_SECTION in (".get_family("$mysection").")";		
}
if ( isset($_GET["q"]))  {
	$q=secure_input($dbc,$_GET["q"]);
	$query .= " and p_nom like '".$q."%'";
}
$query .= " order by p.P_NOM";



//mysqli_query($dbc,'SET CHARACTER SET utf8');
mysqli_set_charset($dbc,'utf8');
$result=mysqli_query($dbc,$query);

# Collect the results
$data = array();
while($row = mysqli_fetch_array($result)) {
	$data[] = $row;
}

# JSON-encode the response
$json_response = json_encode($data);

# Optionally: Wrap the response in a callback function for JSONP cross-domain support
if ( isset($_GET["callback"]))  {
	if($_GET["callback"])
		$json_response = $_GET["callback"] . "(" . $json_response . ")";
}

# Return the response
echo $json_response;

?>
