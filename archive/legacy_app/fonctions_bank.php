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

//=====================================================================
// fonctions RIB
//=====================================================================

function RIB_calculate ($Banque, $Guichet, $Compte) {
    $Compte_Num = strtr (strtoupper ($Compte),
               "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
               "12345678912345678923456789");
    $Clef = 97 - 
        (int) fmod (89 * $Banque  + 
            15 * $Guichet +
            3  * $Compte_Num, 97);
    
    $Clef = str_pad($Clef, 2, "0", STR_PAD_LEFT);
    return $Clef;
}

function get_RIB ($type, $id) {
    global $dbc;
    $query ="select concat (CODE_BANQUE,' ',ETABLISSEMENT,'-',GUICHET,'-',COMPTE,'-',CLE_RIB) from compte_bancaire where CB_TYPE = '".$type."' and CB_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
       $row=mysqli_fetch_array($result);
       return $row[0];
}

function get_RIB_section($section) {
    global $dbc;
    $query ="select concat (ETABLISSEMENT,';',GUICHET,';',COMPTE) from compte_bancaire where CB_TYPE = 'S' and CB_ID=".intval($section);
    $result=mysqli_query($dbc,$query);
       $row=mysqli_fetch_array($result);
       return $row[0];
}

//=====================================================================
// fonctions IBAN
//=====================================================================
function Rib2Iban($codebanque,$codeguichet,$numerocompte,$cle){
        $charConversion = array("A" => "10","B" => "11","C" => "12","D" => "13","E" => "14","F" => "15","G" => "16","H" => "17",
        "I" => "18","J" => "19","K" => "20","L" => "21","M" => "22","N" => "23","O" => "24","P" => "25","Q" => "26",
        "R" => "27","S" => "28","T" => "29","U" => "30","V" => "31","W" => "32","X" => "33","Y" => "34","Z" => "35");
        $tmpiban = strtr(strtoupper($codebanque.$codeguichet.$numerocompte.$cle)."FR00",$charConversion);
        // Soustraction du modulo 97 de l'IBAN temporaire à 98
        $cleiban = strval(98 - intval(my_bcmod($tmpiban,"97")));
        if (strlen($cleiban) == 1)
                $cleiban = "0".$cleiban;
        return "FR".$cleiban.$codebanque.$codeguichet.$numerocompte.$cle;
}

function my_bcmod( $x, $y ){
    // how many numbers to take at once? carefull not to exceed (int) 
    $take = 5;
    $mod = '';
    do {
        $a = (int)$mod.substr( $x, 0, $take );
        $x = substr( $x, $take );
        $mod = $a % $y;
    } 
    while ( strlen($x) );

    return (int)$mod;
} 

function get_IBAN ($type, $id) {
    global $dbc;
    $query ="select IBAN from compte_bancaire where CB_TYPE = '".$type."' and CB_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return $row[0];
}

function display_IBAN($iban) {
    $out=substr($iban,0,4)."-".substr($iban,4,4)."-".substr($iban,8,4)."-".substr($iban,12,4)."-".substr($iban,16,4)."-".substr($iban,20,4)."-".substr($iban,24,4)."-".substr($iban,28,4);
    $out = rtrim($out);
    $out =rtrim($out,'-');
    return $out;
}

