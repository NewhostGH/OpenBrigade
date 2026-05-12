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
writehead();

if (isset ($_POST['evenement'])) $evenement = intval($_POST['evenement']);
else $evenement = intval($_GET['evenement']);

if ( ! is_operateur_pc($id,$evenement)) 
    check_all(76);

$query="select S_ID from evenement where E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$S_ID=$row[0];

if (check_rights($id, 15, $S_ID)) $granted_event=true;
else if ( is_chef_evenement($id, $evenement) ) $granted_event=true;
else if ( is_operateur_pc($id,$evenement)) $granted_event=true;
else {
    check_all(15);
    check_all(24);
}


// cas changement center
if ( isset ($_POST['centerlat'])) {
    $lat = (float)($_POST['centerlat']);
    $lng = (float)($_POST['centerlng']);
    $_SESSION['centerMapFor'] = $evenement;
    $_SESSION['centerlat'] = $lat;
    $_SESSION['centerlng'] = $lng;
    exit;
}

// cas changement zoomlevel
if ( isset ($_POST['zoomlevel'])) {
    $zoomlevel = intval($_POST['zoomlevel']);
    $query="update geolocalisation set ZOOMLEVEL=".$zoomlevel." where type='E' and  CODE = ".$evenement;
    $result=mysqli_query($dbc,$query);
    exit;
}

// cas changement maptypeid
if ( isset ($_POST['maptypeid'])) {
    $maptypeid = secure_input($dbc,$_POST['maptypeid']);
    $query="update geolocalisation set MAPTYPEID='".strtoupper($maptypeid)."' where type='E' and  CODE = ".$evenement;
    $result=mysqli_query($dbc,$query);
    exit;
}


// cas deplacement custom marker
if ( isset ($_POST['custom'])) {
    $custom=intval($_POST['custom']);
    if ( isset ($_POST['latitude'])) {
        $lat = (float) $_POST['latitude'];
        $lng = (float) $_POST['longitude'];
        if ( $lat == 0 ) {
            $query="delete from geolocalisation where TYPE in ('1','2','3','4') and CODE = ".$evenement." and CODE2=".$custom;
        }
        else {
            $query="update geolocalisation set LAT='".$lat."', LNG='".$lng."' where TYPE in ('1','2','3','4') and CODE = ".$evenement." and CODE2=".$custom;
        }
        $result=mysqli_query($dbc,$query);
    }
    exit;
}

// cas deplacement flag
if ( isset ($_POST['flag'])) {
    $flag=intval($_POST['flag']);
    if ( isset ($_POST['latitude']) ) {
        $lat = (float) $_POST['latitude'];
        $lng = (float) $_POST['longitude'];
        if ( $lat == 0 ) {
            $query="delete from geolocalisation where TYPE='F' and CODE = ".$evenement." and CODE2=".$flag;
        }
        else {
            $query="update geolocalisation set LAT='".$lat."', LNG='".$lng."' where TYPE='F' and CODE = ".$evenement." and CODE2=".$flag;
        }
        $result=mysqli_query($dbc,$query);
    }
    exit;
}

// changement nom flag
if ( isset ($_GET['flag'])) {
    $_SESSION['addflag'] = '0';
    $_SESSION['addmarker'] = '0';
    $flag=intval($_GET['flag']);
    if ( isset ($_GET['flagtxt'])) {
        $txt = mysqli_real_escape_string($dbc,$_GET['flagtxt']);
        $txt = str_replace("\"","",$txt);
        $query="update geolocalisation set COMMENT=\"".$txt."\" where TYPE='F' and CODE = ".$evenement." and CODE2=".$flag;
        $result=mysqli_query($dbc,$query);
    }
    echo "<body onload=\"javascript:history.back(1);\">";
    exit;
}

// modification geolocalisation ou status equipe
if ( isset ($_POST['equipe'])) {
    $equipe = intval($_POST['equipe']);
    if ( isset ($_POST['latitude'])) {
        $lat = (float) $_POST['latitude'];
        $lng = (float) $_POST['longitude'];
        $address = secure_input($dbc,utf8_decode($_POST['address']));

        $query="update geolocalisation set LAT='".$lat."', LNG='".$lng."' where TYPE='Q' and CODE = ".$evenement." and CODE2=".$equipe;
        $result=mysqli_query($dbc,$query);

        if ( mysqli_affected_rows($dbc) == 0 ) {
            $query="insert into geolocalisation(TYPE,CODE,CODE2,LAT,LNG) values ('Q',".$evenement.",".$equipe.",'".$lat."','".$lng."')";
            $result=mysqli_query($dbc,$query);
        }

        $query="update evenement_equipe set EE_ADDRESS=\"".$address."\" where E_CODE=".$evenement." and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
    }

    // modification status
    if ( isset ($_POST['status'])) {
        $status = intval($_POST['status']);
        $query="update evenement_equipe set IS_ID=".$status." where E_CODE=".$evenement." and EE_ID=".$equipe;
        $result=mysqli_query($dbc,$query);
    }
}

// modification geolocalisation intervention
if ( isset ($_POST['intervention'])) {
    $intervention = intval($_POST['intervention']);
    if ( isset ($_POST['latitude'])) {
        $lat = (float) $_POST['latitude'];
        $lng = (float) $_POST['longitude'];
        $address = secure_input($dbc,utf8_decode($_POST['address']));

        $query="update geolocalisation set LAT='".$lat."', LNG='".$lng."' where TYPE='I' and CODE = ".$evenement." and CODE2=".$intervention;
        $result=mysqli_query($dbc,$query);

        if ( mysqli_affected_rows($dbc) == 0 ) {
            $query="insert into geolocalisation(TYPE,CODE,CODE2,LAT,LNG) values ('I',".$evenement.",".$intervention.",'".$lat."','".$lng."')";
            $result=mysqli_query($dbc,$query);
        }

        $query="update evenement_log set EL_ADDRESS=\"".$address."\" where E_CODE=".$evenement." and EL_ID=".$intervention;
        $result=mysqli_query($dbc,$query);
    }
}

// modification geolocalisation cav
if ( isset ($_POST['cav'])) {
    $cav = intval($_POST['cav']);
    if ( isset ($_POST['latitude'])) {
        $lat = (float) $_POST['latitude'];
        $lng = (float) $_POST['longitude'];
        $address = secure_input($dbc,utf8_decode($_POST['address']));

        $query="update geolocalisation set LAT='".$lat."', LNG='".$lng."' where TYPE='C' and CODE = ".$evenement." and CODE2=".$cav;
        $result=mysqli_query($dbc,$query);

        if ( mysqli_affected_rows($dbc) == 0 ) {
            $query="insert into geolocalisation(TYPE,CODE,CODE2,LAT,LNG) values ('C',".$evenement.",".$cav.",'".$lat."','".$lng."')";
            $result=mysqli_query($dbc,$query);
        }

        $query="update centre_accueil_victime set CAV_ADDRESS=\"".$address."\" where E_CODE=".$evenement." and CAV_ID=".$cav;
        $result=mysqli_query($dbc,$query);
    }
}
writefoot();
?>
