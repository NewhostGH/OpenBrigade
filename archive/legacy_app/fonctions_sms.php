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
// SMS functions
//=====================================================================

function get_sms_config($section) {
    global $dbc, $sms_provider, $sms_user, $sms_password, $sms_api_id;
    $parent=get_section_parent($section);
    $query="select s.S_ID, s.SMS_LOCAL_PROVIDER, s.SMS_LOCAL_USER, s.SMS_LOCAL_PASSWORD, s.SMS_LOCAL_API_ID 
            from section s, section_flat sf
            where s.S_ID in(".intval($section).",".intval($parent).")
            and sf.S_ID = s.S_ID
            and s.SMS_LOCAL_PROVIDER > 0
            and s.S_ID > 0 
            order by sf.NIV desc";
    $result=mysqli_query($dbc,$query);
    $row = mysqli_fetch_array($result);
    $SMS_SECTION_ACCOUNT=@$row[0];
    $SMS_LOCAL_PROVIDER=@$row[1];
    $SMS_LOCAL_USER=@$row[2];
    $SMS_LOCAL_PASSWORD=@$row[3];
    $SMS_SPECIFIC=@$row[4];
    if ( $SMS_LOCAL_PROVIDER == 0 ) {
        $SMS_SECTION_ACCOUNT=0;
        $SMS_LOCAL_PROVIDER=$sms_provider;
        $SMS_LOCAL_USER=$sms_user;
        $SMS_LOCAL_PASSWORD=$sms_password;
        $SMS_SPECIFIC=$sms_api_id;
        $SMS_PROVIDER_NAME = "Aucun";
    }
    if ( $SMS_LOCAL_PROVIDER == 1 ) {
        $SMS_URL = "https://www.envoyersmspro.com";
        $SMS_PROVIDER_NAME = "envoyersmspro.com";
    }
    else if ( $SMS_LOCAL_PROVIDER == 2 ) {
        $SMS_URL = "http://www.envoyersms.org";
        $SMS_PROVIDER_NAME = "envoyersms.org";
    }
    else if ( $SMS_LOCAL_PROVIDER == 3 ) {
        $SMS_URL = "https://www.clickatell.com/login";
        $SMS_PROVIDER_NAME = "clickatell.com - ancien";
    }
    else if ( $SMS_LOCAL_PROVIDER == 4 ) {
        $SMS_URL = "";
        $SMS_PROVIDER_NAME = "SMS Gateway Android";
    }
    else if ( $SMS_LOCAL_PROVIDER == 5 ) {
        $SMS_URL = "https://www.smsmode.com";
        $SMS_PROVIDER_NAME = "SMS Mode";
    }
    else if ( $SMS_LOCAL_PROVIDER == 6 ) {
        $SMS_URL = "https://portal.clickatell.com";
        $SMS_PROVIDER_NAME = "clickatell.com";
    }
    else if ( $SMS_LOCAL_PROVIDER == 7 ) {
        $SMS_URL = "https://smsgateway.me";
        $SMS_PROVIDER_NAME = "SMSGatewayMe ";
    }
    else if ( $SMS_LOCAL_PROVIDER == 8 ) {
        $SMS_URL = "http://".$SMS_SPECIFIC;
        $SMS_PROVIDER_NAME = "SMSEagle";
    } 
    else $SMS_URL = "";
    
    return array($SMS_SECTION_ACCOUNT, $SMS_LOCAL_PROVIDER, $SMS_LOCAL_USER, $SMS_LOCAL_PASSWORD, $SMS_SPECIFIC, $SMS_URL, $SMS_PROVIDER_NAME);
}
        

//=====================================================================
// get infos in order to send SMS
//=====================================================================

