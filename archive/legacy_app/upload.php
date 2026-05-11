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

if (isset ($_POST["operation"])) $operation=$_POST["operation"];
else $operation='';
if (isset ($_POST["old"])) $old=$_POST["old"];
else $old='';

$allowed_image_types = array('image/jpeg'=>"jpg",'image/pjpeg'=>"jpg",'image/jpg'=>"jpg",'image/png'=>"png",'image/x-png'=>"png");
$allowed_image_ext = array_unique($allowed_image_types);
$image_ext = "";
foreach ($allowed_image_ext as $mime_type => $ext) {
    $image_ext.= strtoupper($ext)."/";
}
$image_ext =  rtrim($image_ext, "/");
//=====================================================================
// upload icone
//=====================================================================
if(isset($_FILES['icone']['name'])) {
    $size = (int) @$_SERVER['CONTENT_LENGTH'];
    if ( $size > $MAX_SIZE ) {
        $error = $MAX_SIZE_ERROR;
    }
    else {
        $filename = $_FILES['icone']['name'];
        $userfile_type = $_FILES['icone']['type'];
        $uploadfile = $specific_dir."/".str_replace(" ","",$filename);
        $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));

        $grade_exist = count_entities("grade", "G_ICON='" . $uploadfile . "'");
        if ($grade_exist) {
            echo -1;
            exit();
        }

        $found = 0;
        $error = "";
        foreach ($allowed_image_types as $mime_type => $ext) {
            if ( $file_ext == $ext and $userfile_type==$mime_type) {
                $found++;
            }
        }
        if ( $found == 0 && $operation == "update") {
            echo -2;
            exit;
        }
        elseif ($found == 0 && $operation == "insert"){
            echo -3;
            exit;
        }

        if ( strlen ( $error ) == 0) {
            if ( is_file($uploadfile)) unlink($uploadfile);
            move_uploaded_file($_FILES['icone']['tmp_name'], $uploadfile);
        }

    }

    if ( strlen ( $error ) > 0 && $operation == "update") {
        echo -4;
        exit;
    }
    elseif (strlen ( $error ) > 0 && $operation == "insert"){
        echo -5;
        exit;
    }

    $newG_ICON = $uploadfile;
    echo $newG_ICON;
    exit;
}
echo "";
