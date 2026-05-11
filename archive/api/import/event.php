<?php

/* --------------------------------------------------------------------
call the API like this
curl -X POST https://localhost/ebrigade/api/import/event.php \
-d data=<json array>

input json array example

{   
    "token": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    "event_code":"0",
    "event_name":"Formation initiale PSC1",
    "event_type":"FOR",
    "location":"bureau ADPC 06",
    "address":"16 rue de la Serre, 06800 Cagnes sur Mer"
    "section":"1",
    "competence":"51",
    "type_formation":"I",
    "stagiaires":"8",
    "contact_entreprise":"Pierre Dupont",
    "contact_tel":"0635963635",
    "comment":"C'est une formation de secourisme pour les débutants",
    "tarif":"59",
    "url":"https://adpc06.org",
    "telephone" : "0805625800",
    "event_sessions":[
        {"session_id":"1","start":"2020-11-17 10:15","end":"2020-11-17 18:00"},
        {"session_id":"2","start":"2020-11-18 10:00","end":"2020-11-18 16:00"}
    ],
    "people":[
        {"user_id":"560","function_id":"5"},
        {"user_id":"560","function_id":"3"}
    ],
}

Or see example in test/event.php
-----------------------------------------------------------------------*/

$base="../..";
include_once ($base."/config.php");
$dbc=connect();
$debug=0;

//==========================================================
//   Function generate error
//==========================================================

function return_error($num,$msg) {
    if ($num == 0 ) $status="success";
    else $status="error";
    print "{\"status\": \"".$status."\",\"errnum\": \"".$num."\",\"message\": \"".$msg."\"}";
    exit;
}

//==========================================================
//   Parse json input file and validate input
//==========================================================
$error_msg="";

// test import api activated
if ( ! $import_api ) return_error(10,"Import API not activated");

// test json input
$input = json_decode(file_get_contents('php://input'), true);
if ( ! $input )  return_error(20,"Incorrect json input");

// test token
if (isset($input['token'])) $token=secure_input($dbc,$input['token'],$strict=true);
else return_error(30,"token not provided");
if ( $token <> $import_api_token ) return_error(40,"Wrong token, access to this webservice forbidden expected");

// test other parameters
if (isset($input['event_code'])) $event_code=intval($input['event_code']);
else return_error(1050,"Missing event_code");

if (isset($input['event_name'])) $event_name=secure_input($dbc,str_replace("\"","",utf8_decode($input['event_name'])));
else return_error(1060,"Missing event_name");

if (isset($input['event_type'])) $event_type=secure_input($dbc,$input['event_type']);
else return_error(1070,"Missing event_type");

if (isset($input['location'])) $location=secure_input($dbc,str_replace("\"","",utf8_decode($input['location'])));
else return_error(1080,"Missing location");

if (isset($input['address'])) $address=secure_input($dbc,str_replace("\"","",utf8_decode($input['address'])));
else return_error(1090,"Missing address");

if (isset($input['section'])) $section=intval($input['section']);
else return_error(1100,"Missing section");

if (isset($input['comment'])) $comment=secure_input($dbc,str_replace("\"","",utf8_decode($input['comment'])));
else $comment="";

if (isset($input['tarif'])) $tarif=floatval($input['tarif']);
else $tarif="null";

if (isset($input['url'])) $url=secure_input($dbc,str_replace("\"","",utf8_decode($input['url'])));
else $url="";
if ( $url <> '' and ! filter_var($url, FILTER_VALIDATE_URL))
    return_error(1110,"URL invalid: ".$url);

if (isset($input['competence'])) $competence=intval($input['competence']);
else $competence=0;
if ( $competence > 0 ) {
    $NB=count_entities("poste", "PS_ID='".$competence."' and PS_FORMATION=1");
    if ( $NB == 0 ) return_error(1120,"Wrong value for competence");
}

if (isset($input['contact_entreprise'])) $contact_entreprise=secure_input($dbc,str_replace("\"","",utf8_decode($input['contact_entreprise'])));
else $contact_entreprise='';

if (isset($input['contact_tel'])) $contact_tel=secure_input($dbc,str_replace("\"","",$input['contact_tel']));
else $contact_tel='';
if ( $contact_tel <> "" and intval($contact_tel) == 0 )
        return_error(1130,"contact_tel invalid: ".$contact_tel);