function mySmsGet($ids,$mode,$provider){
    global $sms_account,$phone_prefix;
    global $dbc;
    $SmsTo="";
    $ids = str_replace(",,,,",",",$ids);
    $ids = str_replace(",,,",",",$ids);
    $ids = str_replace(",,",",",$ids);
    $ids = trim($ids, ',');
    $destinataires=explode(",", $ids);
    $d = 0;
    $m = count($destinataires);
    $k = 0;
    for($i=0; $i < $m ; $i++){
        // cas numero de telephone
        if ( $destinataires[$i] > 1000000 ) {
            $cur=$phone_prefix.$destinataires[$i];
            $SmsTo .= $cur.",";
            $k++;
        }
    }
    $T = array();
    $query="select P_NOM, P_PHONE, P_PRENOM from pompier, grade 
            where P_PHONE <>''
            and P_GRADE = G_GRADE
            and P_PHONE  like '0%'
            and P_ID in (".$ids.")
            order by G_LEVEL desc";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
           $P_PHONE=$row['P_PHONE'];
           $P_NOM=$row['P_NOM'];
           $P_PRENOM=$row['P_PRENOM'];
           if (!in_array($P_PHONE, $T)) {
               $T[$d] = $P_PHONE;
               $d++;
           }
    }
    $n =  count($T);
    for($i=0; $i < $n ; $i++){
        if ( $provider == 4 ) $cur = $T[$i];
        else $cur = $phone_prefix.substr($T[$i], 1, 9);
        $SmsTo .= $cur.",";
        $k++;
    }
    $SmsTo = rtrim($SmsTo,',');
    if ( $mode == 'data' ) return $SmsTo;
    else return $k;
}

//=====================================================================
// SMS nb credits
//=====================================================================    

function get_sms_credits($section) {
    $credits = "ERREUR";
    $SMS_CONFIG=get_sms_config($section);
    $provider=$SMS_CONFIG[1];
    $user=$SMS_CONFIG[2];
    $passwd=$SMS_CONFIG[3];
    $api=$SMS_CONFIG[4];
    if ( $provider == 1 ) {
         $credits = getSMSCredit_1($user,$passwd);
    }
    if ( $provider == 2 ) {
         $credits = getSMSCredit_2($user,$passwd);
    }
    if ( $provider == 5 ) {
         $credits = getSMSCredit_5($user,$passwd);
    }
    else if ( $provider == 3 ) {
         $conn = preg_split('/:/',connectSMS_3($user,$passwd,$api));
         if ( $conn[0] == 'KO' ) $credits="ERREUR";
        else $credits = getSMSCredit_3("$conn[1]");
    }
    else if ($provider == 4) {
        if ( $api <> "" and $passwd <> "" ) $credits = getSMSCredit_4($api);
        else $credits = "ERREUR";
    }
    else if ($provider == 6) {
        if ( $api <> "" and $passwd <> "" ) $credits = getSMSCredit_6($api);
        else $credits = "ERREUR";
    }
    else if ($provider == 7) {
        if ($passwd <> "" and $api <> "" ) $credits = getSMSCredit_7($passwd, $api);
        else $credits = "ERREUR";
    }
    else if ($provider == 8) {
        if ($passwd <> "" and $api <> "" and $user <> "") $credits = getSMSCredit_8($api);
        else $credits = "ERREUR";
    }
    return $credits;
}

//=====================================================================
// display balance and infos
//=====================================================================    

function show_sms_account_balance($section, $credits) {
    global $dbc,$wikiurl;
    $SMS_CONFIG=get_sms_config($section);
    if (  $SMS_CONFIG[1] > 0 ) {
        
        if ( $SMS_CONFIG[1] == 6 )  
            echo " Clickatell <b><a href=".$SMS_CONFIG[5]." target=_blank title='voir mon compte sms'>".$credits."</a></b>";
        else if ( $SMS_CONFIG[1] == 4 )
            echo " SMS Gateway - <b> ".$credits."</b>";
        else if ( $SMS_CONFIG[1] == 8)
            echo " SMSEagle - <b> ".$credits."</b>";
        else if ( $SMS_CONFIG[1] == 5)
            echo " SMSMode - <b> ".$credits."</b>";
        else 
            echo " Il vous reste <b><a href=".$SMS_CONFIG[5]." target=_blank title='voir mon compte sms'>".intval($credits)."</a></b> SMS.";
        echo " <a href='".$wikiurl."/sms' target=_blank>
                 <i class='fa fa-info-circle fa-lg' title='Voir la notice de paramétrage du compte SMS'></i></a>";
    }
}

