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
$import_url = $url."/api/import/event.php";

// -------------------------------------------------------------------
// provide the key for accessing import API
// -------------------------------------------------------------------
// for test purpose, use local server $import_api_token is defined in the config
// in a real world hardcode the key here
// $import_api_token = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

// -------------------------------------------------------------------
// prepare the json payload from array
// -------------------------------------------------------------------

$data = array(
    "token" => "".$import_api_token."",
    "event_code" => "0",
    "event_name"=>"Formation initiale PSC1",
    "event_type" => "FOR",
    "location" => "bureau ADPC 07",
    "address" => "16 rue de la Serre, 06800 Cagnes sur Mer",
    "section" => "1",
    "competence" => "51",
    "type_formation" => "I",
    "stagiaires" => "8",
    "contact_entreprise" => "Pierre Dupont",
    "contact_tel" => "0635963635",
    "comment" => "C'est une formation de secourisme pour les débutants",
    "tarif" => "59",
    "url" => "https://adpc06.org",
    "telephone" => "0805625800",
    "event_sessions" => array (
        array("session_id" => "1" ,"start" => "2020-11-17 11:15","end" => "2020-11-17 18:00" ),
        array("session_id" => "2" ,"start" => "2020-11-18 10:00","end" => "2020-11-18 16:30" )
        ),
    "people" => array (
        array("user_id" => "81" ,"function_id" => "1" ),
        array("user_id" => "429856","function_id" => "3")
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

