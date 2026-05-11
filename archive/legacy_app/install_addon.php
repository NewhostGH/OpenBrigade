<?php
# project: eBrigade
# homepage: http://sourceforge.net/projects/ebrigade/
# version: 5.2

# Copyright (C) 2004, 2020 Nicolas MARCHE
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
include_once ("fonctions_backup.php");
include_once ("fonctions_sql.php");
check_all(14);
$id=$_SESSION['id'];

if (isset($_POST['module'])) $module = secure_input($dbc, $_POST['module']);
else $module = '';
if (isset($_POST['reason'])) $reason = secure_input($dbc, $_POST['reason']);
else $reason = '';
if (isset($_POST['version'])) $version = secure_input($dbc, $_POST['version']);
else $version = '';
if (isset($_POST['licence'])) $licence = secure_input($dbc, $_POST['licence']);
else $licence = '';
if (isset($_POST['libelle'])) $libelle = secure_input($dbc, $_POST['libelle']);
else $libelle = '';
if (isset($_POST['description'])) $description = secure_input($dbc, $_POST['description']);
else $description = '';
if (isset($_POST['end_datetime'])) $end_datetime = secure_input($dbc, $_POST['end_datetime']);
else $end_datetime = '';
if (isset($_POST['section_id'])) $section_id = secure_input($dbc, $_POST['section_id']);
else $section_id = '';
if (isset($_POST['seats'])) $seats = secure_input($dbc, $_POST['seats']);
else $seats = '';
$error = 0;

if ( $reason == '') return;
if ($reason == 'unzip' and $module == '' ) return;

header('Content-Type: application/json');

if ($reason == 'install') {
    $error=1;
    $path =  './modules/'.$module.'.zip';
    $zip = new ZipArchive;
    if ($zip->open($path) === true) {
        if ($zip->extractTo('./modules/'.$module.'/') === true ) $error = 0;
        $zip->close();
    }
    import_module($module, $libelle, $version, $description);
    sleep(1.2);
}

if ($reason == 'install_db') {
    $error=import_module($module, $libelle, $version, $description);
    sleep(1.4);
    echo json_encode($error);
    return;
}

if ($reason == 'import_licence') {
    $error = import_licence($module, $licence, $section_id, $seats, $end_datetime);
    sleep(1.3);
    echo json_encode($error);
    return;
}

if ($reason == 'delete') {
    $error=1;
    $path =  './modules/'.$module.'.zip';
    if (unlink($path) === true)
        $error = 0;
    sleep(1.9);
}


if ($error == 0) {
    echo json_encode($error);
}
?>