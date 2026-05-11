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
check_all(29);
$section=$_SESSION['SES_SECTION'];
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
?>

<html>
<SCRIPT language=JavaScript>

function redirect(from, even) {
    let url ='';
    if(from == 'evenement_display.php' && even != '')
        url = 'evenement_display.php?tab=1&evenement='+even;
    else
        url = 'company.php';
    self.location.href=url;
}

function suppress(id) {
    if ( id == 0 ) {
        swalAlert("Il ne faut pas supprimer Particulier");
        url="upd_company.php?C_ID=0";
        redirect(url);
    }
    else if ( confirm("Voulez vous vraiment supprimer cette entreprise?\n")) {
        url="del_company.php?C_ID="+id;
        self.location.href=url;
    }
    else {
        url="upd_company.php?C_ID="+id;
        redirect(url);
    }
}
</SCRIPT>

<?php

include_once ("config.php");
$operation=$_GET["operation"];

if ($operation == 'delete' ) {
   $C_ID=intval($_GET["C_ID"]);
   echo "<body onload=suppress('".$C_ID."')>";
}
else {

$groupe=intval($_GET["groupe"]);
$TC_CODE=secure_input($dbc,$_GET["TC_CODE"]);
$C_NAME=$_GET["C_NAME"];
$parent=intval($_GET["parent"]);
if ( $parent == 0 ) $parent='null';
$C_DESCRIPTION=secure_input($dbc,$_GET["C_DESCRIPTION"]);
$C_SIRET=secure_input($dbc,$_GET["C_SIRET"]);
$C_ADDRESS=secure_input($dbc,$_GET["address"]);
$C_ZIP_CODE=secure_input($dbc,$_GET["zipcode"]);
$C_CITY=secure_input($dbc,$_GET["city"]);
$C_EMAIL=secure_input($dbc,$_GET["email"]);
$C_PHONE=secure_input($dbc,$_GET["phone"]);
$C_FAX=secure_input($dbc,$_GET["fax"]);
$C_CONTACT_NAME=secure_input($dbc,$_GET["relation_nom"]);

$C_NAME=STR_replace("\"","",$C_NAME);
$C_DESCRIPTION=STR_replace("\"","",$C_DESCRIPTION);
$C_ADDRESS=STR_replace("\"","",$C_ADDRESS);
$C_ZIP_CODE=STR_replace("\"","",$C_ZIP_CODE);
$C_CITY=STR_replace("\"","",$C_CITY);
$C_CONTACT_NAME=STR_replace("\"","",$C_CONTACT_NAME);


if ( $C_NAME == "") {
    write_msgbox("erreur", $error_pic, "Le nom de l'entreprise doit être renseigné.<br>
    <p align=center><input type=submit class='btn btn-default' value='Retour' onclick=\"javascript:history.back(1);\"> ",10,0);
    exit;
}
//=====================================================================
// update la fiche
//=====================================================================

if ( $operation == 'update' ) {
    $C_ID=intval($_GET["C_ID"]);
    $query="update company set
           TC_CODE=\"".$TC_CODE."\",
           C_NAME=\"".$C_NAME."\",
           S_ID=".$groupe.",
           C_PARENT=".$parent.",
           C_ADDRESS=\"".$C_ADDRESS."\",
           C_ZIP_CODE=\"".$C_ZIP_CODE."\",
           C_CITY=\"".$C_CITY."\",
           C_EMAIL=\"".$C_EMAIL."\",
           C_PHONE=\"".$C_PHONE."\",
           C_FAX=\"".$C_FAX."\",
           C_SIRET=\"".$C_SIRET."\",
           C_CONTACT_NAME=\"".$C_CONTACT_NAME."\",
           C_DESCRIPTION=\"".$C_DESCRIPTION."\"
           where C_ID =".$C_ID;
    $result=mysqli_query($dbc,$query);
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert' ) {
   $query = "select max(C_ID) +1 'NEWID' from company";
   $result=mysqli_query($dbc,$query);
   $row=@mysqli_fetch_array($result);
   $NEWID=$row["NEWID"];
   if ( $NEWID == '' ) $NEWID=1;
 
   $query="insert into company 
   (C_ID,TC_CODE, C_NAME, S_ID, C_PARENT, C_DESCRIPTION, C_ADDRESS, C_ZIP_CODE, C_CITY, C_EMAIL, C_PHONE, C_FAX, C_CONTACT_NAME, C_CREATED_BY, C_CREATE_DATE, C_SIRET )
   values
   ($NEWID, \"$TC_CODE\",\"$C_NAME\", $groupe, $parent,\"$C_DESCRIPTION\", \"$C_ADDRESS\", \"$C_ZIP_CODE\", 
   \"$C_CITY\", \"$C_EMAIL\", \"$C_PHONE\", \"$C_FAX\", \"$C_CONTACT_NAME\",".$id.", NOW(), \"$C_SIRET\" )";
   $result=mysqli_query($dbc,$query);

}

$from = isset($_GET['from']) ? $_GET['from'] : '';
$evenement = isset($_GET['evenement']) ? $_GET['evenement'] : '';

echo "<body onload=\"redirect('$from','$evenement');\">";
}
?>
