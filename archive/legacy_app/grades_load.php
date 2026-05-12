<?php
include_once ("config.php");
check_all(18);
test_permission_level(18);
get_session_parameters();

$query = $_SESSION['query'];
$result=mysqli_query($dbc, $query);
$totalNotFiltered=mysqli_num_rows($result);
$out="";

if (isset($_GET["data"])) {
    header('Content-Type: application/json; charset=ISO-8859-1');
    $out = "{
      \"total\": ".$totalNotFiltered.",
      \"totalNotFiltered\": ".$totalNotFiltered.",
      \"rows\": [";
while (custom_fetch_array($result)) {

    $checked = $G_FLAG == 1 ? "checked" : "";
    if ($NB_grade_actif > 0) {
        $class = 'active';
        $typeclass = 'active-badge';
    }
    else {
        $class = '';
        $typeclass = 'inactive-badge';
    }

    if ( $grades == 1 ) {
        $file=$G_ICON;
        if ( file_exists($file)) $t="<img class='img-max-30 ' style='border-radius: 3px;' src=".$G_ICON." title=".$G_DESCRIPTION.">";
        else $t = "<span>".$G_DESCRIPTION."</span>";
    }

    $g_grade = "'".$G_GRADE."'";

    $out .= "{
         \"id\":\"".$G_GRADE."\",
         \"grade\":\"<div style='display:none;'>".$G_LEVEL."</div>$t<span class='badge $typeclass ml-3'>$NB_grade_actif</span>\",
         \"categorie\":\"<span class='boldy'>".$G_CATEGORY."</span>\",
         \"description\":\"<span class='boldy'>".$G_DESCRIPTION."</span>\",
         \"code\":\"<span class='boldy'>".$G_GRADE."</span>\",
         \"niveau\":\"<span class='boldy'>".$G_LEVEL."</span>\",
         \"actif\":\"<label class='switch'><input type=checkbox id='etatgrade' name='etatgrade' value='".$G_FLAG."' $checked onchange=activGrade($g_grade)><span class='slider round'></span></label>\"
        },";
}
$out = rtrim($out , ",");
    $out .= "]}";
    $out = str_replace("\\", "", $out);
    echo $out;
    exit;
}
