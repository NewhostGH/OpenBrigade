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
// import functions
//=====================================================================

function get_curl_errors($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function cleanup_data($string) {
    global $dbc;
    $out=utf8_decode($string);
    $out=str_replace('\\','',$out);
    $out=str_replace('"','',$out);
    $out=str_replace('\t',' ',$out);
    $out=rtrim($out);
    $out=ltrim($out);
    $out=secure_input($dbc,$out);
    return $out;
}

function clean_phone($string) {
    $string=str_replace("-","",$string);
    $string=str_replace(".","",$string);
    $string=str_replace(" ","",$string);
    $string=str_replace("_","",$string);
    $string=str_replace("/","",$string);
    return cleanup_data($string);
}

function get_nb_records_api($cli=0) {
    global $import_api_url, $import_api_token;
    $url=$import_api_url."/licencie?token=".$import_api_token."&length=1";
    $json = @file_get_contents($url)
        or die("ERROR - cannot retrieve data from $url");
    if ($json === false) {
        if ( $cli ) print "ERROR - l'appel a l'API licencie retourne l'erreur suivante\n".get_curl_errors($url)."\n";
        else print  "<div id='msgError' class='alert alert-danger' role='alert'><strong>ERREUR </strong> Impossible d'accéder à l'URL de l'API ".$import_api_url.", vérifier le token. <br>".get_curl_errors($import_api_url)."</div>";
        return 0;
    }
    $array=json_decode($json, true);
    $result=$array['recordsFiltered'];
    return intval($result);
}

//=====================================================================
// import one user, parameter pid = ID in external application
//=====================================================================

function import_one_user($pid) {
    $pid=intval($pid);
    global $import_api_url, $import_api_token;
            
    $url=$import_api_url."/licencie/:id?id=".$pid."&token=".$import_api_token;
    $out = "<div align=center>";
    
    $json = file_get_contents($url)
        or die("ERROR - cannot retrieve data from $url");
    if ($json === false) {
        $out .= "<div id='msgError' class='alert alert-danger' role='alert'><strong>ERREUR </strong> id licencié $pid probablement non trouvé<br>".get_curl_errors($url)."</div>";
    }
    else {
        $array=json_decode($json, true);
        $result=$array['success'];
    
        if ( $result == 1 ) {
            $dataset = $array['data'];
            $out .= update_ebrigade_user($pid, $dataset);
        }
    }
    $out .= "</div>";
    print $out;
}


//=====================================================================
// import all users
//=====================================================================

function import_multiple_users($limit,$start,$cli=0) {
    global $import_api_url, $import_api_token, $starttime;
    global $total_updated, $total_inserted, $total_unchanged, $total_mapping, $total_errors;
    
    $max_block=2000;
    $total_updated=0;
    $total_inserted=0;
    $total_unchanged=0;
    $total_mapping=0;
    $total_errors=0;
    
    if ( $cli ) {
        $br="\n";
        $b1="";
        $b2="";
    }
    else {
        $br="<br>";
        $b1="<b>";
        $b2="</b>";
    }
    if ( ! $cli ) print "<div align='left' class='progressbox' id='importconsole'>";

    // traitement par blocs de $max_bloc max
    $limit = intval($limit);
    $nbrecords = get_nb_records_api($cli);
    if ( $limit == 0 or $limit > $nbrecords ) $limit = $nbrecords;
    if ( $limit > $max_block ) $block = $max_block;
    else $block = $limit;
    $reste=$limit;
    $i=0;
    
    $s1="";$s2="";
    if ( intval($start) > 0 ) $s1=" a partir de la #".$start;
    if ( $limit > $max_block ) $s2 =" par blocs de ".$max_block;
    print $b1."Import de ".$limit." fiches ".$s1.$s2.$b2.$br;
    
    while ( $reste > 0 ) {
        $url=$import_api_url."/licencie?token=".$import_api_token;
        if ( $block > $reste ) 
            $block=$reste;
        $url .= "&length=".$block;
        $url .= "&start=".$start;
    
        $json = @file_get_contents($url)
            or die("ERROR - cannot retrieve data from $url");
        if ($json === false) {
            get_nb_records_api($cli=0);
        }
        else {
            $array=json_decode($json, true);
            $result=$array['success'];
            if ( $result == 1 ) {
                optimize_table_pompier();
                if ( $limit > $max_block ) {
                    if ( $block == 0 ) $t = 'toutes les';
                    else $t = $block;
                    if ( intval($start) > 0 ) $s=" a partir de la #".$start;
                    else $s="";
                    print $b1."Import de ".$t." fiches ".$s.$b2.$br;
                }
                $fiches = $array['data'];
                if (ob_get_level() == 0) ob_start();
                foreach ($fiches as $dataset ){
                    $i++;
                    print update_ebrigade_user($dataset["Id"], $dataset, $cli);
                    if ( $i % 10 == 0) {
                        ob_flush();
                        flush();
                    }
                }
            }
        }
        $reste = $reste - $block;
        $start = $start + $block;
    }
    
    ob_end_flush();
    optimize_table_pompier();
    $endtime=get_time();
    $totaltime = round(($endtime - $starttime),2);

    print $br.$b1."TOTAL enregistrements API : ".$i.$b2.$br;
    print "Nombre mappings : ".$total_mapping.$br;
    print "Nombre updates : ".$total_updated.$br;
    print "Nombre insert : ".$total_inserted.$br;
    print "Nombre inchanges : ".$total_unchanged.$br;
    print "Nombre erreurs : ".$total_errors.$br;
    print "Temps d'execution : ".$totaltime." secondes".$br;
    
    if (! $cli ) print "</div><p>";
    return 0;
}

//=====================================================================
// update ebrigade, update or insert
//=====================================================================

function update_ebrigade_user($pid, $dataset,$cli=0) {
    $id=intval(@$_SESSION['id']);
    $pid=intval($pid);
    
    if ( $cli ) $br="\n";
    else $br="<br>";
    
    global $affiliations,$cisname;
    global $dbc,$application_title,$password_length, $log_actions;
    global $total_updated, $total_inserted, $total_unchanged, $total_mapping, $total_errors;
    
    $NumeroLcs=cleanup_data($dataset['NumeroLcs']);
    $PrenomLcs=cleanup_data($dataset['PrenomLcs']);
    $NomLcs=cleanup_data($dataset['NomLcs']);
    $DateNaissanceLcs=cleanup_data($dataset['DateNaissanceLcs']);
    $SexeLcs=cleanup_data($dataset['SexeLcs']);
    $LieuNaissanceLcs=cleanup_data($dataset['LieuNaissanceLcs']);
    $dptNaissanceLcs=cleanup_data($dataset['dptNaissanceLcs']);
    $PaysLcs=cleanup_data($dataset['PaysLcs']); // not used
    $NationaliteLcs=cleanup_data($dataset['NationaliteLcs']);
    $ClubLcs=cleanup_data($dataset['ClubLcs']);
    $TelephoneLcs=clean_phone($dataset['TelephoneLcs']);
    $PortableLcs=clean_phone($dataset['PortableLcs']);
    $EmailLcs=cleanup_data($dataset['EmailLcs']);
    $AdresseLcs=cleanup_data($dataset['AdresseLcs']);
    $AdresseSuiteLcs=cleanup_data($dataset['AdresseSuiteLcs']);
    $CodePostalLcs=cleanup_data($dataset['CodePostalLcs']);
    $VilleLcs=cleanup_data($dataset['VilleLcs']);
    $CommentaireLcs=cleanup_data($dataset['CommentaireLcs']);
    $DateCreaLcs=cleanup_data($dataset['DateCreaLcs']);
    $DateModifLcs=cleanup_data($dataset['DateModifLcs']);
    $DateValiditeLcs=cleanup_data($dataset['DateValiditeLcs']);
    
    if (isset ($dataset['PasswordLcs']))
        $PasswordLcs= secure_input($dbc,$dataset['PasswordLcs']);
    else
        $PasswordLcs="";
    
    if ( $SexeLcs == 'Homme' ) {
        $Sexe='M'; $Civilite='1';
    }
    else {
        $Sexe='F'; $Civilite='2';
    }
    $Address = $AdresseLcs;
    if ( $AdresseSuiteLcs <> '' ) $Address .="\n".$AdresseSuiteLcs;
    
    $Nationalite=map_nationalite($NationaliteLcs);
    $Departement=map_departement($dptNaissanceLcs);
    $Section = get_section_from_club($ClubLcs);

    $query="select count(1) as NB from pompier where ID_API=".$pid;
    $res=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($res);
    $NB=$row["NB"];

    $out="";
    // -------------------------------------------------------------
    // cas où la fiche eBrigade existe mais sans le api
    // -------------------------------------------------------------
    if ( $NB == 0 ) {
        // y a t'il une fiche active avec le numéro de licence.
        $query="select count(1) as NB from pompier where ( ID_API is null or ID_API = '' ) and P_LICENCE=\"".$NumeroLcs."\"";
        $res=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($res);
        $NB2=$row["NB"];
        
        if ( $NB2 > 1 ) { // si doublons de licences, on essaye de supprimer la licence des fiches anciennes
            $query="select count(1) as NB from pompier where ( ID_API is null or ID_API = '' ) and P_OLD_MEMBER=0 and P_LICENCE=\"".$NumeroLcs."\"";
            $res=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($res);
            $NB3=$row["NB"];
            if ( $NB3 > 0 ) {
                // enlever la licence des anciennes fiches
                $query="update pompier set P_LICENCE=null where P_OLD_MEMBER > 0 and P_LICENCE=\"".$NumeroLcs."\"";
                $row=mysqli_fetch_array($res);
                $NB2=$NB3;
            }
        }
        
        if ( $NB2 == 0 ) { 
        // on peut essayer de faire le mapping sur un ancien
            $query="select count(1) as NB from pompier where P_OLD_MEMBER > 0 and ( ID_API is null or ID_API = '' ) and P_LICENCE=\"".$NumeroLcs."\"";
            $res=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($res);
            $NB2=$row["NB"];
        }
        // --------------------
        // erreur doublons LIC
        // --------------------
        if ( $NB2 > 1 ) {
            $out .= "ERROR duplicate LIC NUM (x".$NB2.")- $pid - $NomLcs $PrenomLcs - $NumeroLcs".$br;
        }
        else if ( $NB2 == 1 ) { // si oui on lui attribue l'ID_API
            $query="update pompier set ID_API=".$pid." where P_LICENCE=\"".$NumeroLcs."\"";
            $res=mysqli_query($dbc,$query);
            $errmsg = mysqli_error($dbc);
            if ( $errmsg <> '' ) {
                $out .="ERROR in update ID_API ".$pid." - ".$errmsg.$br;
                $total_errors++;
            }
            if ( mysqli_affected_rows($dbc) > 0 ) {
                $NB=1;
                $total_mapping++;
                $out .= "SUCCESS mapping LICENCE - $pid - $NomLcs $PrenomLcs - $NumeroLcs".$br;
                if ($log_actions == 1) {
                    $P_ID=get_code_from_api($pid);
                    insert_log('UPDP', $P_ID, "mapping via api licence");
                }
            }
        }
        else if ( $NB2 == 0 ) { // sinon on recherche un homonyme actif avec même date de naissance
            $query="update pompier set ID_API=".$pid.", P_LICENCE = \"".$NumeroLcs."\" 
                    where P_NOM=\"".strtolower($NomLcs)."\" 
                    and P_PRENOM=\"".strtolower($PrenomLcs)."\"
                    and P_BIRTHDATE=".revert_date($DateNaissanceLcs)."
                    and ( ID_API is null or ID_API = '' )
                    and P_OLD_MEMBER=0";
            $res=mysqli_query($dbc,$query);
            $errmsg = mysqli_error($dbc);
            if ( $errmsg <> '' ) {
                $out .="ERROR in update ID_API and LIC for ".$pid." - ".$errmsg.$br;
                $total_errors++;
            }
            if ( mysqli_affected_rows($dbc) > 0 ) {
                $NB=1;
                $total_mapping++;
                $out .= "SUCCESS mapping NAME/BIRTHDATE - $pid - $NomLcs $PrenomLcs - $NumeroLcs".$br;
                if ($log_actions == 1) {
                    $P_ID=get_code_from_api($pid);
                    insert_log('UPDP', $P_ID, "mapping via api nom/prénom/date");
                }
            }
            else { 
                // sinon on recherche un homonyme ancien avec même date de naissance et on le réactive
                $query="update pompier set ID_API=".$pid.", P_LICENCE = \"".$NumeroLcs."\", P_OLD_MEMBER = 0, GP_ID=0, GP_ID2=0
                    where P_NOM=\"".strtolower($NomLcs)."\" 
                    and P_PRENOM=\"".strtolower($PrenomLcs)."\"
                    and P_BIRTHDATE=".revert_date($DateNaissanceLcs)."
                    and ( ID_API is null or ID_API = '' )
                    and P_OLD_MEMBER > 0";
                    
                $res=mysqli_query($dbc,$query);
                $errmsg = mysqli_error($dbc);
                if ( $errmsg <> '' ) {
                    $out .="ERROR in update ID_API and LIC for ".$pid." - ".$errmsg.$br;
                    $total_errors++;
                }
                if ( mysqli_affected_rows($dbc) > 0 ) {
                    $NB=1;
                    $total_mapping++;
                    $out .= "SUCCESS mapping NAME/BIRTHDATE reactivate - $pid - $NomLcs $PrenomLcs - $NumeroLcs".$br;
                    if ($log_actions == 1) {
                        $P_ID=get_code_from_api($pid);
                        insert_log('UPDP', $P_ID, "mapping via api nom/prénom/date reactivé");
                    }
                }
                else {
                    // au pire juste un homonyme sans date de naissance renseignée
                    $query="select P_ID as NB from pompier where P_OLD_MEMBER=0 and P_NOM=\"".strtolower($NomLcs)."\" and P_PRENOM=\"".strtolower($PrenomLcs)." and ( ID_API is null or ID_API = '' ) and P_BIRTHDATE is null";
                    $res=mysqli_query($dbc,$query);
                    if ( @mysqli_num_rows($res) == 1 ) {
                        $row=mysqli_fetch_array($res);
                        $query="update pompier set ID_API=".$pid.", P_LICENCE = \"".$NumeroLcs."\", where P_ID=".$row["P_ID"];
                        $res=mysqli_query($dbc,$query);
                        $errmsg = mysqli_error($dbc);
                        if ( $errmsg <> '' ) {
                            $out .="ERROR in update ID_API and LIC for ".$pid." - ".$errmsg.$br;
                            $total_errors++;
                        }
                        if ( mysqli_affected_rows($dbc) > 0 ) {
                            $NB=1;
                            $total_mapping++;
                            $out .= "SUCCESS mapping NAME/NO BIRTHDATE - $pid - $NomLcs $PrenomLcs - $NumeroLcs".$br;
                            if ($log_actions == 1) {
                                $P_ID=get_code_from_api($pid);
                                insert_log('UPDP', $P_ID, "mapping via api nom/prénom");
                            }
                        }
                    }
                }
            }
        }
    }

    // ------------
    // update
    // ------------
    if ( $NB == 1 ) {
        $query="update pompier set 
                P_NOM=\"".strtolower($NomLcs)."\",
                P_PRENOM=\"".strtolower($PrenomLcs)."\",
                P_BIRTHDATE=".revert_date($DateNaissanceLcs).",
                P_BIRTHPLACE=\"".$LieuNaissanceLcs."\",
                P_ADDRESS=\"".$Address."\",
                P_ZIP_CODE=\"".$CodePostalLcs."\",
                P_CITY=\"".$VilleLcs."\",
                P_EMAIL=\"".$EmailLcs."\",
                P_PHONE=\"".$PortableLcs."\",
                P_PHONE2=\"".$TelephoneLcs."\",
                P_SEXE='".$Sexe."',
                P_CIVILITE=".$Civilite.",
                P_LICENCE=\"".$NumeroLcs."\",
                P_LICENCE_DATE=".revert_date($DateModifLcs).",
                P_LICENCE_EXPIRY=".revert_date($DateValiditeLcs).",
                P_SECTION=".$Section;
        if ( $Nationalite <> 0 ) $query .= ", P_PAYS=".$Nationalite;
        if ( $Departement <> 0 ) $query .= ", P_BIRTH_DEP=".$Departement;
        if ( $PasswordLcs <> '' ) $query .= ", P_MDP=\"".$PasswordLcs."\"";

        $query .=" where ID_API=".$pid;
        $res=mysqli_query($dbc,$query);
        $updated = mysqli_affected_rows($dbc);
        $errmsg = mysqli_error($dbc);
        if ( $errmsg <> '' ) {
            $out .="ERROR in update ".$pid." - ".$errmsg.$br;
            $total_errors++;
        }
        if ( $updated == 1 ) {
            $t='SUCCESS update';
            $total_updated++;
            if ($log_actions == 1) {
                $P_ID=get_code_from_api($pid);
                insert_log('UPDP', $P_ID, "update via api");
            }
        }
        else {
            $t='INFO no change';
            $total_unchanged++;
        }
        if ( $t <> 'INFO no change' or ! $cli )
            $out .= $t." - $pid - $NomLcs $PrenomLcs - $NumeroLcs".$br;
        //$out .= $query."<p>";
    }
    // ------------
    // insert
    // ------------
    if ( $NB == 0 ) {
        if ( $Nationalite == 0 ) $Nationalite = 'null';
        if ( $Departement == 0 ) $Departement = 'null';
        else $Departement = "\"".$Departement."\"";
        $mylength=max($password_length , 8);
        if ( $PasswordLcs == '' ) {
            $mypass=generatePassword($mylength);
            $hash = my_create_hash($mypass);
        }
        else 
            $hash = $PasswordLcs;
        $identifiant= generate_identifiant($PrenomLcs,$NomLcs,$Departement);
        
        $query="insert into pompier (P_CODE,P_PRENOM,P_NOM,P_SEXE,P_CIVILITE,
        P_GRADE,P_PROFESSION,P_STATUT,P_MDP,P_DATE_ENGAGEMENT,
        P_BIRTHDATE,P_BIRTHPLACE,P_BIRTH_DEP,P_SECTION,GP_ID,
        GP_ID2, P_EMAIL, P_PHONE, P_PHONE2, P_ADDRESS,P_CITY,
        P_ZIP_CODE,P_HIDE,P_CREATE_DATE,TP_ID,P_PAYS,P_LICENCE, P_LICENCE_DATE, P_LICENCE_EXPIRY, ID_API)
        values (\"".$identifiant."\",LOWER(\"".$PrenomLcs."\"),LOWER(\"".$NomLcs."\"),'".$Sexe."','".$Civilite."',
            '-','SPP','BEN',\"".$hash."\",".revert_date($DateCreaLcs).",
            ".revert_date($DateNaissanceLcs).",\"".$LieuNaissanceLcs."\",".$Departement.",".$Section.",0,
            0, \"".$EmailLcs."\",\"".$PortableLcs."\",\"".$TelephoneLcs."\",\"".$Address."\",\"".strtoupper($VilleLcs)."\",
            \"".$CodePostalLcs."\",1, CURDATE(), 0, ".$Nationalite.",\"".$NumeroLcs."\",".revert_date($DateModifLcs).",".revert_date($DateValiditeLcs).", ".$pid."
        )";
        $res=mysqli_query($dbc,$query);
        $inserted = mysqli_affected_rows($dbc);
        $errmsg = mysqli_error($dbc);
        if ( $errmsg <> '' ) {
            $out .='ERROR in insert $pid - '.$errmsg.$br;
            $total_errors++;
        }
        if ( $inserted == 1 ) {
            $t='SUCCESS';
            $total_inserted++;
            if ($log_actions == 1) {
                $P_ID=get_code($identifiant);
                insert_log('INSP', $P_ID, "insert via API");
            }
        }
        else $t='ERROR';
        $out .= $t." insert - $pid - $NomLcs $PrenomLcs - $NumeroLcs".$br;
        
        if ( $EmailLcs <> "" and $inserted == 1 ) {
            $message  = "Bonjour ".ucfirst($PrenomLcs).",\n";
            $n=ucfirst($PrenomLcs)." ".strtoupper($NomLcs);
            $subject  = "Nouveau compte licencié pour - ".$n;
            $message .= "Je viens de créer votre compte personnel sur ".$application_title."\n";
            $message .= "identifiant: ".$identifiant."\n";
            if ( $PasswordLcs == '' )
                $message .= "mot de passe: ".$mypass."\n";
            else
                $message .= "mot de passe identique à celui du site fédéral.\n";
            
            $query="insert into mailer(MAILDATE, MAILTO, SENDERNAME, SENDERMAIL, SUBJECT, MESSAGE)
                    values( NOW(), \"".$EmailLcs ."\", \"Admin ".$cisname."\",\"donotreply\",\"".$subject."\", \"".$message."\")";
            $result=mysqli_query($dbc,$query);
        }
    }
    if ( $cli == 0 ) $out = colorize($out);
    return $out;
}

function colorize($text) {
    $out = str_replace("SUCCESS","<span style='color:green;font-weight:bold;'>SUCCESS</span>", $text);
    $out = str_replace("INFO","<span style='color:blue;font-weight:bold;'>INFO</span>", $out);
    $out = str_replace("ERROR","<span style='color:red;font-weight:bold;'>ERROR</span>", $out);
    return $out;
}

//=====================================================================
// import all sections from external application
//=====================================================================

function build_list_organismes() {
    global $import_api_url, $import_api_token, $dbc;
    $url=$import_api_url."/organisme?length=1000&token=".$import_api_token;
    
    $json = @file_get_contents($url) 
        or die("ERROR - cannot retrieve data from $url");
    $arrout = array();
    if ($json === false) {
        return "<div align=left id='msgError' class='alert alert-danger' role='alert'><strong>Erreur! </strong>".get_curl_errors($url)."</div>";
    }
    else {
        $array=json_decode($json, true);
        $result=$array['success'];
        if ( $result == "true" ) {
            $sections = $array['data'];
            foreach ($sections as $section){
                $arrout[$section["Id"]] = $section["NumAffiliationOrga"];
            }
        }
    }
    return $arrout;
}

//=====================================================================
// load sections from eBrigade into array
//=====================================================================

function build_affiliations() {
    global $dbc;
    $arrout= array();
    $query="select S_ID, S_AFFILIATION from section where S_AFFILIATION is not null and S_AFFILIATION <> '' ";
    $res=mysqli_query($dbc,$query);
    while ( $row = mysqli_fetch_array($res)) {
        $arrout[$row["S_AFFILIATION"]] = $row["S_ID"];
    }
    return $arrout;
}

//=====================================================================
// map club to section
//=====================================================================
function get_section_from_club($club) {
    global $dbc, $affiliations;
    $num_affiliation= get_numero_affiliation($club);
    $Section=0;
    if ( $num_affiliation <>'' ) {
        if (isset($affiliations[$num_affiliation]))
            $Section = intval($affiliations[$num_affiliation]);
        else {
            $query="select S_ID from section where S_AFFILIATION=\"".$num_affiliation."\"";
            $res=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($res);
            if ( mysqli_num_rows($res) == 0 ) $Section = get_default_import_section();
            else $Section=intval($row["S_ID"]);
        }
    }
    else 
        $Section = get_default_import_section();

    return $Section;
}

//=====================================================================
// find a default import section for new 
//=====================================================================

function get_default_import_section() {
    global $dbc, $DefaultImportSection;
    if ( isset($DefaultImportSection) and intval($DefaultImportSection) > 0 ) 
        return $DefaultImportSection;
    $query="select S_ID from section where S_CODE='IMPORT'";
    $res=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($res);
    $DefaultImportSection=intval($row["S_ID"]);
    return $DefaultImportSection;
}

//=====================================================================
// optimize
//=====================================================================
function optimize_table_pompier() {
    global $dbc;
    $query="optimize table pompier";
    mysqli_query($dbc,$query);
}


//=====================================================================
// get numero affiliation
//=====================================================================

function get_numero_affiliation($id_club) {
    global $import_api_url, $import_api_token, $dbc;
    global $organismes;
    if ( isset($organismes[$id_club])) {
        $out=cleanup_data($organismes[$id_club]);
        return $out;
    }
    $url=$import_api_url."/organisme/:id?id=".$id_club."&token=".$import_api_token;
    
    $json = file_get_contents($url)
        or die("ERROR - cannot retrieve data from $url");
    if ($json === false) {
        return "<div id='msgError' align=left class='alert alert-danger' role='alert'><strong>Erreur! </strong>".get_curl_errors($url)."</div>";
    }
    else {
        $array=json_decode($json, true);
        $result=$array['success'];
        if ( $result == "true" ) {
            $out=cleanup_data($array['data']['NumAffiliationOrga']);
            return $out;
        }
    }
    return 0;
}

//=====================================================================
// mapping nationalité
//=====================================================================

function map_nationalite($code) {
    $nationalites = array(
        array("279", "Française",65),
        array("215", "Algérienne",4),
        array("216", "Allemande",5),
        array("217", "Américaine",61),
        array("218", "Andorrane",6),
        array("219", "Angolaise",7),
        array("220", "Argentine",10),
        array("221", "Arménienne",11),
        array("222", "Australienne",12),
        array("223", "Autrichienne",13),
        array("224", "Azerbaïdjanaise",14),
        array("225", "Bahamienne",15),
        array("226", "Bahreïnienne",16),
        array("227", "Bangladaise",17),
        array("228", "Barbadienne",18),
        array("229", "Bélarussienne",24),
        array("230", "Belge",20),
        array("231", "Bélizienne",21),
        array("232", "Béninoise",22),
        array("233", "Bhoutanaise",23),
        array("234", "Bolivienne",26),
        array("235", "Botswanaise",28),
        array("236", "Brésilienne",29),
        array("237", "Britannique",148),
        array("238", "Bulgare",31),
        array("239", "Burkinabè",32),
        array("240", "Burundaise",33),
        array("241", "Cambodgienne",34),
        array("242", "Camerounaise",35),
        array("243", "Canadienne",36),
        array("244", "Cap-verdienne",37),
        array("245", "Centrafricaine",144),
        array("246", "Chilienne",38),
        array("247", "Chinoise",39),
        array("248", "Chypriote",40),
        array("249", "Colombienne",41),
        array("250", "Comorienne",42),
        array("251", "Congolaise",43),
        array("252", "Coréenne",47),
        array("253", "Costaricienne",48),
        array("254", "Croate",50),
        array("255", "Cubaine",51),
        array("256", "Danoise",52),
        array("257", "De Bosnie-et-Herzégovine",27),
        array("258", "De Guinée-Bissau",74),
        array("259", "De São Tomé E Príncipe",159),
        array("260", "De Sierra Leone",162),
        array("261", "Des Émirats Arabes Unis",56),
        array("262", "Des Îles Cook",45),
        array("263", "Des Îles Fidji",63),
        array("264", "Djiboutienne",53),
        array("265", "Dominicaine",145),
        array("266", "Dominiquaise",54),
        array("267", "Du Brunei",30),
        array("268", "Du Lesotho",97),
        array("269", "D’Antigua-et-Barbuda",8),
        array("270", "Égyptienne",55),
        array("271", "Équato-guinéenne",75),
        array("272", "Équatorienne",57),
        array("273", "Érythréenne",58),
        array("274", "Espagnole",59),
        array("275", "Est-timorais",0),
        array("276", "Estonienne",60),
        array("277", "Éthiopienne",62),
        array("278", "Finlandaise",64),
        array("280", "Gabonaise",66),
        array("281", "Gambienne",67),
        array("282", "Géorgienne",68),
        array("283", "Ghanéenne",69),
        array("284", "Grecque",70),
        array("285", "Grenadine",155),
        array("286", "Guatémaltèque",72),
        array("287", "Guinéenne",73),
        array("288", "Guyanienne",76),
        array("289", "Haïtienne",77),
        array("290", "Hondurienne",78),
        array("291", "Hongroise",79),
        array("292", "Indienne",80),
        array("293", "Indonésienne",81),
        array("294", "Iranienne",82),
        array("295", "Iraquienne",83),
        array("296", "Irlandaise",84),
        array("297", "Islandaise",85),
        array("298", "Israélienne",86),
        array("299", "Italienne",87),
        array("300", "Ivoirienne",49),
        array("301", "Jamaïquaine",88),
        array("302", "Japonaise",89),
        array("303", "Jordanienne",90),
        array("304", "Kazakhe",91),
        array("305", "Kényane",92),
        array("306", "Kirghize",93),
        array("307", "Kiribatienne",94),
        array("308", "Koweïtienne",95),
        array("309", "Laotienne",96),
        array("310", "Lettone",98),
        array("311", "Libanaise",99),
        array("312", "Libérienne",100),
        array("313", "Libyenne",101),
        array("314", "Liechtensteinoise",102),
        array("315", "Lituanienne",103),
        array("316", "Luxembourgeoise",104),
        array("317", "Malaisienne",107),
        array("318", "Malawienne",108),
        array("319", "Maldivienne",109),
        array("320", "Malgache",106),
        array("321", "Malienne",110),
        array("322", "Maltaise",111),
        array("323", "Marocaine",112),
        array("324", "Marshallaise",113),
        array("325", "Mauricienne",114),
        array("326", "Mauritanienne",115),
        array("327", "Mexicaine",116),
        array("328", "Micronésienne",117),
        array("329", "Moldave",118),
        array("330", "Monégasque",119),
        array("331", "Mongole",120),
        array("332", "Monténégrine",0),
        array("333", "Mozambicaine",121),
        array("334", "Namibienne",122),
        array("335", "Nauruane",123),
        array("336", "Néerlandaise",138),
        array("337", "Néo-zélandaise",130),
        array("338", "Népalaise",124),
        array("339", "Nicaraguayenne",125),
        array("340", "Nigériane",127),
        array("341", "Nigérienne",126),
        array("342", "Niuéane",128),
        array("343", "Nord-coréenne",46),
        array("344", "Norvégienne",129),
        array("345", "Omanaise",131),
        array("346", "Ougandaise",132),
        array("347", "Ouzbèke",133),
        array("348", "Pakistanaise",134),
        array("349", "Palauane",0),
        array("350", "Panaméenne",135),
        array("351", "Paraguayenne",137),
        array("352", "Péruvienne",139),
        array("353", "Philippine",140),
        array("354", "Polonaise",141),
        array("355", "Portugaise",142),
        array("356", "Qatarienne",143),
        array("357", "Roumaine",147),
        array("358", "Russe",149),
        array("359", "Rwandaise",150),
        array("360", "Saint-lucienne",152),
        array("361", "Saint-marinaise",153),
        array("362", "Salomonaise",156),
        array("363", "Salvadorienne",157),
        array("364", "Samoane",158),
        array("365", "Saoudienne",9),
        array("366", "Sénégalaise",160),
        array("367", "Serbe",0),
        array("368", "Seychelloise",161),
        array("369", "Singapourienne",163),
        array("370", "Slovaque",164),
        array("371", "Slovène",165),
        array("372", "Somalienne",166),
        array("373", "Soudanaise",167),
        array("374", "Sri-lankaise",168),
        array("375", "Sud-africaine",2),
        array("376", "Sud-coréenne",47),
        array("377", "Suédoise",169),
        array("378", "Suisse",170),
        array("379", "Surinamaise",171),
        array("380", "Swazie",172),
        array("381", "Syrienne",173),
        array("382", "Tadjike",174),
        array("383", "Taïwanaise",0),
        array("384", "Tanzanienne",175),
        array("385", "Tchadienne",176),
        array("386", "Tchèque",146),
        array("387", "Thaïlandaise",177),
        array("388", "Togolaise",178),
        array("389", "Tongane",179),
        array("390", "Trinidadienne",180),
        array("391", "Tunisienne",181),
        array("392", "Turkmène",182),
        array("393", "Turque",183),
        array("394", "Tuvaluane",184),
        array("395", "Ukrainienne",185),
        array("396", "Uruguayenne",186),
        array("397", "Vanuatuane",187),
        array("398", "Vénézuélienne",188),
        array("399", "Vietnamienne",189),
        array("400", "Yéménite",190),
        array("401", "Zambienne",193),
        array("402", "Zimbabwéenne",194),
        array("751", "Afghane",1),
        array("752", "Albanaise",3)
    );
    
    foreach ($nationalites as $item) {
        if ( $item[0] == $code ) return $item[2];
    }
    //echo "<div align=left><pre>";
    //print_r($nationalites);
    //echo "</pre>";
    return 0;
}


//=====================================================================
// mapping départements
//=====================================================================

function map_departement($code) {
    $departements = array(
        array("31", "67", "Bas Rhin"),
        array("32", "68", "Haut Rhin"),
        array("34", "24", "Dordogne"),
        array("35", "33", "Gironde"),
        array("36", "40", "Landes"),
        array("37", "47", "Lot et Garonne"),
        array("38", "64", "Pyrénées Atlantiques"),
        array("40", "03", "Allier"),
        array("41", "15", "Cantal"),
        array("42", "43", "Haute loire"),
        array("43", "63", "Puy de Dôme"),
        array("45", "14", "Calvados"),
        array("46", "50", "Manche"),
        array("47", "61", "Orne"),
        array("49", "21", "Côte d'or"),
        array("50", "58", "Nièvre"),
        array("51", "71", "Saône et Loire"),
        array("52", "89", "Yonne"),
        array("54", "22", "Côtes d'Armor"),
        array("55", "29", "Finistère"),
        array("56", "35", "Ille et Vilaine"),
        array("57", "56", "Morbihan"),
        array("59", "18", "Cher"),
        array("60", "28", "Eure et Loir"),
        array("61", "36", "Indre"),
        array("62", "37", "Indre et Loire"),
        array("63", "41", "Loir et Cher"),
        array("64", "45", "Loiret"),
        array("66", "08", "Ardennes"),
        array("67", "10", "Aube"),
        array("68", "51", "Marne"),
        array("69", "52", "Haute Marne"),
        array("71", "2A", "Corse du Sud"),
        array("72", "2B", "Haute Corse"),
        array("74", "25", "Doubs"),
        array("75", "39", "Jura"),
        array("76", "70", "Haute Saône"),
        array("77", "90", "Territoire de Belfort"),
        array("79", "27", "Eure"),
        array("80", "76", "Seine Maritime"),
        array("82", "75", "Paris"),
        array("83", "77", "Seine et Marne"),
        array("84", "78", "Yvelines"),
        array("85", "91", "Essonne"),
        array("86", "92", "Haut de seine"),
        array("87", "93", "Seine Saint Denis"),
        array("88", "94", "Val de Marne"),
        array("89", "95", "Val d'Oise"),
        array("91", "11", "Aude"),
        array("92", "30", "Gard"),
        array("93", "34", "Hérault"),
        array("94", "48", "Lozère"),
        array("95", "66", "Pyrénées Orientales"),
        array("97", "19", "Corrèze"),
        array("98", "23", "Creuse"),
        array("99", "87", "Haute Vienne"),
        array("101", "54", "Meurthe et Moselle"),
        array("102", "55", "Meuse"),
        array("103", "57", "Moselle"),
        array("104", "88", "Vosges"),
        array("106", "09", "Ariège"),
        array("107", "12", "Aveyron"),
        array("108", "31", "Haute Garonne"),
        array("109", "32", "Gers"),
        array("110", "46", "Lot"),
        array("111", "65", "Hautes Pyrénées"),
        array("112", "81", "Tarn"),
        array("113", "82", "Tarn et Garonne"),
        array("115", "59", "Nord"),
        array("116", "62", "Pas de Calais"),
        array("118", "04", "Alpes de haute provence"),
        array("119", "05", "Hautes alpes"),
        array("120", "06", "Alpes maritimes"),
        array("121", "13", "Bouches du rhône"),
        array("122", "83", "Var"),
        array("123", "84", "Vaucluse"),
        array("125", "44", "Loire Atlantique"),
        array("126", "49", "Maine et Loire"),
        array("127", "53", "Mayenne"),
        array("128", "72", "Sarthe"),
        array("129", "85", "Vendée"),
        array("131", "02", "Aisne"),
        array("132", "60", "Oise"),
        array("133", "80", "Somme"),
        array("135", "16", "Charente"),
        array("136", "17", "Charente maritime"),
        array("137", "79", "Deux Sèvres"),
        array("138", "86", "Vienne"),
        array("140", "01", "Ain"),
        array("141", "07", "Ardèche"),
        array("142", "26", "Drôme"),
        array("143", "38", "Isère"),
        array("144", "42", "Loire"),
        array("145", "69", "Rhône"),
        array("146", "73", "Savoie"),
        array("147", "74", "Haute Savoie"),
        array("149", "971", "Guadeloupe"),
        array("150", "972", "Martinique"),
        array("151", "973", "Guyane"),
        array("152", "974", "Réunion"),
        array("160", "975", "Saint-Pierre-et-Miquelon"),
        array("161", "976", "Mayotte"),
        array("162", "984", "Terres Australes et Antarctiques"),
        array("163", "986", "Wallis et Futuna"),
        array("164", "987", "Polynésie Française"),
        array("165", "988", "Nouvelle-Calédonie"),
        array("753", "999", "Etranger"),
        array("811", "978", "Saint-Martin")
    );
    
    foreach ($departements as $item) {
        if ( $item[0] == $code ) return $item[1];
    }
    return 0;
}



/*
stdClass Object
(
    [success] => 1
    [data] => stdClass Object
        (
            [Id] => 1
            [NumeroLcs] => 1000001|H|F|N|60|01691
            [PrenomLcs] => Dominique
            [NomLcs] => GODARD
            [label] => GODARD Dominique
            [DateNaissanceLcs] => 22-03-1948
            [SexeLcs] => Homme
            [LieuNaissanceLcs] => Montataire
            [dptNaissanceLcs] => 464
            [PaysLcs] => 583
            [NationaliteLcs] => 279
            [nationalite] => Française
            [ClubLcs] => 53
            [clubLabel] => Sauveteurs de l'Oise
            [TelephoneLcs] => 
            [PortableLcs] => 
            [EmailLcs] => dominique-godard@orange.fr
            [AdresseLcs] => 16 rue Matisse
            [AdresseSuiteLcs] => 
            [CodePostalLcs] => 60290
            [VilleLcs] => LAIGNEVILLE
            [CommentaireLcs] => 0
            [DateCreaLcs] => 01-10-2008
            [DateModifLcs] => 01-10-2018

}
*/

?>
