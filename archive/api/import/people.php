<?php

/* --------------------------------------------------------------------
call the API like this
curl -X POST https://localhost/ebrigade/api/import/event.php \
-d data=<json array>

input json array example

{   
    "token":"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    "action":"ImportPersonnel",
    "P_ID":"0",
    "P_CODE":"jdupont23",
    "P_NOM":"dupont",
    "P_NOM_NAISSANCE":"martin",
    "P_PRENOM":"jeanne",
    "P_PRENOM2":"françoise",
    "P_BIRTHDATE":"1996-10-15",
    "P_BIRTHPLACE":"paris",
    "P_BIRTHDEP":"75",
    "P_SEXE":"M",
    "P_CIVILITE":"2",
    "P_MDP":"3417357e6cf7541bca502b918a74826e",
    "P_DATE_ENGAGEMENT":"2020-11-05",
    "P_SECTION":"200",
    "P_STATUT":"BEN",
    "P_EMAIL":"jeanne.dupont@gmail.com",
    "P_PHONE":"0630229911",
    "P_PHONE2":"0626555555",
    "P_ADDRESS":"3 rue de l'église",
    "P_ZIP_CODE":"17400",
    "P_CITY":"Saint Jean d'Angély",
    "P_PAYS":"65",
    "P_RELATION_PRENOM":"Pierre",
    "P_RELATION_NOM":"Dupont",
    "P_RELATION_PHONE":"061010110",
    "P_RELATION_MAIL":"pierre.dupont@yahoo.fr",
    "competences":[
        {"id":"2","expiration":"2022-12-31"},
        {"id":"19","expiration":"2020-10-30"}
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
//   Parse json input file and validate token
//==========================================================
$error_msg="";

// test import api activated
if ( ! $import_api ) return_error(10,"Import API not activated");

// test json input
$input = json_decode(file_get_contents('php://input'), true);
if ( ! $input )  return_error(20,"Incorrect json input");

// test token
if (isset($input['token'])) $token=secure_input($dbc,$input['token']);
else return_error(30,"token not provided");
if ( $token <> $import_api_token ) return_error(40,"Wrong token, access to this webservice forbidden");

//==========================================================
//   Validations user inputs
//==========================================================

if (isset($input['action'])) $action=secure_cleanup_string($input['action']);
else return_error(1050,"Missing action_code");
if ( $action <> "ImportPersonnel" and $action <> "UpdatePersonnel" ) return_error(1051,"Invalid action_code: ".$action);

if (isset($input['P_CODE'])) $P_CODE=secure_cleanup_string(str_replace("'","",$input['P_CODE']));
else return_error(1060,"Missing P_CODE");
if (! validateString($P_CODE,20,$numbers_allowed=true)) return_error(1061,"Invalid P_CODE (maximum 20 characters)");

if (isset($input['P_ID'])) $P_ID=intval($input['P_ID']);
else $P_ID=0;

if (isset($input['P_STATUT'])) $P_STATUT=secure_cleanup_string($input['P_STATUT']);
else return_error(1065,"Missing P_STATUT");
if (! validateString($P_STATUT,5)) return_error(1066,"Invalid P_STATUT (maximum 5 characters)");
$NB=count_entities("statut", "S_STATUT='".$P_STATUT."'");
if ($NB == 0 ) return_error(1067,"Invalid P_STATUT ".$P_STATUT.", it does not exist in the database");

if (isset($input['P_NOM'])) $P_NOM=secure_cleanup_string($input['P_NOM'], $lower=true);
else return_error(1070,"Missing P_NOM");
if (! validateString($P_NOM,30)) return_error(1071,"Invalid P_NOM (maximum 30 characters)");

if (isset($input['P_PRENOM'])) $P_PRENOM=secure_cleanup_string($input['P_PRENOM']);
else return_error(1080,"Missing P_PRENOM");
if (! validateString($P_PRENOM,25)) return_error(1081,"Invalid P_PRENOM (maximum 25 characters)");

if (isset($input['P_PRENOM2'])) $P_PRENOM2=secure_cleanup_string($input['P_PRENOM2'], $lower=true);
else $P_PRENOM2="";
if (! validateString($P_PRENOM2,25)) return_error(1082,"Invalid P_PRENOM2 (maximum 25 characters)");

if (isset($input['P_NOM_NAISSANCE'])) $P_NOM_NAISSANCE=secure_cleanup_string($input['P_NOM_NAISSANCE'], $lower=true );
else $P_NOM_NAISSANCE="";
if (! validateString($P_NOM_NAISSANCE,30)) return_error(1084,"Invalid P_NOM_NAISSANCE (maximum 30 characters)");

$expected_format='Y-m-d';
if (isset($input['P_BIRTHDATE'])) $P_BIRTHDATE=secure_input($dbc,$input['P_BIRTHDATE']);
else return_error(1090,"Missing P_BIRTHDATE");
validateDate($P_BIRTHDATE, $format = $expected_format) or return_error(1091,"P_BIRTHDATE is invalid ".$P_BIRTHDATE." expected format is '".$expected_format."'");

if (isset($input['P_BIRTHPLACE'])) $P_BIRTHPLACE=secure_cleanup_string($input['P_BIRTHPLACE'], $lower=true);
else $P_BIRTHPLACE="";
if (! validateString($P_BIRTHPLACE,40)) return_error(1092,"Invalid P_BIRTHPLACE (maximum 40 characters)");

if (isset($input['P_BIRTHDEP'])) $P_BIRTHDEP=secure_input($dbc,$input['P_BIRTHDEP']);
else $P_BIRTHDEP="";
if (! validateString($P_BIRTHDEP,3,$numbers_allowed=true)) return_error(1093,"Invalid P_BIRTHDEP (maximum 3 characters)");

if (isset($input['P_SEXE'])) $P_SEXE=secure_input($dbc,$input['P_SEXE']);
else return_error(1100,"Missing P_SEXE");
if ($P_SEXE <> 'M' and $P_SEXE <> 'F') return_error(1101,"Invalid P_SEXE (M or F)");

if (isset($input['P_CIVILITE'])) $P_CIVILITE=intval($input['P_CIVILITE']);
else return_error(1110,"Missing P_CIVILITE");
if ($P_CIVILITE <> 1 and $P_CIVILITE <> 2 and $P_CIVILITE <> 3) return_error(1111,"Invalid P_CIVILITE (1,2 or 3)");

if (isset($input['P_MDP'])) {
    $P_MDP=secure_input($dbc,$input['P_MDP']);
    if (! validateMd5String($P_MDP)) return_error(1115,"Invalid P_MDP, expected md5 encoded string (32 characters)");
}
else
    $P_MDP = md5(generatePassword());

if (isset($input['P_DATE_ENGAGEMENT'])) $P_DATE_ENGAGEMENT=secure_input($dbc,$input['P_DATE_ENGAGEMENT']);
else return_error(1120,"Missing P_DATE_ENGAGEMENT");
validateDate($P_DATE_ENGAGEMENT, $format = $expected_format) or return_error(1121,"P_DATE_ENGAGEMENT is invalid ".$P_DATE_ENGAGEMENT." expected format is '".$expected_format."'");

if (isset($input['P_SECTION'])) $P_SECTION=secure_input($dbc,$input['P_SECTION']);
else return_error(1130,"Missing P_SECTION");
if ( intval($P_SECTION) == 0 and $P_SECTION !== 0 ) return_error(1131,"Invalid P_SECTION ".$P_SECTION." must be numeric");
$NB=count_entities("section", "S_ID=".$P_SECTION);
if ($NB == 0 ) return_error(1132,"Invalid P_SECTION ".$P_SECTION." it does not exist");

if (isset($input['P_EMAIL'])) $P_EMAIL=secure_input($dbc,$input['P_EMAIL']);
else $P_EMAIL="";
if ( ! validateEmail($P_EMAIL) and $P_EMAIL <> '' ) return_error(1140,"P_EMAIL is invalid ".$P_EMAIL);

if (isset($input['P_PHONE'])) $P_PHONE=secure_cleanup_string($input['P_PHONE']);
else $P_PHONE='';
if ( $P_PHONE <> "" and intval($P_PHONE) == 0 ) return_error(1150,"invalid P_PHONE ".$P_PHONE);

if (isset($input['P_PHONE2'])) $P_PHONE2=secure_input($dbc,str_replace("\"","",$input['P_PHONE2']));
else $P_PHONE2='';
if ( $P_PHONE2 <> "" and intval($P_PHONE2) == 0 ) return_error(1151,"invalid P_PHONE2 ".$P_PHONE2);

if (isset($input['P_ADDRESS'])) $P_ADDRESS=secure_cleanup_string($input['P_ADDRESS']);
else $P_ADDRESS="";
if (! validateString($P_ADDRESS,150, $numbers_allowed = true)) return_error(1160,"Invalid P_ADDRESS: $P_ADDRESS (maximum 150 characters)");

if (isset($input['P_ZIP_CODE'])) $P_ZIP_CODE=secure_input($dbc,str_replace("\"","",utf8_decode($input['P_ZIP_CODE'])));
else $P_ZIP_CODE="";
if (intval($P_ZIP_CODE) == 0 and $P_ZIP_CODE <> '') return_error(1170,"Invalid P_ZIP_CODE");

if (isset($input['P_CITY'])) $P_CITY=secure_cleanup_string($input['P_CITY']);
else $P_CITY="";
if (! validateString($P_CITY,30, $numbers_allowed = true)) return_error(1180,"Invalid P_CITY");

if (isset($input['P_RELATION_PRENOM'])) $P_RELATION_PRENOM=secure_cleanup_string($input['P_RELATION_PRENOM']);
else $P_RELATION_PRENOM="";
if (! validateString($P_RELATION_PRENOM,20)) return_error(1200,"Invalid P_RELATION_PRENOM (maximum 20 characters)");

if (isset($input['P_RELATION_NOM'])) $P_RELATION_NOM=secure_cleanup_string($input['P_RELATION_NOM']);
else $P_RELATION_NOM="";
if (! validateString($P_RELATION_NOM,30)) return_error(1201,"Invalid P_RELATION_NOM (maximum 30 characters)");

if (isset($input['P_RELATION_PHONE'])) $P_RELATION_PHONE=secure_cleanup_string($input['P_RELATION_PHONE']);
else $P_RELATION_PHONE='';
if ( $P_RELATION_PHONE <> "" and intval($P_RELATION_PHONE) == 0 ) return_error(1210,"invalid P_RELATION_PHONE ".$P_RELATION_PHONE);

if (isset($input['P_RELATION_MAIL'])) $P_RELATION_MAIL=secure_input($dbc,$input['P_RELATION_MAIL']);
else $P_RELATION_MAIL="";
if ( ! validateEmail($P_RELATION_MAIL) and $P_RELATION_MAIL <> '' ) return_error(1220,"P_RELATION_MAIL is invalid ".$P_RELATION_MAIL);

if (isset($input['P_PAYS'])) $P_PAYS=intval($input['P_PAYS']);
else $P_PAYS='65';
if ( intval($P_PAYS) == 0 ) return_error(1230,"Invalid P_PAYS ".$P_PAYS." must be numeric");
$NB=count_entities("pays", "ID=".$P_PAYS);
if ($NB == 0 ) return_error(1231,"Invalid P_PAYS ".$P_PAYS.", it does not exist");

if (isset($input['P_GRADE'])) $P_GRADE=secure_input($dbc,$input['P_GRADE']);
else $P_GRADE="-";
if (! validateString($P_GRADE,6)) return_error(1240,"Invalid P_GRADE (maximum 6 characters)");
$NB=count_entities("grade", "G_GRADE=\"".$P_GRADE."\"");
if ($NB == 0 ) return_error(1241,"Invalid P_GRADE ".$P_GRADE.", it does not exist");

if (isset($input['competences'])) {
    $competences=$input['competences'];
    foreach ( $competences as $competence ) {
        $PS_ID = intval($competence['id']);
        if ( $PS_ID == 0 ) return_error(1300,"Wrong value for competence ".$competence['id']." must be numeric");
        $NB=count_entities("poste", "PS_ID='".$PS_ID."'");
        if ( $NB == 0 ) return_error(1301,"Wrong value for competence ".$PS_ID." not found");
        if ( isset($competence['expiration'])) {
            $expiration = secure_input($dbc,$competence['expiration']);
            validateDate($expiration, $format = $expected_format) or return_error(1302,"expiration date is invalid ".$expiration." expected format is '".$expected_format."'");
        }
    }
}

//==========================================================
//   Avoid creation of duplicate
//==========================================================
if ( $P_ID == 0 ) {
    $query=" select P_ID, P_BIRTHDATE, P_STATUT, P_OLD_MEMBER
            from pompier
            where P_NOM=\"".$P_NOM."\"
            and P_PRENOM=\"".$P_PRENOM."\"
            and ( P_BIRTHDATE is null or P_BIRTHDATE = '".$P_BIRTHDATE."')";
    if ( $P_ID > 0 ) $query .=" and P_ID <> ".$P_ID;

    $res=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($res);
    if (@$row[0] > 0 ) 
        return_error(1400,"You are trying to insert a duplicate Name,Firstname,Birthdate see this person: ".$row[0]);

    $query=" select count(1) from pompier where P_CODE='".$P_CODE."'";
    if ( $P_ID > 0 ) $query .=" and P_ID <> ".$P_ID;
    $res=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($res);
    if (@$row[0] > 0 ) 
        return_error(1400,"You are trying to insert a duplicate see this person: ".$row[0]);
}
//==========================================================
//   Perform database insert
//==========================================================

$by=get_admin_id();
$user=0;
if ( $action == "ImportPersonnel" ) {
        if ( $P_STATUT == 'EXT' ) $permission=-1;
        else  $permission=0;
        $query="insert into pompier 
                (P_CODE,P_PRENOM,P_PRENOM2,P_NOM,P_NOM_NAISSANCE,P_SEXE,P_CIVILITE,P_STATUT, P_MDP, P_DATE_ENGAGEMENT, P_BIRTHDATE,
                 P_BIRTHPLACE, P_BIRTH_DEP, P_SECTION, GP_ID, GP_ID2, P_EMAIL, P_PHONE, P_PHONE2,
                 P_ADDRESS,P_CITY,P_ZIP_CODE,
                 P_RELATION_NOM,P_RELATION_PRENOM,P_RELATION_PHONE,P_RELATION_MAIL, P_HIDE,
                 P_CREATE_DATE, P_PAYS,P_GRADE)
                values (\"".$P_CODE."\",\"".$P_PRENOM."\",\"".$P_PRENOM2."\",\"".$P_NOM."\",\"".$P_NOM_NAISSANCE."\",'".$P_SEXE."','".$P_CIVILITE."','".$P_STATUT."',\"".$P_MDP."\",'".$P_DATE_ENGAGEMENT."','".$P_BIRTHDATE."',
                   \"".$P_BIRTHPLACE."\",\"".$P_BIRTHDEP."\",".$P_SECTION.",".$permission.", ".$permission.",\"".$P_EMAIL."\",\"".$P_PHONE."\",\"".$P_PHONE2."\",
                   \"".$P_ADDRESS."\",\"".strtoupper($P_CITY)."\",\"".$P_ZIP_CODE."\",
                   \"".$P_RELATION_NOM."\",\"".$P_RELATION_PRENOM."\",\"".$P_RELATION_PHONE."\",\"".$P_RELATION_MAIL."\",1,
                   CURDATE(), ".$P_PAYS.",\"".$P_GRADE."\")";
        if ( $debug ) print $query."<br>";
        $result=mysqli_query($dbc,$query) or return_error(1420,"Error during insert ".$P_NOM." ".$P_PRENOM);
        $user=get_code($P_CODE);
        $query ="update pompier set ID_API=".$P_ID." where P_ID=".$P_ID;
        $result=mysqli_query($dbc,$query) or return_error(1422,"Error during update ID_API ".$P_ID." ".$P_NOM." ".$P_PRENOM);
        $msg="Insert successful, new user created, number ".$user;
        insert_log('INSP', $user, $complement="insert via API");
}
else if ( $action == "UpdatePersonnel" ) {
    if ( $P_ID == 0 ) return_error(1430,"Error invalid P_ID, must be provided and numeric");
    $NB=count_entities("pompier", "P_ID='".$P_ID."'");
    if ( $NB == 0 ) return_error(1431,"Wrong value for P_ID, ".$P_ID." not found in the database.");

    $NB=count_entities("pompier", "P_ID=".$P_ID." and P_NOM = \"".$P_NOM."\"");
    if ( $NB == 0 ) return_error(1432,"This provided P_ID,".$P_ID." does not match the name P_NOM ".$P_NOM);
    
    $user = $P_ID;
    $query="update pompier set
            P_CODE=\"".$P_CODE."\",
            P_PRENOM=\"".$P_PRENOM."\",
            P_PRENOM2=\"".$P_PRENOM2."\",
            P_NOM=\"".$P_NOM."\",
            P_NOM_NAISSANCE=\"".$P_NOM_NAISSANCE."\",
            P_SEXE='".$P_SEXE."',
            P_CIVILITE='".$P_CIVILITE."',
            P_DATE_ENGAGEMENT='".$P_DATE_ENGAGEMENT."',
            P_BIRTHDATE='".$P_BIRTHDATE."',
            P_BIRTHPLACE=\"".$P_BIRTHPLACE."\",
            P_BIRTH_DEP=\"".$P_BIRTHDEP."\",
            P_SECTION=".$P_SECTION.",
            P_EMAIL=\"".$P_EMAIL."\",
            P_PHONE=\"".$P_PHONE."\",
            P_PHONE2=\"".$P_PHONE2."\",
            P_ADDRESS=\"".$P_ADDRESS."\",
            P_CITY=\"".strtoupper($P_CITY)."\",
            P_ZIP_CODE=\"".$P_ZIP_CODE."\",
            P_RELATION_NOM=\"".$P_RELATION_NOM."\",
            P_RELATION_PRENOM=\"".$P_RELATION_PRENOM."\",
            P_RELATION_PHONE=\"".$P_RELATION_PHONE."\",
            P_RELATION_MAIL=\"".$P_RELATION_MAIL."\",
            P_PAYS=".$P_PAYS."
            where P_ID=".$user."
            and ID_API=".$user;
    $result=mysqli_query($dbc,$query) or return_error(1450,"Error during update ".$P_ID." ".$P_NOM." ".$P_PRENOM);
    insert_log('UPDP', $P_ID, $complement="update via API");
    $msg="Update successful, for user ".$user;
}

if ( count($competences) > 0 and $user > 0) {
    $query="delete from qualification where P_ID=".$user;
    if ( $debug ) print $query."<br>";
    $result=mysqli_query($dbc,$query) or return_error(1510,"Error during delete competences ".$user);
    $query="insert into qualification (P_ID,PS_ID, Q_VAL, Q_EXPIRATION, Q_UPDATED_BY, Q_UPDATE_DATE) values";
    foreach ($competences as $competence) {
        if ( isset($competence['expiration']))
            $query .= "(".$user.", ".$competence['id'].", 1, '".$competence['expiration']."', ".$by.", NOW() ),";
        else 
            $query .= "(".$user.", ".$competence['id'].", 1, null, ".$by.", NOW() ),";
    }
    $query = rtrim($query,',');
    if ( $debug ) print $query."<br>";
    $result=mysqli_query($dbc,$query) or return_error(1520,"Error during insert competences ".$user);
}

//==========================================================
//   output success json
//==========================================================
return_error("0",$msg);


