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
$nomenu=1;
$id=$_SESSION['id'];
if ( isset($_GET["section"])) $section=intval($_GET["section"]);
else $section="";
if ( isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement="0";
if ( isset($_GET["pompier"])) $pompier=intval($_GET["pompier"]);
else $pompier="0";
if ( isset($_GET["vehicule"])) $vehicule=intval($_GET["vehicule"]);
else $vehicule="0";
if ( isset($_GET["victime"])) $victime=intval($_GET["victime"]);
else $victime="0";
if ( isset($_GET["intervention"])) $intervention=intval($_GET["intervention"]);
else $intervention="0";
if ( isset($_GET["materiel"])) $materiel=intval($_GET["materiel"]);
else $materiel="0";
if ( isset($_GET["file"])) $file=secure_input($dbc,$_GET["file"]);
else $file="";
if ( isset($_GET["message"])) $message=intval($_GET["message"]);
else $message="0";
if ( isset($_GET["diplome"])) $diplome=intval($_GET["diplome"]);
else $diplome="0";
if ( isset($_GET["sst"])) $sst=intval($_GET["sst"]);
else $sst="0";
if ( isset($_GET["note"])) $note=intval($_GET["note"]);
else $note="0";
if ( isset($_GET["dossier"])) $dossier=intval($_GET["dossier"]);
else $dossier="0";
if ( isset($_GET["charte"])) $charte=intval($_GET["charte"]);
else $charte="0";

// secure file
$file = secure_file_name($file);

if ( $sst == 1 ) $filepath=$userdocdir."/SST";
else if ( $sst == 2 ) $filepath=$userdocdir."/PSC1";
else if ( $diplome == 1 ) $filepath=$filesdir."/diplomes";
else if ( $message > 0 ) $filepath=$filesdir."/files_message/".$message;
else if ( $evenement > 0 ) $filepath=$filesdir."/files/".$evenement;
else if ( $pompier > 0 ) $filepath=$filesdir."/files_personnel/".$pompier;
else if ( $vehicule > 0 ) $filepath=$filesdir."/files_vehicule/".$vehicule;
else if ( $victime > 0 ) $filepath=$filesdir."/files_victime/".$victime;
else if ( $intervention > 0 ) $filepath=$filesdir."/files_interventions/".$intervention;
else if ( $materiel > 0 ) $filepath=$filesdir."/files_materiel/".$materiel;
else if ( $note > 0 ) $filepath=$filesdir."/files_note/".$note;
else if ( $dossier > 0 ) $filepath=$filesdir."/files_section/".$section."/".$dossier;
else if ( $charte == 1 ) $filepath=$filesdir."/charte";
else $filepath=$filesdir."/files_section/".$section;

//=====================================================================
// contrôle de sécurité
//=====================================================================
if ( $pompier > 0 and $id <> $pompier) {
    $his_section = get_section_of("$pompier");
    if ( ! check_rights($id, 2,"$his_section")) {
        $infos_visible=false;
        // si rôles hors département, tester permissions sur autre départements, rendre infos visibles
        if ( check_rights($id, 2)) {
            $query="select distinct S_ID EXTERNAL_SECTION from section_role where P_ID=".$pompier." and S_ID <> ".intval($his_section);
            $result=mysqli_query($dbc,$query);
            while (custom_fetch_array($result)) {
                if (check_rights($id, 2, "$EXTERNAL_SECTION")) {
                    $infos_visible=true;
                    break;
                }
            }
        }
        // vraiment pas de permissions
        if ( ! $infos_visible ) {
            param_error_msg($button = 'close');
            exit;
        }
    }
}

//=====================================================================
// afficher les fichiers stockés même en dehors de la racine web
//=====================================================================
function SendFile($path,$filename) {
    global $error_pic;
    $filename = str_replace("\\","",$filename);
    $filefullpath=$path."/".$filename;
    
    $path_parts = pathinfo($filefullpath);
    $extension = $path_parts['extension'];
    if ( strtoupper($extension) == 'PDF' ) {
        
        if (!  @is_file($filefullpath)) {
            writehead();
            echo "<script type='text/javascript'>
            function closeme(){
                var obj_window = window.open('', '_self');
                obj_window.opener = window;
                obj_window.focus();
                opener=self;
                self.close();
            }
            </script>";
            write_msgbox("erreur", $error_pic, "Le fichier ".$filename." est introuvable.<br><p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick='javascript:history.back(-1);'> ",10,0);
            exit;
        }
    
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($filefullpath));
        header('Accept-Ranges: bytes');
        readfile($filefullpath);
    }
    else {
        //header("Content-Type: " . mime_content_type($file));
        // if you are not allowed to use mime_content_type, then hardcode MIME type
        // use application/octet-stream for any binary file
        // use application/x-executable-file for executables
        // use application/x-zip-compressed for zip files
        
 
        if (!  @is_file(str_replace("\\","",$filefullpath)) ) {
            writehead();
            write_msgbox("erreur", $error_pic, "Le fichier ".$filename." est introuvable.<br><p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
            exit;
        }
 
        header("Content-Type: application/octet-stream");
        header("Content-Length: " . filesize($filefullpath));
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        if (file_exists($filefullpath)) {
            $fp = fopen($filefullpath,"rb");
            fpassthru($fp);
            fclose($fp);
        }
    }
}

//=====================================================================
// main
//=====================================================================

if ( $diplome == 1 or $message > 0 or $charte == 1 )
    SendFile($filepath,$file);
else {
    $query="select ds.F_ID, d.D_CREATED_BY , td.TD_SECURITY
        from document_security ds, document d, type_document td
        where d.DS_ID = ds.DS_ID
        and td.TD_CODE = d.TD_CODE
        and d.S_ID=".$section."
        and d.D_NAME='".$file."'";
    $query .= " and d.E_CODE=".$evenement;
    $query .= " and d.P_ID=".$pompier;
    $query .= " and d.NF_ID=".$note;
    $query .= " and d.EL_ID=".$intervention;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);

    if (! check_rights($id, intval($row["TD_SECURITY"]))) {
        writehead();
        write_msgbox("erreur permission",$error_pic,"Vous n'êtes pas autorisés à voir ce type de documents, la permission  n°".intval($row["TD_SECURITY"])." est requise 
        <a href=".$basedir."/habilitations.php target=_blank>".$miniquestion_pic."</a> 
        <p><a class='btn btn-secondary' href=\"javascript:history.back(1)\">Retour</a>",30,90);
        exit;
    }
    
    if ( $row["F_ID"] == 0
        or check_rights($id, $row["F_ID"], $section)
        or ($_SESSION['SES_PARENT'] == $section and check_rights($id, 47, $_SESSION['SES_SECTION']))
        or ($row["F_ID"]== 52 and $_SESSION['SES_PARENT'] == $section and check_rights($id, 52, $_SESSION['SES_SECTION']))
        or ( $syndicate == 1 and $row["F_ID"]== 52 and ($_SESSION['SES_PARENT'] = $section or $_SESSION['SES_SECTION'] == $section ))
        or ( $syndicate == 1 and $row["F_ID"]== 16 and check_rights($id, 16 ))
        or ( $syndicate == 1 and $row["F_ID"]== 45 and check_rights($id, 45))
        or $row["D_CREATED_BY"] ==$id
        or is_chef_evenement($id, $evenement)
        or $pompier == $id )
            $allowed=true;
    else $allowed=false;
    
    if ( $evenement > 0 and $row["F_ID"] == 120 ) {
        if (is_inscrit($id,$evenement)) $allowed=true;
    }
    
    if ( $allowed )
        SendFile($filepath,$file);
    else {
        writehead();
        write_msgbox("erreur permission",$error_pic,"Vous n'êtes pas autorisés à voir ce fichier <br> 
        <a href=".$basedir."/habilitations.php target=_blank>".$miniquestion_pic."</a> 
        <a class='btn btn-secondary' href=\"javascript:history.back(1)\">Retour</a>",30,30);
    }
}
?>
