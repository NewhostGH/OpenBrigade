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
check_all(14);

if (isset($_POST['package'])) $package_name = secure_input($dbc, $_POST['package']);
else $package_name = '';
if (isset($_POST['md5sum'])) $package_md5sum = secure_input($dbc, $_POST['md5sum']);
else $package_md5sum = '';
if (isset($_POST['reason'])) $reason = secure_input($dbc, $_POST['reason']);
else $reason = '';
$error = '';

function download_package($package_name, $package_md5sum) {
    global $download_url;

    if ($package_name == null || $package_md5sum == null)
        return 1;
    file_put_contents($package_name, fopen($download_url.$package_name, 'r'));
    $md5 = md5_file($package_name);
    if ($md5 <> $package_md5sum)
        return 1;
    return 0;
}

if ($reason <> '') {
    if ( is_file($package_name) ) unlink($package_name);
    $error = download_package($package_name, $package_md5sum);
    if ($error == 0) {
        $error = 'false';
        header('Content-Type: application/json');
        echo json_encode($error);
    }
}
?>