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

# to activate write a .htaccess file at the root of your ebrigade install, including the 2 rows
# ErrorDocument 404 /error.php?errno=404
# ErrorDocument 403 /error.php?errno=403
#  
  
include_once ("config.php");
check_all(0);
$nomenu=1;
writehead();

if (isset($_GET["errno"])) $errno = intval($_GET["errno"]);
else $errno="404";

if ( $errno == "403" ) {
    $msg="Erreur, accès interdit.";
}
else {
    $errno="404";
    $msg="Erreur, la page demandée n'existe pas.";
}    
      
$msg .="<p align=center><input type='button'  class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>";
    
write_msgbox("Erreur $errno",$error_pic,$msg,30,30);

writefoot();
exit;
    
