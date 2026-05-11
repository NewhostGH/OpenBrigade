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

// from evenement, consommable
if ( isset ($_GET["from"])) $from=$_GET["from"];
else if (isset($_POST["from"])) $from=$_POST["from"];
else $from='evenement';
if ( isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=intval($_POST["evenement"]);
if ( isset ($_GET["action"])) $action=$_GET["action"];
else if ( isset($_POST['action'])) $action=$_POST['action'];
else $action='add';
if ( isset ($_GET["C_ID"])) $consommable=intval($_GET["C_ID"]);
else if (isset($_POST["C_ID"])) $consommable=$_POST["C_ID"];
else $consommable=0;
if ( isset ($_GET["TC_ID"])) $type_consommable=intval($_GET["TC_ID"]);
else $type_consommable=0;
if ( isset ($_GET["EC_ID"])) $EC_ID=intval($_GET["EC_ID"]);
else if (isset($_POST["EC_ID"])) $EC_ID=$_POST["EC_ID"];
else $EC_ID=0;
if ( isset ($_POST["nb"])) $nb=intval($_POST["nb"]);
else $nb='0';
if ( $nb < 0 ) $nb=0;

if ( isset($_GET['EC'])) $EC=intval($_GET['EC']);
else if ( isset($_POST['EC'])) $EC=intval($_POST['EC']);
else $EC=$evenement;

if ( ! is_chef_evenement($id, $evenement) ) {
    check_all(71);
    $query="select E_OPEN_TO_EXT, S_ID from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $S_ID=$row["S_ID"];
    $E_OPEN_TO_EXT=$row["E_OPEN_TO_EXT"];
    if (($E_OPEN_TO_EXT == 0 ) and (! check_rights($id, 71, "$S_ID")) and (! check_rights($id, 15, "$S_ID"))) check_all(24);
}

?>

<SCRIPT>
function redirect(url) {
	 self.location.href = url;
}
</SCRIPT>
<?php

if ( $action == 'nb') {
	if ( $consommable > 0 ) {
		// décrémenter stock ou remettre en stock
		
		$query="select C_NOMBRE from consommable where C_ID=".$consommable;
		$result=mysqli_query($dbc,$query);
		$row=mysqli_fetch_array($result);
		$STOCK=intval($row["C_NOMBRE"]);
		
		$query="select EC_NOMBRE from evenement_consommable where EC_ID=".$EC_ID;
		$result=mysqli_query($dbc,$query);
		$row=mysqli_fetch_array($result);
		$CONSO1=intval($row["EC_NOMBRE"]);
		$CONSO2 =  $nb - $CONSO1;
		
		if ( $CONSO2 > $STOCK ) $CONSO2 = $STOCK;
		$nb = $CONSO1 + $CONSO2;
		
		$query="update consommable set C_NOMBRE = C_NOMBRE -".$CONSO2." where C_ID = ".$consommable;
		mysqli_query($dbc,$query);
		$query="update consommable set C_NOMBRE = 0 where C_ID = ".$consommable." and C_NOMBRE < 0 ";
		mysqli_query($dbc,$query);
	}

	$query="update evenement_consommable set EC_NOMBRE =  ".$nb." where EC_ID=".$EC_ID;
}
else if ( $action == 'remove') {
	if ( $consommable > 0 ) {
		// remettre dans le stock
		$query="select EC_NOMBRE from evenement_consommable where EC_ID=".$EC_ID;
		$result=mysqli_query($dbc,$query);
		$row=mysqli_fetch_array($result);
		$CONSO1=intval($row["EC_NOMBRE"]);
		$query="update consommable set C_NOMBRE = C_NOMBRE + ".$CONSO1." where C_ID = ".$consommable;
		mysqli_query($dbc,$query);
	}

   $query="delete from evenement_consommable where EC_ID=".$EC_ID;
}
else if ( $action == 'add') {
	if ( $consommable > 0 ) {
		// décrémenter stock de 1
		$query="update consommable set C_NOMBRE = C_NOMBRE -1 where C_ID = ".$consommable." and C_NOMBRE > 0 ";
		mysqli_query($dbc,$query);
		
		$query="insert into evenement_consommable (E_CODE, TC_ID, C_ID, EC_DATE_CONSO, EC_NOMBRE)
       	select ".$evenement.", TC_ID, C_ID , NOW(), 1
       	from consommable 
       	where C_ID=".$consommable;
	}
	else 
	    $query="insert into evenement_consommable (E_CODE, TC_ID, C_ID, EC_DATE_CONSO, EC_NOMBRE)
       	select ".$evenement.", ".$type_consommable.", 0 , NOW(), 1
       	from type_consommable 
       	where TC_ID=".$type_consommable;	
}

$result=mysqli_query($dbc,$query);

echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."&from=consommables&tab=3&child=3');>";

?>
