<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.2

  # Copyright (C) 2004, 2020 Nicolas MARCHE
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
$id=intval($_SESSION['id']);
destroy_my_session_if_forbidden($id);
writehead();
verify_csrf('personnel_preferences');

if (isset($_POST["pid"])) $pid=intval($_POST["pid"]);
else $pid=$id;

$P_SECTION=get_section_of($pid);
$P_STATUT=get_statut($pid);

if ( $pid <> $id ) {
    if ( $P_STATUT == 'EXT' ) {
        check_all(37);
        if (! check_rights($id, 37,"$P_SECTION")) check_all(24);
    }
    else {
        check_all(2);
        if (! check_rights($id, 2,"$P_SECTION")) check_all(24);
    }
}
?>

<SCRIPT>
function redirect(pid,errcode) {
    self.location.href = "personnel_preferences.php?pid="+pid+"&tab=2&saved="+errcode;
}
</SCRIPT>
<?php

function save_preferences($pid, $prefid, $value, $type){
    global $dbc, $id, $errocde, $affected_rows, $P_STATUT, $P_SECTION;

    if ($type == "INSERT" ) {
        $query = "INSERT INTO personnel_preferences (PP_ID,P_ID,PP_VALUE,PP_DATE) VALUES ( $prefid, $pid, '$value', NOW())";
        $result=mysqli_query($dbc,$query) or $errocde = 1;
    }
    else { 
        if ($prefid == 3) {
            if ( $P_STATUT == 'EXT' or ! check_rights($pid,40)) {
                $value = $P_SECTION;
            }
            $query = "UPDATE pompier SET P_FAVORITE_SECTION = ".intval($value)." WHERE P_ID = ".$pid;
            $result=mysqli_query($dbc,$query) or $errocde = 1;

            if ( $id == $pid ) {
                $_SESSION['SES_FAVORITE'] = intval($value);
                $_SESSION['filter'] = intval($value);
            }
        } 
        else {
            $query = "UPDATE personnel_preferences SET PP_VALUE = '".$value."' WHERE P_ID =".$pid." AND PP_ID = ".intval($prefid);
            $result=mysqli_query($dbc,$query) or $errocde = 1;
        }
    }
    if ( mysqli_affected_rows($dbc) > 0 ) $affected_rows++;

    if ( $id == $pid ) {
        switch ($prefid) {
            case '1':
                $_SESSION['TOOLTIP'] = $value;
                break;
            case '2':
                $_SESSION['LANGUE'] = $value;
                break;
            case '4':
                $_SESSION['sectionorder'] = $value;
                break;
            default:
                //nothing
                break;
        }
    }
}

$query =   "SELECT p.PP_ID, pp.PP_VALUE
            FROM preferences p
            LEFT JOIN personnel_preferences pp
            ON (pp.PP_ID=p.PP_ID and pp.P_ID=".$pid.")
            ORDER BY p.PP_ID";

$result=mysqli_query($dbc,$query);
$errcode='nothing';
$total_error=0;
$affected_rows=0;
while ($row=@mysqli_fetch_array($result)) {
    $ID=$row["PP_ID"];
    $VALUE=$row['PP_VALUE'];
    if ( $ID == 3 ) $VALUE = intval($_SESSION['SES_FAVORITE']);
    if (isset($_POST["f".$ID])) {
        $NEWVALUE=$_POST["f".$ID];
        $NEWVALUE=secure_input($dbc, $NEWVALUE);
        if ($VALUE <> $NEWVALUE) {
            if ($VALUE == NULL and $ID <> 3 ) {
                save_preferences($pid, $ID, $NEWVALUE, "INSERT");
            }
            else {
                save_preferences($pid, $ID, $NEWVALUE, "UPDATE");
            }
        }
    }
}

if ( $errcode == 'nothing' and $affected_rows > 0 )  $errcode=0;

echo "<body onload=\"redirect('".$pid."','".$errcode."');\">";
writefoot();
?>
