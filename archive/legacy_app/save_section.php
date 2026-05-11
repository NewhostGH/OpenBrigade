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
$id=$_SESSION['id'];
destroy_my_session_if_forbidden($id);
verify_csrf('section');
writehead();
?>

<SCRIPT type="text/javascript">

function redirect(status) {
     url="departement.php?status="+status;
     self.location.href=url;
}

function suppress(p1,p2) {
    if ( confirm("Voulez vous vraiment supprimer la section "+ p2 +" "+ p1+ "?")) {
        url="del_section.php?S_ID="+p1+"&S_CODE="+p2;
        self.location.href=url;
    }
    else{
       redirect();
    }
}

function retour(p1,p2,p3) {
    url="upd_section.php?S_ID="+p1+"&status="+p3+"&from=save&msg='"+p2+"'";
    if ( p2 == 'updated') {
         self.location.href=url;
    }
    else if ( p3 == 'documents') {
         self.location.href="upd_section.php?S_ID="+p1+"&status="+p3;
    }
    else {
        self.location.href=url;
    }
}
</SCRIPT>
</head>
<?php
$msg="";
if (isset($_POST["S_ID"])) $S_ID=intval($_POST["S_ID"]);
else $S_ID=0;
if (isset($_POST["operation"])) $operation=$_POST["operation"];
else $operation='';

if ( isset($_POST['nom'])) $nom=stripslashes(secure_input($dbc,$_POST["nom"])); else $nom='';
if ( isset($_POST['code'])) $code=stripslashes(secure_input($dbc,$_POST["code"])); else $code='';
if ( isset($_POST['parent'])) $parent=intval($_POST["parent"]);
if ( isset($_POST['ordre'])) $ordre=intval($_POST["ordre"]);
else $ordre=0;
if ( isset($_POST['address'])) $address=trim(secure_input($dbc,$_POST["address"]));
else $address="";
if ( isset($_POST['address_complement'])) $address_complement=trim(stripslashes(secure_input($dbc,$_POST["address_complement"])));
else $address_complement="";
if ( isset($_POST['zipcode'])) $zipcode=secure_input($dbc,$_POST["zipcode"]);
else $zipcode="";
if ( isset($_POST['city'])) $city=trim(stripslashes(secure_input($dbc,$_POST["city"])));
else $city="";
if (isset ($_POST["phone"])) $phone=secure_input($dbc,$_POST["phone"]);
else $phone='';
if (isset ($_POST["phone2"])) $phone2=secure_input($dbc,$_POST["phone2"]);
else $phone2='';
if (isset ($_POST["phone3"])) $phone3=secure_input($dbc,$_POST["phone3"]);
else $phone3='';
if (isset ($_POST["fax"])) $fax=secure_input($dbc,$_POST["fax"]);
else $fax='';
if ( isset($_POST['hide'])) $hide=intval($_POST["hide"]);
else $hide=0;
if ( isset($_POST['SHOW_PHONE3'])) $SHOW_PHONE3=intval($_POST["SHOW_PHONE3"]);
else $SHOW_PHONE3=0;
if ( isset($_POST['SHOW_EMAIL3'])) $SHOW_EMAIL3=intval($_POST["SHOW_EMAIL3"]);
else $SHOW_EMAIL3=0;
if ( isset($_POST['SHOW_URL'])) $SHOW_URL=intval($_POST["SHOW_URL"]);
else $SHOW_URL=0;
if ( isset($_POST['inactive'])) $inactive=intval($_POST["inactive"]);
else $inactive=0;
if (isset ($_POST["siret"])) $siret=secure_input($dbc,$_POST["siret"]);
else $siret='';
if (isset ($_POST["affiliation"])) $affiliation=secure_input($dbc,$_POST["affiliation"]);
else $affiliation='';
if (isset($_POST["whatsapp_group"])) $whatsapp_group=secure_input($dbc,str_replace("\"","",$_POST["whatsapp_group"]));
else $whatsapp_group="";
$whatsapp_group=str_replace($whatsapp_chat_url.'/','',$whatsapp_group);
$whatsapp_group=str_replace(' ','',$whatsapp_group);
$whatsapp_group=str_replace('.','',$whatsapp_group);
$whatsapp_group=str_replace('-','',$whatsapp_group);


$phone=STR_replace("-",".",$phone);
$phone=STR_replace(" ",".",$phone);
$phone=STR_replace(".","",$phone);
$phone2=STR_replace("-",".",$phone2);
$phone2=STR_replace(" ",".",$phone2);
$phone2=STR_replace(".","",$phone2);
$phone3=STR_replace("-",".",$phone3);
$phone3=STR_replace(" ",".",$phone3);
$phone3=STR_replace(".","",$phone3);
$fax=STR_replace("-",".",$fax);
$fax=STR_replace(" ",".",$fax);
$fax=STR_replace(".","",$fax);

