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
$transaction_type = "virement";
include_once ("config_prelevements.php");

check_all(53);
$id=$_SESSION['id'];
$highestsection=get_highest_section_where_granted($_SESSION['id'],53);
get_session_parameters();
// vérifier qu'on a les droits d'afficher pour cette section
$list = preg_split('/,/' , get_family("$highestsection"));
if (! in_array($filter,$list) and ! check_rights($id, 24)) $filter=$highestsection;

$possibleorders= array('P_STATUT','P_NOM','P_SECTION', 'P_PROFESSION', 'MONTANT','COMMENTAIRE','PC_ID','P_DATE_ENGAGEMENT','P_FIN','PC_DATE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='P_NOM';

header('Content-Disposition: attachment; filename=virements.txt');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

$output=$FIRSTROW;


$query1 = "select p.P_ID, p.P_NOM, p.P_PRENOM, pc.PC_ID, date_format(pc.PC_DATE,'%d-%m-%Y') PC_DATE,
			pc.COMMENTAIRE, pc.MONTANT, s.S_ID, s.S_CODE, p.P_PROFESSION,
			date_format(p.P_DATE_ENGAGEMENT,'%d-%m-%Y') P_DATE_ENGAGEMENT,
			date_format(p.P_FIN,'%d-%m-%Y') P_FIN, 
			pc.ETABLISSEMENT, pc.GUICHET, pc.COMPTE, pc.CODE_BANQUE, pc.REMBOURSEMENT, pc.COMPTE_DEBITE";

$queryadd=" from personnel_cotisation pc, pompier p, section s
			where p.P_ID = pc.P_ID
			and p.P_SECTION = s.S_ID
			and pc.REMBOURSEMENT = 1
			and pc.TP_ID=2";
	
if ( $subsections == 1 ) {
	if ( $filter > 0 ) 
  	$queryadd .= "\n and p.P_SECTION in (".get_family("$filter").")";
}
else {
  	$queryadd .= "\n and p.P_SECTION =".$filter;
}

if ( $compte_a_debiter > 0 ) {
	$queryadd .= "\n and pc.COMPTE_DEBITE =".$compte_a_debiter;
}

if ( $include_old == 0 ) $queryadd .= "\n and p.P_OLD_MEMBER = 0 and p.SUSPENDU = 0";


if ( $dtdb <> "" ) {
	$tmp=explode ( "-",$dtdb); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
	$queryadd .="\n and pc.PC_DATE  >= '$year1-$month1-$day1'";
}
if ( $dtfn <> "" ) {
	$tmp=explode ( "-",$dtfn); $month2=$tmp[1]; $day2=$tmp[0]; $year2=$tmp[2];
	$queryadd .="\n and pc.PC_DATE <= '$year2-$month2-$day2'";
}

$query1 .= $queryadd." order by ". $order;
if ( $order == "P_DATE_ENGAGEMENT" or  $order == "P_FIN" or  $order == "PC_DATE" or  $order == "MONTANT") $query1 .= " desc";


$result1=mysqli_query($dbc,$query1);
$TOTAL=0;
$err=0;
$i=0;

while ($row=@mysqli_fetch_array($result1)) {
    $P_NOM=strtoupper($row["P_NOM"]);
	$MONTANT=intval( 100 * $row["MONTANT"]);
	$ETABLISSEMENT=$row["ETABLISSEMENT"];
	$GUICHET=$row["GUICHET"];
	$COMPTE=$row["COMPTE"];
	$CODE_BANQUE=$row["CODE_BANQUE"];
	$COMPTE_DEBITE=$row["COMPTE_DEBITE"];
	$COMMENTAIRE=substr(remove_returns($row["COMMENTAIRE"]),0,31);	
	if ( $MONTANT > 0 ) {
		$code=		str_pad($A2, 12);
		$numem=		str_pad($B, 13);
		$date=		str_pad('',5);
		$nome=		str_pad($P_NOM,24);
		$nomb=		str_pad($CODE_BANQUE,26);
		$e=			str_pad('E',6);
		$guichet=	str_pad($GUICHET,5);
		$compte=	str_pad($COMPTE,11);
		$montant=	str_pad($MONTANT,16,'0', STR_PAD_LEFT);
		if ( $COMMENTAIRE <> "" ) $libelle=	str_pad($COMMENTAIRE,31);
		else $libelle=	str_pad($C,31);
		$etab=		str_pad($ETABLISSEMENT,5);
		$blanc=		str_pad('',6);
		
		$TOTAL = $TOTAL + $MONTANT;
	
		if ( $COMPTE == '' or $ETABLISSEMENT == '' or $GUICHET == '' ) $err++;
		$i++;
		$output .= $code.$numem.$date.$nome.$nomb.$e.$guichet.$compte.$montant.$libelle.$etab.$blanc."\r\n";
	}
}

// last row                                      
$code=		str_pad($A3, 12);
$numem=		str_pad($B, 13);
$date=		str_pad('',5);
$nome=		str_pad('',24);
$nomb=		str_pad('',26);
$e=			str_pad('',6);
$guichet=	str_pad('',5);
$compte=	str_pad('',11);
$montant=	str_pad($TOTAL,16,'0', STR_PAD_LEFT);
$libelle=	str_pad('',31);
$etab=		str_pad('',5);
$blanc=		str_pad('',6);  

$output .= $code.$numem.$date.$nome.$nomb.$e.$guichet.$compte.$montant.$libelle.$etab.$blanc."\r\n";

if ( $err == 0 ) $errmsg="";
else $errmsg=
"=========================================================================\r
ATTENTION : $err coordonnees bancaires sont incompletes sur les $i\r
=========================================================================\r\n";

echo $errmsg.$output;


?>
