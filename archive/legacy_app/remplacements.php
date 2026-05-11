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

check_all(41);
$id=$_SESSION['id'];
get_session_parameters();
test_permission_level(41);

if (isset($_GET["replaced"])) $replaced = intval($_GET["replaced"]);
else $replaced = 0;

if (isset($_GET["substitute"])) $substitute = intval($_GET["substitute"]);
else $substitute = 0;

writehead();
writeBreadCrumb();
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/remplacements.js'></script>
<?php

echo  "</head><body>";

//=====================================================================
// formulaire filtre
//=====================================================================
echo "<form><div class='table-responsive div-decal-left' align=left>";
if ( $nbsections == 0 ) {
    //filtre section
    echo "<select id='filter' name='filter' onchange=\"changeParam(document.getElementById('filter').value);\"
           class='selectpicker' ".datalive_search()."  data-style='btn-default' data-container='body'>";
    echo display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
}

// choix remplaçant
echo "<select name='substitute' id='substitute' onchange=\"changeParam('".$filter."');\"
        class='selectpicker smalldropdown2' data-live-search='true' data-style='btn-default' data-container='body'>
        <option value='0'>Tous les remplaçants</option>";
$query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE , p.P_STATUT from pompier p
        where p.P_OLD_MEMBER = 0 and P_STATUT <> 'EXT'";
if ( $filter > 0 ) 
    $query.= " and p.P_SECTION in (".get_family("$filter").")";
$query.= " order by P_NOM, P_PRENOM";

$result=mysqli_query($dbc,$query);
while ($row=mysqli_fetch_array($result)) {
    $R = strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
    if ( $grades ) $R .= " (".$row["P_GRADE"].")";
    if ( $row["P_ID"] == $substitute ) $selected='selected';
    else $selected='';
    echo "<option value='".$row["P_ID"]."' class='".$row["P_STATUT"]."' $selected>".$R."</option>";
}
echo"</select>";

// choix remplaçé
echo "<select name='replaced' id='replaced' onchange=\"changeParam('".$filter."');\" 
        class='selectpicker smalldropdown2' data-live-search='true' data-style='btn-default' data-container='body'>
        <option value='0'>Tous les remplacés</option>";
$query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE , p.P_STATUT from pompier p
        where p.P_OLD_MEMBER = 0 and P_STATUT <> 'EXT'";
if ( $filter > 0 ) 
    $query.= " and p.P_SECTION in (".get_family("$filter").")";
$query.= " order by P_NOM, P_PRENOM";
$result=mysqli_query($dbc,$query);
while ($row=mysqli_fetch_array($result)) {
    $R = strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
    if ( $grades ) $R .= " (".$row["P_GRADE"].")";
    if ( $row["P_ID"] == $replaced ) $selected='selected';
    else $selected='';
    echo "<option value='".$row["P_ID"]."' class='".$row["P_STATUT"]."' $selected>".$R."</option>";
}
echo"</select>";

// choix statut 
echo "
<select id='status' name='status' onchange=\"changeParam('".$filter."');\" class='selectpicker smalldropdown2' data-style='btn-default' data-container='body'>";
if ( $status == 'ALL' ) $selected='selected'; else $selected='';
echo "<option value='ALL' $selected>Tous les statuts</option>\n";
if ( $status == 'DEM' ) $selected='selected'; else $selected='';
echo  "<option value='DEM' $selected>Demandé</option>\n";
if ( $status == 'ACC' ) $selected='selected'; else $selected='';
echo  "<option value='ACC' $selected>Accepté par le remplaçant</option>\n";
if ( $status == 'VAL' ) $selected='selected'; else $selected='';
echo  "<option value='VAL' $selected>Approuvé</option>\n";
if ( $status == 'REJ' ) $selected='selected'; else $selected='';
echo  "<option value='REJ' $selected>Rejeté</option>\n";
if ( $status == 'ATT' ) $selected='selected'; else $selected='';
echo  "<option value='ATT' $selected>A approuver</option>\n";
echo "</select>";

// Choix Dates
echo "<div class='dropdown-right' style='float:right;'>Du
        <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker datepicker2' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtdb)'>
     au
        <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker datepicker2' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtfn)'>
    <button class='btn btn-secondary search-wen' value='go' onclick=\"changeParam('".$filter."');\"><i class='fas fa-search'></i></button>
    </div></div></form>";
      
echo table_remplacements($evenement=0, $status, $dtdb, $dtfn, $replaced, $substitute, $filter );
echo "<p></div>";
echo writefoot();
?>