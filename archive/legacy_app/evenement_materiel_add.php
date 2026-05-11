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

// from evenement, materiel
if ( isset ($_GET["from"])) $from=$_GET["from"];
else $from=$_POST["from"];
if ( isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=intval($_POST["evenement"]);
if ( isset ($_GET["action"])) $action=$_GET["action"];
else $action=$_POST["action"];
if ( isset ($_GET["MA_ID"])) $materiel=intval($_GET["MA_ID"]);
else $materiel=intval($_POST["MA_ID"]);
if ( isset ($_POST["nb"])) $nb=intval($_POST["nb"]);
else $nb='0';

// used for evenement_materiel link
if ( isset ($_GET["order"])) $order=$_GET["order"];
else $order='date';
if ( isset ($_GET["date"])) $date=$_GET["date"];
else $date='FUTURE';
if ( isset ($_GET["filtermateriel"])) $filtermateriel=$_GET["filtermateriel"];
else $filtermateriel='ALL';

if ( isset($_GET['EC'])) $EC=intval($_GET['EC']);
else if ( isset($_POST['EC'])) $EC=intval($_POST['EC']);
else $EC=$evenement;


if ( ! is_chef_evenement($id, $evenement) ) {
    if ( ! check_rights($id, 15)) check_all(70);
    $query="select E_OPEN_TO_EXT, S_ID from evenement where E_CODE=".$evenement;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $S_ID=$row["S_ID"];
    $E_OPEN_TO_EXT=$row["E_OPEN_TO_EXT"];
    if (($E_OPEN_TO_EXT == 0 ) and (! check_rights($id, 70, "$S_ID")) and (! check_rights($id, 15, "$S_ID"))) check_all(24);
}


?>

<SCRIPT>
function redirect(url) {
    self.location.href = url;
}
</SCRIPT>
<?php

if ( $action == 'nb') {
    $query="select MA_NB from materiel where MA_ID=".$materiel;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $max=$row["MA_NB"];
 
    if ( $nb > $max ) $nb=$max;
    $query="update evenement_materiel set EM_NB=$nb 
    where (E_CODE =".$evenement."  or E_CODE=".$EC.")  
    and MA_ID=".$materiel;
}

if ( $action == 'remove') {
    $query="delete from evenement_materiel
    where (E_CODE =".$evenement."  or E_CODE=".$EC.") 
    and MA_ID in ( select MA_ID from materiel where MA_ID=".$materiel." or MA_PARENT=".$materiel.")";
}
else if ( $action == 'demande') {
    $query="insert into evenement_materiel (E_CODE, MA_ID)
           select ".$evenement.", MA_ID 
           from materiel 
           where (MA_ID=".$materiel." or MA_PARENT=".$materiel.")";
}
$result=mysqli_query($dbc,$query);
if ( $from == 'evenement' ) 
    echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."&from=materiel&tab=3&child=2');>";
else
    echo "<body onload=redirect('evenement_materiel.php?materiel=".$filtermateriel."&date=".$date."&order=".$order."');>";
?>
