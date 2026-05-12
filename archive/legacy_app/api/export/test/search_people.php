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
$export_url = $url."/api/export/search.php";

// -------------------------------------------------------------------
// provide the key for accessing import API
// -------------------------------------------------------------------
// for test purpose, use local server $webservice_key is defined in the config
// in a real world hardcode the key here
// $webservice_key = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

// -------------------------------------------------------------------
// prepare the json payload from array
// -------------------------------------------------------------------

$data = array(
    "token" => "".$webservice_key."",
    "lastname" => "mare",
    "qstrict" => "1",
);

$payload  = json_encode($data);

// -------------------------------------------------------------------
// use CURL POST to call the API 
// -------------------------------------------------------------------

$ch = curl_init($export_url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
print curl_error($ch);
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