//=====================================================================
// SMS envoyersmspro.com
//=====================================================================
function sendSMS_1 (array $numbers, $message, $from, $user, $password){ 

    define('ENVOYERSMSPRO_LOGIN',$user);
    define('ENVOYERSMSPRO_PASSWORD',$password);
    define('ENVOYERSMSPRO_HOST',"www.envoyersmspro.com");
    define('ENVOYERSMSPRO_PROTOCOL',"https");
     
    //Appel à la méthode sendMessage
    //text, recipients, sender name
    $resultXML=sendMessageEnvoyerSMSPro(
                        $message,
                        $numbers,
                        $from);
     
    return $resultXML;
}

function sendMessageEnvoyerSMSPro($text, array $recipients, $senderName)
{
    //construction de l'url
    $url=ENVOYERSMSPRO_PROTOCOL."://".ENVOYERSMSPRO_HOST."/api/message/send";
    $recipients=implode(",", $recipients);
    $recipients=trim($recipients,",");
     
    //les paramètres à passer au serveur en POST
    $postParameters="text=".urlencode($text)."&recipients=".$recipients."&sendername=".cleanSpecialCharacters($senderName);
     
    //Configuration de la requête
    $requestConfig = array( 'http' => array(
                            'method' => 'POST',
                            'header'=>"Authorization: Basic ".base64_encode(ENVOYERSMSPRO_LOGIN.":".ENVOYERSMSPRO_PASSWORD)."\r\n"
                            ."Content-type: application/x-www-form-urlencoded\r\n",
                            'content' => $postParameters
                            ));

    //Retour du serveur
    $response = file_get_contents($url, false, stream_context_create($requestConfig));
     
    if ($response === false) {
        throw new Exception("Problem reading data from $url");
    }
     
    //Création d'un Objet XML depuis le retour du serveur
    $responseXML = simplexml_load_string($response);
     
    if ($responseXML === null) {
        throw new Exception("failed to decode $response as xml");
    }

    return $responseXML;
}


function getSMSCredit_1($user, $password){ 
    $url="http://www.envoyersmspro.com/api/account/getallaccounts";
    $requestConfig = array( 'http' => array(
                            'method' => 'POST',
                            'header'=>"Authorization: Basic ".base64_encode($user.":".$password)."\r\n"
                            ."Content-type: application/x-www-form-urlencoded\r\n",
                    ));
    $response = @file_get_contents($url, false, stream_context_create($requestConfig)); 
    if ($response === false)
        return "ERREUR";
    $responseXML = simplexml_load_string($response);
    if($responseXML->status=="success") {
        $accountsObject=$responseXML->accounts;
        $credits = 0;
        foreach($accountsObject->account as $account) {
            $credits = $credits + $account->sms_remaining;
        }
    }
    else
        $credits = "ERREUR";
    return $credits;
}

//=====================================================================
// SMS envoyersms.org
//=====================================================================
function sendSMS_2($to, $message, $from, $user, $password){ 
    $typesms = "0"; // 0 = sms, 1 = flash
    $path="http://www.envoyersms.org/exe/api.php";
    $request = "?login=".$user;
    $request .= "&pass=".$password;
    $request .= "&msg=".rawurlencode($message);
    $request .= "&dest=".$to;
    $request .= "&exp=".rawurlencode($from);
    $request .= "&mode=".$typesms;
    return @file_get_contents($path.$request); 
} 

function getSMSCredit_2($user, $password){ 
    $path="http://www.envoyersms.org/exe/api.php";
    $request = "?login=".$user."&pass=".$password."&action=credit";
    $buffer = @file_get_contents($path.$request); 
    return (substr($buffer, 0, 7)==='CREDIT ')? (int)substr($buffer, 7) : 'ERREUR'; 
}

//=====================================================================
// SMS clickatell.com - ancien
//=====================================================================

function connectSMS_3($user, $password, $api) {
    $baseurl ="http://api.clickatell.com";
    $url = "$baseurl/http/auth?user=$user&password=$password&api_id=$api";
    $ret = file($url);
    $sess = preg_split('/:/',$ret[0]);
    if ($sess[0] == "OK") {
        $sess_id = trim($sess[1]);
        return "OK:$sess_id";
    }
    else return "KO:$ret[0]";
}

