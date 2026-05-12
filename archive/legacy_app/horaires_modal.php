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

$pid=intval($_GET["pid"]);
$week=intval($_GET["week"]);
$year=intval($_GET["year"]);
$day=intval($_GET["day"]); // 0 = sunday, 6 = saturday

if ( check_rights($id, 14)) $update_allowed=true;
else if ( $syndicate == 0 and $id == $pid ) $update_allowed=true;
else if ( $syndicate == 1 and $id == $pid ) $update_allowed=false;
else if ( check_rights($id, 13, get_section($pid))) $update_allowed=true;
else $update_allowed=false;

if ( $update_allowed ) $disabled = '';
else $disabled = 'disabled';


// ------------------------
// Save
// ------------------------
if (isset($_GET["comment"]) and $update_allowed) {
    $comment=secure_input($dbc,str_replace("\"","",urldecode($_GET["comment"])));
    $theday=get_day_from_week($week,$year,$day,'N');
    $query ="select count(1) from horaires where P_ID = ".$pid." and H_DATE = '".$theday."'";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $row_exists = intval(@$row[0]);
    if ( $row_exists == 0 )
        $query="insert into horaires (P_ID, H_DATE,H_COMMENT) values ( ".$pid.", '".$theday."',\"".$comment."\" )";
    else 
        $query="update horaires set H_COMMENT = \"".$comment."\" where P_ID = ".$pid." and H_DATE = '".$theday."'";
    $result=mysqli_query($dbc,$query);
    // echo "<body onload=\"javascript:self.location.href='horaires.php?view=week&person=".$pid."&week=".$week."&year=".$year."'\";>";
    echo "<body onload='history.go(-1)'>";
    exit;
}

// ------------------------
// Display
// ------------------------
$theday=get_day_from_week($week,$year,$day,'N');
$day_text=get_day_from_week($week,$year,$day,'S');

$H_COMMENT='';
$query="select H_COMMENT from horaires where P_ID=".$pid." and H_DATE='".$theday."'";
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if ( $H_COMMENT == '' ) $icon='fa-filetext';
else  $icon='fa-file-text';

$modal=true;
$nomenu=1;
writehead();
write_modal_header("<i class='fa ".$icon." fa-lg'></i> <b>".$day_text."</b> Commentaire");

print "
<div align=center><p>
<textarea class='form-control form-control-sm' style='font-size:10pt; font-family:Arial;' cols=50 rows=4 ".$disabled."
    name='modalcomment".$day."' id='modalcomment".$day."'
    title='saisir ici le commentaire pour ce jour de travail'
    onchange=\"update_icon(this,icon_".$day.");\"
>".$H_COMMENT."</textarea>
<p><input type='button' class='btn btn-success' value='Sauvegarder'
onclick=\"update_comment('".$pid."','".$week."','".$year."','".$day."');\">";

writefoot($loadjs=false);
?>