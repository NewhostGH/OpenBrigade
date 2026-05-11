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
check_all(41);
$id=$_SESSION['id'];

$evenement=intval($_GET["evenement"]);
$pid=intval($_GET["pid"]);

$query="select E_TARIF, S_ID from evenement where E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

$evts=get_event_and_renforts($evenement);
$query="select distinct p.P_ID, ep.E_CODE as EC, p.P_ID, p.P_NOM, P_PHONE, p.P_PRENOM, 
        ep.EP_TARIF, ep.EP_PAID, ep.MODE_PAIEMENT, ep.NUM_CHEQUE, ep.NOM_PAYEUR, tp.TP_DESCRIPTION
        from pompier p, evenement_participation ep left join type_paiement tp on tp.TP_ID=ep.MODE_PAIEMENT
        where ep.E_CODE in (".$evts.")
        and p.P_ID=ep.P_ID
        and p.P_ID=".$pid;
        
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);
$for=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);

$modal=true;
$nomenu=1;
writehead();

write_modal_header("Tarif pour ".$for);

if (check_rights($id, 15, $S_ID)) $granted_event=true;
else if ( $chef ) $granted_event=true;
else $granted_event=false;

if ( $granted_event ) $disabled_tarif='';
else $disabled_tarif='disabled';

if ( $EP_TARIF == '' ) $tarif = floatval($E_TARIF);
else $tarif = floatval($EP_TARIF);

if ( $EP_PAID == 1 ) $checked='checked';
else $checked='';

$out=  "<div align=center><p><table class='noBorder' cellspacing=0>
            <tr>
                <td>Montant</td>
                <td> <input type=text id='tarif_".$P_ID."' name='tarif_".$P_ID."' style='width:50px;' $disabled_tarif
                title=\"saisissez le tarif de la formation pour ".$for."\"
                onchange='checkFloat(tarif_".$P_ID.",\"$tarif\")'
                value='".$tarif."'></td>
            </tr>
            <tr>
                <td>Payé</td>
                <td><input type=checkbox id='paid_".$P_ID."' name='paid_".$P_ID."'  $disabled_tarif
                 title=\"cochez cette case si ".$for." a payé sa formation\" value='1' $checked></td>
            </tr>
            <tr>
                <td>Mode paiement </td>
                <td><select name='mode_".$P_ID."' id='mode_".$P_ID."' $disabled_tarif onchange=\"javascript:activate_cheque('".$P_ID."');\">";
$query1="select TP_ID, TP_DESCRIPTION from type_paiement";
$result1=mysqli_query($dbc,$query1);
while ( custom_fetch_array($result1)) {
    if ( $MODE_PAIEMENT == $TP_ID ) $selected='selected';
    else $selected='';
    $out .= "<option value=".$TP_ID." $selected>".$TP_DESCRIPTION."</option>";
}
$out .= "   </select></td>";
    
if ( $MODE_PAIEMENT == 4 ) $style="";
else $style="style='display:none'";

$out .= "<tr $style id='rowcheque_".$P_ID."' >
        <td>Numéro chèque</td>
            <td> <input type=text id='numcheque_".$P_ID."' name='numcheque_".$P_ID."' style='width:100px;' $disabled_tarif
            title=\"Facultatif: saisissez ici le numéro du chèque ".$for."\"
            value=\"".$NUM_CHEQUE."\"></td>
        </tr>
        <tr>
            <td>Nom du payeur</td>
            <td> <input type=text id='payeur_".$P_ID."' name='payeur_".$P_ID."' style='width:150px;' $disabled_tarif
            title=\"Facultatif: saisissez ici le nom de la personne ou société qui a payé ".$for."\"
            value=\"".$NOM_PAYEUR."\"></td>
        </tr>
        </table><p>";

if ( $granted_event ) 
$out .= "<div align=center>
            <a class='btn btn-default' href=\"javascript:SavePaiement('".$P_ID."','".$evenement."')\">sauver</a>
            </div>"; 
$out .= "<p></div>";

print $out;

writefoot();
?>