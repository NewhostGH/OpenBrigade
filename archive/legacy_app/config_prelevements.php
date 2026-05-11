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
get_session_parameters();

if ( ! isset($transaction_type)) 
	$transaction_type="prelevement";

// CONSTANTES PEUVENT ETRE MODIFIEES PAR UTILISATEUR
if ( $transaction_type == "prelevement" ) {
$A1='308';
$A2='608';
$A3='808';
$B='428791';
$C='COTIS MENSUELLE '.$cisname;
$D='FEDERATION AUTONOME SPP';
}
else if ( $transaction_type == "virement" ) {
$A1='0302';
$A2='0602';
$A3='0802';
$B='428791';
$C="VIREMENT DE ".$cisname;
$D='FEDERATION AUTONOME SPP';
$filter=$compte_a_debiter;
}

// DONNEES SPECIFIQUES A EXTRAIRE
$RIB=get_RIB_section("$filter");
list($_ETABLISSEMENT, $_GUICHET, $_COMPTE) = explode(";",get_RIB_section("$filter"));

// FORMAT du fichier
// code 				12
// num emetteur			13
// date					5
// nom/raison sociale	24
// nom banque			26
// E					6
// code guichet 		5
// compte				11
// montant en centimes	16
// libelle prelev		31
// code établissement 	5
// blanc 				6

$code=		str_pad($A1, 12);
$numem=		str_pad($B, 13);
$date=		date('dm').substr(date('Y'),3,1);
$nome=		str_pad($D,24);
$nomb=		str_pad('',26);
$e=			str_pad('E',6);
$guichet=	str_pad($_GUICHET,5);
$compte=	str_pad($_COMPTE,11);
$montant=	str_pad('',16);
$libelle=	str_pad('',31);
$etab=		str_pad($_ETABLISSEMENT,5);
$blanc=		str_pad('',6);

$FIRSTROW=$code.$numem.$date.$nome.$nomb.$e.$guichet.$compte.$montant.$libelle.$etab.$blanc."\r\n";

?>
