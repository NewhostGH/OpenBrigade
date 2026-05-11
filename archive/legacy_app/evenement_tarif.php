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
check_all(15);
$id=$_SESSION['id'];
$evenement=intval($_GET["evenement"]);
$pid=intval($_GET["pid"]);
writehead();
?>
<SCRIPT language=JavaScript>

function redirect1(url) {
    self.location.href = url;
}

</SCRIPT>
</head>
<?php

$S_ID=get_section_organisatrice($evenement);
if ( ! is_chef_evenement($id, $evenement)  and  ! check_rights($id, 15, "$S_ID")) check_all(24);

//=====================================================================
// enregistrer les tarifs
//=====================================================================

$tarif=floatval(secure_input($dbc,$_GET["tarif"]));
$paid=intval($_GET["paid"]);
$mode=intval($_GET["mode"]);
$tarif=floatval(secure_input($dbc,$_GET["tarif"]));
$tarif=floatval(secure_input($dbc,$_GET["tarif"]));
if ( $mode == 4 ) $numcheque=secure_input($dbc,str_replace("\"","",$_GET["numcheque"]));
else $numcheque="";
$payeur=secure_input($dbc,str_replace("\"","",$_GET["payeur"]));

$evts=get_event_and_renforts($evenement);
$query="update evenement_participation set EP_TARIF=".$tarif.", EP_PAID=".$paid.", MODE_PAIEMENT=".$mode.", NUM_CHEQUE=\"".$numcheque."\", NOM_PAYEUR=\"".$payeur."\"
        where P_ID = ".$pid." and E_CODE in (".$evts.")";
$result=mysqli_query($dbc,$query);

echo "<body onload=redirect1('evenement_display.php?evenement=".$evenement."&from=tarif');>";
writefoot();
?>
