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
ini_set('max_input_vars', 5000);
check_all(53);
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);

$S_ID=intval($_POST["filter"]);
$year=intval($_POST["year"]);
$periode=secure_input($dbc,$_POST["periode"]);
$type_paiement=intval($_POST["type_paiement"]);

if (! check_rights($id, 53, "$S_ID")) check_all(24);
if ( isset ( $_POST["people"] )) $people = preg_split('/,/' , secure_input($dbc,$_POST["people"]));

//=====================================================================
// enregistrer les cotisations saisies
//=====================================================================
$num=0;
$total=0;
@set_time_limit($mytimelimit);

for ( $i=0; $i < sizeof($people); $i++ ) {
    $pid=intval($people[$i]);
    if ( $pid > 0 ) {
        if ( isset ($_POST["paid_".$pid])) $paid=1;
        else $paid=0;
        $montant=(float)$_POST["montant_".$pid];
        $date=$_POST["date_".$pid];
        if ( $date <> '' ) {
            $tmp=explode ( "-",$date); $y=$tmp[2]; $m=$tmp[1]; $d=$tmp[0];
            $date=$y."-".$m."-".$d;
        }
        else $date=date('Y-m-d');
    
        $commentaire=STR_replace("\"","",$_POST["commentaire_".$pid]);
        $commentaire=secure_input($dbc,$commentaire);
        if ( $type_paiement == 1 ) {
            $etablissement=STR_replace("\"","",$_POST["etablissement_".$pid]);
            $etablissement="\"".secure_input($dbc,$etablissement)."\"";
            
            $guichet=STR_replace("\"","",$_POST["guichet_".$pid]);
            $guichet="\"".secure_input($dbc,$guichet)."\"";
            
            $compte=STR_replace("\"","",$_POST["compte_".$pid]);
            $compte="\"".secure_input($dbc,$compte)."\"";
            
            $code_banque=STR_replace("\"","",$_POST["code_banque_".$pid]);
            $code_banque="\"".secure_input($dbc,$code_banque)."\"";
            
            $iban=STR_replace("\"","",$_POST["iban_".$pid]);
            $iban="\"".secure_input($dbc,$iban)."\"";
            
            $bic=STR_replace("\"","",$_POST["bic_".$pid]);
            $bic="\"".secure_input($dbc,$bic)."\"";
        }
        else {
            $etablissement="null";
            $guichet="null";
            $compte="null";
            $code_banque="null";
            $bic="null";
            $iban="null";
        }
            

        if ( isset ($_POST["num_cheque_".$pid])) $num_cheque=intval($_POST["num_cheque_".$pid]);
        else $num_cheque='null';

        $query="delete from personnel_cotisation where P_ID=".$pid." and ANNEE='".$year."' and PERIODE_CODE='".$periode."'";
        $result=mysqli_query($dbc,$query);
    
        if ( $paid == 1 ) {
        
            $query="insert into personnel_cotisation (P_ID,ANNEE,PERIODE_CODE,PC_DATE,MONTANT,TP_ID,COMMENTAIRE,NUM_CHEQUE,
                ETABLISSEMENT,GUICHET,COMPTE,CODE_BANQUE,IBAN, BIC, REMBOURSEMENT)
            values (".$pid.",'".$year."','".$periode."','".$date."',".$montant.",".$type_paiement.",\"".$commentaire."\",".$num_cheque.",        
                ".$etablissement.",".$guichet.",".$compte.",".$code_banque.",".$iban.",".$bic.",0)";
            $result=mysqli_query($dbc,$query);
            
            $cmt="Paiement de ".$montant.$default_money_symbol." pour ".$year;
            insert_log('INSCOT', $pid, $cmt);
            
            $num++;
            $total =$total+$montant;
            
            // cas de la personne en prélèvement, on considère que la régul a été faite.
            $query="update pompier set MONTANT_REGUL=0 where P_ID=".$pid." and TP_ID=".$type_paiement." and TP_ID = 1 
                    and ( MONTANT_REGUL <> 0 and MONTANT_REGUL is not null)";
            $result=mysqli_query($dbc,$query);
            $query2="update rejet set REGULARISE=1, REPRESENTER=0 , DATE_REGUL = '".$date."'
                    where P_ID = ".$pid." and REPRESENTER=1";
            $result2=mysqli_query($dbc,$query2);
        }
    }
}

write_msgbox("OK", $star_pic, "Les cotisations ont été enregistrées,<br>pour $num personnes<br>montant total: <b>$total $default_money_symbol</b><p align=center><a href=cotisations.php><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);


?>
