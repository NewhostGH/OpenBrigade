<?php
// -------------------------------------------------------------------
// this page shows a simple example to test the API
// -------------------------------------------------------------------
$base="../../..";
include_once ($base."/config.php");
$nomenu=1;
writehead();
check_all(14);
echo "<body style='padding-top:0px;'>";

// -------------------------------------------------------------------
// Define eBrigade Server
// -------------------------------------------------------------------
// for test purpose, use local server
$url="http://127.0.0.1/ebrigade";
// in real world write site URL, example
//$url="https://mywebsite.org";
$import_url = $url."/api/import/people.php";

// -------------------------------------------------------------------
// provide the key for accessing import API
// -------------------------------------------------------------------
// for test purpose, use local server $import_api_token is defined in the config
// in a real world hardcode the key here
// $import_api_token = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

// -------------------------------------------------------------------
// prepare the json payload from array UTF-8 encoding required ofr this file
// -------------------------------------------------------------------

$data = array(
    "token" => "".$import_api_token."",
    "action" => "UpdatePersonnel",
    "P_ID" => "475673",
    "P_CODE" => "jdupont23",
    "P_NOM" => "dupont",
    "P_NOM_NAISSANCE" => "martin",
    "P_PRENOM" => "jeanne",
    "P_PRENOM2" => "françoise",
    "P_BIRTHDATE" => "1996-10-15",
    "P_BIRTHPLACE" => "paris",
    "P_BIRTHDEP" => "75",
    "P_SEXE" => "M",
    "P_CIVILITE" => "2",
    "P_MDP" => "3417357e6cf7541bca502b918a74826e",
    "P_DATE_ENGAGEMENT" => "2020-11-05",
    "P_SECTION" => "200",
    "P_STATUT" => "BEN",
    "P_EMAIL" => "jeanne.dupont@gmail.com",
    "P_PHONE" => "0630229911",
    "P_PHONE2" => "0626555555",
    "P_ADDRESS" => "3 rue de l'église",
    "P_ZIP_CODE" => "17400",
    "P_CITY" => "Saint Jean d'Angély",
    "P_PAYS" => "65",
    "P_RELATION_PRENOM" => "Pierre",
    "P_RELATION_NOM" => "Dupont",
    "P_RELATION_PHONE" => "061010110",
    "P_RELATION_MAIL" => "pierre.dupont@yahoo.fr",
    "competences" => array (
        array("id" => "2","expiration" => "2022-12-15"),
        array("id" => "19","expiration" => "2021-08-30"),
        array("id" => "28"),
    )
);

$payload  = json_encode($data);

// -------------------------------------------------------------------
// use CURL POST to call the API 
// -------------------------------------------------------------------

$ch = curl_init($import_url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
$result = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

// -------------------------------------------------------------------
// show json response
// -------------------------------------------------------------------
print $result;

// -------------------------------------------------------------------
// debug traces
// -------------------------------------------------------------------
//var_dump($info);
//print $payload;
// delete from pompier where P_CODE='jdupont23';

