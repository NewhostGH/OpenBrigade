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
check_all(37);
$C_ID=intval($_GET["C_ID"]);
$TCR_CODE=secure_input($dbc,$_GET["TCR_CODE"]);
if (isset($_GET["P_ID"])) $P_ID=intval($_GET["P_ID"]);
else $P_ID=-1;

$query="select S_ID from company where C_ID=".$C_ID;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$S_ID=$row["S_ID"];

if (! check_rights($_SESSION['id'], 37, $S_ID)) check_all(24);

// current
$query3="select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION
         from company_role cr, pompier p
         where p.P_ID = cr.P_ID
         and cr.C_ID=".$C_ID." 
         and cr.TCR_CODE = '".$TCR_CODE."'";
$result3=mysqli_query($dbc,$query3);
$row3=@mysqli_fetch_array($result3);
$CURPID=intval(@$row3["P_ID"]);

$nomenu=1;
writehead();
echo "<script type='text/javascript' src='js/company.js'></script>";

// ------------------------------------
// enregistrement nouveau responsable
// ------------------------------------
if ( $P_ID >= 0 ) {
    $query="delete from company_role where C_ID=".$C_ID." and TCR_CODE='".$TCR_CODE."'";
    $result=mysqli_query($dbc,$query);
    if ( $P_ID > 0 ) {
            $query="insert company_role (C_ID,TCR_CODE,P_ID) 
                values (".$C_ID.",'".$TCR_CODE."',".$P_ID.")";
            $result=mysqli_query($dbc,$query);
    }
    echo "<body onload=\"displaymanager('".$C_ID."');\">";
    exit;
}

// ------------------------------------
// choix nouveau responsable
// ------------------------------------
write_modal_header("Choix des responsables");

?>
<script type="text/javascript">

</script>
</head>
<body class="top15">
<?php

echo "<div align=center><table class=noBorder>
      <tr><td>
      <h5>".get_company_name("$C_ID")."</h5></td></tr>
      </table>";

// infos role
$query2="select TCR_DESCRIPTION, TCR_CODE from type_company_role 
         where TCR_CODE='".$TCR_CODE."'";
$result2=mysqli_query($dbc,$query2);
$row2=@mysqli_fetch_array($result2);
$TCR_DESCRIPTION=$row2["TCR_DESCRIPTION"];

echo "<table class=noBorder>";
echo "<tr>
         <th><strong>Choix ".$TCR_DESCRIPTION."</strong></th>
      </tr>";

//lisbox
echo "<tr><td>Nom: 
        <select id='resp' name='resp' class='smallcontrol3'
            onchange=\"saveresponsable('".$C_ID."','".$TCR_CODE."',document.getElementById('resp').value);\">
           <option value='0' selected >--personne--</option>\n
        <OPTGROUP LABEL='Personnel externe'>\n";

// list personnel externe
$query="select p.P_ID, p.P_PRENOM, p.P_NOM, s.S_CODE, p.P_SECTION, c.C_NAME, c.C_ID
        from pompier p, section s, company c
        where p.P_CODE <> '1234'
           and p.P_SECTION = s.S_ID
        and p.C_ID = c.C_ID
        and P_STATUT = 'EXT'";
if ( $S_ID <> 0 ) $query .= " and  p.P_SECTION in (".get_family("$S_ID").")";
$query .= " order by P_NOM";

$result=mysqli_query($dbc,$query);

while (custom_fetch_array($result)) {
    if ( $C_ID > 0 ) $ent="- ".$C_NAME;
    else $ent="";
    if ( $P_ID == $CURPID ) $selected='selected';
    else $selected=""; 
    $detail = "(".$S_CODE." ".substr($ent,0,30).")";
    echo "<option value='".$P_ID."' $selected>".strtoupper($P_NOM)." 
              ".ucfirst($P_PRENOM)." ".$detail."</option>\n";
}

echo "<OPTGROUP LABEL='Personnel interne'>\n";
// list personnel interne
$query="select p.P_ID, p.P_PRENOM, p.P_NOM, s.S_CODE, p.P_SECTION, c.C_NAME, c.C_ID
        from pompier p, section s, company c
        where p.P_CODE <> '1234'
           and p.P_SECTION = s.S_ID
        and p.C_ID = c.C_ID
        and P_STATUT <> 'EXT'";
if ( $S_ID <> 0 ) $query .= " and  p.P_SECTION in (".get_family("$S_ID").")";
$query .= " order by P_NOM";

$result=mysqli_query($dbc,$query);

while (custom_fetch_array($result)) {
    if ( $C_ID > 0 ) $ent="- ".$C_NAME;
    else $ent="";
    if ( $P_ID == $CURPID ) $selected='selected';
    else $selected="";
    $detail = "(".$S_CODE." ".substr($ent,0,30).")";
    echo "<option value='".$P_ID."' $selected>".strtoupper($P_NOM)." 
              ".ucfirst($P_PRENOM)." ".$detail."</option>\n";
}

echo "</select>";
echo "</td></tr></table>";
echo "</div>";
echo "<p>";
writefoot();
?>
