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

if (isset($_POST['package'])) $package_name = secure_input($dbc, $_POST['package']);
else $package_name = '';
if (isset($_POST['reason'])) $reason = secure_input($dbc, $_POST['reason']);
else $reason = '';
if (isset($_POST['patch_version'])) $patch_version = secure_input($dbc, $_POST['patch_version']);
else $patch_version = '';
$error = 0;

if ( $reason == '') return;
if ($reason == 'unzip' and $package_name == '' ) return;

header('Content-Type: application/json');

function check_auto_backup() {
    global $dbc;
    $query = "select VALUE from configuration where NAME = 'auto_backup'";
    $result = mysqli_query($dbc, $query);
    $row = @mysqli_fetch_array($result);

    if ($row["VALUE"] == 1) {
        // AUTO BACKUP
        return backup('interactif');
    }
    sleep(2);
}

if ($reason == 'backup')
    $error = check_auto_backup();

if ($reason == 'unzip') {
    $error=1;
    $path =  $basedir."/".$package_name;
    $zip = new ZipArchive;
    if ($zip->open($path) === true) {
        if ($zip->extractTo($basedir) === true ) $error = 0;
        $zip->close();
    }
    $newdir = pathinfo($package_name, PATHINFO_FILENAME);
    recursive_copy($newdir,'.');
    full_rmdir($newdir);
    unlink($path);
    sleep(1);
}

if ($reason == 'db_upgrade') {
    $error = upgrade_sql($write_box = 0);
    sleep(1);
    echo json_encode($error);
    return;
}

if ($reason == 'maintenance_on') {
    $query = "update configuration set VALUE = '1' where NAME = 'maintenance_mode'";
    mysqli_query($dbc, $query) or $error = 1;
    sleep(1);
}

if ($reason == 'maintenance_off') {
    $query = "update configuration set VALUE = '0' where NAME = 'maintenance_mode'";
    mysqli_query($dbc, $query) or $error = 1;
    
    $query="insert into version_history(PATCH_VERSION,VH_DATE,VH_BY)
            values ('".$patch_version."',NOW(),".$id.")";
    mysqli_query($dbc, $query) or $error = 1;
    sleep(1);
}

if ($error == 0) {
    echo json_encode($error);
}
?>