if (isset ($_POST["email"])) $email=secure_input($dbc,$_POST["email"]);
else $email='';
if (isset ($_POST["email2"])) $email2=secure_input($dbc,$_POST["email2"]);
else $email2='';
if (isset ($_POST["email3"])) $email3=secure_input($dbc,$_POST["email3"]);
else $email3='';
if (isset ($_POST["type"])) $TD_CODE=secure_input($dbc,$_POST["type"]);
else $TD_CODE='';
if (isset ($_POST["url"])) $URL=secure_input($dbc,$_POST["url"]);
else $URL='';
if (isset ($_POST["security"])) $DS_ID=intval($_POST["security"]);
else $DS_ID='';
if (isset ($_POST["dossier"])) $dossier=intval($_POST["dossier"]);
else $dossier="";
if ( isset($_POST['status']) ) $status=$_POST['status'];
else $status = 'infos';
if ( isset($_POST['dps']) ) $dps=intval($_POST['dps']);
else $dps = 'null';

if ( isset($_POST['rad1']) ) $rad1=secure_input($dbc,$_POST['rad1']);
else $rad1 = '';
if ( isset($_POST['rad2']) ) $rad2=secure_input($dbc,$_POST['rad2']);
else $rad2 = '';

$S_PDF_MARGE_TOP=(isset($_POST["pdf_marge_top"])?$_POST["pdf_marge_top"]:15);
$S_PDF_MARGE_LEFT=(isset($_POST["pdf_marge_left"])?$_POST["pdf_marge_left"]:15);
$S_PDF_TEXTE_TOP=(isset($_POST["pdf_texte_top"])?$_POST["pdf_texte_top"]:40);
$S_PDF_TEXTE_BOTTOM=(isset($_POST["pdf_texte_bottom"])?$_POST["pdf_texte_bottom"]:25);
$S_PDF_SIGNATURE = addslashes((isset($_POST["pdf_signature"])?$_POST["pdf_signature"]:""));
$S_DEVIS_DEBUT = addslashes((isset($_POST["devis_debut"])?$_POST["devis_debut"]:""));
$S_DEVIS_FIN = addslashes((isset($_POST["devis_fin"])?$_POST["devis_fin"]:""));
$S_FACTURE_DEBUT = addslashes((isset($_POST["facture_debut"])?$_POST["facture_debut"]:""));
$S_FACTURE_FIN = addslashes((isset($_POST["facture_fin"])?$_POST["facture_fin"]:""));
$NB_DAYS_BEFORE_BLOCK=(isset($_POST["NB_DAYS_BEFORE_BLOCK"])?intval($_POST["NB_DAYS_BEFORE_BLOCK"]):-1);
$SMS_LOCAL_PROVIDER=(isset($_POST["SMS_LOCAL_PROVIDER"])?intval($_POST["SMS_LOCAL_PROVIDER"]):0);
$SMS_LOCAL_USER=(isset($_POST["SMS_LOCAL_USER"])?secure_input($dbc,$_POST["SMS_LOCAL_USER"]):"");
$SMS_LOCAL_PASSWORD=(isset($_POST["SMS_LOCAL_PASSWORD"])?secure_input($dbc,$_POST["SMS_LOCAL_PASSWORD"]):"");
$SMS_LOCAL_API_ID=(isset($_POST["SMS_LOCAL_API_ID"])?secure_input($dbc,$_POST["SMS_LOCAL_API_ID"]):"");

if ( $operation == 'delete' ) check_all(19);
else if ( $operation == 'insert' or isset ($_FILES['userfile'])) check_all(22);
else if ((! check_rights($id, 29, "$S_ID")) and (! check_rights($id, 30, "$S_ID")) and (! check_rights($id, 36, "$S_ID")))
check_all(22);

$_SESSION['status'] = "infos";
//=====================================================================
// vérifier le code section choisi
//=====================================================================
if ( $code <> '' ) {
    $query="select count(1) as NB from section where S_CODE=\"".$code."\" and S_ID <> ".$S_ID;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);

    if ( $operation <> 'delete'  and  $row["NB"] <> 0 ) {     
        write_msgbox("erreur", $error_pic, "Le code choisi (".$code.") est déjà utilisé pour une autre section.<br><p align=center>
        <input type=submit class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
       exit;
    }
}

