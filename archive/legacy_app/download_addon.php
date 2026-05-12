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
if (isset($_POST['module'])) $module = secure_input($dbc, $_POST['module']);
else $module = '';
if (isset($_POST['version'])) $version = secure_input($dbc, $_POST['version']);
else $version = '';
if (isset($_POST['md5sum'])) $package_md5sum = secure_input($dbc, $_POST['md5sum']);
else $package_md5sum = '';
if (isset($_POST['reason'])) $reason = secure_input($dbc, $_POST['reason']);
else $reason = '';
$error = '';
$url = "http://127.0.0.1/monitor/modules/";

function download_addon($module, $package_md5sum): bool
{
    global $url, $version;
    $file_headers = @get_headers($url.$module.'/'.$module.'_'.$version.'.zip');
    if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
        return 1;
    }
    else {
        createPath('./modules/'.$module.'/');
        if ($module == null || $package_md5sum == null)
            return 1;
        file_put_contents('./modules/'.$module.'.zip', fopen($url.$module.'/'.$module.'_'.$version.'.zip', 'r'));
        $md5 = md5_file('./modules/'.$module.'.zip');
        if ($md5 <> $package_md5sum)
            return 1;
        return 0;
    }
}
function createPath($path): bool
{
    if (is_dir($path)) return true;
    $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
    $return = createPath($prev_path);
    return ($return && is_writable($prev_path)) ? mkdir($path) : false;
}

if ($reason <> '') {
    $error = download_addon($module, $package_md5sum);
    if ($error == 0) {
        $error = 0;
        sleep(1.6);
        header('Content-Type: application/json');
        echo json_encode($error);
    }
}