if (isset($input['telephone'])) $telephone=secure_input($dbc,str_replace("\"","",$input['telephone']));
else $telephone='';
if ( $telephone <> "" and intval($telephone) == 0 )
        return_error(1140,"telephone invalid: ".$telephone);


if (isset($input['type_formation'])) $type_formation=secure_input($dbc,$input['type_formation']);
else $type_formation="";
if ( $type_formation <> '' ) {
    $NB=count_entities("type_formation", "TF_CODE='".$type_formation."'");
    if ( $NB == 0 ) return_error(1150,"Wrong value for type_formation, try 'I' or 'R'");
}

if (isset($input['stagiaires'])) $stagiaires=intval($input['stagiaires']);
else $stagiaires="0";

if (isset($input['event_sessions'])) {
    $event_sessions=array();
    $found_1=false;
    foreach ( $input['event_sessions'] as $i ) {
        $num = intval($i['session_id']);
        if ( $num == 1) $found_1=true;
        $start[$num] = secure_input($dbc,$i['start']);
        $end[$num] = secure_input($dbc,$i['end']);
        $duration[$num] = 0;
    }
    if ( ! $found_1 ) return_error(1200,"No_session 1");
}
else return_error(1210,"Missing event_sessions");

if (isset($input['people'])) {
    $functions=array();
    $users=array();
    foreach ( $input['people'] as $j ) {
        if ( ! isset($j['user_id'])) return_error(1300,"Wrong values for people");
        if ( ! isset($j['function_id'])) return_error(1310,"Wrong values for people");
        $user = intval($j['user_id']);
        if ( $user == 0 ) return_error(1320,"Wrong values for people, user ".$j['user_id']);
        array_push($users,$user);
        $functions[$user] = intval($j['function_id']);
    }
}

//==========================================================
//   Validations user inputs
//==========================================================

$prevdate='';
$expected_format='Y-m-d H:i';
for ($k=1; $k <= sizeof($start); $k++) {
    validateDate($start[$k], $format = $expected_format) or return_error(1400,"start date $k is invalid ".$start[$k]." expected format is '".$expected_format."'");
    validateDate($end[$k], $format = $expected_format) or return_error(1410,"end date $k is invalid ".$end[$k]." expected format is '".$expected_format."'");
    $start_date = strtotime($start[$k]);
    $end_date = strtotime($end[$k]);
    $duration[$k] = ($end_date - $start_date)/60/60;
    if ( $end_date < $start_date )
        return_error(1420,"dates of session $k are invalid end date must be after start date");
    if( $prevdate <> '' ) {
        if ( $start[$k] < $prevdate ) {
            return_error(1430,"Inconsistent dates, sessions must be in chronological order");
        }
    }
    $prevdate = $start[$k];
}

// test event type
$NB=count_entities("type_evenement", "TE_CODE='".$event_type."'");
if ($NB == 0 )
    return_error(1450,"Wrong event_type ".$event_type.", possible values: FOR");

// avoid creation of duplicate
if ( $event_code == 0 ) {
    $query="select e.E_CODE from evenement e, evenement_horaire eh where e.TE_CODE=\"".$event_type."\" and e.S_ID=".$section."
            and eh.E_CODE = e.E_CODE and eh.EH_ID=1 and eh.EH_DATE_DEBUT = '".substr($start[1],0,10)."' and eh.EH_DEBUT='".substr($start[1],11,5)."'";
    $res=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($res);
    if ($row[0] > 0 ) 
        return_error(1460,"You are trying to insert a duplicate see this event: ".$row[0]);
}
else {
    // test existence of event if event_code > 0
    $NB=count_entities("evenement", "E_CODE=".$event_code."");
    if ($NB == 0 )
        return_error(1470,"Unknown event code: ".$event_code);
}


//==========================================================
//   Perform database insert
//==========================================================

// some data are not managed by import API yet
$heure_rdv=substr($start[1],11,5);
$nombre=0;
$company=0;
$visible_outside=0;
$exterieur=0;

if ( $competence == 0 ) $competence = "null";
if ( $type_formation == '' ) $type_formation = "null";
else $type_formation = "'".$type_formation."'";

$by=get_admin_id();

