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
check_all(19);
check_all(55);
?>

<html>
<SCRIPT language=JavaScript>

function redirect() {
    url="departement.php";
    self.location.href=url;
}

</SCRIPT>

<?php
$id=$_SESSION['id'];
$S_ID=intval($_GET["S_ID"]);

$query1="select S_PARENT, NIV, S_CODE, S_DESCRIPTION from section_flat where S_ID=".$S_ID;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NIV=$row1["NIV"];
$parent=$row1["S_PARENT"];
$code=$row1["S_CODE"];
$desc=$row1["S_DESCRIPTION"];

if (! check_rights($id, 55, "$parent")) check_all(24);

//=====================================================================
// dans certains cas on ne supprime pas
//=====================================================================
$regime=get_regime($S_ID);

if ( $regime > 0 and $gardes ) {
    if ( in_array($S_ID,array(1,2,3,4,$regime))) {
    write_msgbox("erreur permission",$error_pic,"Vous ne pouvez pas supprimer cette section, elle est utilisée dans les tableaux de garde.<p align=center><input type=submit class='btn btn-default' value='Retour' onclick='javascript:history.back(1);'>",10,0);
    exit;
    }
}

//=====================================================================
// suppression fiche
//=====================================================================

set_time_limit($mytimelimit);

insert_log('DELSS',$parent, $code." - ".$desc);

$query="delete from section where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from section_flat where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from document where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from document_folder where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from section_role where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from astreinte where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from section_cotisation where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from element_facturable where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from agrement where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from element_facturable where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from diplome_param where S_ID=".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="delete from section_stop_evenement where S_ID=".$S_ID;
$result=mysqli_query($dbc,$query);


//=====================================================================
// mise à jour données
//=====================================================================

$query="update pompier set P_SECTION =".$parent." where P_SECTION =".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="update section set S_PARENT =".$parent." where S_PARENT =".$S_ID ;
$result=mysqli_query($dbc,$query);
$nb_subsections=mysqli_affected_rows($dbc);

$query="update type_garde set ASSURE_PAR1 =".$parent." where ASSURE_PAR1 =".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="update type_garde ASSURE_PAR2 =".$parent." where ASSURE_PAR2 =".$S_ID ;
$result=mysqli_query($dbc,$query);

$query="update pompier set P_FAVORITE_SECTION =".$parent." where P_FAVORITE_SECTION =".$S_ID ;
$result=mysqli_query($dbc,$query);

@set_time_limit($mytimelimit);
$level=get_level($parent);
if ( $nb_subsections > 0 ) {
    rebuild_section_flat($parent,$level,6);
}    

$tables = array ('vehicule','evenement','planning_garde_status','disponibilite','indisponibilite',
                 'audit','message','qualification','smslog','company','materiel','consommable','equipe','note_de_frais','type_garde');
for ( $n = 0; $n < sizeof($tables); $n++ ) {
    $query="update ".$tables[$n]." set S_ID=".$parent." where S_ID=".$S_ID ;
    $result=mysqli_query($dbc,$query);
}

$mypath=$filesdir."/files_section/".$S_ID;
if(is_dir($mypath)) {
   full_rmdir($mypath);
}


echo "<body onload=redirect()>";

?>
