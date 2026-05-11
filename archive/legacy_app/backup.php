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
include_once ("fonctions_backup.php");
check_all(0);
writehead();
?>
<script language="JavaScript">
function redirect1(){
    self.location.href="restore.php";
    return true
}
function redirect2(){
    self.location.href="index.php";
    return true
}
</script>
<?php

if (! isset($_GET['mode']) ) $mode="auto";
else $mode=$_GET["mode"];

if ( $mode == "auto" ) {
    $ret = backup("auto");
    if ( $ret == 1) 
        write_msgbox("Error", $error_pic, "<p align=center><font face=arial>Il existe déja une sauvegarde.",10,0);
    else
        write_msgbox("backup", $star_pic, "<p align=center><font face=arial>Une sauvegarde de la base de données a été réalisée.",10,0);
}
else {
    check_all(14);
    $ret = backup($mode);
}

if ( $mode == 'interactif' ) {
    echo "<body onload=redirect1()>";
}
else 
    echo "<body onload=redirect2()>";

writefoot();
?>
