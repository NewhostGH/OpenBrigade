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
check_all(47);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
writehead();
?>
<html>
<head>
<SCRIPT type="text/javascript">

function redirect(section, from, eid) {
    if(from == 'materiel'){
        url='upd_materiel.php?mid='+eid+'&tab=2';
    }
    else if(from == 'vehicule'){
        url='upd_vehicule.php?vid='+eid+'&tab=2';
    }
    else
        url="documents.php?filter="+section;
    self.location.href=url;
}

</SCRIPT>
</head>
<?php
$msg="";
if (isset($_POST["S_ID"])) $S_ID=intval($_POST["S_ID"]);
else $S_ID=0;
if (isset($_POST["operation"])) $operation=$_POST["operation"];
else $operation='';

if (isset ($_POST["type"])) $TD_CODE=secure_input($dbc,$_POST["type"]); 
else $TD_CODE='';
if (isset ($_POST["url"])) $URL=secure_input($dbc,$_POST["url"]); 
else $URL='';
if (isset ($_POST["security"])) $DS_ID=intval($_POST["security"]); 
else $DS_ID='';
if (isset ($_POST["dossier"])) $dossier=intval($_POST["dossier"]); 
else $dossier="";

if (isset($_POST['from'])) $from=$_POST['from'];
else $from='';
if (isset($_POST['vehicule'])) $eid=intval($_POST['vehicule']);
elseif (isset($_POST['materiel'])) $eid=intval($_POST['materiel']);
else $eid=0;

if (! check_rights($id, 47, "$S_ID"))
check_all(24);
    
//============================================================
//   Upload file
//============================================================
$error = 0;

$size = (int) $_SERVER['CONTENT_LENGTH'];
if ( $size > $MAX_SIZE ) {
    $error = 1;
    $msgstring = $MAX_SIZE_ERROR;
}

if ( isset ($_FILES['userfile'])) {

    for( $i=0 ; $i < count($_FILES['userfile']['name']) ; $i++ ) {
        if (check_rights($id, 47, "$S_ID")) {

            include_once ($basedir."/fonctions_documents.php");
            if ( $dossier > 0 ) $upload_dir = $filesdir."/files_section/".$S_ID."/".$dossier."/";
            else $upload_dir = $filesdir."/files_section/".$S_ID."/";

            $upload_result = upload_doc($i);
            list($file_name, $error, $msgstring ) = explode(";", $upload_result);

            if ( $error == 0 ) {
                    // upload réussi: insérer les informations relatives au document dans la base
                    $_SESSION['dossier'] = intval($dossier);
                    $query="insert into document(S_ID,D_NAME,TD_CODE,DS_ID,D_CREATED_BY,D_CREATED_DATE,DF_ID)
                           values (".$S_ID.",\"".$file_name."\",\"".$TD_CODE."\",\"".$DS_ID."\",".$id.",NOW(),".intval($dossier).")";
                    $result=mysqli_query($dbc,$query);
                       
                    if ( isset($_SESSION['td'])) {
                    if ( $TD_CODE <> $_SESSION['td'] and $_SESSION['td'] <> 'ALL') $_SESSION['td']=$TD_CODE;
                }
            }
            else {
                write_msgbox("ERREUR", $error_pic, "$msgstring<br><p align=center>
                <input type='button' class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>",10,0);
                exit;
            }
            $operation="retour";
        }
    }
}

//=====================================================================
// changer infos d'un document
//=====================================================================
if ( $operation == 'updatedoc' ) {
    $docid=intval($_POST["docid"]);
    $S_ID=intval($_POST["S_ID"]);
    $isfolder=intval($_POST["isfolder"]);
    $parentfolder=intval($_POST["parentfolder"]);
    
    $parentfoldertype=
    $TD_CODE=secure_input($dbc,$_POST["type"]);
    if (isset ($_POST["security"])) $DS_ID=intval($_POST["security"]); 
    else $DS_ID=1;
    if ( $DS_ID == 0 ) $DS_ID=1;

    if ( $isfolder == 0 ) {
        // move file ?
        $query="select D_NAME, DF_ID from document where D_ID=".$docid;
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $filename=$row[0];
        $oldfolder=$row[1];
        if ( $oldfolder <> $parentfolder ) { // let's move
            $source=$filesdir."/files_section/".$S_ID;
            if ( $oldfolder == 0 ) $source .="/".$filename;
            else $source .="/".$oldfolder."/".$filename;
            $dest=$filesdir."/files_section/".$S_ID;
            if ( $parentfolder == 0 ) $dest .="/".$filename;
            else $dest .="/".$parentfolder."/".$filename;
            @rename($source, $dest);
        }
        
        $query="update document set TD_CODE='".$TD_CODE."', DS_ID=".$DS_ID.", DF_ID = ".$parentfolder."
            where D_ID=".$docid;
        $result=mysqli_query($dbc,$query);
    }
    else {
        // renommage du dossier
        if (isset ($_POST["foldername"])) {
            $foldername=secure_input($dbc,$_POST["foldername"]);
            $foldername = str_replace("\\","",$foldername);
            $foldername = str_replace("/","",$foldername);
            $foldername = str_replace("\"","",$foldername);
            $foldername = str_replace("'","",$foldername);
            $query="update document_folder set DF_NAME=\"".$foldername."\" where DF_ID=".$docid;
            $result=mysqli_query($dbc,$query);
        }
    
        $query="update document_folder set TD_CODE='".$TD_CODE."'
                where DF_ID=".$docid." or DF_PARENT=".$docid;
        $result=mysqli_query($dbc,$query);
        
        $query="update document set TD_CODE='".$TD_CODE."'
                where DF_ID in (select DF_ID from document_folder where DF_ID=".$docid." or DF_PARENT=".$docid.")";
        $result=mysqli_query($dbc,$query);
        
        $query="update document_folder set DF_PARENT=".$parentfolder."
                where DF_ID =".$docid;
        $result=mysqli_query($dbc,$query);
        
    }
    // forcer le type de document ou dossier, idem parent
    if ( $parentfolder > 0 ) {
        $query="select TD_CODE from document_folder where DF_ID=".$parentfolder;
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $TD_CODE=$row[0];
        
        if ( $isfolder == 1 ) {
            $query="update document_folder set TD_CODE='".$TD_CODE."'
                where DF_ID=".$docid." or DF_PARENT=".$docid;
            $result=mysqli_query($dbc,$query);
        }
        
        $query="update document set TD_CODE='".$TD_CODE."'
                where DF_ID in (select DF_ID from document_folder where DF_ID=".$parentfolder." or DF_PARENT=".$parentfolder.")";
        $result=mysqli_query($dbc,$query);
    }

    if ( isset($_SESSION['td'])) {
        if ( $TD_CODE <> $_SESSION['td'] and $_SESSION['td'] <> 'ALL') $_SESSION['td']=$TD_CODE;
    }
}

 echo "<body onload=\"redirect('$S_ID', '$from', $eid);\">";
writefoot();

?>