if ( $event_code == 0 ) {
    $evenement=generate_evenement_number();
    $query="insert into evenement (E_CODE, TE_CODE, S_ID, E_LIBELLE, E_LIEU, E_HEURE_RDV, E_NB, E_COMMENT,
            E_CREATE_DATE, E_CREATED_BY, C_ID, E_CONTACT_LOCAL, E_CONTACT_TEL, E_ADDRESS, E_VISIBLE_OUTSIDE, E_TARIF, E_NB_STAGIAIRES, E_EXTERIEUR, E_URL,
            PS_ID, TF_CODE, E_TEL)
            values (".$evenement.",\"".$event_type."\",".$section.",\"".$event_name."\",\"".$location."\",'".$heure_rdv."',".$nombre.",\"".$comment."\",
            NOW(),".$by.",".$company.",\"".$contact_entreprise."\",\"".$contact_tel."\",\"".$address."\",'".$visible_outside."',".$tarif.",".$stagiaires.", ".$exterieur.",\"".$url."\",
            ".$competence.",".$type_formation.",\"".$telephone."\")";
    if ( $debug ) print $query."<br>";
    $result=mysqli_query($dbc,$query) or return_error(1480,"Error during insert event ".$evenement);
    insert_log("INSEVT", $evenement, $complement="insert via API", $code="");
    $msg="Insert successful, new event created, number ".$evenement;
}
else {
    $evenement = $event_code;
    $query="delete from evenement_horaire where E_CODE=".$evenement;
    if ( $debug ) print $query."<br>";
    $result=mysqli_query($dbc,$query) or return_error(1485,"Error during delete sessions ".$evenement);
    $query="update evenement set 
            TE_CODE=\"".$event_type."\",
            S_ID=".$section.",
            E_LIBELLE=\"".$event_name."\",
            E_LIEU=\"".$location."\",
            E_HEURE_RDV= '".$heure_rdv."',
            E_NB=".$nombre.",
            E_COMMENT=\"".$comment."\",
            C_ID=".$company.",
            E_CONTACT_LOCAL=\"".$contact_entreprise."\",
            E_CONTACT_TEL=\"".$contact_tel."\",
            E_ADDRESS=\"".$address."\",
            E_VISIBLE_OUTSIDE='".$visible_outside."',
            E_TARIF=".$tarif.",
            E_NB_STAGIAIRES=".$stagiaires.",
            E_EXTERIEUR=".$exterieur.",
            E_URL=\"".$url."\",
            PS_ID=".$competence.",
            TF_CODE=".$type_formation.",
            E_TEL=\"".$telephone."\"
            where E_CODE=".$evenement;
    if ( $debug ) print $query."<br>";
    $result=mysqli_query($dbc,$query) or return_error(1490,"Error during update event ".$evenement);
    $msg = "Update successful, event number ".$evenement;
    insert_log("UPDEVT", $evenement, $complement="update via API", $code="");
}

for ($k=1; $k <= sizeof($start); $k++) {
    $date_start = substr($start[$k],0,10);
    $time_start = substr($start[$k],11,5);
    $date_end = substr($end[$k],0,10);
    $time_end = substr($end[$k],11,5);
    $query="insert into evenement_horaire (E_CODE,EH_ID, EH_DATE_DEBUT,EH_DATE_FIN,EH_DEBUT, EH_FIN, EH_DUREE)
       values (".$evenement.",".$k.",'".$date_start."','".$date_end."','".$time_start."','".$time_end."','".$duration[$k]."')";
    if ( $debug ) print $query."<br>";
    $result=mysqli_query($dbc,$query) or return_error(1500,"Error during insert session ".$k." on event ".$evenement);
}

foreach ($users as $user) {
    $query="delete from evenement_participation where E_CODE=".$evenement." and P_ID=".$user;
    if ( $debug ) print $query."<br>";
    $result=mysqli_query($dbc,$query) or return_error(1510,"Error during delete people ".$user." on event ".$evenement);
    $query="insert into evenement_participation (E_CODE,EH_ID, P_ID, EP_DUREE, TP_ID, EP_DATE, EP_BY)
       select E_CODE, EH_ID, ".$user.", EH_DUREE, ".$functions[$user].", NOW(), ".$by."
       from evenement_horaire where E_CODE=".$evenement;
    if ( $debug ) print $query."<br>";
    $result=mysqli_query($dbc,$query) or return_error(1520,"Error during insert people ".$user." on event ".$evenement);
}
//==========================================================
//   output success json
//==========================================================
return_error("0",$msg);
