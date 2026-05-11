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
check_all(19);

if (isset($_GET['cat_old'])) $cat_old = secure_input($dbc, $_GET['cat_old']);
else $cat_old = '';
if ($cat_old != '') {
    $query = "DELETE FROM type_consommable WHERE type_consommable.CC_CODE = \"".$cat_old."\"";
    $result = mysqli_query($dbc, $query);
    $query = "DELETE FROM categorie_consommable WHERE categorie_consommable.CC_CODE = \"".$cat_old."\"";
    $result = mysqli_query($dbc, $query);
}
header("Location: parametrage.php?tab=3&child=4&id=0");
exit();
?>