//=====================================================================
// vérifier le code ID Radio choisi (unique)
//=====================================================================
if ( $rad1 <> '' and $rad2 <> '' ) {
    $query="select count(1) as NB from section where S_ID_RADIO=\"".$rad1.$rad2."\" and S_ID <> ".$S_ID;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    
    if ( $operation <> 'delete'  and $row["NB"] <> 0 ) {
        write_msgbox("erreur", $error_pic, "L'identifiant ID Radio choisi (".$rad1.$rad2.") est déjà utilisé pour une autre section.<br><p align=center>
        <input type=submit class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
       exit;
    }
}

//=====================================================================
// update la fiche
//=====================================================================
if ( $operation == 'update'  and  $status == 'infos') {
    // interdire les dependences circulaires
     $list = preg_split('/,/' , get_family("$S_ID").",".$S_ID);
     if (in_array("$parent", $list)) {
      write_msgbox("erreur", $error_pic,
      "La section ne peut pas avoir comme section parente une de ses sections filles.<br><p align=center>
      <input type=submit class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
       exit;
     }

    if ( $dps == '' ) $dps = 'null';
    
    $queryP="select S_PARENT,S_CODE,S_DESCRIPTION,S_INACTIVE,S_ADDRESS,S_ADDRESS_COMPLEMENT,S_CITY,S_ZIP_CODE,S_ORDER,
            S_PHONE,S_PHONE2,S_PHONE3,S_FAX,S_EMAIL,S_EMAIL2,S_EMAIL3,S_URL,DPS_MAX_TYPE,S_ID_RADIO, SHOW_PHONE3, SHOW_EMAIL3, SHOW_URL, S_SIRET, S_AFFILIATION, S_WHATSAPP
            from section where S_ID=".$S_ID;
    $resultP=mysqli_query($dbc,$queryP);
    $previous=@mysqli_fetch_array($resultP);

    if (check_rights($id, 55, $previous["S_PARENT"])){
        if ($code == "") {
            write_msgbox("erreur", $error_pic,
            "Le code de la section ne peut pas être vide.<br><p align=center>
            <input type=submit class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
            exit;
        }
        
        if ( $gardes )
            log_update_section($S_ID, 'S_ORDER', $ordre, $previous["S_ORDER"], 'UPDS34');

        log_update_section($S_ID, 'S_PARENT', $parent, $previous["S_PARENT"], 'MOVES');
        log_update_section($S_ID, 'S_CODE', $code, $previous["S_CODE"], 'UPDS1');
        log_update_section($S_ID, 'S_DESCRIPTION', $nom, $previous["S_DESCRIPTION"], 'UPDS2');
        log_update_section($S_ID, 'S_INACTIVE', $inactive, $previous["S_INACTIVE"], 'UPDS23');
        
        if ( $previous["S_PARENT"] <> $parent ) {
            set_time_limit($mytimelimit);
            rebuild_section_flat(-1,0,6);
        }
        else {
             $query="update section_flat set S_CODE=\"".$code."\",
                   S_DESCRIPTION=\"".$nom."\" where S_ID=".$S_ID ;
             $result=mysqli_query($dbc,$query);
        }
    }
    
    if (check_rights($id, 22, "$S_ID")){
        
        log_update_section($S_ID, 'S_FAX',$fax, $previous["S_FAX"], 'UPDS9');
        log_update_section($S_ID, 'S_PHONE', $phone, $previous["S_PHONE"], 'UPDS6');
        log_update_section($S_ID, 'S_PHONE2', $phone2, $previous["S_PHONE2"], 'UPDS7');
        log_update_section($S_ID, 'S_PHONE3',$phone3, $previous["S_PHONE3"], 'UPDS8');
        log_update_section($S_ID, 'S_FAX', $fax, $previous["S_FAX"], 'UPDS9');
        log_update_section($S_ID, 'S_EMAIL', $email, $previous["S_EMAIL"], 'UPDS10');
        log_update_section($S_ID, 'S_EMAIL2', $email2, $previous["S_EMAIL2"], 'UPDS11');
        log_update_section($S_ID, 'S_EMAIL3', $email3, $previous["S_EMAIL3"], 'UPDS12');
        log_update_section($S_ID, 'S_URL', $URL, $previous["S_URL"], 'UPDS13');
        log_update_section($S_ID, 'DPS_MAX_TYPE', intval($dps), intval($previous["DPS_MAX_TYPE"]), 'UPDS22');
        log_update_section($S_ID, 'S_SIRET', $siret, $previous["S_SIRET"], 'UPDS31');
        log_update_section($S_ID, 'S_AFFILIATION', $affiliation, $previous["S_AFFILIATION"], 'UPDS32');
        log_update_section($S_ID, 'S_WHATSAPP', $whatsapp_group, $previous["S_WHATSAPP"], 'UPDS33');
        
        if  ( $assoc ) {
            log_update_section($S_ID, 'SHOW_PHONE3', $SHOW_PHONE3, $previous["SHOW_PHONE3"], 'UPDS28');
            log_update_section($S_ID, 'SHOW_EMAIL3', $SHOW_EMAIL3, $previous["SHOW_EMAIL3"], 'UPDS29');
            log_update_section($S_ID, 'SHOW_URL', $SHOW_URL, $previous["SHOW_URL"], 'UPDS30');
        }
        
        // prevenir National si changement adresse
        $query="select S_CODE code, S_DESCRIPTION nom from section where S_ID=".$S_ID;
        $result=mysqli_query($dbc,$query);
        custom_fetch_array($result);
        
        if ( $address <> "" and $city <> "" and $address <> $previous["S_ADDRESS"]) {
            gelocalize($S_ID, 'S');
        }
        else {
            $query = "delete from geolocalisation where type ='S' and CODE=".$S_ID;
            $result=mysqli_query($dbc,$query);
        }
        $m=0;
        $m = $m + log_update_section($S_ID, 'S_ADDRESS', $address, secure_input($dbc,$previous["S_ADDRESS"]), 'UPDS3');
        $m = $m + log_update_section($S_ID, 'S_ADDRESS_COMPLEMENT',$address_complement, secure_input($dbc,$previous["S_ADDRESS_COMPLEMENT"]), 'UPDS24');
        $m = $m + log_update_section($S_ID, 'S_CITY', strtoupper($city), $previous["S_CITY"], 'UPDS4');
        $m = $m + log_update_section($S_ID, 'S_ZIP_CODE', $zipcode, $previous["S_ZIP_CODE"], 'UPDS5');
        
        $level = get_level($S_ID);
        if ( $m > 0 and ( $level == $nbmaxlevels -2 )) {
            $subject = "Modification adresse de \"".$code." - ".$nom."\"";
            $message = "L'adresse enregistrée pour \"".$code." - ".$nom."\" a été modifiée.\nLa nouvelle adresse est:";
            $message .= "\n".$address." ".$address_complement."\n".$zipcode." ".strtoupper($city);
            $destid=get_granted(25,0,'local','yes');
            if ( $destid <> "" ) {
                $nb = mysendmail("$destid" , $id , "$subject" , "$message");
            }
        }
    }
    
    // ID Radio, administrateur seulement
    if ( check_rights($id,14) and $rad1 <> '' ) {
        if ( $rad1 == '' or $rad2 == '' )
            $idradio="null";
        else 
            $idradio="'".$rad1.$rad2."'";
        log_update_section($S_ID, 'S_ID_RADIO', $rad1.$rad2, $previous["S_ID_RADIO"], 'UPDS27');
    }
    else if ( $previous["S_ID_RADIO"] == '' ) {
        $idradio=generate_id_radio($S_ID);
        if (intval($idradio) > 0 ) {
            log_update_section($S_ID, 'S_ID_RADIO', $idradio, $previous["S_ID_RADIO"], 'UPDS27');
        }
    }
    
    $operation="retour";
}
//=====================================================================
// sauver les agréments
//=====================================================================

if (( $operation == 'update' ) and ( $status == 'agrements')) {
    if (check_rights($id, 36, "$S_ID")) $granted_agrements = true;
    else $granted_agrements = false;
    if (check_rights($id, 22, "$S_ID")) $granted22 = true;
    else $granted22 = false;
    
    $query="select TA_CODE, TA_FLAG from type_agrement";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)){
        $TA_CODE=$row["TA_CODE"];
        $TA_FLAG=$row["TA_FLAG"];
        
        if (( $granted22 and $TA_FLAG == 1 ) or $granted_agrements ) {
            $debut='';$fin='';$val='';$comment='';
            if (isset($_POST["deb_".$TA_CODE])) {
                if ( $_POST["deb_".$TA_CODE] <> '' ) {
                    $special = preg_split('/\-/',secure_input($dbc,$_POST["deb_".$TA_CODE]));
                    $day=$special[0];
                    $month=$special[1];
                    $year=$special[2];
                    $debut=$year.'-'.$month.'-'.$day;
                }
            }
            if (isset($_POST["fin_".$TA_CODE])) {
                if ( $_POST["fin_".$TA_CODE] <> '' ) {
                    $special = preg_split('/\-/',secure_input($dbc,$_POST["fin_".$TA_CODE]));
                    $day=$special[0];
                    $month=$special[1];
                    $year=$special[2];
                    $fin=$year.'-'.$month.'-'.$day;
                }
            }
            if (isset($_POST["val_".$TA_CODE])) {
                if ( $_POST["val_".$TA_CODE] <> '' )
                    $val=secure_input($dbc,$_POST["val_".$TA_CODE]);
            }
            if (isset($_POST["comment_".$TA_CODE])) {
                if ( $_POST["comment_".$TA_CODE] <> '' )
                    $comment=secure_input($dbc,$_POST["comment_".$TA_CODE]);
                    $comment=STR_replace("\"","",$comment);
            }
        
            $query2="delete from agrement where TA_CODE='".$TA_CODE."' and S_ID=".$S_ID;
            $result2=mysqli_query($dbc,$query2);
            if ( $debut <> ''  or $fin <> ''  or  $val <> '' or $comment <> ''){
                $query2="insert into agrement (TA_CODE,S_ID,A_DEBUT,A_FIN,A_COMMENT,TAV_ID) 
                    values ( '".$TA_CODE."',".$S_ID.",";
                if ( $debut <> '' ) $query2 .= "'".$debut."',";
                else $query2 .= "NULL,";
                if ( $fin <> '' ) $query2 .= "'".$fin."',";
                else $query2 .= "NULL,";
                if ( $comment <> '' ) $query2 .= "\"".$comment."\",";
                else $query2 .= "NULL,";
                if ( $val <> '' ) $query2 .= "'".$val."')";
                else $query2 .= "NULL)";
                $result2=mysqli_query($dbc,$query2);
            }
        }
    }
    insert_log('UPDS14',$S_ID); 
    $operation="retour";
}

//=====================================================================
// changement montant cotisations
//=====================================================================
if (( $operation == 'update' ) and ( $status == 'cotisations')) {
    if (check_rights($id, 22, "$S_ID")) {

        // sauver montants de cotisations
        $query2="select TP_CODE, TP_DESCRIPTION from type_profession tp";
        if ( $syndicate == 0 ) $query2 .=" where TP_CODE='SPP'";

        $result2=mysqli_query($dbc,$query2);
        while ($row2=@mysqli_fetch_array($result2)) {
            $TP_CODE=$row2["TP_CODE"];
            $old_cotisation=get_param_cotisation("$S_ID",$TP_CODE);
            $old_montant=$old_cotisation[0];
            if (isset($_POST["idem_".$TP_CODE])) {
                 $idem=1;
                // cas particulier syndicat FA SPP PATS, la reference est le niveau 1 et pas le niveau 0
                if ( $syndicate == 1 ) $n=1;
                else $n=0;
                $query3="select S_ID,S_CODE from section_flat where S_ID=(select min(S_ID) from section_flat where NIV=".$n.")";
                $result3=mysqli_query($dbc,$query3);
                $row3=@mysqli_fetch_array($result3);
                $S_ID3=$row3[0];
                
                $cotisation_defaut=get_param_cotisation("$S_ID3",$TP_CODE);
                $montant=$cotisation_defaut[0];     
            }
            else {
                if ($S_ID > 0 ) $idem=0;
                else  $idem=1;
                $montant=(float)$_POST["montant_".$TP_CODE];
            }
            
            if (isset($_POST["commentaire_".$TP_CODE])) $commentaire=secure_input($dbc,$_POST["commentaire_".$TP_CODE]);
            else $commentaire="";
            $query3="delete from section_cotisation where S_ID=".$S_ID." and TP_CODE='".$TP_CODE."'";
            $result3=mysqli_query($dbc,$query3);
            $query3="insert into section_cotisation (S_ID, TP_CODE, MONTANT, IDEM, COMMENTAIRE)
                        values ( ".$S_ID.",'".$TP_CODE."','".$montant."',".$idem.",\"".$commentaire."\")";
            $result3=mysqli_query($dbc,$query3);
                 
            if ( ($syndicate == 0 and $S_ID == 0) or ( $syndicate == 1 and $S_ID == 1 )) {
                $query3="update section_cotisation set MONTANT='".$montant."' where TP_CODE='".$TP_CODE."' and IDEM=1";
                $result3=mysqli_query($dbc,$query3);
            }
            if ( $old_montant <> $montant) {
                if ( $syndicate == 1 ) $t = $TP_CODE;
                else $t="";
                insert_log('UPDS15',$S_ID,"Montant cotisation ".$t.": ".$old_montant." ".$default_money_symbol." -> ".$montant." ".$default_money_symbol);
            }
            
        }

        if ( $bank_accounts == 1 ) {
            if (isset ($_POST["bic"])) $bic=secure_input($dbc,$_POST["bic"]);
            else $bic="";
            if (isset ($_POST["iban"])) $iban=secure_input($dbc,$_POST["iban"]);
            else $iban="";
            $iban=str_replace(" ","",$iban);
            
            if ( strlen($iban) > 0 and (strlen($iban) < 16 or strlen($iban) > 32 )) {
                write_msgbox("erreur", $error_pic, "Code IBAN incorrect, entre 16 et 32 caractères requis, IBAN non modifié<br><p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'> ",10,0);
                exit;
            }
            
            $query="select IBAN,BIC from compte_bancaire where CB_TYPE = 'S' and CB_ID=".$S_ID;
            $result=mysqli_query($dbc,$query);
            $row=@mysqli_fetch_array($result);
            $old_iban=$row[0];
            $old_bic=$row[1];

            if ( $old_bic <> $bic or $old_iban <> $iban) {
                $query="delete from compte_bancaire where CB_TYPE = 'S' and CB_ID=".$S_ID;
                $result=mysqli_query($dbc,$query);
                $query="insert into compte_bancaire (CB_TYPE,CB_ID,BIC,IBAN,UPDATE_DATE)
                        values ('S', ".$S_ID.",\"".$bic."\",\"".$iban."\",NOW())";
                $result=mysqli_query($dbc,$query);
                
                if ( $old_bic <> $bic) 
                    insert_log('UPDS25',$S_ID,$old_bic." -> ".$bic);
                if ( $old_iban <> $iban) 
                    insert_log('UPDS26',$S_ID,$old_iban." -> ".$iban);
            }
        } 
    }
    $operation="retour";
}

// ===================================================
// Modèles de documents
// ===================================================

if (( $operation == 'update' ) and ( $status == 'parametrage')) {
$_SESSION['status'] = "parametrage";
// MODELE PDF
if (check_rights($id, 29, "$S_ID")) {
    if(isset($_FILES['pdf_page'])) {
        if($_FILES['pdf_page']['name']!=""){
            $dossier = $basedir.'/images/user-specific/';
            $fichier = basename($_FILES['pdf_page']['name']);
            $taille_maxi = 2000000;
            $taille = filesize($_FILES['pdf_page']['tmp_name']);
            $extensions_page = array('.pdf');
            $extension = strtolower(strrchr($_FILES['pdf_page']['name'], '.')); 
            //Début des vérifications de sécurité...
            if(!in_array($extension, $extensions_page)) //Si l'extension n'est pas dans le tableau
                $erreur = 'Vous devez uploader un fichier de type pdf.';
            if($taille>$taille_maxi)
                $erreur = 'Le fichier est trop gros...';
            if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
            {
                $fichier = "pdf_page_".generatePassword()."_".$S_ID.".pdf";
                if(! move_uploaded_file($_FILES['pdf_page']['tmp_name'], $dossier . $fichier))
                    $msg .= 'Echec de l\'upload !';
                else {
                    $sql = "UPDATE section set S_PDF_PAGE = '$fichier' WHERE s_id = ".$S_ID;
                    $result=mysqli_query($dbc,$sql);
                }
            }
            else $msg .= $erreur;
        }
    }
    $sql = "UPDATE section set 
           S_PDF_MARGE_TOP = $S_PDF_MARGE_TOP,
           S_PDF_MARGE_LEFT = $S_PDF_MARGE_LEFT,
           S_PDF_TEXTE_TOP = $S_PDF_TEXTE_TOP,
           S_PDF_TEXTE_BOTTOM = $S_PDF_TEXTE_BOTTOM,
           S_PDF_SIGNATURE = \"$S_PDF_SIGNATURE\",
           S_DEVIS_DEBUT = \"$S_DEVIS_DEBUT\",
           S_DEVIS_FIN = \"$S_DEVIS_FIN\",
           S_FACTURE_DEBUT = \"$S_FACTURE_DEBUT\",
           S_FACTURE_FIN = \"$S_FACTURE_FIN\"
    WHERE s_id =".$S_ID;
    $result=mysqli_query($dbc,$sql);
    if(isset($_POST['delpage'])){
        $sql="select S_PDF_PAGE from section where S_ID='$S_ID'";
        $result=mysqli_query($dbc,$sql);
        $row=mysqli_fetch_array($result);
        $S_PDF_PAGE = $row["S_PDF_PAGE"];
        $pdffile = $basedir.'/images/user-specific/'.$S_PDF_PAGE;
        if ( @is_file($pdffile)) unlink($pdffile);
        $sql = "update section set S_PDF_PAGE = NULL where S_ID='$S_ID'";
        $result=mysqli_query($dbc,$sql);
    }
}

// interdire changements  dans le passé
if (check_rights($id, 22, "$S_ID") and $NB_DAYS_BEFORE_BLOCK >= 0 ) {
    $sql = "UPDATE section set NB_DAYS_BEFORE_BLOCK = \"$NB_DAYS_BEFORE_BLOCK\"
    WHERE s_id =".$S_ID;
    $result=mysqli_query($dbc,$sql);
    // et aussi pour les antennes
    $NIV=$nbmaxlevels -1;
    $sql = "UPDATE section set NB_DAYS_BEFORE_BLOCK = \"$NB_DAYS_BEFORE_BLOCK\"
    WHERE S_ID in (select S_ID from section_flat where s_parent=".$S_ID." and NIV = ".$NIV.")";
    $result=mysqli_query($dbc,$sql);
    if ( mysqli_affected_rows($dbc) > 0 ) insert_log('UPDS17',$S_ID, "-> ".$NB_DAYS_BEFORE_BLOCK);
}
// masquer événements aux autres départements
if (check_rights($id, 22, "$S_ID")) {
    $NIV_antenne=$nbmaxlevels -1;
    $NIV_dep=$nbmaxlevels -2;
    $sql = "UPDATE section set S_HIDE = ".$hide."
    WHERE  S_ID in ( select S_ID from section_flat
                     where (S_ID =".$S_ID." and NIV = ".$NIV_dep.")
                            or (S_PARENT=".$S_ID." and NIV = ".$NIV_antenne.")
                    )";
    $result=mysqli_query($dbc,$sql);
    if ( mysqli_affected_rows($dbc) > 0 ) insert_log('UPDS18',$S_ID,"-> ".$hide);
}
// param SMS
if (check_rights($id, 22, "$S_ID")) {
    $sql = "UPDATE section set 
    SMS_LOCAL_PROVIDER = ".$SMS_LOCAL_PROVIDER.",
    SMS_LOCAL_USER = \"".$SMS_LOCAL_USER."\",";

    $len = strlen(str_replace('*','',$SMS_LOCAL_PASSWORD));
    if ( $len > 0)
        $sql .= " SMS_LOCAL_PASSWORD = \"".$SMS_LOCAL_PASSWORD."\",";
    if ( intval($SMS_LOCAL_PROVIDER) >= 3 ) 
        $sql .= " SMS_LOCAL_API_ID = \"".$SMS_LOCAL_API_ID."\"";
    else
        $sql .= " SMS_LOCAL_API_ID = null";
    $sql .= " WHERE S_ID =".$S_ID;    
    $result=mysqli_query($dbc,$sql);
    if ( mysqli_affected_rows($dbc) > 0 ) insert_log('UPDS19',$S_ID);

}
    
// MODELE BADGE
if (check_rights($id, 30, "$S_ID")) {
    if(isset($_FILES['pdf_badge'])) {
        if($_FILES['pdf_badge']['name']!="") {
        $dossier = $basedir.'/images/user-specific/';
        $fichier = basename($_FILES['pdf_badge']['name']);
        $taille_maxi = 200000; // 200 Ko
        $taille = filesize($_FILES['pdf_badge']['tmp_name']);
        $extensions_badge = array('.gif','.png','.jpg');
        $extension = strtolower(strrchr($_FILES['pdf_badge']['name'], '.')); 
        //Début des vérifications de sécurité...
        if(!in_array($extension, $extensions_badge)) //Si l'extension n'est pas dans le tableau
            $erreur = 'Vous devez uploader un fichier de type  gif, png ou jpg.';
        if($taille>$taille_maxi)
            $erreur = 'Le fichier est trop gros...';
        if(!isset($erreur)) {
             //On formate le nom du fichier ici...
             $fichier = "badge_".generatePassword()."_".$S_ID.$extension;
             if( !move_uploaded_file($_FILES['pdf_badge']['tmp_name'], $dossier . $fichier))
                $msg .= 'Echec de l\'upload !';
             else {
                $sql = "UPDATE section set 
                  S_PDF_BADGE = '$fichier'
                  WHERE s_id = $S_ID;
                  ";
                $result=mysqli_query($dbc,$sql); 
                if ( mysqli_affected_rows($dbc) > 0 ) insert_log('UPDS20',$S_ID);
             }
        }
        else $msg .= $erreur;
        }
    }
    if(isset($_POST['delbadge'])){
        $sql="select S_PDF_BADGE from section where S_ID='$S_ID'";
        $result=mysqli_query($dbc,$sql);
        $row=mysqli_fetch_array($result);
        $S_PDF_BADGE = $row["S_PDF_BADGE"];
        $badgefile = $basedir.'/images/user-specific/'.$S_PDF_BADGE;
        if ( @is_file($badgefile)) unlink($badgefile);
        $sql = "update section set S_PDF_BADGE = NULL where S_ID='$S_ID'";
        $result=mysqli_query($dbc,$sql);
        insert_log('UPDS20',$S_ID,"Suppression du modèle de badge");
    }
}

//IMAGE SIGNATURE
if (check_rights($id, 22, "$S_ID")) {
    if(isset($_FILES['image_signature'])) {
        if($_FILES['image_signature']['name']!="") {
        $dossier = $basedir.'/images/user-specific/';
        $fichier = basename($_FILES['image_signature']['name']);
        $taille_maxi = 200000; // 200 Ko
        $taille = filesize($_FILES['image_signature']['tmp_name']);
        $extensions_signature = array('.gif','.png','.jpg');
        $extension = strtolower(strrchr($_FILES['image_signature']['name'], '.')); 
        //Début des vérifications de sécurité...
        if(!in_array($extension, $extensions_signature)) //Si l'extension n'est pas dans le tableau
            $erreur = 'Vous devez uploader un fichier de type gif, png ou jpg pour la signature';
        if($taille>$taille_maxi)
            $erreur = 'Le fichier signature est trop gros...';
        if(!isset($erreur)) {
             //On formate le nom du fichier ici...
             $fichier = "signature_".generatePassword()."_".$S_ID.$extension;
             if( !move_uploaded_file($_FILES['image_signature']['tmp_name'], $dossier . $fichier))
                $msg .= 'Echec de l\'upload de la signature!';
             else {
                $sql = "UPDATE section set 
                  S_IMAGE_SIGNATURE = '$fichier'
                  WHERE s_id = $S_ID;
                  ";
                $result=mysqli_query($dbc,$sql);
                if ( mysqli_affected_rows($dbc) > 0 ) insert_log('UPDS21',$S_ID);
             }
        }
        else $msg .= $erreur;
        }
    }
    if(isset($_POST['delsignature'])){
        $sql="select S_IMAGE_SIGNATURE from section where S_ID='$S_ID'";
        $result=mysqli_query($dbc,$sql);
        $row=mysqli_fetch_array($result);
        $S_IMAGE_SIGNATURE = $row["S_IMAGE_SIGNATURE"];
        $signaturefile = $basedir.'/images/user-specific/'.$S_IMAGE_SIGNATURE;
        if ( @is_file($signaturefile)) unlink($signaturefile);
        $sql = "update section set S_IMAGE_SIGNATURE = NULL where S_ID='$S_ID'";
        $result=mysqli_query($dbc,$sql);
        insert_log('UPDS21',$S_ID,"Suppression de la signature");
    }
}

if ( $msg <> '' ) {
    write_msgbox("ERREUR", $error_pic, $msg."<br><p align=center><input type=submit class='btn btn-secondary' value='Retour' onclick='javascript:history.back(1);'>",10,0);
    exit;
}
$operation="retour";
}

//=====================================================================
// insertion nouvelle fiche
//=====================================================================

if ( $operation == 'insert'  and check_rights($id, 55, "$parent")) {
    $query="select max(S_ID) as NB from section";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $newsid = $row["NB"] + 1 ;
    if ( $webservice_key <> "" ) $key = "'".md5($webservice_key.$newsid)."'";
    else $key = "null";

    $query="INSERT INTO section ( S_ID, S_CODE, S_DESCRIPTION , S_PARENT , 
             S_PHONE, S_PHONE2, S_PHONE3, S_FAX , S_ADDRESS ,  S_ADDRESS_COMPLEMENT ,S_ZIP_CODE , S_CITY , 
             S_EMAIL , S_EMAIL2, S_EMAIL3, S_URL, WEBSERVICE_KEY, S_ORDER, S_SIRET, S_AFFILIATION, S_WHATSAPP)
    values ( ".$newsid.", \"".$code."\",\"".$nom."\",".$parent.",
            '".$phone."','".$phone2."','".$phone3."','".$fax."',\"".$address."\",\"".$address_complement."\",'".$zipcode."',\"".$city."\",
            \"".$email."\",\"".$email2."\",\"".$email3."\",\"".$URL."\",".$key.",".$ordre.",\"".$siret."\",\"".$affiliation."\",\"".$whatsapp_group."\")";
    $result=mysqli_query($dbc,$query);

    insert_log('INSS', $newsid, $code." - ".$nom);
    insert_log('INSSS',$parent, $code." - ".$nom);

    @set_time_limit($mytimelimit);
    $level=get_level($parent);
    rebuild_section_flat($parent,$level,6);
    
    $idradio=generate_id_radio($newsid);
    if (intval($idradio) > 0 ) {
        $query1="update section set S_ID_RADIO='".$idradio."' where S_ID=".$newsid;
        $result1=mysqli_query($dbc,$query1);
    }
}

switch ($operation){
case "delete":
    echo "<body onload=suppress('".$S_ID."','".$code."')></body></html>";
    break;
case "retour":
    echo "<body onload=retour('".$S_ID."','".urlencode($msg)."','".$status."')></body></html>";
    break;
case "retour2":
    echo "<body onload=retour('".$S_ID."','updated','".$status."')></body></html>";
    break;
default:
    echo "<body onload=redirect('".$status."')></body></html>";
}
?>
