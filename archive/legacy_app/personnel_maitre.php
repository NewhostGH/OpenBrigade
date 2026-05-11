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
$mysection=$_SESSION['SES_SECTION'];

$pid=intval($_GET["pid"]);
$maitre=intval($_GET["maitre"]);
$civilite=intval($_GET["civilite"]);
$his_section=get_section_of($pid);
$his_statut= get_statut($pid);
if (isset($_GET["action"])) $action = $_GET["action"];
else $action="select";

if ($his_statut == 'EXT') {
    check_all(37);
    if ( ! check_rights($id, 37, $his_section )) check_all(24);
}
else {
    check_all(2);
    if ( ! check_rights($id, 2, $his_section )) check_all(24);
}
get_session_parameters();


//=====================================================================
// sauver nouveau maitre
//=====================================================================

if ( $action == "save" ) {
    $query="update pompier set P_MAITRE = ".$maitre.", P_CIVILITE=".$civilite." where P_ID = ".$pid;
    $result=mysqli_query($dbc,$query);
    echo "<body onload=\"javascript:self.location.href='upd_personnel.php?pompier=".$pid."&tab=1'\"/>";
    exit;
}

$nomenu=1;
writehead();
write_modal_header("Choix du maître");

?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/personnel_maitre.js'></script>
<?php

echo "</head>";
echo "<body style='padding-top:10px'>";
//=====================================================================
// recupérer infos animal
//=====================================================================

$query="select P_NOM, P_PRENOM, P_MAITRE from pompier where P_ID=".$pid;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$P_NOM=$row["P_NOM"];
$P_PRENOM=$row["P_PRENOM"];
$P_MAITRE=$row["P_MAITRE"];

if ( $civilite >= 4 ) $pic='dog.png';
else if ( $civilite == 1) $pic='boy.png';
else $pic='girl.png';

echo "<div align=center><table class='noBorder'>
      <tr class=white><td>
      <span class='ebrigade-h4'><img src=images/".$pic." class='img-max-40'> ".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)."</span></td></tr>
      </table>";


//=====================================================================
// choix maitre
//=====================================================================

echo "<div align=center>";
echo "<b>Nom du Maître</b><br>";
      
// liste des personnes
$sectionfilter=true;
    
$query="select P_ID, P_PRENOM, P_NOM, S_CODE
         from pompier, section
            where section.S_ID = P_SECTION
            and P_OLD_MEMBER = 0
         and P_CODE <> '1234'
         and P_STATUT <> 'EXT'
         and P_MAITRE=0
         and P_CIVILITE <= 3
         and P_ID <> ".$pid."
         and ( S_ID in (".get_family("$his_section").") or P_ID = ".$maitre.")";
if ( $his_statut <> 'EXT' ) $query .= " and P_STATUT <> 'EXT'";
$query .=" order by P_NOM, P_PRENOM";

$result=mysqli_query($dbc,$query);

echo "<select id='newmaitre' name='newmaitre'
        onchange=\"choisirM('".$pid."',document.getElementById('newmaitre').value, '".$civilite."')\">";
if ( $maitre == '' ) $selected='selected';
else $selected='';
echo "<option value='0' $selected>choix maître</option>\n";
while ($row=@mysqli_fetch_array($result)) {
        $P_NOM=$row["P_NOM"];
        $P_PRENOM=$row["P_PRENOM"];
        $P_ID=$row["P_ID"];
        $S_CODE=$row["S_CODE"];
        if ( $P_ID == $maitre ) $selected='selected';
        else $selected='';
        echo "<option value='".$P_ID."' $selected>".strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM)."</option>\n";
}
echo "</select></div><p>";

writefoot();
?>
