<?php

  # written by: Nicolas MARCHE, Jean-Pierre KUNTZ
  # contact: nico.marche@free.fr
  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 2.9

  # Copyright (C) 2004, 2013 Nicolas MARCHE
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

include_once ("../../config.php");

// script to be used by cronjob in command line. Or for test purpose in a browser  
if(! defined('STDIN')) {
  // for test purpose only
  check_all(14);
}

$query="select CB_TYPE,CB_ID,ETABLISSEMENT,GUICHET,COMPTE,CLE_RIB,CODE_BANQUE,BIC,IBAN,UPDATE_DATE 
		from compte_bancaire where ( CHAR_LENGTH(IBAN) = 0 or IBAN is null or CHAR_LENGTH(BIC) = 0 or CHAR_LENGTH(CLE_RIB) = 0) and COMPTE is not null and COMPTE <> ''";
$result=mysqli_query($dbc,$query);

$i=0;
while ( $row=mysqli_fetch_array($result)) {
    $CB_TYPE=$row["CB_TYPE"];
    $CB_ID=$row["CB_ID"];
	$ETABLISSEMENT=$row["ETABLISSEMENT"];
	$GUICHET=$row["GUICHET"];
	$COMPTE=$row["COMPTE"];
	$CLE_RIB=$row["CLE_RIB"];
	$CODE_BANQUE=$row["CODE_BANQUE"];
	$BIC=$row["BIC"];
	$IBAN=$row["UPDATE_DATE"];
	$NEWCLE_RIB=$CLE_RIB;
	
	if ( $NEWCLE_RIB == '' ) {
		$NEWCLE_RIB=RIB_calculate($ETABLISSEMENT, $GUICHET, $COMPTE);
	}
	$NEWIBAN=Rib2Iban($ETABLISSEMENT,$GUICHET,$COMPTE,$NEWCLE_RIB);
	$NEWBIC=etablissement_to_bic($ETABLISSEMENT);
	
	if ( $BIC == '' ) {
		$query2="update compte_bancaire 
			set BIC='".$NEWBIC."'
			where CB_TYPE = '".$CB_TYPE."' 
			and CB_ID='".$CB_ID."'";
		$result2=mysqli_query($dbc,$query2);
	}
	if ( $IBAN == '' ) {
		$query2="update compte_bancaire 
			set IBAN='".$NEWIBAN."'
			where CB_TYPE = '".$CB_TYPE."' 
			and CB_ID='".$CB_ID."'";
		$result2=mysqli_query($dbc,$query2);			
	}
	if ( $CLE_RIB == '' ) {
		$query2="update compte_bancaire 
			set CLE_RIB='".$NEWCLE_RIB."'
			where CB_TYPE = '".$CB_TYPE."' 
			and CB_ID='".$CB_ID."'";
		$result2=mysqli_query($dbc,$query2);
	}
	if ( $BIC == '' or $IBAN == '' or  $CLE_RIB == '' )
		echo "$CB_TYPE - $CB_ID - $ETABLISSEMENT _ $GUICHET - $COMPTE - $NEWCLE_RIB - $NEWIBAN - $NEWBIC - $CODE_BANQUE <br>";
	$i++;
	if ( $i == 200 ) exit;
}

?>
