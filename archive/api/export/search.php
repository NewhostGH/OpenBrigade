<?php

/* --------------------------------------------------------------------
call the API like this
curl -X POST https://localhost/ebrigade/api/export/search.php \
-d data=<json array>

input json array example
{
    "token":"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "lastname":"marche",
    "qstrict":"1"
}

Or see example in test/search_people.php
-----------------------------------------------------------------------*/

$base="../..";
include_once ($base."/config.php");
$dbc=connect();
$debug=0;

//==========================================================
//   Function generate error
//==========================================================

header('Content-Type: application/json; charset=utf-8');

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
if ( ! $webservice_key ) return_error(10,"Export API not activated");

// test json input
$input = json_decode(file_get_contents('php://input'), true);

if ( ! $input ) return_error(20,"Incorrect json input");

// test token
if (isset($input['token'])) $token=secure_input($dbc,$input['token']);
else return_error(30,"token not provided");
if ( $token <> $webservice_key ) return_error(40,"Wrong token $token instead of $webservice_key, access to this webservice forbidden");

$sq = array();
if (isset($input["qstrict"])) $qstrict=intval($input["qstrict"]);
else $qstrict=0;
if (isset($input["exclude_old"])) $exclude_old=intval($input["exclude_old"]);
else $exclude_old=0;
if (isset($input["id"])) $id = $input["id"];
else $id=0;
if (isset($input["username"])) $username = $input["username"];
else $username=0;
if (isset($input["lastname"])) $lastname = $input["lastname"];
else $lastname=0;
if (isset($input["firstname"])) $firstname = $input["firstname"];
else $firstname=0;
if (isset($input["phone"])) $phone = $input["phone"];
else $phone=0;
if (isset($input["email"])) $email = $input["email"];
else $email=0;

if ($id) {
    $id=intval($id);
    $sq[]= "p.P_ID = $id";
}

if ($username) {
    $username=secure_input($dbc,$username);
    if($qstrict)
        $sq[]= "p.P_CODE = lower(\"".$username."\")";
    else
        $sq[]= "p.P_CODE like lower(\"".$username."%\")";
}

if ($lastname) {
    $lastname=secure_input($dbc,$lastname);
    if($qstrict)
        $sq[]= "p.P_NOM = lower(\"".$lastname."\")";
    else
        $sq[]= "p.P_NOM like lower(\"".$lastname."%\")";
}

if ($firstname){
    $firstname=secure_input($dbc,$firstname);
    if($qstrict)
        $sq[]= "p.P_PRENOM = lower(\"".$firstname."\")";
    else
        $sq[]= "p.P_PRENOM like lower(\"".$firstname."%\")";
}

if ($phone){
    $phone=secure_input($dbc,$phone,$strict=true);
    if($qstrict)
        $sq[]= "(p.p_phone = \"".$phone."\" or p.p_phone2 = \"".$phone."\")";
    else
        $sq[]= "(p.p_phone like \"".$phone."%\" or p.p_phone2 like \"".$phone."%\")";
}

if ($email){
    $email=secure_input($dbc,$email,$strict=true);
    if($qstrict)
        $sq[]= "p.P_EMAIL = \"".$email."\"";
    else
        $sq[]= "p.P_EMAIL like \"".$email."%\"";
}

$query="select distinct
P_ID, P_NOM, P_PRENOM, P_EMAIL, P_CODE, P_BIRTHDATE, P_PHONE, P_PHONE2, P_SEXE, P_OLD_MEMBER, P_SECTION
from pompier p
where ".implode(' and ', $sq)."
and p.p_statut <> 'EXT'";

if ($exclude_old) {
    $query .= " and p.P_OLD_MEMBER=0";
}

$result=mysqli_query($dbc,$query);
$pompiers = array();

while ($row=@mysqli_fetch_array($result)) {
    $competences=array();
    $query2="select p.TYPE, p.PS_ID from qualification q, poste p
    where p.PS_ID = q.PS_ID
    and ( q.Q_EXPIRATION is null or q.Q_EXPIRATION >= NOW() )
    and q.P_ID=".$row['P_ID'];
    $result2=mysqli_query($dbc,$query2);

    while ($row2=@mysqli_fetch_array($result2)) {
        $competences[$row2["PS_ID"]]=utf8_encode($row2["TYPE"]);
    }

    $data=array();
    $data['id'] = $row['P_ID'];
    $data['username'] = $row['P_CODE'];
    $data['lastname'] = utf8_encode($row['P_NOM']);
    $data['firstname'] = utf8_encode($row['P_PRENOM']);
    $data['email'] = $row['P_EMAIL'];
    $data['birthdate'] = $row['P_BIRTHDATE'];
    $data['phone'] = $row['P_PHONE'];
    $data['phone2'] = $row['P_PHONE2'];
    $data['sexe'] = $row['P_SEXE'];
    $data['section'] = $row['P_SECTION'];
    $data['skills'] = $competences;
    $pompiers[]=$data;
}

echo json_encode($pompiers);
