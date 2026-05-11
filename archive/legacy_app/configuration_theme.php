<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade
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
check_all(14);
$id=$_SESSION['id'];

if (isset($_GET["image"])) 
    $image=secure_input($dbc,$_GET["image"]);
else if (isset($_POST["image"]))
    $image=secure_input($dbc,$_POST["image"]);
else
    $image = "";

$pic=get_theme_image($image);
// -------------------------------------------
// Update
// -------------------------------------------

if (isset($_POST["image"])) {
    if ( $_POST["action"] == 'delete' ) {
        if ( is_file ($pic)) unlink($pic);
        if ($image =="favicon") {
            $query="update configuration set VALUE='' where IS_FILE=1 and NAME='apple_icon'";
            $result=mysqli_query($dbc,$query);
        }
        
        $query="update configuration set VALUE='' where IS_FILE=1 and NAME='".$image."'";
        $result=mysqli_query($dbc,$query);
    }
    if ( $_POST["action"] == 'upload' and isset($_FILES["upload"])) {
        $size = (int) $_SERVER['CONTENT_LENGTH'];
        if ( $size > $MAX_SIZE ) {
            $error = $MAX_SIZE_ERROR;
        }
        else {
            $allowed_image_types = array('image/jpeg'=>"jpg",'image/pjpeg'=>"jpg",'image/jpg'=>"jpg",'image/png'=>"png",'image/x-png'=>"png");
            $allowed_image_ext = array_unique($allowed_image_types);
            $image_ext = "";
            foreach ($allowed_image_ext as $mime_type => $ext) {
                $image_ext.= strtoupper($ext)." ";
            }
            
            $filename = basename($_FILES['upload']['name']);
            $userfile_type = $_FILES['upload']['type'];
            $uploadfile = $specific_dir."/uploaded_".str_replace(" ","",onlyLettersAndNumbers($filename));
            $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
            
            if ( is_file($uploadfile)) unlink($uploadfile);
            if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadfile)) {
                $error = "<br>Votre image est un <strong>".$userfile_type." extension ".$file_ext."</strong>
                    <br>Mais seules les images suivantes sont acceptées <strong>".$image_ext."</strong> sont acceptées<br>";
                foreach ($allowed_image_types as $mime_type => $ext) {
                    //loop through the specified image types and if they match the extension then break out
                    if ( $file_ext==$ext and $userfile_type==$mime_type) {
                        $error = "";
                        break;
                    }
                }
            }
            else {
                $error = "Erreur d'upload du fichier ".$filename;
            }
        }
        if ( strlen ( $error ) > 0) {
            write_msgbox("erreur", $error_pic, $error."<p><div align=center><a href=configuration.php?tab=conf4>
                <input type='submit' class='btn btn-secondary' value='Retour'></a></div>", 30, 30);
            exit;
        }
        else {
            if ( is_file($uploadfile)) {
                if ($image == "favicon") {
                    $query="update configuration set VALUE=\"".$uploadfile."\" where IS_FILE=1 and NAME='apple_icon'";
                $result=mysqli_query($dbc,$query);
                }
                $query="update configuration set VALUE=\"".$uploadfile."\" where IS_FILE=1 and NAME='".$image."'";
                $result=mysqli_query($dbc,$query);
            }
        }
    }
    echo "<body onload=\"javascript:self.location.href='configuration.php?tab=conf4';\">";
    exit;
}

// -------------------------------------------
// Display
// -------------------------------------------

$modal=true;
$nomenu=1;
writehead();

$query="select DESCRIPTION from configuration where NAME='".$image."'";
$result = mysqli_query($dbc,$query);
$row = mysqli_fetch_array($result);
$DESCRIPTION=$row["DESCRIPTION"];

if ( $image == 'splash_screen' ) $DESCRIPTION .= ". Taille maximum des fichiers pour upload ".$MAX_FILE_SIZE_MB." MB";

$helper="<a href='#' data-toggle='popover' title=\"Aide ".$image."\" data-trigger='hover' data-placement='bottom'
            data-content=\"".$DESCRIPTION."\" ><i class='fas fa-info-circle'></i></a>";
        
write_modal_header("Personnalisation ".$image." ".$helper);

$html = "<script>
$(document).ready(function(){
    $('[data-toggle=\"popover\"]').popover();
});
</SCRIPT>
</HEAD>";
$pos = strpos($pic, 'user-specific');

$html .= "<body><div align=center>
<input type='hidden' name='image' value='".$image."'>
<p><table class='noBorder' >
    <tr height=10 style='background-color:white;'>
    <td align=center>";

if ( file_exists($pic)) {
    if ( $pos == 0 ) $t=' - image par défaut';
    else $t='';
    if ( $image == 'favicon' ) $max=22;
    else if ( $image == 'apple_icon' ) $max=40;
    else if ( $image == 'splash_screen' ) $max=160;
    else $max=60;
    $html .= "<i>Image actuelle ".$t."</i><p><img src=\"".$pic."\" class='img-max-".$max."' border='0' title='image actuelle'/>";
}
else
    $html .= "<i>Aucune image utilisée actuellement</i>";
if ( $pos > 0 ) {
    $html .= "<form action='configuration_theme.php' method='POST'>";
    $html .= "<input type='hidden' name='action' value='delete' />";
    $html .= "<input type='hidden' name='image' value='".$image."' />";
    $html .= "<input type='submit' class='btn btn-danger' value='Supprimer' title=\"l'image par défaut sera utilisée\">";
    $html .= "</form>";
}
$html .= "</td> 
    </tr></table>";
$html.= "<form id='imageform' name='imageform' action='configuration_theme.php' enctype='multipart/form-data' method='POST' >
    <input type='hidden' name='action' value='upload' />
    <input type='hidden' name='image' value='".$image."' />
    <label class='btn btn-success btn-file' title='Choisir une nouvelle image personnalisée'>
        <i class='far fa-image fa-lg'></i> Choisir
        <input type='file' id='upload' name='upload' style='display: none;' onchange=\"javascript:document.getElementById('imageform').submit();\" >
    </label>
    <button type='button' class='btn btn-default' data-dismiss='modal' style='margin-bottom:6px;'>Fermer</button>";
$html.= "</form></div></body></html>";

print $html;
writefoot($loadjs=false);
?>
