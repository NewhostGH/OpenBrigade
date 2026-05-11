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
destroy_my_session_if_forbidden($id);

?>
<script type='text/javascript' src='js/equipe.js'></script>
</head>
<?php

$EQ_ID=intval($_GET["EQ_ID"]);
if($_GET["operation"] == "Ajouter")
	$operation = "insert";
elseif($_GET["operation"] == "Sauvegarder")
	$operation = "update";
elseif($_GET["operation"] == "Supprimer")
	$operation = "delete";

// default values
$EQ_ORDER=0;
$EQ_NOM="";

// get params
if (isset($_GET["EQ_NOM"])) $EQ_NOM=secure_input($dbc,$_GET["EQ_NOM"]);
if (isset($_GET["EQ_ORDER"])) $EQ_ORDER=intval($_GET["EQ_ORDER"]);
if (isset($_GET["groupe"])) $groupe=intval($_GET["groupe"]);
else $groupe = 0;

if ( $pompiers == 1 and $nbsections == 0) {
    check_all(5);
    if ( ! check_rights($id,5, $groupe)) check_all(24);
}
else {
    check_all(18);
    if ( ! check_rights($id,18, $groupe)) check_all(24);
}

//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
   $query="update equipe set
           EQ_ORDER=".$EQ_ORDER.",
           EQ_NOM=\"".$EQ_NOM."\"
          where EQ_ID=".$EQ_ID ;
   $result=mysqli_query($dbc,$query);
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
    $query2="select max(EQ_ID) from equipe";
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $EQ_ID=intval($row2[0]) + 1;
    
    $query="insert into equipe (EQ_ID, EQ_NOM, EQ_ORDER) values (".$EQ_ID.", \"".$EQ_NOM."\", ".$EQ_ORDER.")";
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// update categorie_evenement_affichage
//=====================================================================
$query2="select distinct CEV_CODE from categorie_evenement";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
    $CEV_CODE=$row["CEV_CODE"];
    if (isset($_GET[$CEV_CODE])) $CEV_CODE_VALUE=intval($_GET[$CEV_CODE]);
    else $CEV_CODE_VALUE=0;
    $query3="delete from categorie_evenement_affichage 
        where CEV_CODE='".$CEV_CODE."'
        and EQ_ID=".$EQ_ID ;
    $result3=mysqli_query($dbc,$query3);
    if ( $operation <> 'delete' ) {
        $query3="insert into categorie_evenement_affichage (CEV_CODE, EQ_ID, FLAG1)
             values ('".$CEV_CODE."', ".$EQ_ID.", ".$CEV_CODE_VALUE.")";
        $result3=mysqli_query($dbc,$query3);
    } 
}

if ($operation == 'delete' ) {
   echo "<body onload=suppress('".$EQ_ID."','COMPETENCE')>";
}
else {
  echo "<body onload=\"redirect('COMPETENCE');\">";
}
?>