function sendSMS_3($sess_id, $to, $message){ 
    $baseurl ="http://api.clickatell.com";
    $text = rawurlencode($message);
    $url = "$baseurl/http/sendmsg?session_id=$sess_id&to=$to&text=$text";
    $ret = file($url);
    $send = preg_split('/:/',$ret[0]);
    //print_r ($ret);
    if ($send[0] == "ID") return "OK";
    else return "$send[0]:$send[1]";
        
} 

function getSMSCredit_3($sess_id){
    global $phone_prefix;
    // see https://www.clickatell.com/pricing-and-coverage/standard-coverage/
    if ( $phone_prefix == '33' ) $sms_cost = 1.5;
    else $sms_cost = 1;
     $baseurl ="http://api.clickatell.com";
    $url = "$baseurl/http/getbalance?session_id=$sess_id";
    $ret = file($url);
    $send = preg_split('/:/',$ret[0]);
    //print_r ($ret);
    //Credit: 250.0 SMS.
    if ($send[0] == "Credit") {
         $credit =  intval( $send[1] / $sms_cost );
         return $credit;
    }
    else return "ERREUR";
}

//=====================================================================
// SMS clickatell.com - nouveau
//=====================================================================

function sendSMS_6($to, $message, $token){
    if ( substr($to,0,1) <> '+' ) $to = "+".$to;
    $url="https://platform.clickatell.com/messages";
    $postData = array(
        'content' => $message ,
        'to' => array("$to"),
        );
    $requestConfig = array(
        'http' => array(
            'method'    => 'POST',
            'header'    =>  array( "Content-Type: application/json",
                                   "Accept: application/json", 
                                   "Authorization: ".$token
                            ),
            'content'   => json_encode($postData),
        )
    );
    // echo "<pre>";
    // print_r($requestConfig);
    // echo "</pre>";
    $result = file_get_contents($url, false, stream_context_create($requestConfig));
    if ($result === false)
        throw new Exception("Problem reading data from $url");
    $response = json_decode( $result , true );
    // echo "<pre>";
    // print_r($response);
    // echo "</pre>";
    if (isset($response["messages"]["0"]["accepted"])) {
        return intval($response["messages"]["0"]["accepted"]);
    }
    else
        return 0;
}

function getSMSCredit_6($token){
    return "OK";
}

//=====================================================================
// SMS Android GATEWAY  
//=====================================================================
// see http://www.abavala.com/2012/07/09/sms-gateway-une-passerelle-sms-a-la-maison
// $url = 'http://192.123.22.333:9090/sendsms?phone=0612121212&text=Hello'&password=xxxxx;

function sendSMS_4($to, $message, $password, $url){ 
    $request ="http://".$url."/sendsms?";
    $request .= "phone=".$to;
    $request .= "&text=".rawurlencode($message);
    $request .= "&password=$password";
    return @file_get_contents($request); 
} 


function getSMSCredit_4($url){
    if (! test_with_curl($url)) return  'ERREUR1';
    $buffer = @file_get_contents("http://".$url); 
    if ( strpos($buffer,"Welcome to") !== false ) return "Solde illimité";
    else return  'ERREUR2';
}

