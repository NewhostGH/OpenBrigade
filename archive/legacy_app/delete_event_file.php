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
?>
<script language="JavaScript">
function redirect(url){
    self.location.href=url;
    return true
}
</script>
<?php

if (isset ($_GET["fileid"])) $fileid=intval($_GET["fileid"]);
else $fileid=0;
$number=intval($_GET["number"]);
$type=$_GET["type"];
$folder=0;
if ( $fileid == 0 and isset($_GET["file"])) $file=$_GET["file"]; // backward compatibility
else $file=get_file_name($fileid);

if ( $type == 'evenement' ) {
    if (! check_rights($id, 47, get_section_organisatrice("$number"))
        and ! is_chef_evenement($id, $number) )
        check_all(15);
    $path=$filesdir."/files/".$number;
    
    $query="delete from document where E_CODE=".$number." and D_ID=".$fileid;
    $result=mysqli_query($dbc,$query);
    $url="evenement_display.php?from=document&evenement=".$number;
    
}
else if ( $type == 'pompier' ) {
    if (! check_rights($id, 2, get_section_of("$number"))) {
        check_all(2);check_all(24);
    }
    $path=$filesdir."/files_personnel/".$number;
    
    $query="delete from document where P_ID=".$number." and D_ID=".$fileid;
    $result=mysqli_query($dbc,$query);
    insert_log('DELDOC', $number, "$file");
    
    $url="upd_personnel.php?from=document&pompier=".$number;

}
else if ( $type == 'vehicule' ) {
    if (! check_rights($id, 17, get_section_of_vehicule("$number"))) {
        check_all(17);check_all(24);
    }
    $path=$filesdir."/files_vehicule/".$number;
    
    $query="delete from document where V_ID=".$number." and D_ID=".$fileid;
    $result=mysqli_query($dbc,$query);
    
    $url="upd_vehicule.php?vid=".$number."&tab=2";
}
else if ( $type == 'victime' ) {
    $evenement = intval(get_evenement_victime("$number"));
    if (! check_rights($id, 47, get_section_organisatrice($evenement))
        and ! is_chef_evenement($id, $evenement) 
        and ! is_operateur_pc($id, $evenement) )
        check_all(15);
    $path=$filesdir."/files_victime/".$number;
    
    $query="delete from document where VI_ID=".$number." and D_ID=".$fileid;
    $result=mysqli_query($dbc,$query);
    
    $url="victimes.php?victime=".$number."&modevictime=doc";
}
else if ( $type == 'intervention' ) {
    $evenement = $_GET["evenementinter"];
    if (! check_rights($id, 47, get_section_organisatrice($evenement))
        and ! is_chef_evenement($id, $evenement)
        and ! is_operateur_pc($id, $evenement) )
        check_all(15);
    $path=$filesdir."/files_interventions/".$number;

    $query="delete from document where EL_ID=".$number." and D_ID=".$fileid;
    $result=mysqli_query($dbc,$query);

    $url="intervention_edit.php?evenement=".$evenement."&numinter=".$number."&action=update&modeinter=doc";
}
else if ( $type == 'materiel' ) {
    if (! check_rights($id, 70, get_section_of_materiel("$number"))) {
        check_all(70);check_all(24);
    }
    $path=$filesdir."/files_materiel/".$number;
    
    $query="delete from document where M_ID=".$number." and D_ID=".$fileid;
    $result=mysqli_query($dbc,$query);
    
    $url="upd_materiel.php?mid=".$number."&tab=2";
}
else if ( $type == 'note' ) {
    $beneficiaire=get_beneficiaire_note("$number");
    $sb=get_section_of($beneficiaire);
    if (! check_rights($id, 73, $sb) and $beneficiaire <> $id) {
        check_all(73);check_all(24);
    }
    $path=$filesdir."/files_note/".$number;
    
    $query="delete from document where NF_ID=".$number." and D_ID=".$fileid;
    $result=mysqli_query($dbc,$query);
    
    $url="note_frais_edit.php?action=update&person=".$beneficiaire."&nfid=".$number;

}
else if ( $type == 'section' ) {
    if ( isset($_GET["folder"])) {
        $folder=intval($_GET["folder"]);
    }
    
    if (! check_rights($id, 47, "$number")) check_all(22);
    $path=$filesdir."/files_section/".$number;
    
    if ( $folder == 0 ) { // un document
        $query1 = "select DF_ID from document where D_ID= ".$fileid;
        $result1=mysqli_query($dbc,$query1);
        $row1=@mysqli_fetch_array($result1);
        $subfolder=$row1[0];
        if ( $subfolder > 0 ) $path=$path."/".$subfolder;
    
        $query="delete from document where S_ID=".$number." and D_ID=".$fileid;
        $result=mysqli_query($dbc,$query);
    }
    else { // un dossier
        $query="delete from document where DF_ID =".$fileid;
        $result=mysqli_query($dbc,$query);
        $query="delete from document_folder where DF_ID =".$fileid;
        $result=mysqli_query($dbc,$query);
        
        // et les sous-dossiers
        $query1 = "select DF_ID from document_folder where DF_PARENT= ".$fileid;
        $result1=mysqli_query($dbc,$query1);
        while ( $row1=@mysqli_fetch_array($result1)) {
            $subfolder=$row1[0];
            $query="delete from document where DF_ID =".$subfolder;
            $result=mysqli_query($dbc,$query);
            $query="delete from document_folder where DF_ID =".$subfolder;
            $result=mysqli_query($dbc,$query);
            @full_rmdir($path."/".$subfolder);
        }
    }
    $url="documents.php?S_ID=".$number;
}
else check_all(14);

if ( $folder ) @full_rmdir($path."/".$fileid);
else @unlink($path."/".$file);

echo "<body onload=redirect('".$url."');>";

?>
