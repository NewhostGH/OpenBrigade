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

$filter=intval($_POST["filter"]);
$subsections=intval($_POST["subsections"]);
$year=intval($_POST["year"]);
$periode=secure_input($dbc,$_POST["periode"]);
$fraction=get_fraction($periode);

if (! check_rights($id, 53, "$filter")) check_all(24);

$querycnt="select count(*) as NB";
$query="select p.P_ID, pc.PERIODE_CODE,  p.P_PROFESSION, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE, pc.PC_ID, pc.MONTANT, 
        date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
        date_format(p.P_FIN,'%d-%m-%Y') P_FIN,
        p.P_SECTION, p.MONTANT_REGUL, s.S_PARENT,
        cb.ETABLISSEMENT,cb.GUICHET,cb.COMPTE,cb.CODE_BANQUE,cb.BIC,cb.IBAN";
$queryadd=" from  section s,
     pompier p left join personnel_cotisation pc on ( pc.P_ID = p.P_ID and pc.ANNEE = '".$year."' and pc.PERIODE_CODE='".$periode."' )
     left join compte_bancaire cb on ( cb.CB_TYPE = 'P' and  p.P_ID = cb.CB_ID )
     where p.P_SECTION=s.S_ID
     and p.P_NOM <> 'admin'
     and p.P_STATUT <> 'EXT'
     and p.TP_ID  = 1
     and pc.PC_DATE is null
     and p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
        $queryadd .= "\nand p.P_SECTION in (".get_family("$filter").")";
}
else {
      $queryadd .= "\nand p.P_SECTION =".$filter;
}
$period_month=get_month_from_period($periode);
if ( $period_month <> "0" ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < DATE_ADD('".$year."-".$period_month."-01', INTERVAL 1 MONTH) or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-".$period_month."-01' or p.P_FIN is null )";
}
else if ( $periode == 'T1' ) {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-04-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-01-01' or p.P_FIN is null )";
}
else if ( $periode == 'T2' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-04-01' or p.P_FIN is null )";
}
else if ( $periode == 'T3' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-10-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-07-01' or p.P_FIN is null )";
}
else if ( $periode == 'T4' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN > '".$year."-10-01' or p.P_FIN is null )";
}
else if ( $periode == 'S1' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT < '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT > '".$year."-01-01' or p.P_DATE_ENGAGEMENT is null )";
}
else if ( $periode == 'S2' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT > '".$year."-07-01' or p.P_DATE_ENGAGEMENT is null )";
}
else if ( $periode == 'A' )  {
    $queryadd .= "\nand ( p.P_DATE_ENGAGEMENT <= '".$year."-12-31' or p.P_DATE_ENGAGEMENT is null )";
    $queryadd .= "\nand ( p.P_FIN >= '".$year."-01-01' or p.P_FIN is null )";
}

$querycnt = $querycnt.$queryadd;
$query = $query.$queryadd;

//=====================================================================
// enregistrer toutes les cotisations par prélèvement
//=====================================================================
$num=0;
$total=0;
$reguls = 0;

@set_time_limit($mytimelimit);

$date_prelev=$_POST["date_prelev"];
$tmp=explode ( "-",$date_prelev); $y=$tmp[2]; $m=$tmp[1]; $d=$tmp[0];
$date_prelev=$y."-".$m."-".$d;
$number_months_to_pay = 12 / $fraction;

$result=mysqli_query($dbc,$query);
while ($row=@mysqli_fetch_array($result)) {
    $P_ID=$row["P_ID"];
    $P_PROFESSION=$row["P_PROFESSION"];
    $P_SECTION=$row["P_SECTION"];
    $S_PARENT=$row["S_PARENT"];
    $etablissement=$row["ETABLISSEMENT"];
    $guichet=$row["GUICHET"];
    $compte=$row["COMPTE"];
    $code_banque=$row["CODE_BANQUE"];
    $bic=$row["BIC"];
    $iban=$row["IBAN"];
    
    $MONTANT_REGUL=$row["MONTANT_REGUL"];
    $EXPECTED_MONTANT= get_montant($P_SECTION,$S_PARENT,$P_PROFESSION);
    
    $EXPECTED_MONTANT = round($EXPECTED_MONTANT / $fraction , 2);
    $montant = $EXPECTED_MONTANT + $MONTANT_REGUL;
    $reguls = $MONTANT_REGUL + $reguls;
    
    $commentaire = $number_months_to_pay." mois ".$EXPECTED_MONTANT.$default_money_symbol;
    if ( $MONTANT_REGUL > 0 ) $commentaire .=" et régul de ".$MONTANT_REGUL." ".$default_money_symbol;

    $query2="insert into personnel_cotisation (P_ID,ANNEE,PERIODE_CODE,PC_DATE,MONTANT,TP_ID,COMMENTAIRE,NUM_CHEQUE,
            ETABLISSEMENT,GUICHET,COMPTE,CODE_BANQUE,BIC,IBAN,REMBOURSEMENT)
            values (".$P_ID.",'".$year."','".$periode."','".$date_prelev."',".$montant.",1,\"".$commentaire."\",null,        
                \"".$etablissement."\",\"".$guichet."\",\"".$compte."\",\"".$code_banque."\",\"".$bic."\",\"".$iban."\",0)";
    $result2=mysqli_query($dbc,$query2);

    //$cmt="Prélèvement de ".$montant.$default_money_symbol." pour ".$periode." ".$year;
    //insert_log('INSCOT', $P_ID, $cmt);

    $num++;
    $total =$total+$montant;
    
    if ( $MONTANT_REGUL > 0 ) {
        // cas de la personne en prélèvement, on considère que la régul a été faite.
        $query2="update pompier set MONTANT_REGUL=0 where P_ID=".$P_ID." and TP_ID = 1 
                and ( MONTANT_REGUL <> 0 and MONTANT_REGUL is not null)";
        $result2=mysqli_query($dbc,$query2);
        $query2="update rejet set REGULARISE=1, REPRESENTER=0 , DATE_REGUL = '".$date_prelev."', REGUL_ID=3
                where P_ID = ".$P_ID." and REPRESENTER=1";
        $result2=mysqli_query($dbc,$query2);
    }
}

$query="OPTIMIZE TABLE 'personnel_cotisation'";
mysqli_query($dbc,$query);

//show_total_time();

echo "<body bgcolor=#FFFFFF text=$mydarkcolor link=$mydarkcolor vlink=$mydarkcolor alink=$mydarkcolor>";
write_msgbox("OK", $star_pic, "Les cotisations ont été enregistrées,<br>pour $num personnes<br>montant total: 
                             <b>".$total.$default_money_symbol."</b><br> dont <b>".$reguls.$default_money_symbol."</b> de régularisations
                               <p align=center><a href=cotisations.php?tab=2><input type='submit' class='btn btn-default' value='Retour'></a>",30,0);
?>
