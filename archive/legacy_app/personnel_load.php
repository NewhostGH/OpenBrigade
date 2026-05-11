<?php
include_once ("config.php");
check_all(0);
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];
get_session_parameters();

// validate permissions
if ( $category == 'EXT' ) {
    if ( intval($_SESSION['SES_COMPANY']) == 0 )
        check_all(37);
    if ( check_rights($id, 37))
        test_permission_level(37);
    else {
        check_all(45);
    }
}
else {
    test_permission_level(56);
}

$query = $_SESSION['query'];

$result=mysqli_query($dbc, $query);
$totalNotFiltered=mysqli_num_rows($result);

$hide_phone=true;
if ( is_chef($id,$filter) )
    $hide_phone=false;
else if ((is_children($filter,$mysection)) or (check_rights($id, 24))) {
    if ( check_rights($id, 2) and $category=='INT' ) $hide_phone=false;
    if ( check_rights($id, 37) and $category=='EXT' ) $hide_phone=false;
    if ( check_rights($id, 12) and $category=='INT' ) $hide_phone=false;
}

$out="";

if (isset($_GET["data"])) {
    header('Content-Type: application/json; charset=ISO-8859-1');
    $out = "{
      \"total\": ".$totalNotFiltered.",
      \"totalNotFiltered\": ".$totalNotFiltered.",
      \"rows\": [";
    while (custom_fetch_array($result)) {

        $img = "<img class='profile-picture img-max-60' ";

        if ($P_OLD_MEMBER > 0)
            $img .= "style='filter: grayscale(100%);'";

        if( ($P_PHOTO <> "") and file_exists($trombidir."/".$P_PHOTO))
            $img .= "src='".$trombidir."/".$P_PHOTO."'>";
        else
            if ($P_SEXE == "F")
                $img .= "src='./images/girl.png'>";
            elseif ($P_SEXE == "M")
                $img .= "src='./images/boy.png'>";

        $t=0;

        if ( $grades == 1 ) {
           $file = $G_ICON;
            if ( file_exists($file) and $G_CATEGORY <> 'PATS' ) $t="<img class='img-max-30' style='border-radius: 3px;' src=".$file." title='".str_replace("'"," ",$G_DESCRIPTION)."'>";
            else $t = "<span title='".$G_DESCRIPTION."'>".$P_GRADE."</span>";
        }

        if ( $P_EMAIL!='' or  check_rights($id, 30)) {
            $checkbox = "<input type='checkbox' name='SendMail' id='SendMail' value='".$P_ID."' />";
        } else {
            $checkbox = "<input type='checkbox' name='SendMail' id='SendMail' value='".$P_ID."' />";
        }

        if ($P_OLD_MEMBER > 0) {
            $etat = "Archivé";
            $label_etat = "label-archive";
        }
        else if ($GP_ID == -1) {
            $etat = "Bloqué";
            $label_etat = "label-bloqued";
        }
        else {
            $etat = "Actif";
            $label_etat = "label-actif";
        }

        if ($pompiers) {
            $index = 0;
        }
        else {
            $index = 1;
        }
        $label_type = 0;
        
        if ( $P_PHONE <> '' ) {
            $P_PHONE=str_replace(" ", "", $P_PHONE);
            if ($P_HIDE == 1 and $hide_phone and $P_ID <> $id)
                $P_PHONE="**********";
        }
        
        if ( $P_PHONE2 <> '' ) {
            $P_PHONE2=str_replace(" ", "", $P_PHONE2);
            if ($P_HIDE == 1 and $hide_phone)
                $P_PHONE2="";
        }
        
        
        $type = "Mauvaise configuration du type d'organisation";
        foreach ($_SESSION['available_statut'] as $value) {
            if ($value[0] == $P_STATUT){
                $label_type = "label-".strtolower($value[0]);
                $type = $value[$index];
            }
        }
        $font = "<font style='font-weight: 600; color: #3F4254; font-size: 13px;'>";
        $out .= "{
         \"id\":\"".$P_ID."\",
         \"checkbox\":\"".$checkbox."\",
         \"photo\":\"<center>".$img."</center>\",
         \"grade\":\"<div style='display:none;'>".$G_LEVEL."</div>$t\",
         \"profession\":\"<span class='boldy'>".$P_PROFESSION."</span>\",
         \"lastname\":\"<div style='display:none'>".str_replace("\t"," ",$P_NOM.$P_PRENOM)."</div><span class='boldy'>".str_replace("\t"," ",trim(strtoupper($P_NOM))." ".trim(my_ucfirst($P_PRENOM)))."</span>\",
         \"telephone\":\"<span class='boldy'>".$P_PHONE." <br> ".$P_PHONE2." </span><div style='display: none;'>".$P_PHONE." ".$P_PHONE2."</div>\",
         \"matricule\":\"<span class='boldy'>".fixcharset($P_CODE)."</span>\",
         \"section\":\"<a href=upd_section.php?S_ID=$P_SECTION><span class='boldy'>$S_CODE</span></a>\",
         \"statut\":\"<span class='label label-inline $label_type'>$type</span>\",
         \"regime\":\"".$P_REGIME."\",
         \"etat\":\"<span class='label label-inline $label_etat'>$etat</span>\",
         \"birthdate\":\"<div style='display:none'>".$P_BIRTHDATE_SORT."</div><span class='boldy'>".$P_BIRTHDATE."</span>\",
         \"entree\": \"<div style='display:none'>".$P_DATE_ENGAGEMENT."</div><span class='boldy'>".$P_DATE_ENGAGEMENT1."</span>\"
        },";
    }
    $out = rtrim($out , ",");
    $out .= "]}";

    $out = str_replace("\\", "", $out);
    // $out = str_replace("/", "", $out);
    // $out = str_replace("'", "", $out);
    echo $out;
    exit;
}