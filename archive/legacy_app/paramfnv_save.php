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
check_all(18);
get_session_parameters();
?>
<script type="text/javascript" src="js/paramfn.js"></script>
<?php

include_once ("config.php");

$TFV_ID=intval($_GET["TFV_ID"]);
if (isset($_GET["operation"])) $operation=secure_input($dbc,$_GET["operation"]);
else $operation="update";

if($_GET["operation"] == "Ajouter")
	$operation = "insert";
elseif($_GET["operation"] == "Sauvegarder")
	$operation = "update";
elseif($_GET["operation"] == "Supprimer")
	$operation = "delete";

if ( $operation <> 'delete' ) {
    $TFV_NAME=stripslashes(secure_input($dbc,str_replace("\"","",$_GET["TFV_NAME"])));
    $TFV_ORDER=intval($_GET["TFV_ORDER"]);
    $TFV_DESCRIPTION=stripslashes(secure_input($dbc,str_replace("\"","",$_GET["TFV_DESCRIPTION"])));
}
else if (isset($_GET["confirmed"])) $confirmed=true;
else $confirmed=false;
//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
   $query="update type_fonction_vehicule set
           TFV_NAME=\"".$TFV_NAME."\",
           TFV_ORDER=".$TFV_ORDER.",
           TFV_DESCRIPTION=\"".$TFV_DESCRIPTION."\"
          where TFV_ID=".$TFV_ID ;
   $result=mysqli_query($dbc,$query);
   

}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="insert into type_fonction_vehicule
   (TFV_NAME, TFV_ORDER, TFV_DESCRIPTION)
   values
   (\"$TFV_NAME\", $TFV_ORDER, \"$TFV_DESCRIPTION\")";
   $result=mysqli_query($dbc,$query);
   
}

if ($operation == 'delete' ) {
   if ( $confirmed) {
        $query="delete from type_fonction_vehicule where TFV_ID=".$TFV_ID;
        $result=mysqli_query($dbc,$query);
        $query="update evenement_vehicule set TFV_ID=null where TFV_ID=".$TFV_ID;
          $result=mysqli_query($dbc,$query);
          echo "<body onload=redirect2()>";
   }
   else
        echo "<body onload=suppress2('".$TFV_ID."')>";
}
else {
  echo "<body onload=redirect2()>";
}
?>
