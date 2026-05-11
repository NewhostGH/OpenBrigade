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
check_all(53);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);

verify_csrf('update_personnel');
?>
<html>
<SCRIPT language=JavaScript>
function redirect(page) {
     self.location.href=page;
}
</SCRIPT>

<?php

if ( isset ($_POST["P_ID"])) $P_ID=intval($_POST["P_ID"]);
else if ( isset ($_GET["P_ID"])) $P_ID=intval($_GET["P_ID"]);
else $P_ID=0;

if ( $P_ID == 0 ) {
    param_error_msg();
    exit;
}
if (!  check_rights($id, 53, get_section_of("$P_ID"))) check_all(24);
$_SESSION['from_cotisation']=1;

// sauver infos infos adhérent
// post parameters

$type_paiement=(isset($_POST["type_paiement"])?intval($_POST["type_paiement"]):0);
$montant_regul=(isset($_POST["montant_regul"])?(float)$_POST["montant_regul"]:0);

$query="update pompier 
        set TP_ID=".$type_paiement.",
        MONTANT_REGUL=".$montant_regul."
        where P_ID=".$P_ID;
$result=mysqli_query($dbc,$query);

if ( $bank_accounts == 1 ) {

    // permission nationale requise
    if (!  check_rights($id, 53, "0")) check_all(24);
    
    // sauver infos BIC
    if (isset ($_POST["bic"])) $bic=secure_input($dbc,$_POST["bic"]);
    else $bic="";
    
    $OLDBIC=get_BIC('P',$P_ID);
    if ( strlen($bic) > 0 and strlen($bic) <> 11 ) {
        write_msgbox("erreur", $error_pic, "Code BIC incorrect, 11 caractères requis, BIC non modifié<br><p align=center><input type=submit class='btn btn-default' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
        exit;
    }
    
    if (isset ($_POST["iban"])) $iban=str_replace(' ','',secure_input($dbc,$_POST["iban"]));
    else $iban="";
     
    $OLDIBAN=get_IBAN('P',$P_ID);
    if ( strlen($iban) > 0 and (strlen($iban) < 16 or strlen($iban) > 32 )) {
        write_msgbox("erreur", $error_pic, "Code IBAN incorrect, entre 16 et 32 caractères requis, IBAN non modifié<br><p align=center><input type=submit class='btn btn-default' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
        exit;
    }

    if ( $OLDBIC <> $bic or $OLDIBAN <> $iban) {
        $query="delete from compte_bancaire where CB_TYPE = 'P' and CB_ID=".$P_ID;
        $result=mysqli_query($dbc,$query);
        $query="insert into compte_bancaire (CB_TYPE,CB_ID,BIC,IBAN,UPDATE_DATE)
                    values ('P', ".$P_ID.",\"".$bic."\",\"".$iban."\",NOW())";
        $result=mysqli_query($dbc,$query);

        if ( $OLDBIC <> $bic ) insert_log('UPDBIC', $P_ID, "ancien BIC: ".$OLDBIC );
        if ( $OLDIBAN <> $iban ) insert_log('UPDIBAN', $P_ID, "ancien IBAN: ".$OLDIBAN );
    }
    
}
echo "<body onload='redirect(\"upd_personnel.php?tab=8&pompier=".$P_ID."\")'>";

?>
