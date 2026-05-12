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

?>
<script type="text/javascript" src="js/poste.js"></script>
<?php

if (isset($_POST["PS_ID"])) $PS_ID=intval($_POST["PS_ID"]);
else $PS_ID=0;
if (isset ($_POST["PS_ORDER"])) $PS_ORDER=intval($_POST["PS_ORDER"]);

if($_POST["operation"] == "Ajouter")
	$operation = "insert";
elseif($_POST["operation"] == "Sauvegarder")
	$operation = "update";
elseif($_POST["operation"] == "Supprimer")
	$operation = "delete";

if (isset ($_POST["TYPE"])) $TYPE=secure_input($dbc,$_POST["TYPE"]);
if (isset ($_POST["DESCRIPTION"])) $DESCRIPTION=secure_input($dbc,$_POST["DESCRIPTION"]);
if (isset ($_POST["PS_EXPIRABLE"])) $PS_EXPIRABLE=intval($_POST["PS_EXPIRABLE"]);
if (isset ($_POST["PS_AUDIT"])) $PS_AUDIT=intval($_POST["PS_AUDIT"]);
if (isset ($_POST["PS_DIPLOMA"])) $PS_DIPLOMA=intval($_POST["PS_DIPLOMA"]);
else $PS_DIPLOMA=0;
if (isset ($_POST["PS_NUMERO"])) $PS_NUMERO=intval($_POST["PS_NUMERO"]);
if (isset ($_POST["PS_FORMATION"])) $PS_FORMATION=intval($_POST["PS_FORMATION"]);
if (isset ($_POST["PS_SECOURISME"])) $PS_SECOURISME=intval($_POST["PS_SECOURISME"]);
if (isset ($_POST["PS_NATIONAL"])) $PS_NATIONAL=intval($_POST["PS_NATIONAL"]);
if (isset ($_POST["PS_PRINTABLE"])) $PS_PRINTABLE=intval($_POST["PS_PRINTABLE"]);
else $PS_PRINTABLE=0;
if (isset ($_POST["PS_PRINT_IMAGE"])) $PS_PRINT_IMAGE=intval($_POST["PS_PRINT_IMAGE"]);
else $PS_PRINT_IMAGE=0;
if (isset ($_POST["PS_RECYCLE"])) $PS_RECYCLE=intval($_POST["PS_RECYCLE"]);
if (isset ($_POST["PS_USER_MODIFIABLE"])) $PS_USER_MODIFIABLE=intval($_POST["PS_USER_MODIFIABLE"]);
if (isset ($_POST["EQ_ID"])) $EQ_ID=intval($_POST["EQ_ID"]);
if (isset ($_POST["F_ID"])) $F_ID=intval($_POST["F_ID"]);
if (isset ($_POST["PH_CODE"])) $PH_CODE=secure_input($dbc,$_POST["PH_CODE"]);
else $PH_CODE = '';
if (isset ($_POST["DAYS_WARNING"])) $DAYS_WARNING=intval($_POST["DAYS_WARNING"]);
else $DAYS_WARNING = 0;
if (isset ($_POST["PH_LEVEL"])) $PH_LEVEL=intval($_POST["PH_LEVEL"]);
if ( $PH_CODE == '' ) {
    $PH_CODE="null";
    $PH_LEVEL="null";
}
else $PH_CODE = "\"".$PH_CODE."\"";
if ( $PS_DIPLOMA == 0 ) $PS_PRINTABLE = 0;
if ( $PS_DIPLOMA == 0 ) $PS_NUMERO = 0;
if ( $PS_PRINTABLE == 0 ) $PS_PRINT_IMAGE=0;
//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
   $query="update poste set
           PS_ORDER=".$PS_ORDER.",
           TYPE=\"".$TYPE."\",
           DESCRIPTION=\"".$DESCRIPTION."\",
           F_ID=".$F_ID.",
           EQ_ID=".$EQ_ID.",
           PS_FORMATION=".$PS_FORMATION.",
           PS_EXPIRABLE=".$PS_EXPIRABLE.",
           DAYS_WARNING=".$DAYS_WARNING.",
           PS_AUDIT=".$PS_AUDIT.",
           PS_DIPLOMA=".$PS_DIPLOMA.",
           PS_NUMERO=".$PS_NUMERO.",
           PS_SECOURISME=".$PS_SECOURISME.",
           PS_NATIONAL=".$PS_NATIONAL.",
           PS_PRINTABLE=".$PS_PRINTABLE.",
           PS_PRINT_IMAGE=".$PS_PRINT_IMAGE.",
           PS_RECYCLE=".$PS_RECYCLE.",
           PS_USER_MODIFIABLE=".$PS_USER_MODIFIABLE.",
           PH_CODE=".$PH_CODE.",
           PH_LEVEL=".$PH_LEVEL."
    where PS_ID=".$PS_ID ;
    $result=mysqli_query($dbc,$query);
   
    if ( $PS_EXPIRABLE == 0 ) {
        $query1="update qualification set
           Q_EXPIRATION=null
           where PS_ID=".$PS_ID ;
           $result1=mysqli_query($dbc,$query1);
    }
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query="select max(PS_ID) from poste";
   $result=mysqli_query($dbc,$query);
   $row=@mysqli_fetch_array($result);
   $NEXTPS_ID=intval($row[0]) + 1;
    
   $query="insert into poste
   (PS_ID, EQ_ID, TYPE, DESCRIPTION, PS_EXPIRABLE, DAYS_WARNING, PS_AUDIT, PS_FORMATION, 
       PS_DIPLOMA, PS_NUMERO, PS_SECOURISME, PS_NATIONAL, PS_PRINTABLE, PS_PRINT_IMAGE,  PS_RECYCLE, PS_USER_MODIFIABLE, F_ID, PH_CODE, PH_LEVEL)
   values
   ($NEXTPS_ID, $EQ_ID,\"$TYPE\",\"$DESCRIPTION\", $PS_EXPIRABLE, $DAYS_WARNING, $PS_AUDIT, $PS_FORMATION, 
   $PS_DIPLOMA, $PS_NUMERO, $PS_SECOURISME, $PS_NATIONAL, $PS_PRINTABLE, $PS_PRINT_IMAGE, $PS_RECYCLE, $PS_USER_MODIFIABLE, $F_ID, $PH_CODE, $PH_LEVEL)";
   $result=mysqli_query($dbc,$query);
}

if ($operation == 'delete' ) {
   echo "<body onload=suppress('$PS_ID')>";
}
else {
   echo "<body onload=redirect()>";
}
?>
