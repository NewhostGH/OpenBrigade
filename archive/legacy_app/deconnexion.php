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
$dbc=@connect();
?>
<html>
<SCRIPT language=JavaScript>
function redirect(url) {
    top.location.href=url;
}
</SCRIPT>
<?php

if (isset($_SESSION['id'])) {
    $query="update audit set A_FIN=NOW(), A_LAST_PAGE='deconnexion.php' where A_FIN is NULL and P_ID=".intval($_SESSION['id']);
    $result=@mysqli_query($dbc,$query);
    $query="delete from demande where P_ID=".intval($_SESSION['id']);
    $result=mysqli_query($dbc,$query);
    @setcookie("evenement", "", time()-3600);
    @setcookie ("PHPSESSID", $_COOKIE['PHPSESSID'], time() - 864000, '/');
    session_unset();
    session_destroy();
}
@mysqli_close($dbc);
if ( @$_SERVER["HTTP_HOST"] == '127.0.0.1' ) $goto='index.php';
else if (isset($deconnect_redirect)) $goto=$deconnect_redirect;
else $goto='index.php';

echo "<body onload='redirect(\"$goto\")'></body>";
?>