function isValidIban($iban) {
    /*Régles de validation par pays*/
    static $rules = array(
    'AL'=>'[0-9]{8}[0-9A-Z]{16}',
    'AD'=>'[0-9]{8}[0-9A-Z]{12}',
    'AT'=>'[0-9]{16}',
    'BE'=>'[0-9]{12}',
    'BA'=>'[0-9]{16}',
    'BG'=>'[A-Z]{4}[0-9]{6}[0-9A-Z]{8}',
    'HR'=>'[0-9]{17}',
    'CY'=>'[0-9]{8}[0-9A-Z]{16}',
    'CZ'=>'[0-9]{20}',
    'DK'=>'[0-9]{14}',
    'EE'=>'[0-9]{16}',
    'FO'=>'[0-9]{14}',
    'FI'=>'[0-9]{14}',
    'FR'=>'[0-9]{10}[0-9A-Z]{11}[0-9]{2}',
    'GE'=>'[0-9A-Z]{2}[0-9]{16}',
    'DE'=>'[0-9]{18}',
    'GI'=>'[A-Z]{4}[0-9A-Z]{15}',
    'GR'=>'[0-9]{7}[0-9A-Z]{16}',
    'GL'=>'[0-9]{14}',
    'HU'=>'[0-9]{24}',
    'IS'=>'[0-9]{22}',
    'IE'=>'[0-9A-Z]{4}[0-9]{14}',
    'IL'=>'[0-9]{19}',
    'IT'=>'[A-Z][0-9]{10}[0-9A-Z]{12}',
    'KZ'=>'[0-9]{3}[0-9A-Z]{3}[0-9]{10}',
    'KW'=>'[A-Z]{4}[0-9]{22}',
    'LV'=>'[A-Z]{4}[0-9A-Z]{13}',
    'LB'=>'[0-9]{4}[0-9A-Z]{20}',
    'LI'=>'[0-9]{5}[0-9A-Z]{12}',
    'LT'=>'[0-9]{16}',
    'LU'=>'[0-9]{3}[0-9A-Z]{13}',
    'MK'=>'[0-9]{3}[0-9A-Z]{10}[0-9]{2}',
    'MT'=>'[A-Z]{4}[0-9]{5}[0-9A-Z]{18}',
    'MR'=>'[0-9]{23}',
    'MU'=>'[A-Z]{4}[0-9]{19}[A-Z]{3}',
    'MC'=>'[0-9]{10}[0-9A-Z]{11}[0-9]{2}',
    'ME'=>'[0-9]{18}',
    'NL'=>'[A-Z]{4}[0-9]{10}',
    'NO'=>'[0-9]{11}',
    'PL'=>'[0-9]{24}',
    'PT'=>'[0-9]{21}',
    'RO'=>'[A-Z]{4}[0-9A-Z]{16}',
    'SM'=>'[A-Z][0-9]{10}[0-9A-Z]{12}',
    'SA'=>'[0-9]{2}[0-9A-Z]{18}',
    'RS'=>'[0-9]{18}',
    'SK'=>'[0-9]{20}',
    'SI'=>'[0-9]{15}',
    'ES'=>'[0-9]{20}',
    'SE'=>'[0-9]{20}',
    'CH'=>'[0-9]{5}[0-9A-Z]{12}',
    'TN'=>'[0-9]{20}',
    'TR'=>'[0-9]{5}[0-9A-Z]{17}',
    'AE'=>'[0-9]{19}',
    'GB'=>'[A-Z]{4}[0-9]{14}'
    );
    /*On vérifie la longueur minimale*/
    if(mb_strlen($iban) < 18) {
        return false;
    }
    /*On récupère le code ISO du pays*/
    $ctr = substr($iban,0,2);
    if(isset($rules[$ctr]) === false) {
        return false;
    }
    /*On récupère la règle de validation en fonction du pays*/
    $check = substr($iban,4);
    /*Si la règle n'est pas bonne l'IBAN n'est pas valide*/
    if(preg_match('~'.$rules[$ctr].'~',$check) !== 1) {
        return false;
    }
    /*On récupère la chaine qui permet de calculer la validation*/
    $check = $check.substr($iban,0,4);
    /*On remplace les caractères alpha par leurs valeurs décimales*/
    $check = str_replace(
        array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T','U', 'V', 'W', 'X', 'Y', 'Z'),
        array('10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35'),
        $check
    );
    /*On effectue la vérification finale*/
    return my_bcmod($check,"97") == 1;
}



//=====================================================================
// fonctions BIC
//=====================================================================

function get_BIC ($type, $id) {
    global $dbc;
    $query ="select BIC from compte_bancaire where CB_TYPE = '".$type."' and CB_ID=".intval($id);
    $result=mysqli_query($dbc,$query);
       $row=mysqli_fetch_array($result);
       return $row[0];
}

function etablissement_to_bic($etablissement) {
    global $dbc;
    $query = "select BIC from migration_bic where ETABLISSEMENT='".$etablissement."'";
    $result=mysqli_query($dbc,$query);
       $row=mysqli_fetch_array($result);
    if ( $row[0] == "" ) {
        $query = "select BIC from compte_bancaire where ETABLISSEMENT='".$etablissement."' and BIC <> ''";
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
    }
    return $row[0];
}

?>