function test_with_curl($url) {
    // without curl, bypass the test
    if (! function_exists('curl_version')) return true;
    // test if URL responds in less than 1 second, else raise an error
    $ch = curl_init("http://".$url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($ch);
    if( curl_errno($ch) ) return  false;
    return true;
}

//=====================================================================
// SMS Eagle
//=====================================================================

function getSMSCredit_8($url){
    if (! test_with_curl($url)) return  'ERREUR1';
    $buffer = @file_get_contents("http://".$url);
    if ( strpos($buffer,"SMSEagle") !== false ) return "Solde illimité";
    else return  'ERREUR2';
}

function sendSMS_8($to, $message, $user, $password, $url){ 
    $base_url = "http://".$url."/index.php/http_api/send_sms";
    $params = array(
        'login'     => $user,
        'pass'      => $password,
        'to'        => $to,
        'message'   => $message,
    );
    $data = '?'.http_build_query($params);
    return @file_get_contents($base_url.$data);
} 


//=====================================================================
// SMSGatewayMe 
//=====================================================================

function sendSMS_7($to, $message, $token, $deviceID){
    include_once "lib/SMSGatewayMe/smsGateway.php";
    $smsGateway = new SmsGateway($token);
    $result = $smsGateway->sendMessageToNumber($to, $message, $deviceID);
    
    //echo "<pre>";
    //print_r($result);
    //echo "</pre>";
    
    if (isset($result["response"][0]["status"]))
        if ( in_array($result["response"][0]["status"], array('success','pending'))) return 1;

    return 0;
}

function getSMSCredit_7($token, $deviceID) {
    include_once "lib/SMSGatewayMe/smsGateway.php";
    $smsGateway = new SmsGateway($token);
    $result = $smsGateway->getDevice($deviceID);
    if (isset ($result["status"])) {
        $status = intval($result["status"]);
        if ( $status == 200 ) return "Solde illimité";
    }
    return  'ERREUR';
}

//=====================================================================
// SMS Mode
//=====================================================================

function sendSMS_5($to, $message, $from, $user, $password){
    if ($to == '') return 0;
    $path="https://api.smsmode.com/http/1.6/sendSMS.do";
    $request = "?pseudo=".urlencode($user);
    $request .= "&pass=".urlencode($password);
    $request .= "&message=".rawurlencode($message);
    $request .= "&numero=".$to;
    $request .= "&emetteur=".rawurlencode($from);
    $buffer = file_get_contents($path.$request);
    //$buffer="0 | Accepté | SIlEzLzeADJE";
    $ret = explode(" | ",$buffer);
    return intval($ret[0]);
} 

function getSMSCredit_5($user, $password){ 
    $path="https://api.smsmode.com/http/1.6/credit.do";
    $request = "?pseudo=".urlencode($user)."&pass=".urlencode($password);
    $buffer = rtrim(file_get_contents($path.$request));
    if ( $buffer < 0 ) 
        return "Solde illimité";
    else if ( intval($buffer) > 0 and $buffer <> 0 ) 
        return $buffer;
    if ( $buffer == '0' )
        return $buffer;
    return $buffer."ERREUR";
}


//===================================================================
// Envoi des SMS
//===================================================================
// param1 = $id (P_ID de l'expéditeur)
// param2 = $dest (liste des P_ID ou numeros de telephone, séparés par virgules)
// param3 = message
// param4 = section qui paye les SMS
// param5 = page qui a cree les SMS

function send_sms ( $id, $dest, $message, $section, $create_page ) {

    global $dbc, $cisurl, $star_pic, $error_pic, $maxchar_sms;
    $SMS_CONFIG=get_sms_config($section);
    $provider = $SMS_CONFIG[1];
    $phonelist = mySmsGet("$dest",'data', $provider);
    $phone_numbers=explode(",", $phonelist);
    $nb_phone_numbers =  count($phone_numbers);
    $nb = mySmsGet("$dest",'nb', $provider);
    $message=substr($message,0,$maxchar_sms);
    $message=fixcharset($message);
    $from = substr(fixcharset(my_ucfirst($_SESSION['SES_PRENOM']." ".strtoupper($_SESSION['SES_NOM']))), 0, 11);
    $sent=0;
    //---------------------------
    // EnvoyerSMSpro.com 
    //---------------------------
    if ( $provider== 1 ) {
        $credits = getSMSCredit_1($SMS_CONFIG[2],$SMS_CONFIG[3]);
        if ( $credits === 0 ) {
             write_msgbox("ERREUR", $error_pic, 
            "Vous n'avez plus de crédits chez ".$SMS_CONFIG[6]."<br>
            <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
         
        }
        else if ($credits == 'ERREUR' ) {
             write_msgbox("ERREUR", $error_pic, 
            "Impossible de se connecter chez ".$SMS_CONFIG[6]."<br>
            Vérifiez identifiant et mot de passe dans la configuration
            <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
        else {
            $resultXML=sendSMS_1($phone_numbers, "$message", "$from" , $SMS_CONFIG[2],$SMS_CONFIG[3]);

            if($resultXML->status=="success") {
                $messageObect=$resultXML->message;
                $sent=intval($messageObect->sms_sent);
            }
        
            if ( $sent  > 0) {
                write_msgbox("OK", $star_pic, 
                "Le sms a bien été envoyé à <b>".$sent."</b> numéros de téléphone sur ".$nb."<br>
                <p><font face=courrier-new size=1>Le texte du SMS:<br> ".nl2br($message)."</font>
                <p>Il vous reste:<b> ".$messageObect->sms_remaining."</b> crédits chez ".$SMS_CONFIG[6]."</b>
                <p align=center><a href='index.php'><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
            }
            else {
                write_msgbox("ERREUR", $error_pic, 
                "Aucun SMS sur ".$nb." n'a été envoyé.<br>
                Une erreur est survenue lors de l'envoi du SMS via ".$SMS_CONFIG[6].":<br>".$resultXML->status."<br>".$resultXML->error->error_message."
                <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
            }
        }
    }
    
    //---------------------------
    // EnvoyerSMS.org 
    //---------------------------
    if ( $provider == 2 ) {
        /* Messages correspondants aux différents retours possibles de l'API */ 
        $description = array('OK' => 'Message envoyé avec succès',
                         'ERR_01' => 'Login ou mot de passe incorrect', 
                         'ERR_02' => 'Manque de paramètres', 
                         'ERR_03' => 'Crédit insuffisant', 
                         'ERR_04' => 'Le numéro du destinataire est invalide', 
                         'ERR_05' => 'Message vide ou trop long (160 caracteres)'); 
        
        $credits = getSMSCredit_2($SMS_CONFIG[2],$SMS_CONFIG[3]);
        if ( $credits == 0 ) {
             write_msgbox("ERREUR", $error_pic, 
            "Vous n'avez plus de crédits chez ".$SMS_CONFIG[6]."<br>
            <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
         
        }
        else if ($credits == 'ERREUR' ) {
             write_msgbox("ERREUR", $error_pic, 
            "Impossible de se connecter chez ".$SMS_CONFIG[6]."<br>
            Vérifiez identifiant et mot de passe dans la configuration
            <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
        else { // on peut envoyer
            for($i=0; $i < $nb_phone_numbers ; $i++){
                 $number = $phone_numbers[$i];
                $retour=sendSMS_2($number, "$message", "$from" , $SMS_CONFIG[2],$SMS_CONFIG[3]); 
                if(array_key_exists($retour, $description)) $reponse=$description[$retour]; 
                if ( $retour == 'OK') $sent = $sent +1;
            }
            if ( $sent  > 0) {
                write_msgbox("OK", $star_pic, 
                "Le sms a bien été envoyé à <b>".$sent."</b> numéros de téléphone sur ".$nb."<br>
                <p><font face=courrier-new size=1>Le texte du SMS:<br> ".nl2br($message)."</font>
                <p>Il vous reste:<b> ".getSMSCredit_2($SMS_CONFIG[2],$SMS_CONFIG[3])."</b> crédits chez ".$SMS_CONFIG[6]."</b>
                <p align=center><a href='index.php'><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
            }
            else {
                 write_msgbox("ERREUR", $error_pic, 
                "Aucun SMS sur ".$nb." n'a été envoyé.<br>
                Une erreur est survenue lors de l'envoi du SMS via ".$SMS_CONFIG[6].":<br>".$reponse."<br>
                <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
            }
        }
    }
    //---------------------------
    // SMSMode.com
    //---------------------------
    if ( $provider == 5 ) {
        /* Messages correspondants aux différents retours possibles de l'API */ 
        $description = array('0' => 'Accepté - le message a été accepté par le système et est en cours de traitement',
                         '31' => 'Erreur interne', 
                         '32' => 'Erreur d’authentification', 
                         '33' => 'Crédit insuffisant', 
                         '35' => 'Paramètre obligatoire manquant', 
                         '50' => 'Temporairement inaccessible'); 
        
        $credits = getSMSCredit_5($SMS_CONFIG[2],$SMS_CONFIG[3]);
        if ( $credits == 0 and $credits <> "Solde illimité" ) {
             write_msgbox("ERREUR", $error_pic, 
            "Vous n'avez plus de crédits chez ".$SMS_CONFIG[6]."<br>
            <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
         
        }
        else if ($credits == 'ERREUR' ) {
             write_msgbox("ERREUR", $error_pic, 
            "Impossible de se connecter chez ".$SMS_CONFIG[6]."<br>
            Vérifiez identifiant et mot de passe dans la configuration
            <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
        else { // on peut envoyer
            for($i=0; $i < $nb_phone_numbers ; $i++){
                 $number = $phone_numbers[$i];
                $retour=sendSMS_5($number, "$message", "$from" , $SMS_CONFIG[2],$SMS_CONFIG[3]); 
                if(array_key_exists($retour, $description)) $reponse=$description[$retour];
                if ( $retour == '0') $sent = $sent +1;
            }
            if ( $sent  > 0) {
                write_msgbox("OK", $star_pic, 
                "Le sms a bien été envoyé à <b>".$sent."</b> numéros de téléphone sur ".$nb."<br>
                <p><font face=courrier-new size=1>Le texte du SMS:<br> ".nl2br($message)."</font>
                <p>Il vous reste:<b> ".getSMSCredit_5($SMS_CONFIG[2],$SMS_CONFIG[3])."</b> chez ".$SMS_CONFIG[6]."</b>
                <p align=center><a href='index.php'><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
            }
            else {
                 write_msgbox("ERREUR", $error_pic, 
                "Aucun SMS sur ".$nb." n'a été envoyé.<br>
                Une erreur est survenue lors de l'envoi du SMS via ".$SMS_CONFIG[6].":<br>".$reponse."<br>
                <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
            }
        }
    }
    //---------------------------
    // clickatell.com - ancien
    //---------------------------
    if ( $provider == 3 ) {
        $conn = preg_split("/:/",connectSMS_3($SMS_CONFIG[2],$SMS_CONFIG[3],$SMS_CONFIG[4]));
        if ( $conn[0] == 'KO' ) {
                 write_msgbox("ERREUR", $error_pic, 
                "Une erreur est survenue lors de la connexion à ".$SMS_CONFIG[6].":<br>".$conn[1]." ".$conn[2]."<br>
                <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
        else { // on peut envoyer
            for($i=0; $i < $nb_phone_numbers ; $i++){
                 $number = $phone_numbers[$i];
                 $retour = sendSMS_3("$conn[1]", "$number", "$message");
                if ( $retour == 'OK' )  {
                     $sent = $sent +1;
                }
            }
            if ( $sent  <> 0) {
                 write_msgbox("OK", $star_pic, 
                "Le sms a bien été envoyé à <b>".$sent."</b> numéros de téléphone sur ".$nb."<br>
                <p><font face=courrier-new size=1>Le texte du SMS:<br> ".nl2br($message)."</font>
                <p>Il vous reste:<b> ".getSMSCredit_3($conn[1])."</b> crédits</b>
                <p>en utilisant votre api ".$SMS_CONFIG[4]." de ".$SMS_CONFIG[6]."</b>
                <p align=center><a href='index.php'><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
            }
            else {
                write_msgbox("ERREUR", $error_pic, 
                "Une erreur est survenue lors de l'envoi, en utilisant votre api ".$SMS_CONFIG[4]." de ".$SMS_CONFIG[6].".<br>
                $retour<br>
                <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
            }
        }
    }
    //---------------------------
    // clickatell.com - nouveau
    //---------------------------
    if ( $provider == 6 ) {
        for ($i=0; $i < $nb_phone_numbers ; $i++){
            $number = $phone_numbers[$i];
            $retour=sendSMS_6($number, "$message", $SMS_CONFIG[4]); 
            $sent = $sent + intval($retour);
        }
        if ( $sent  > 0) {
            write_msgbox("OK", $star_pic, 
            "Le sms a bien été envoyé à <b>".$sent."</b> numéros de téléphone sur ".$nb."<br>
            <p><font face=courrier-new size=1>Le texte du SMS:<br> ".nl2br($message)."</font>
            <p>Il vous reste:<b> ".getSMSCredit_6($SMS_CONFIG[3])."</b> crédits chez ".$SMS_CONFIG[6]."</b>
            <p align=center><a href='index.php'><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
        else {
            write_msgbox("ERREUR", $error_pic, 
            "Aucun SMS sur ".$nb." n'a été envoyé.<br>
            Une erreur est survenue lors de l'envoi du SMS via ".$SMS_CONFIG[6].":<br>
            <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
    }
    //---------------------------
    // Android SMS GATEWAY
    //---------------------------
    if ( $provider == 4 ) {
        for($i=0; $i < $nb_phone_numbers ; $i++){
            $number = $phone_numbers[$i];
            $retour=sendSMS_4($number, "$message", $SMS_CONFIG[3], $SMS_CONFIG[4]);
            if ( $retour ) $sent = $sent +1;
        }
        if ( $sent  > 0) {
            write_msgbox("OK", $star_pic, 
                "Le sms a bien été envoyé à <b>".$sent."</b> numéros de téléphone sur ".$nb."<br>
                <p><font face=courrier-new size=1>Le texte du SMS:<br> ".nl2br($message)."</font>
                <p align=center><a href='index.php'><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
        else {
             write_msgbox("ERREUR", $error_pic, 
                "Aucun SMS sur ".$nb." n'a été envoyé.<br>
                Une erreur est survenue lors de l'envoi du SMS 
                <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
    }
    //---------------------------
    // SMS Eagle
    //---------------------------
    if ( $provider == 8 ) {
        for($i=0; $i < $nb_phone_numbers ; $i++){
            $number = $phone_numbers[$i];
            $retour=sendSMS_8($number, "$message", $SMS_CONFIG[2], $SMS_CONFIG[3], $SMS_CONFIG[4]);
            if (substr($retour,0,2) == "OK") $sent = $sent +1;
        }
        if ( $sent  > 0) {
            write_msgbox("OK", $star_pic, 
                "Le sms a bien été envoyé à <b>".$sent."</b> numéros de téléphone sur ".$nb."<br>
                <p><font face=courrier-new size=1>Le texte du SMS:<br> ".nl2br($message)."</font>
                <p align=center><a href='index.php'><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
        else {
             write_msgbox("ERREUR", $error_pic, 
                "Aucun SMS sur ".$nb." n'a été envoyé.<br>
                Une erreur est survenue lors de l'envoi du SMS 
                <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
    }
    
    
    
    //---------------------------
    // SMSGateway.me
    //---------------------------
    if ( $provider == 7 ) {
        $token = $SMS_CONFIG[3];
        $deviceID = $SMS_CONFIG[4];
        for($i=0; $i < $nb_phone_numbers ; $i++){
            $to = $phone_numbers[$i];
            $retour=sendSMS_7($to, "$message",  $token, $deviceID );
            if ( $retour ) $sent = $sent +1;
        }
         
        
        if ( $sent  > 0) {
            write_msgbox("OK", $star_pic, 
                "Le sms a bien été envoyé à <b>".$sent."</b> numéros de téléphone sur ".$nb."<br>
                <p><font face=courrier-new size=1>Le texte du SMS:<br> ".nl2br($message)."</font>
                <p align=center><a href='index.php'><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
        else {
             write_msgbox("ERREUR", $error_pic, 
                "Aucun SMS sur ".$nb." n'a été envoyé.<br>
                Une erreur est survenue lors de l'envoi du SMS 
                <p align=center><a href=".$create_page."><input type='submit' class='btn btn-secondary' value='Retour'></a>",30,0);
        }
    }
    //---------------------------
    // save history
    //---------------------------
    if ( $sent > 0 ) {
        // insérer dans la table smslog
        $query="insert into smslog (P_ID, S_DATE, S_NB, S_TEXTE, S_ID, S_PROVIDER)
         select ".$id.", NOW(),'".$sent."',\"".$message."\", ".$SMS_CONFIG[0].",\"".$SMS_CONFIG[6]."\"";
        $result=mysqli_query($dbc,$query);
    }
    return $sent;
}

?>