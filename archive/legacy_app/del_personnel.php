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
check_all(3);
writehead();
?>

<SCRIPT language=JavaScript>

    function redirect() {
        url="personnel.php?order=P_NOM";
        self.location.href=url;
    }
    function redirect_to(person) {
        url="upd_personnel.php?pompier="+person;
        self.location.href=url;
    }
</SCRIPT>
</head>
<?php

$id=$_SESSION['id'];
if (isset ($_GET["P_ID"])) $P_ID=intval($_GET["P_ID"]);
else $P_ID=0;
if (isset ($_GET["redirect_to"])) $redirect_to=intval($_GET["redirect_to"]);
else $redirect_to=0;

if ( $P_ID == 0 ) {
    write_msgbox("erreur de paramètres", $P_ID." ".$error_pic, $error_8."<p align=center><a href=personnel.php><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
    exit;
}

if ( $P_ID == 1 ) {
    write_msgbox("erreur", $error_pic, "Cette fiche personnelle ne doit pas être supprimée<p align=center><a href=upd_personnel.php?pompier=".$P_ID."><input type='submit' class='btn btn-default' value='Retour'></a> ",10,0);
    exit;
}

verify_csrf('delete_personnel');

if (! check_rights($id, 3, get_section_of("$P_ID"))) check_all(24);

//=====================================================================
// suppression fiche
//=====================================================================
delete_personnel($P_ID);

if ( $redirect_to > 0 ) echo "<body onload=redirect_to('".$redirect_to."');>";
else echo "<body onload=redirect();>";

writefoot();
?>
