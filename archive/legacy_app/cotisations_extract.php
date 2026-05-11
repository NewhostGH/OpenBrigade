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
include_once ("config_prelevements.php");

check_all(53);
$id=$_SESSION['id'];
$highestsection=get_highest_section_where_granted($_SESSION['id'],53);
get_session_parameters();
// vérifier qu'on a les droits d'afficher pour cette section
$list = preg_split('/,/' , get_family("$highestsection"));
if (! in_array($filter,$list) and ! check_rights($id, 24)) $filter=$highestsection;

$possibleorders= array('P_STATUT','P_NOM','P_SECTION', 'P_PROFESSION', 'TP_DESCRIPTION','COMMENTAIRE','PC_ID','P_DATE_ENGAGEMENT','P_FIN','PC_DATE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

header('Content-Disposition: attachment; filename=prelevements.txt');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

$output=$FIRSTROW;

$query1="select p.P_ID, pc.PERIODE_CODE, p.P_NOM , p.P_PRENOM, pc.MONTANT, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE, 
        pc.COMMENTAIRE , pc.NUM_CHEQUE , pc.PC_ID,
        date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
        date_format(p.P_FIN,'%d-%m-%Y') P_FIN,
        date_format(p.P_DATE_ENGAGEMENT, '%c') MONTH_ENGAGEMENT,
        YEAR(p.P_DATE_ENGAGEMENT) YEAR_ENGAGEMENT,
        date_format(p.P_FIN, '%c') MONTH_FIN,
        YEAR(p.P_FIN) YEAR_FIN,
        p.MONTANT_REGUL,
        p.P_STATUT, p.P_SECTION, s.S_CODE, p.P_EMAIL, p.P_PROFESSION, tp.TP_ID, tp.TP_DESCRIPTION,
        cb.ETABLISSEMENT, cb.GUICHET, cb.COMPTE, cb.CODE_BANQUE,
        s.S_PARENT";

$queryadd=" from  section s, type_paiement tp,
     pompier p left join personnel_cotisation pc on ( pc.P_ID = p.P_ID and pc.ANNEE = '".$year."' and pc.PERIODE_CODE='".$periode."' )
     left join compte_bancaire cb on ( cb.CB_TYPE = 'P' and cb.CB_ID = p.P_ID and  pc.REMBOURSEMENT=0)
     where p.P_SECTION=s.S_ID
     and p.TP_ID = tp.TP_ID
     and p.P_NOM <> 'admin' 
     and p.P_STATUT <> 'EXT'
     and p.TP_ID = 1";
    
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
if ( $paid == 1 ) $queryadd .= "\nand pc.PC_DATE is not null";
else if ( $paid == 0 ) $queryadd .= "\nand pc.PC_DATE is  null";

if ( $include_old == 0 ) $queryadd .= "\nand p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";

$query1 .= $queryadd." order by ". $order;
if ( $order == "P_DATE_ENGAGEMENT" or  $order == "P_FIN" or  $order == "PC_DATE") $query1 .= " desc";

$result1=mysqli_query($dbc,$query1);
$TOTAL=0;
$err=0;
$i=0;

while ($row=@mysqli_fetch_array($result1)) {
    $P_NOM=strtoupper($row["P_NOM"]);
    $MONTANT=intval( 100 * $row["MONTANT"]);
    $TOTAL = $TOTAL + $MONTANT;
    $ETABLISSEMENT=$row["ETABLISSEMENT"];
    $GUICHET=$row["GUICHET"];
    $COMPTE=$row["COMPTE"];
    $CODE_BANQUE=$row["CODE_BANQUE"];
    
    if ( $MONTANT > 0 ) {
        $code=        str_pad($A2, 12);
        $numem=        str_pad($B, 13);
        $date=        str_pad('',5);
        $nome=        str_pad($P_NOM,24);
        $nomb=        str_pad($CODE_BANQUE,26);
        $e=            str_pad('E',6);
        $guichet=    str_pad($GUICHET,5);
        $compte=    str_pad($COMPTE,11);
        $montant=    str_pad($MONTANT,16,'0', STR_PAD_LEFT);
        $libelle=    str_pad($C,31);
        $etab=        str_pad($ETABLISSEMENT,5);
        $blanc=        str_pad('',6);
    
        if ( $COMPTE == '' or $ETABLISSEMENT == '' or $GUICHET == '' ) $err++;
        $i++;
        $output .= $code.$numem.$date.$nome.$nomb.$e.$guichet.$compte.$montant.$libelle.$etab.$blanc."\r\n";
    }
}

// last row                                      
$code=        str_pad($A3, 12);
$numem=        str_pad($B, 13);
$date=        str_pad('',5);
$nome=        str_pad('',24);
$nomb=        str_pad('',26);
$e=            str_pad('',6);
$guichet=    str_pad('',5);
$compte=    str_pad('',11);
$montant=    str_pad($TOTAL,16,'0', STR_PAD_LEFT);
$libelle=    str_pad('',31);
$etab=        str_pad('',5);
$blanc=        str_pad('',6);  

$output .= $code.$numem.$date.$nome.$nomb.$e.$guichet.$compte.$montant.$libelle.$etab.$blanc."\r\n";

if ( $err == 0 ) $errmsg="";
else $errmsg=
"=========================================================================\r
ATTENTION : $err coordonnees bancaires sont incompletes sur les $i\r
=========================================================================\r\n";

echo $errmsg.$output;


?>
